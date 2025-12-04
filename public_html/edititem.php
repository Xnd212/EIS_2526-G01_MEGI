<?php
session_start();
require "db.php";

// =============================================
// 0. VALIDAR LOGIN
// =============================================
if (!isset($_SESSION['user_id'])) {
    die("Erro: Tem de iniciar sessão para editar itens.");
}
$userId = (int)$_SESSION['user_id'];

// =============================================
// 1. VALIDAR ITEM ID
// =============================================
if (!isset($_GET['id'])) {
    die("Erro: Nenhum item especificado.");
}
$itemId = intval($_GET['id']);

// =============================================
// 2. BUSCAR DADOS DO ITEM + TYPE
// =============================================
$stmt = $pdo->prepare("
    SELECT i.*, t.name AS type_name
    FROM item i
    LEFT JOIN type t ON i.type_id = t.type_id
    WHERE i.item_id = ?
");
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Erro: Item não encontrado.");
}

// =============================================
// 3. BUSCAR COLEÇÕES DO UTILIZADOR
// =============================================
$stmtCols = $pdo->prepare("
    SELECT collection_id, name
    FROM collection
    WHERE user_id = ?
    ORDER BY name
");
$stmtCols->execute([$userId]);
$collections = $stmtCols->fetchAll(PDO::FETCH_ASSOC);

// =============================================
// 4. BUSCAR COLEÇÕES A QUE O ITEM PERTENCE
// =============================================
$stmtItemCols = $pdo->prepare("
    SELECT collection_id
    FROM contains
    WHERE item_id = ?
");
$stmtItemCols->execute([$itemId]);
$itemCollectionIds = array_column($stmtItemCols->fetchAll(PDO::FETCH_ASSOC), 'collection_id');

$message = "";
$redirectAfterSuccess = false;

// =============================================
// 5. PROCESSAR UPDATE (POST)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Campos principais
    $name        = trim($_POST['itemName'] ?? '');
    $price       = isset($_POST['itemPrice']) ? (float)$_POST['itemPrice'] : 0.0;
    $typeName    = trim($_POST['itemType'] ?? '');
    $importance  = isset($_POST['itemImportance']) ? (int)$_POST['itemImportance'] : 0;
    $accDate     = $_POST['acquisitionDate'] ?? '';
    $accPlace    = trim($_POST['acquisitionPlace'] ?? '');
    $description = trim($_POST['itemDescription'] ?? '');
    $selectedCollections = isset($_POST['collections']) ? $_POST['collections'] : [];

    // Validações simples
    if ($name === '' || $typeName === '' || empty($selectedCollections) || $importance < 1 || $importance > 10) {
        $message = "⚠ Preencha todos os campos obrigatórios e selecione pelo menos uma coleção.";
    } else {

        try {
            $pdo->beginTransaction();

            // ---------- A) TYPE (encontrar ou criar) ----------
            $stmtType = $pdo->prepare("SELECT type_id FROM type WHERE name = ? LIMIT 1");
            $stmtType->execute([$typeName]);
            $typeRow = $stmtType->fetch(PDO::FETCH_ASSOC);

            if ($typeRow) {
                $typeId = (int)$typeRow['type_id'];
            } else {
                $stmtInsType = $pdo->prepare("INSERT INTO type (name) VALUES (?)");
                $stmtInsType->execute([$typeName]);
                $typeId = (int)$pdo->lastInsertId();
            }

            // ---------- B) IMAGEM (se nova imagem enviada) ----------
            $imageIdToUse = $item['image_id']; // default: manter atual (pode ser null)

            if (!empty($_FILES['itemImage']['name'])) {
                $file = $_FILES['itemImage'];
                $uploadDir = "images/";
                $safeName  = time() . "_" . basename($file['name']);
                $targetPath = $uploadDir . $safeName;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $stmtImg = $pdo->prepare("INSERT INTO image (url) VALUES (?)");
                    $stmtImg->execute([$targetPath]);
                    $imageIdToUse = (int)$pdo->lastInsertId();
                } else {
                    // não abortamos tudo, mas avisamos
                    $message = "⚠ Erro ao carregar a imagem. Restantes alterações guardadas.";
                }
            }

            // ---------- C) UPDATE DO ITEM ----------
            $stmtUpdate = $pdo->prepare("
                UPDATE item
                   SET type_id     = ?,
                       image_id    = ?,
                       name        = ?,
                       price       = ?,
                       importance  = ?,
                       acc_date    = ?,
                       acc_place   = ?,
                       description = ?
                 WHERE item_id    = ?
            ");
            // se $imageIdToUse for null, deixamos passar como null
            $stmtUpdate->execute([
                $typeId,
                $imageIdToUse ?: null,
                $name,
                $price,
                $importance,
                $accDate ?: null,
                $accPlace ?: null,
                $description ?: null,
                $itemId
            ]);

            // ---------- D) UPDATE DA TABELA CONTAINS ----------
            // Apagar associações antigas
            $stmtDel = $pdo->prepare("DELETE FROM contains WHERE item_id = ?");
            $stmtDel->execute([$itemId]);

            // Inserir novas associações
            $stmtInsContains = $pdo->prepare("
                INSERT INTO contains (collection_id, item_id)
                VALUES (?, ?)
            ");
            foreach ($selectedCollections as $cid) {
                $cid = (int)$cid;
                if ($cid > 0) {
                    $stmtInsContains->execute([$cid, $itemId]);
                }
            }

            $pdo->commit();

            // Mensagem de sucesso
            if ($message === "") {
                $message = "✓ Item atualizado com sucesso! A redirecionar...";
            }
            $redirectAfterSuccess = true;

            // Atualizar dados em memória
            $stmt = $pdo->prepare("
                SELECT i.*, t.name AS type_name
                FROM item i
                LEFT JOIN type t ON i.type_id = t.type_id
                WHERE i.item_id = ?
            ");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            $itemCollectionIds = array_map('intval', $selectedCollections);

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Erro ao atualizar o item: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Edit Item</title>
  <link rel="stylesheet" href="edititem.css" />
</head>
<body>

  <!-- HEADER (igual às outras páginas) -->
  <header>
    <a href="homepage.php" class="logo">
      <img src="images/TrallE_2.png" alt="logo" />
    </a>
    <div class="search-bar">
      <input type="text" placeholder="Search" />
    </div>
    <div class="icons">
      <!-- Notificações -->
      <button class="icon-btn" aria-label="Notificações" id="notification-btn">🔔</button>
      <div class="notification-popup" id="notification-popup">
        <div class="popup-header">
          <h3>Notifications <span>🔔</span></h3>
        </div>
        <ul class="notification-list">
          <li><strong>Ana_Rita_Lopes</strong> added 3 new items to the Pokémon Cards collection.</li>
          <li><strong>Tomás_Freitas</strong> created a new collection: Vintage Stamps.</li>
          <li><strong>David_Ramos</strong> updated his Funko Pop inventory.</li>
          <li><strong>Telmo_Matos</strong> joined the event: Iberanime Porto 2025.</li>
          <li><strong>Marco_Pereira</strong> started following your Panini Stickers collection.</li>
          <li><strong>Ana_Rita_Lopes</strong> added 1 new items to the Pokémon Champion’s Path collection.</li>
          <li><strong>Telmo_Matos</strong> added 3 new items to the Premier League Stickers collection.</li>
          <li><strong>Marco_Pereira</strong> created a new event: Card Madness Meetup.</li>
        </ul>
        <a href="#" class="see-more-link">+ See more</a>
      </div>

      <!-- Perfil -->
      <a href="userpage.php" class="icon-btn" aria-label="Perfil">👤</a>

      <!-- Logout -->
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

        <!-- Âncora para poder fazer scroll para a mensagem -->
        <a id="stay-here"></a>

        <form id="itemForm" method="POST" action="" enctype="multipart/form-data" novalidate>

          <!-- COLEÇÕES (multi-select) -->
          <div class="form-group">
            <label>Collection <span class="required">*</span></label>
            <div class="custom-multiselect">
              <button type="button" id="dropdownBtn">Select Collections ⮟</button>
              <div class="dropdown-content" id="dropdownContent">
                <?php if (!empty($collections)): ?>
                    <?php foreach ($collections as $col): 
                        $cid = (int)$col['collection_id'];
                        $isChecked = in_array($cid, $itemCollectionIds);
                    ?>
                        <label>
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

          <!-- TYPE -->
          <div class="form-group">
            <label for="itemType">Item Type <span class="required">*</span></label>
            <input
                type="text"
                id="itemType"
                name="itemType"
                value="<?= htmlspecialchars($item['type_name'] ?? '') ?>"
                required
            />
          </div>

          <!-- IMPORTANCE (1–10) -->
          <div class="form-group">
            <label for="itemImportance">Importance (1–10) <span class="required">*</span></label>
            <input
                type="number"
                id="itemImportance"
                name="itemImportance"
                min="1"
                max="10"
                value="<?= (int)$item['importance'] ?>"
                required
            />
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
            <button type="submit" class="btn-primary">
              Save Changes
            </button>
          </div>

          <!-- MENSAGEM -->
          <?php if ($message): ?>
            <p id="form-message" class="form-message <?= $redirectAfterSuccess ? 'success' : 'error' ?>">
              <?= htmlspecialchars($message) ?>
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
        <h3>My friends</h3>
        <p><a href="userfriendspage.php"> View Friends</a></p>
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

  <!-- Variáveis JS globais para redirect -->
  <script>
    window.redirectAfterSuccess = <?= $redirectAfterSuccess ? 'true' : 'false' ?>;
    window.itemId = <?= json_encode($itemId) ?>;
  </script>

  <script src="edititem.js"></script>
  <script src="logout.js"></script>
</body>
</html>
