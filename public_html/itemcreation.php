<?php
// ==========================================
// 1. DEBUG & SETUP
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

/* =========================================
   1.1. BLOCK GUESTS & UNLOGGED USERS
========================================= */
$isGuest = isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;

if (!isset($_SESSION['user_id']) || $isGuest) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/db.php";

$user_id = (int) $_SESSION['user_id'];

$message = "";
$messageType = ""; // success | error
$createdItemId = 0;

/* =========================================
   HELPERS PARA REPOVOAR O FORM
========================================= */
$post_collections = isset($_POST['collections']) && is_array($_POST['collections'])
    ? array_map('intval', $_POST['collections'])
    : [];

$post_itemName      = $_POST['itemName']        ?? "";
$post_itemPrice     = $_POST['itemPrice']       ?? "";
$post_itemType      = $_POST['itemType']        ?? "";
$post_importance    = $_POST['itemImportanceNumber'] ?? 5;
$post_acc_date      = $_POST['acc_date']        ?? "";
$post_acc_place     = $_POST['acc_place']       ?? "";
$post_description   = $_POST['itemDescription'] ?? "";

/* ==========================================================
  2. CSV IMPORT HANDLING (FICA IGUAL)
========================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["import_csv"])) {

    $collection_id = isset($_POST['collection_id_csv']) ? (int) $_POST['collection_id_csv'] : 0;

    if ($collection_id === 0) {
        $message = "Please select a valid collection.";
        $messageType = "error";
        goto end_of_form;
    }

    if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== 0) {
        $message = "Please upload a valid CSV file.";
        $messageType = "error";
        goto end_of_form;
    }

    $handle = fopen($_FILES['csvFile']['tmp_name'], "r");
    if ($handle === FALSE) {
        $message = "Error opening CSV file.";
        $messageType = "error";
        goto end_of_form;
    }

    fgetcsv($handle); // skip header

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        $name  = trim($data[0] ?? "");
        $price = trim($data[1] ?? "");
        $type  = trim($data[2] ?? "");

        if ($name === "" || $price === "" || $type === "") {
            continue;
        }

        $price      = (float)$price;
        $importance = isset($data[3]) ? (int)$data[3] : 5;
        $acc_date   = $data[4] ?? date('Y-m-d');
        $acc_place  = trim($data[5] ?? "");
        $description= trim($data[6] ?? "");

        if ($acc_date > date("Y-m-d")) continue;

        // TYPE
        $stmtT = $conn->prepare("SELECT type_id FROM type WHERE name = ? LIMIT 1");
        $stmtT->bind_param("s", $type);
        $stmtT->execute();
        $resT = $stmtT->get_result();

        if ($resT->num_rows > 0) {
            $type_id = (int)$resT->fetch_assoc()['type_id'];
        } else {
            $stmtInsT = $conn->prepare("INSERT INTO type (name) VALUES (?)");
            $stmtInsT->bind_param("s", $type);
            $stmtInsT->execute();
            $type_id = $stmtInsT->insert_id;
            $stmtInsT->close();
        }

        $image_id = 42;

        $stmt = $conn->prepare("
            INSERT INTO item (type_id, image_id, name, price, importance, acc_date, acc_place, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iisdisss",
            $type_id,
            $image_id,
            $name,
            $price,
            $importance,
            $acc_date,
            $acc_place,
            $description
        );

        if ($stmt->execute()) {
            $item_id = $stmt->insert_id;

            $stmtC = $conn->prepare("
                INSERT INTO contains (collection_id, item_id)
                VALUES (?, ?)
            ");
            $stmtC->bind_param("ii", $collection_id, $item_id);
            $stmtC->execute();
        }
    }

    fclose($handle);

    $message = "✔ CSV import finished successfully.";
    $messageType = "success";
    goto end_of_form;
}

/* ==========================================================
  3. MANUAL ITEM CREATION (MULTI COLLECTION)
========================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["import_csv"])) {

    $name       = trim($_POST['itemName'] ?? "");
    $price      = (float) ($_POST['itemPrice'] ?? -1);
    $typeInput  = trim($_POST['itemType'] ?? "");
    $importance = (int) ($_POST['itemImportanceNumber'] ?? 5);
    $acc_date   = trim($_POST['acc_date'] ?? "");
    $acc_place  = trim($_POST['acc_place'] ?? "");
    $description= trim($_POST['itemDescription'] ?? "");
    $selectedCollections = $_POST['collections'] ?? [];

    // VALIDAÇÕES
    if (empty($selectedCollections) || $name === "" || $typeInput === "" || $acc_date === "") {
        $message = "⚠ Please fill in all required fields and select at least one collection.";
        $messageType = "error";
        goto end_of_form;
    }

    if ($price < 0) {
        $message = "⚠ Price cannot be negative.";
        $messageType = "error";
        goto end_of_form;
    }

    if ($acc_date > date("Y-m-d")) {
        $message = "⚠ Acquisition date cannot be in the future.";
        $messageType = "error";
        goto end_of_form;
    }

    // DUPLICADOS (EM QUALQUER COLEÇÃO)
    $placeholders = implode(',', array_fill(0, count($selectedCollections), '?'));

    $sqlDup = "
        SELECT 1
        FROM item i
        JOIN contains c ON i.item_id = c.item_id
        WHERE LOWER(TRIM(i.name)) = LOWER(TRIM(?))
          AND c.collection_id IN ($placeholders)
        LIMIT 1
    ";

    $stmtDup = $conn->prepare($sqlDup);

    $types = "s" . str_repeat("i", count($selectedCollections));
    $bind = [];
    $bind[] = &$types;
    $bind[] = &$name;

    foreach ($selectedCollections as $k => $cid) {
        $selectedCollections[$k] = (int)$cid;
        $bind[] = &$selectedCollections[$k];
    }

    call_user_func_array([$stmtDup, 'bind_param'], $bind);
    $stmtDup->execute();

    if ($stmtDup->get_result()->fetch_assoc()) {
        $message = "⚠ An item with that name already exists in one of the selected collections.";
        $messageType = "error";
        goto end_of_form;
    }
    $stmtDup->close();

    // TYPE
    $stmtT = $conn->prepare("SELECT type_id FROM type WHERE name = ? LIMIT 1");
    $stmtT->bind_param("s", $typeInput);
    $stmtT->execute();
    $resT = $stmtT->get_result();

    if ($resT->num_rows > 0) {
        $type_id = (int)$resT->fetch_assoc()['type_id'];
    } else {
        $stmtInsT = $conn->prepare("INSERT INTO type (name) VALUES (?)");
        $stmtInsT->bind_param("s", $typeInput);
        $stmtInsT->execute();
        $type_id = $stmtInsT->insert_id;
        $stmtInsT->close();
    }

    // IMAGE
    $image_id = 42;

    if (!empty($_FILES["itemImage"]["name"])) {
        $dir = "images/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $file = $dir . time() . "_" . basename($_FILES["itemImage"]["name"]);
        if (move_uploaded_file($_FILES["itemImage"]["tmp_name"], $file)) {
            $stmtImg = $conn->prepare("INSERT INTO image (url) VALUES (?)");
            $stmtImg->bind_param("s", $file);
            $stmtImg->execute();
            $image_id = $stmtImg->insert_id;
        }
    }

    // INSERT ITEM
    $stmt = $conn->prepare("
        INSERT INTO item (type_id, image_id, name, price, importance, acc_date, acc_place, description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iisdisss",
        $type_id,
        $image_id,
        $name,
        $price,
        $importance,
        $acc_date,
        $acc_place,
        $description
    );

    if ($stmt->execute()) {
        $item_id = $stmt->insert_id;

        $stmtC = $conn->prepare("
            INSERT INTO contains (collection_id, item_id)
            VALUES (?, ?)
        ");

        foreach ($selectedCollections as $cid) {
            $cid = (int)$cid;
            $stmtC->bind_param("ii", $cid, $item_id);
            $stmtC->execute();
        }

        $message = "✔ Item created successfully! <span class='redirect-msg'>Redirecting...</span>";
        $messageType = "success";
        $createdItemId = $item_id;
    } else {
        $message = "Database error while creating item.";
        $messageType = "error";
    }
}

/* ==========================================================
   SEMPRE EXECUTA
========================================================== */
end_of_form:

$user_collections = [];
$res = $conn->query("SELECT collection_id, name FROM collection WHERE user_id = $user_id");
while ($row = $res->fetch_assoc()) {
    $user_collections[] = $row;
}

$allTypes = [];
$resTypes = $conn->query("SELECT name FROM type ORDER BY name ASC");
while ($row = $resTypes->fetch_assoc()) {
    $allTypes[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Create Item</title>
  <link rel="stylesheet" href="itemcreation.css" />
  <link rel="stylesheet" href="calendar_popup.css" />
</head>

<body>

<!-- Popup Overlay -->
<div class="popup-overlay" id="csv-overlay"></div>

<!-- CSV Import Popup -->
<div class="csv-import-popup" id="csv-import-popup">
  <div class="popup-header">
    <h3>Bulk Import Items</h3>
    <button class="close-popup" id="close-csv-popup" type="button">✕</button>
  </div>
  
  <div class="popup-content">
    <h4>CSV Format Instructions</h4>
    <p>Your CSV file must have columns in this <strong>exact order</strong>:</p>
    
    <div class="csv-format-box">
      <code>name, price, type, importance, acc_date, acc_place, description</code>
    </div>
    
    <h4>Example CSV:</h4>
    <div class="csv-example">
      <pre>name,price,type,importance,acc_date,acc_place,description
Pikachu Card,25.50,Card,8,2024-01-15,Local Shop,First edition holographic
Charizard Figure,45.00,Figure,10,2024-02-20,Online Store,Rare collectible item
Bulbasaur Plush,15.99,Plush,6,2024-03-10,Convention,Soft and cuddly</pre>
    </div>
    
    <h4>Field Details:</h4>
    <ul>
      <li><strong>name</strong> <span class="required">*</span> - Item name (required)</li>
      <li><strong>price</strong> <span class="required">*</span> - Price in euros, use numbers only (e.g., 25.50)</li>
      <li><strong>type</strong> <span class="required">*</span> - Item type (e.g., Card, Figure, Plush)</li>
      <li><strong>importance</strong> - Number from 1 to 10 (defaults to 5 if empty)</li>
      <li><strong>acc_date</strong> - Acquisition date in format YYYY-MM-DD (defaults to today if empty)</li>
      <li><strong>acc_place</strong> - Where the item was acquired (optional)</li>
      <li><strong>description</strong> - Item description (optional)</li>
    </ul>
    
    <p><strong>Important Notes:</strong></p>
    <ul>
      <li>The first row should be the header (it will be skipped)</li>
      <li>Required fields must not be empty</li>
      <li>If a type doesn't exist, it will be created automatically</li>
      <li>All items will be added to the selected collection</li>
    </ul>
    
    <form method="POST" action="" enctype="multipart/form-data">
      <div class="form-group">
        <label for="collection_id_csv">Target Collection <span class="required">*</span></label>
        <select id="collection_id_csv" name="collection_id_csv" required>
          <option value="">-- Select a Collection --</option>
          <?php 
          if (!empty($user_collections)) {
              foreach ($user_collections as $col) {
                  echo '<option value="' . (int)$col['collection_id'] . '">' . htmlspecialchars($col['name']) . '</option>';
              }
          }
          ?>
        </select>
      </div>
      
      <div class="form-group">
        <label for="csvFile">Upload CSV File <span class="required">*</span></label>
        <input type="file" id="csvFile" name="csvFile" accept=".csv" required />
      </div>
      
      <div class="form-actions">
        <button type="submit" name="import_csv" class="btn-primary">Import Items</button>
      </div>
    </form>
  </div>
</div>

  <header>
    <a href="homepage.php" class="logo">
      <img src="images/TrallE_2.png" alt="logo" />
    </a>
      <div class="search-bar">
          <form action="search.php" method="GET">
              <input type="search" name="q" placeholder="Search for friends, collections, events, items..." required>
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
        <h2 class="page-title">Create New Item</h2>

        <form id="itemForm" method="POST" action="" enctype="multipart/form-data">

            <div class="form-group">
                <label>Collections <span class="required">*</span></label>

                <div class="custom-multiselect">
                    <button type="button" id="collectionDropdownBtn">
                        <?php
                        if (!empty($post_collections)) {
                            $names = [];
                            foreach ($user_collections as $c) {
                                if (in_array((int) $c['collection_id'], $post_collections, true)) {
                                    $names[] = $c['name'];
                                }
                            }
                            echo htmlspecialchars(implode(', ', $names));
                        } else {
                            echo "Select Collections ⮟";
                        }
                        ?>
                    </button>

                    <div class="dropdown-content" id="collectionDropdown">

                        <input
                            type="text"
                            id="collectionSearchInput"
                            class="tag-search-input"
                            placeholder="Search collections..."
                            autocomplete="off"
                            >

                        <?php foreach ($user_collections as $col): ?>
                            <?php $cid = (int) $col['collection_id']; ?>
                            <label data-collection-name="<?php echo strtolower(htmlspecialchars($col['name'])); ?>">
                                <input
                                    type="checkbox"
                                    name="collections[]"
                                    value="<?php echo $cid; ?>"
                                    <?php echo in_array($cid, $post_collections, true) ? 'checked' : ''; ?>
                                    >
                                    <?php echo htmlspecialchars($col['name']); ?>
                            </label>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>


          <div class="form-group">
            <label for="itemName">Name <span class="required">*</span></label>
            <input type="text" id="itemName" name="itemName" placeholder="Enter item name"
                   value="<?php echo htmlspecialchars($post_itemName); ?>" required />
          </div>

          <div class="form-group">
            <label for="itemPrice">Price (€) <span class="required">*</span></label>
            <input type="number" id="itemPrice" name="itemPrice" placeholder="Enter price"
                   step="0.01" min="0"
                   value="<?php echo htmlspecialchars($post_itemPrice); ?>" required />
          </div>

          <!-- TYPE DROPDOWN + BOTÃO CRIAR TYPE -->
          <div class="form-group">
            <label>Item Type <span class="required">*</span></label>

            <div class="type-header">
              <button type="button" id="openTypeModal" class="btn-small">Create type</button>
            </div>

            <div class="custom-multiselect">
              <button type="button" id="typeDropdownBtn">
                <?php echo $post_itemType !== "" ? htmlspecialchars($post_itemType) : "Select Type ⮟"; ?>
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
                      <input type="radio" name="typeRadio"
                             value="<?php echo htmlspecialchars($t['name']); ?>"
                             <?php echo $checked; ?>>
                      <?php echo htmlspecialchars($t['name']); ?>
                    </label>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

            <!-- hidden que vai com o form para o PHP -->
            <input type="hidden" id="itemType" name="itemType"
                   value="<?php echo htmlspecialchars($post_itemType); ?>">
          </div>

          <div class="form-group">
              <label for="itemImportanceSlider">Importance (1–10) <span class="required">*</span></label>
              <div class="slider-wrapper">
                  <input type="range" id="itemImportanceSlider" min="1" max="10" step="1"
                         value="<?php echo (int)$post_importance; ?>" />
                  <input type="number" id="itemImportanceNumber" name="itemImportanceNumber" min="1" max="10"
                         value="<?php echo (int)$post_importance; ?>" />
              </div>
          </div>

          <div class="form-group">
            <label for="acc_date">Acquisition Date (DD-MM-YYYY) <span class="required">*</span></label>
            <input type="date" id="acc_date" name="acc_date"
                   value="<?php echo htmlspecialchars($post_acc_date); ?>" required />
          </div>

          <div class="form-group">
            <label for="acc_place">Acquisition Place</label>
            <input type="text" id="acc_place" name="acc_place"
                   value="<?php echo htmlspecialchars($post_acc_place); ?>"
                   placeholder="Enter where item was acquired" />
          </div>

          <div class="form-group">
            <label for="itemDescription">Description</label>
            <textarea id="itemDescription" name="itemDescription" rows="4" placeholder="Add details about your item"><?php
                echo htmlspecialchars($post_description);
            ?></textarea>
          </div>

          <div class="form-group">
            <label for="itemImage">Item Image (optional)</label>
            <input type="file" id="itemImage" name="itemImage" accept="image/*" />
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">Create Item</button>
            <button type="button" id="bulk-import-btn" class="btn-secondary">Bulk Creation (CSV)</button>
          </div>          
          
        </form>
        
        <?php if ($message): ?>
            <p id="formMessage" class="form-message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

      </section>
    </div>

    <aside class="sidebar">
      <div class="sidebar-section collections-section">
        <h3>My collections</h3>
        <p><a href="collectioncreation.php">Create collection</a></p>
        <p><a href="itemcreation.php"> Create Item</a></p>
        <p><a href="mycollectionspage.php">View collections</a></p>
        <p><a href="myitems.php">View items</a></p>
      </div>
      <div class="sidebar-section friends-section">
        <h3>My bubble</h3>
        <p><a href="userfriendspage.php"> View bubble</a></p>
        <p><a href="allfriendscollectionspage.php">View collections</a></p>
        <p><a href="teampage.php"> Team Page</a></p>
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


<?php if (!empty($createdItemId)): ?>
<script>
    window.NEW_ITEM_ID = <?php echo (int)$createdItemId; ?>;
</script>
<?php endif; ?>
  
  <script src="homepage.js"></script>
  <script src="itemcreation.js"></script>
  <script src="logout.js"></script>

</body>
</html>