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

/* ==========================================================
   2. CSV IMPORT HANDLING
========================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["import_csv"])) {

    $collection_id = isset($_POST['collection_id_csv']) ? (int)$_POST['collection_id_csv'] : 0;

    if ($collection_id === 0) {
        $message = "Please select a valid collection.";
        $messageType = "error";
    }
    elseif (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== 0) {
        $message = "Please upload a valid CSV file.";
        $messageType = "error";
    }
    else {
        $handle = fopen($_FILES['csvFile']['tmp_name'], "r");

        if ($handle === FALSE) {
            $message = "Error opening CSV file.";
            $messageType = "error";
        } else {
            $imported = 0;
            $failed = 0;
            $errors = [];
            $row_number = 0;

            // Skip header
            fgetcsv($handle);

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row_number++;

                if (count($data) < 3) {
                    $failed++;
                    $errors[] = "Row $row_number: Not enough columns.";
                    continue;
                }

                $name  = trim($data[0]);
                $price = trim($data[1]);
                $type  = trim($data[2]);

                if ($name === "" || $price === "" || $type === "") {
                    $failed++;
                    $errors[] = "Row $row_number: Missing required fields.";
                    continue;
                }

                $price = (float)$price;
                $importance = isset($data[3]) && $data[3] !== "" ? (int)$data[3] : 5;
                $acc_date = isset($data[4]) && $data[4] !== "" ? $data[4] : date('Y-m-d');
                $acc_place = isset($data[5]) ? $conn->real_escape_string($data[5]) : "";
                $description = isset($data[6]) ? $conn->real_escape_string($data[6]) : "";

                // Validate date
                if ($acc_date > date("Y-m-d")) {
                    $failed++;
                    $errors[] = "Row $row_number: Acquisition date cannot be in the future.";
                    continue;
                }

                // Handle Type
                $typeSafe = $conn->real_escape_string($type);
                $sql = "SELECT type_id FROM type WHERE name = '$typeSafe' LIMIT 1";
                $res = $conn->query($sql);

                if ($res && $res->num_rows > 0) {
                    $type_id = (int)$res->fetch_assoc()['type_id'];
                } else {
                    $conn->query("INSERT INTO type (name) VALUES ('$typeSafe')");
                    $type_id = $conn->insert_id;
                }

                // Insert item
                $sql_item = "INSERT INTO item (type_id,name,price,importance,acc_date,acc_place,description)
                             VALUES ($type_id,'$name',$price,$importance,'$acc_date','$acc_place','$description')";

                if ($conn->query($sql_item)) {
                    $item_id = $conn->insert_id;

                    $sql_link = "INSERT INTO contains (collection_id,item_id)
                                 VALUES ($collection_id,$item_id)";

                    if ($conn->query($sql_link)) {
                        $imported++;
                    } else {
                        $failed++;
                        $errors[] = "Row $row_number: Failed to link item to collection.";
                    }
                } else {
                    $failed++;
                    $errors[] = "Row $row_number: Failed to insert item.";
                }
            }

            fclose($handle);

            $message = "Import finished! $imported items imported.";
            if ($failed > 0) {
                $message .= " $failed errors occurred.<br>";
                $message .= implode("<br>", array_slice($errors, 0, 5));
                $messageType = "error";
            } else {
                $messageType = "success";
            }
        }
    }
}

/* ==========================================================
   3. MANUAL ITEM CREATION (NORMAL FORM)
========================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["import_csv"])) {

    $name        = trim($_POST['itemName'] ?? "");
    $price       = isset($_POST['itemPrice']) ? (float)$_POST['itemPrice'] : -1;
    $typeInput   = trim($_POST['itemType'] ?? "");
    $importance  = (int)($_POST['itemImportanceNumber'] ?? 5);
    $acc_date    = $_POST['acc_date'] ?? "";
    $acc_place   = $conn->real_escape_string($_POST['acc_place'] ?? "");
    $description = $conn->real_escape_string($_POST['itemDescription'] ?? "");
    $collection_id = (int)($_POST['collection_id'] ?? 0);

    // =============================
    // PHP VALIDATION
    // =============================
    if ($collection_id === 0 || $name === "" || $typeInput === "" || $acc_date === "") {
        $message = "⚠️ Please fill in all required (*) fields.";
        $messageType = "error";
    }
    elseif ($price < 0) {
        $message = "⚠️ Price cannot be negative.";
        $messageType = "error";
    }
    elseif ($acc_date > date("Y-m-d")) {
        $message = "⚠️ Acquisition date cannot be in the future.";
        $messageType = "error";
    }
    else {
        // VALID — proceed

        // 1. Handle Type (create if needed)
        $typeSafe = $conn->real_escape_string($typeInput);
        $sql = "SELECT type_id FROM type WHERE name = '$typeSafe' LIMIT 1";
        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0) {
            $type_id = (int)$res->fetch_assoc()['type_id'];
        } else {
            $conn->query("INSERT INTO type (name) VALUES ('$typeSafe')");
            $type_id = $conn->insert_id;
        }

        // 2. Handle Image (optional)
        $image_id = 42; // default placeholder

        if (isset($_FILES["itemImage"]) && $_FILES["itemImage"]["error"] === 0) {
            $dir = "images/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $file = $dir . basename($_FILES["itemImage"]["name"]);
            if (move_uploaded_file($_FILES["itemImage"]["tmp_name"], $file)) {
                $url = $conn->real_escape_string($file);
                $conn->query("INSERT INTO image (url) VALUES ('$url')");
                $image_id = $conn->insert_id;
            }
        }

        // 3. Insert item
        $sql_item = "
            INSERT INTO item (type_id,image_id,name,price,importance,acc_date,acc_place,description)
            VALUES ($type_id,$image_id,'$name',$price,$importance,'$acc_date','$acc_place','$description')
        ";

        if ($conn->query($sql_item)) {
            $item_id = $conn->insert_id;

            // Link item to collection
            $sql_link = "INSERT INTO contains (collection_id,item_id)
                         VALUES ($collection_id,$item_id)";

            if ($conn->query($sql_link)) {
                $message = "✅ Item created successfully!";
                $messageType = "success";
            } else {
                $message = "Item created but failed to link to collection.";
                $messageType = "error";
            }
        } else {
            $message = "Error inserting item: " . $conn->error;
            $messageType = "error";
        }
    }
}

/* ==========================================================
   4. FETCH COLLECTIONS
========================================================== */
$user_collections = [];
$sql = "SELECT collection_id, name FROM collection WHERE user_id = $user_id";
$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $user_collections[] = $row;
    }
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
  <style>
      /* Simple feedback styles */
      .form-message { text-align: center; font-weight: bold; padding: 10px; margin-top: 15px; border-radius: 5px; }
      .form-message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
      .form-message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
      
      /* Style the standard select box to look decent */
      select#collection_id {
          width: 100%;
          padding: 10px;
          border: 1px solid #ccc;
          border-radius: 5px;
          background-color: white;
          font-size: 14px;
      }
  </style>
</head>


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
                  echo '<option value="' . $col['collection_id'] . '">' . htmlspecialchars($col['name']) . '</option>';
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

<body>

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

        <form id="itemForm" method="POST" action="" enctype="multipart/form-data" novalidate>

          <div class="form-group">
            <label for="collection_id">Collection <span class="required">*</span></label>
            <select id="collection_id" name="collection_id" required>
                <option value="">-- Select a Collection --</option>
                <?php 
                if (!empty($user_collections)) {
                    foreach ($user_collections as $col) {
                        echo '<option value="' . $col['collection_id'] . '">' . htmlspecialchars($col['name']) . '</option>';
                    }
                }
                ?>
            </select>
          </div>

          <div class="form-group">
            <label for="itemName">Name <span class="required">*</span></label>
            <input type="text" id="itemName" name="itemName" placeholder="Enter item name" required />
          </div>

          <div class="form-group">
            <label for="itemPrice">Price (€) <span class="required">*</span></label>
            <input type="number" id="itemPrice" name="itemPrice" placeholder="Enter price" step="0.01" min="0" required />
          </div>

          <div class="form-group">
            <label for="itemType">Item Type <span class="required">*</span></label>
            <input type="text" id="itemType" name="itemType" placeholder="Enter item type (e.g., Card, Figure)" required />
          </div>

          <div class="form-group">
              <label for="itemImportanceSlider">Importance (1–10) <span class="required">*</span></label>
              <div class="slider-wrapper">
                  <input type="range" id="itemImportanceSlider" min="1" max="10" step="1" value="5" />
                  <input type="number" id="itemImportanceNumber" name="itemImportanceNumber" min="1" max="10" value="5" />
              </div>
          </div>

          <div class="form-group">
            <label for="acc_date">Acquisition Date (DD-MM-YYYY) <span class="required">*</span></label>
            <input type="date" id="acc_date" name="acc_date" required />
          </div>

          <div class="form-group">
            <label for="acc_place">Acquisition Place</label>
            <input type="text" id="acc_place" name="acc_place" placeholder="Enter where item was acquired" />
          </div>

          <div class="form-group">
            <label for="itemDescription">Description</label>
            <textarea id="itemDescription" name="itemDescription" rows="4" placeholder="Add details about your item"></textarea>
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
        
        <p id="jsFormMessage" class="form-message"></p> 

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
        <h3>My friends</h3>
        <p><a href="userfriendspage.php"> View Friends</a></p>
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

  <script src="itemcreation.js"></script>
  <script src="logout.js"></script>

</body>
</html>