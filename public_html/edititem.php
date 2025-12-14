<?php
session_start();
require_once __DIR__ . "/db.php";

// =============================================
// 0. VALIDAR LOGIN
// =============================================
if (!isset($_SESSION['user_id'])) {
    die("Error: You need to be logged in to edit items.");
}
$userId = (int)$_SESSION['user_id'];

// =============================================
// 1. VALIDAR ITEM ID
// =============================================
if (!isset($_GET['id'])) {
    die("Error: No item specified.");
}
$itemId = (int)$_GET['id'];

// =============================================
// 2. BUSCAR ITEM + TYPE + IMAGE
// =============================================
$sql = "
    SELECT i.*, t.name AS type_name, img.url AS item_image
    FROM item i
    LEFT JOIN type t ON i.type_id = t.type_id
    LEFT JOIN image img ON i.image_id = img.image_id
    WHERE i.item_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $itemId);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    die("Error: Item not found.");
}

// =============================================
// 3. BUSCAR COLEÇÕES DO UTILIZADOR
// =============================================
$sql = "SELECT collection_id, name FROM collection WHERE user_id = ? ORDER BY name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$collections = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// =============================================
// 4. BUSCAR COLEÇÕES ATUAIS DO ITEM
// =============================================
$sql = "SELECT collection_id FROM contains WHERE item_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $itemId);
$stmt->execute();
$res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$itemCollectionIds = array_map('intval', array_column($res, "collection_id"));
$stmt->close();

// =============================================
// 4.1 BUSCAR TYPES (para dropdown)
// =============================================
$allTypes = [];
$sqlTypes = "SELECT type_id, name FROM type ORDER BY name ASC";
$stmt = $conn->prepare($sqlTypes);
$stmt->execute();
$allTypes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$message = "";
$redirectAfterSuccess = false;

// =====================================================
// 4.2 HELPERS PARA REPOR VALORES NO FORM (SEM WARNINGS)
// =====================================================
$post_itemType = $item['type_name'] ?? "";
$selectedCollectionsForForm = $itemCollectionIds;

// Se houve POST e deu erro, repor o que o user escolheu
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["itemType"])) {
        $post_itemType = trim($_POST["itemType"]);
    }
    if (isset($_POST["collections"]) && is_array($_POST["collections"])) {
        $selectedCollectionsForForm = array_map('intval', $_POST["collections"]);
    }
}

// =============================================
// 5. PROCESSAR UPDATE (POST)
// =============================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Campos principais
    $name   = trim($_POST["itemName"] ?? "");
    $price  = (float)($_POST["itemPrice"] ?? 0);
    $typeName = trim($_POST["itemType"] ?? "");
    $importance = (int)($_POST["itemImportance"] ?? 0);
    $accDate = $_POST["acquisitionDate"] ?: null;
    
    $today = date("Y-m-d");

    if ($accDate && $accDate > $today) {
        $message = "⚠ Acquisition date cannot be in the future.";
    }

    $accPlace = trim($_POST["acquisitionPlace"] ?? "");
    $description = trim($_POST["itemDescription"] ?? "");
    $selectedCollections = $_POST["collections"] ?? [];
    
    // ************************************
    // 5.1 VALIDAÇÕES INICIAIS
    // ************************************
    if ($name === "" || $typeName === "" || empty($selectedCollections)) {
        $message = "⚠ Fill in all the required fields and select at least one collection.";
    }

    // ==============================
    //  VALIDAR ITEM DUPLICADO
    // ==============================
    if ($message === "" && !empty($selectedCollections)) {

        $placeholders = implode(',', array_fill(0, count($selectedCollections), '?'));

        $sqlDup = "
            SELECT 1
            FROM item i
            JOIN contains con ON i.item_id = con.item_id
            WHERE LOWER(TRIM(i.name)) = LOWER(TRIM(?))
              AND con.collection_id IN ($placeholders)
              AND i.item_id != ?
            LIMIT 1
        ";

        $stmtDup = $conn->prepare($sqlDup);

        $types = "s" . str_repeat("i", count($selectedCollections)) . "i";

        $bind = [];
        $bind[] = &$types;
        $bind[] = &$name;

        $collectionVars = [];
        foreach ($selectedCollections as $k => $cid) {
            $collectionVars[$k] = (int) $cid;
            $bind[] = &$collectionVars[$k];
        }

        $bind[] = &$itemId;

        call_user_func_array([$stmtDup, 'bind_param'], $bind);

        $stmtDup->execute();
        $dupExists = $stmtDup->get_result()->fetch_assoc();
        $stmtDup->close();

        if ($dupExists) {
            $message = "⚠ An item with that name already exists in one of the selected collections.";
        }
    }

    // ************************************
    // 5.3 SE NÃO HOUVER ERROS → ATUALIZAR ITEM
    // ************************************
    if ($message === "") {

        $conn->begin_transaction();

        try {
            // ---------- TYPE ----------
            $sql = "SELECT type_id FROM type WHERE name = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $typeName);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($result) {
                $typeId = (int)$result["type_id"];
            } else {
                $sql = "INSERT INTO type (name) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $typeName);
                $stmt->execute();
                $typeId = (int)$stmt->insert_id;
                $stmt->close();
            }

            // ---------- IMAGEM ----------
            $imageIdToUse = (int)$item["image_id"];

            if (!empty($_FILES["itemImage"]["name"])) {
                $file = $_FILES["itemImage"];
                $safeName = time() . "_" . basename($file["name"]);
                $path = "images/" . $safeName;

                if (move_uploaded_file($file["tmp_name"], $path)) {
                    $sql = "INSERT INTO image (url) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $path);
                    $stmt->execute();
                    $imageIdToUse = (int)$stmt->insert_id;
                    $stmt->close();
                }
            }

            // ---------- UPDATE ITEM ----------
            $sql = "
                UPDATE item
                SET type_id = ?, image_id = ?, name = ?, price = ?, importance = ?, acc_date = ?, acc_place = ?, description = ?
                WHERE item_id = ?
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "iisdisssi",
                $typeId,
                $imageIdToUse,
                $name,
                $price,
                $importance,
                $accDate,
                $accPlace,
                $description,
                $itemId
            );
            $stmt->execute();
            $stmt->close();

            // ---------- UPDATE COLLECTIONS ----------
            $sql = "DELETE FROM contains WHERE item_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
            $stmt->close();

            $sql = "INSERT INTO contains (collection_id, item_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);

            foreach ($selectedCollections as $cid) {
                $cid = (int)$cid;
                $stmt->bind_param("ii", $cid, $itemId);
                $stmt->execute();
            }

            $stmt->close();
            $conn->commit();

            // Recarregar info atualizada
            $sql = "
                SELECT i.*, t.name AS type_name, img.url AS item_image
                FROM item i
                LEFT JOIN type t ON i.type_id = t.type_id
                LEFT JOIN image img ON i.image_id = img.image_id
                WHERE i.item_id = ?
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
            $item = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Atualizar repovoamento após sucesso
            $post_itemType = $item['type_name'] ?? "";
            $selectedCollectionsForForm = $itemCollectionIds;

            $message = "✓ Changes saved with success! <span class='redirect-msg'>Redirecting...</span>";
            $redirectAfterSuccess = true;

        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error updating item: " . $e->getMessage();
        }
    }
}

// helper para o texto do botão (coleções selecionadas)
function buildSelectedCollectionsLabel($collections, $selectedIds) {
    $names = [];
    foreach ($collections as $c) {
        if (in_array((int)$c['collection_id'], $selectedIds, true)) {
            $names[] = $c['name'];
        }
    }
    return !empty($names) ? implode(", ", $names) : "Select Collections ⮟";
}

$collectionsBtnLabel = buildSelectedCollectionsLabel($collections, $selectedCollectionsForForm);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Edit Item</title>
  <link rel="stylesheet" href="edititem.css" />
  <link rel="stylesheet" href="calendar_popup.css" />
</head>
<body>

  <!-- HEADER (igual às outras páginas) -->
  <header>
    <a href="homepage.php" class="logo">
      <img src="images/TrallE_2.png" alt="logo" />
    </a>
      <div class="search-bar">
          <form action="search.php" method="GET">
              <input type="text" name="q" placeholder="Search for friends, collections, events, items..." required>
          </form>
      </div>
    <div class="icons">
      <?php include __DIR__ . '/calendar_popup.php'; ?>
      <?php include __DIR__ . '/notifications_popup.php'; ?>

      <a href="userpage.php" class="icon-btn" aria-label="Perfil">👤</a>

      <button class="icon-btn" id="logout-btn" aria-label="Logout">🚪</button>
      <div class="notification-popup logout-popup" id="logout-popup">
        <div class="popup-header">
          <h3>Logout</h3>
        </div>
        <p>Are you sure you want to log out?</p>
        <div class="logout-btn-wrapper">
          <button type="button" class="logout-btn cancel-btn" id="cancel-logout">Cancel</button>
          <button type="button" class="logout-btn confirm-btn" id="confirm-logout">Log out</button>
        </div>
      </div>
    </div>
  </header>

  <div class="main">
    <div class="content">
      <section class="item-creation-section">
        <h2 class="page-title">Edit Item</h2>

        <a id="stay-here"></a>

        <form id="itemForm" method="POST" action="" enctype="multipart/form-data" novalidate>

          <!-- COLEÇÕES (multi-select com search) -->
          <div class="form-group">
            <label>Collections <span class="required">*</span></label>

            <div class="custom-multiselect">
              <button type="button" id="collectionDropdownBtn">
                <?= htmlspecialchars($collectionsBtnLabel) ?>
              </button>

              <div class="dropdown-content" id="collectionDropdown">

                <input
                  type="text"
                  id="collectionSearchInput"
                  class="tag-search-input"
                  placeholder="Search collections..."
                  autocomplete="off"
                >

                <?php if (!empty($collections)): ?>
                  <?php foreach ($collections as $col): 
                      $cid = (int)$col['collection_id'];
                      $isChecked = in_array($cid, $selectedCollectionsForForm, true);
                  ?>
                    <label data-collection-name="<?php echo strtolower(htmlspecialchars($col['name'])); ?>">
                      <input
                        type="checkbox"
                        name="collections[]"
                        value="<?= $cid ?>"
                        <?= $isChecked ? 'checked' : '' ?>
                      >
                      <?= htmlspecialchars($col['name']) ?>
                    </label>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p style="padding:0.5rem;">No collections available.</p>
                <?php endif; ?>

              </div>
            </div>
          </div>

          <!-- NAME -->
          <div class="form-group">
            <label for="itemName">Name <span class="required">*</span></label>
            <input
              type="text"
              id="itemName"
              name="itemName"
              value="<?= htmlspecialchars($item['name']) ?>"
              required
            />
          </div>

          <!-- PRICE -->
          <div class="form-group">
            <label for="itemPrice">Price (€) <span class="required">*</span></label>
            <input
              type="number"
              id="itemPrice"
              name="itemPrice"
              step="0.01"
              min="0"
              value="<?= htmlspecialchars($item['price']) ?>"
              required
            />
          </div>

          <!-- TYPE DROPDOWN + BOTÃO CRIAR TYPE -->
          <div class="form-group">
            <label>Item Type <span class="required">*</span></label>

            <div class="type-header">
              <button type="button" id="openTypeModal" class="btn-small">Create type</button>
            </div>

            <div class="custom-multiselect">
              <button type="button" id="typeDropdownBtn">
                <?= $post_itemType !== "" ? htmlspecialchars($post_itemType) : "Select Type ⮟"; ?>
              </button>

              <div class="dropdown-content" id="typeDropdown">

                <input
                  type="text"
                  id="typeSearchInput"
                  class="tag-search-input"
                  placeholder="Search types..."
                  autocomplete="off"
                />

                <?php if (empty($allTypes)): ?>
                  <div style="padding:0.4rem 0.6rem; color:#777;">No types created yet.</div>
                <?php else: ?>
                  <?php foreach ($allTypes as $t):
                        $checked = ($post_itemType !== "" && $post_itemType === $t['name']) ? "checked" : "";
                  ?>
                    <label data-type-name="<?php echo strtolower(htmlspecialchars($t['name'])); ?>">
                      <input
                        type="radio"
                        name="typeRadio"
                        value="<?php echo htmlspecialchars($t['name']); ?>"
                        <?php echo $checked; ?>
                      >
                      <?php echo htmlspecialchars($t['name']); ?>
                    </label>
                  <?php endforeach; ?>
                <?php endif; ?>

              </div>
            </div>

            <input
              type="hidden"
              id="itemType"
              name="itemType"
              value="<?php echo htmlspecialchars($post_itemType); ?>"
            >
          </div>

          <!-- IMPORTANCE -->
          <div class="form-group">
            <label for="itemImportance">Importance (1–10) <span class="required">*</span></label>

            <div class="importance-wrapper">
              <input
                type="range"
                id="importanceSlider"
                min="1"
                max="10"
                step="1"
                value="<?= (int)$item['importance'] ?>"
              >

              <input
                type="number"
                id="itemImportance"
                name="itemImportance"
                min="1"
                max="10"
                step="1"
                value="<?= (int)$item['importance'] ?>"
                class="importance-number"
              >
            </div>
          </div>

          <!-- ACQUISITION DATE -->
          <div class="form-group">
            <label for="acquisitionDate">Acquisition Date (DD-MM-YYYY) <span class="required">*</span></label>
            <input
              type="date"
              id="acquisitionDate"
              name="acquisitionDate"
              value="<?= htmlspecialchars($item['acc_date']) ?>"
              required
            />
          </div>

          <!-- ACQUISITION PLACE -->
          <div class="form-group">
            <label for="acquisitionPlace">Acquisition Place</label>
            <input
              type="text"
              id="acquisitionPlace"
              name="acquisitionPlace"
              value="<?= htmlspecialchars($item['acc_place']) ?>"
            />
          </div>

          <!-- DESCRIPTION -->
          <div class="form-group">
            <label for="itemDescription">Description</label>
            <textarea
              id="itemDescription"
              name="itemDescription"
              rows="4"
            ><?= htmlspecialchars($item['description']) ?></textarea>
          </div>

          <!-- IMAGE -->
          <div class="form-group">
            <label for="itemImage">Item Image (optional)</label>
            <input type="file" id="itemImage" name="itemImage" accept="image/*" />
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">Save Changes</button>
          </div>

          <?php if ($message): ?>
            <a id="msg-anchor"></a>
            <p id="form-message" class="form-message <?= $redirectAfterSuccess ? 'success' : 'error' ?>">
              <?= $message ?>
            </p>
          <?php endif; ?>

        </form>
      </section>
    </div>

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-section collections-section">
        <h3>My collections</h3>
        <p><a href="collectioncreation.php">Create collection</a></p>
        <p><a href="itemcreation.php">Create item</a></p>
        <p><a href="mycollectionspage.php">View collections</a></p>
        <p><a href="myitems.php">View items</a></p>
      </div>

      <div class="sidebar-section friends-section">
        <h3>My bubble</h3>
        <p><a href="userfriendspage.php"> View bubble</a></p>
        <p><a href="allfriendscollectionspage.php">View collections</a></p>
        <p><a href="teampage.php">Team Page</a></p>
      </div>

      <div class="sidebar-section events-section">
        <h3>Events</h3>
        <p><a href="createevent.php">Create event</a></p>
        <p><a href="upcomingevents.php">View upcoming events</a></p>
        <p><a href="eventhistory.php">Event history</a></p>
      </div>
    </aside>
  </div>

  <!-- MODAL PARA TYPE -->
  <div id="typeModalOverlay" class="modal-overlay hidden"></div>

  <div id="typeModal" class="modal hidden">
    <h3>Create new type</h3>

    <input type="text" id="newTypeInput" placeholder="Type name...">

    <p id="typeFeedback" class="type-feedback"></p>

    <div class="modal-buttons">
      <button id="createTypeBtn" class="btn-primary">Create type</button>
      <button id="closeTypeModal" class="btn-secondary">Close</button>
    </div>
  </div>

  <!-- Variáveis JS globais para redirect -->
  <script>
    window.redirectAfterSuccess = <?= $redirectAfterSuccess ? 'true' : 'false' ?>;
    window.itemId = <?= json_encode($itemId) ?>;
  </script>

  <script src="homepage.js"></script>
  <script src="edititem.js"></script>
  <script src="logout.js"></script>

</body>
</html>
