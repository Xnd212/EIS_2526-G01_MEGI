<?php
// ==========================================
// 1. DEBUG & SETUP
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

/* =========================================
   1.1. BLOQUEAR GUEST E NÃO LOGADOS
   ========================================= */
$isGuest = isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;

if (!isset($_SESSION['user_id']) || $isGuest) {
    // se não estiver autenticado (ou for guest), vai para login
    header("Location: login.php");
    exit();
}

// ==========================================
// 2. DATABASE CONNECTION
// ==========================================
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "sie"; // Your Database Name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ===============================
// 2.1 USER ID (sem fallback)
// ===============================
$user_id = (int) $_SESSION['user_id'];

$message = "";
$messageType = "";

// ==========================================
// 3. HANDLE FORM SUBMISSION
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- A. GET STANDARD INPUTS ---
    $name        = $conn->real_escape_string($_POST['itemName']);
    $price       = !empty($_POST['itemPrice']) ? (float)$_POST['itemPrice'] : 0.00;
    $importance  = (int)$_POST['itemImportanceNumber']; 
    $acc_date    = $_POST['acc_date']; 
    $acc_place   = $conn->real_escape_string($_POST['acc_place']);
    $description = $conn->real_escape_string($_POST['itemDescription']);
    
    $collection_id = isset($_POST['collection_id']) ? (int)$_POST['collection_id'] : 0;

    // --- B. HANDLE TYPE (Find or Create) ---
    $typeNameInput = trim($_POST['itemType']);
    $typeNameSafe  = $conn->real_escape_string($typeNameInput);
    $type_id       = 0;

    $sql_check_type = "SELECT type_id FROM type WHERE name = '$typeNameSafe' LIMIT 1";
    $result_type    = $conn->query($sql_check_type);

    if ($result_type && $result_type->num_rows > 0) {
        $row    = $result_type->fetch_assoc();
        $type_id = $row['type_id'];
    } else {
        $sql_create_type = "INSERT INTO type (name) VALUES ('$typeNameSafe')";
        if ($conn->query($sql_create_type) === TRUE) {
            $type_id = $conn->insert_id;
        } else {
            $message     = "Error creating new Item Type: " . $conn->error;
            $messageType = "error";
        }
    }

    // --- C. HANDLE IMAGE ---
    $image_id = "NULL";

    if (isset($_FILES['itemImage']) && $_FILES['itemImage']['error'] == 0) {
        $target_dir = "images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name   = basename($_FILES["itemImage"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["itemImage"]["tmp_name"], $target_file)) {
            $url    = "images/" . $file_name;
            $url_db = $conn->real_escape_string($url);
            
            $sql_img = "INSERT INTO image (url) VALUES ('$url_db')";
            if ($conn->query($sql_img) === TRUE) {
                $image_id = $conn->insert_id;
            }
        }
    }

    // --- D. INSERT ITEM & LINK TO COLLECTION ---
    if ($type_id > 0 && $collection_id > 0) {
        $img_val = ($image_id === "NULL") ? "NULL" : $image_id;

        $sql_item = "INSERT INTO item (type_id, image_id, name, price, importance, acc_date, acc_place, description) 
                     VALUES ('$type_id', $img_val, '$name', '$price', '$importance', '$acc_date', '$acc_place', '$description')";

        if ($conn->query($sql_item) === TRUE) {
            $new_item_id  = $conn->insert_id;
            $sql_contains = "INSERT INTO contains (collection_id, item_id) VALUES ('$collection_id', '$new_item_id')";

            if ($conn->query($sql_contains) === TRUE) {
                $message     = "Item created successfully!";
                $messageType = "success";
            } else {
                $message     = "Item created, but failed to add to collection. Error: " . $conn->error;
                $messageType = "error";
            }
        } else {
            $message     = "Error inserting item: " . $conn->error;
            $messageType = "error";
        }
    } elseif ($collection_id == 0) {
        $message     = "Please select a valid collection.";
        $messageType = "error";
    }
}

// ==========================================
// 4. FETCH COLLECTIONS FOR DROPDOWN
// ==========================================
$user_collections = [];
$sql_cols   = "SELECT collection_id, name FROM collection WHERE user_id = '$user_id'";
$result_cols = $conn->query($sql_cols);

if ($result_cols && $result_cols->num_rows > 0) {
    while ($row = $result_cols->fetch_assoc()) {
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

<body>

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