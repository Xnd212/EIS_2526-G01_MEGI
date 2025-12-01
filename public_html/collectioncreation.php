<?php
// === DEBUGGING (Remove these lines when finished) ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 1. DATABASE CONNECTION
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sie"; // Your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. CHECK LOGIN
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // For testing only, remove later
    $user_id = 1; 
}

$message = "";

// 3. HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- A. TEXT INPUTS ---
    $name = $conn->real_escape_string($_POST['collectionName']);
    $theme = $conn->real_escape_string($_POST['collectionTheme']);
    $description = $conn->real_escape_string($_POST['collectionDescription']);
    $starting_date = $_POST['creationDate'];

    // --- B. IMAGE HANDLING (File -> Folder, URL -> DB) ---
    $image_id = "NULL"; // Default if no image

    // Check if a file was actually uploaded
    if (isset($_FILES['collectionImage']) && $_FILES['collectionImage']['error'] == 0) {
        
        $target_dir = "images/";
        
        // Ensure folder exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = basename($_FILES["collectionImage"]["name"]);
        $target_file = $target_dir . $file_name; // e.g., "images/myphoto.jpg"
        
        // 1. Move file to the folder
        if (move_uploaded_file($_FILES["collectionImage"]["tmp_name"], $target_file)) {
            
            // 2. Insert URL into 'images' table
            // The URL stored is exactly: "images/filename.ext"
            $url_for_db = $conn->real_escape_string($target_file);
            
            $sql_img = "INSERT INTO images (url) VALUES ('$url_for_db')";
            
            if ($conn->query($sql_img) === TRUE) {
                // 3. Get the new ID to use for the collection
                $image_id = $conn->insert_id;
            } else {
                $message .= "Error saving image info to DB: " . $conn->error;
            }
        } else {
            $message .= "Error moving uploaded file to folder.";
        }
    }

    // --- C. INSERT COLLECTION ---
    // If image_id is a number, use it. If "NULL", use NULL.
    $img_val = ($image_id === "NULL") ? "NULL" : $image_id;

    $sql_coll = "INSERT INTO collection (user_id, Theme, image_id, name, starting_date, description) 
                 VALUES ('$user_id', '$theme', $img_val, '$name', '$starting_date', '$description')";

    if ($conn->query($sql_coll) === TRUE) {
        $new_collection_id = $conn->insert_id;
        $message = "Collection created successfully!";

        // --- D. ADD ITEMS TO COLLECTION (Many-to-Many) ---
        if (!empty($_POST['selectedItems'])) {
            foreach ($_POST['selectedItems'] as $item_id) {
                $item_id = (int)$item_id;
                $sql_contains = "INSERT INTO contains (collection_id, item_id) 
                                 VALUES ('$new_collection_id', '$item_id')";
                $conn->query($sql_contains);
            }
        }
        
    } else {
        $message = "Error creating collection: " . $conn->error;
    }
}

// 4. FETCH ITEMS FOR DROPDOWN (User -> Collection -> Contains -> Item)
$user_items = [];
$sql_items = "
    SELECT DISTINCT i.item_id, i.name 
    FROM item i
    JOIN contains c ON i.item_id = c.item_id
    JOIN collection col ON c.collection_id = col.collection_id
    WHERE col.user_id = '$user_id'
";

$result_items = $conn->query($sql_items);
if ($result_items && $result_items->num_rows > 0) {
    while($row = $result_items->fetch_assoc()) {
        $user_items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Create Collection</title>
  <link rel="stylesheet" href="collectioncreation.css" />
  <style>
    .form-message { 
        text-align: center; 
        font-weight: bold; 
        margin-top: 20px; 
        color: #d9534f; /* Red for errors */
    }
    .success {
        color: #5cb85c; /* Green for success */
    }
  </style>
</head>

<body>

  <header>
    <a href="homepage.php" class="logo"><img src="images/TrallE_2.png" alt="logo" /></a>
    <div class="search-bar"><input type="search" placeholder="Search" /></div>
    <div class="icons">
        <button class="icon-btn">🔔</button>
        <a href="userpage.php" class="icon-btn">👤</a>
        <button class="icon-btn" id="logout-btn">🚪</button>
    </div>
  </header>

  <div class="main">
    <div class="content">
      <section class="collection-creation-section">
        <h2 class="page-title">Create a Collection</h2>

        <form id="collectionForm" method="POST" action="" enctype="multipart/form-data">

          <div class="form-group">
            <label for="collectionName">Name <span class="required">*</span></label>
            <input type="text" id="collectionName" name="collectionName" required />
          </div>

          <div class="form-group">
            <label for="collectionTheme">Theme <span class="required">*</span></label>
            <input type="text" id="collectionTheme" name="collectionTheme" required />
          </div>

          <div class="form-group">
            <label for="creationDate">Starting Date <span class="required">*</span></label>
            <input type="date" id="creationDate" name="creationDate" required />
          </div>

          <div class="form-group">
            <label for="collectionDescription">Description</label>
            <textarea id="collectionDescription" name="collectionDescription" rows="4"></textarea>
          </div>    

          <div class="form-group">
            <label for="collectionTags">Tags</label>
            <input type="text" id="collectionTags" name="collectionTags" />
          </div>

          <div class="form-group">
            <label for="collectionImage">Cover Image (optional)</label>
            <input type="file" id="collectionImage" name="collectionImage" accept="image/*" />
          </div>
          
          <div class="form-group">
            <label>Select Existing Items (optional) </label>
            <div class="custom-multiselect">
              <button type="button" id="dropdownBtn">Select from existing items ⮟</button>
              <div class="dropdown-content" id="dropdownContent">
                <?php 
                if (empty($user_items)) {
                    echo "<label style='padding:5px;'>No items found.</label>";
                } else {
                    foreach ($user_items as $item) {
                        echo '<label><input type="checkbox" name="selectedItems[]" value="' . $item['item_id'] . '"> ' . htmlspecialchars($item['name']) . '</label>';
                    }
                }
                ?>
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">Create Collection</button>
          </div>
        </form>

        <p id="formMessage" class="form-message <?php echo (strpos($message, 'success') !== false) ? 'success' : ''; ?>">
            <?php echo $message; ?>
        </p>

      </section>
    </div>

    <aside class="sidebar">
       <div class="sidebar-section">
           <h3>My collections</h3>
           <p><a href="collectioncreation.php">Create collection</a></p>
           <p><a href="itemcreation.php">Create Item</a></p>
           <p><a href="mycollectionspage.php">View collections</a></p>
       </div>
    </aside>
  </div>

  <script src="collectioncreation.js"></script>
  <script src="logout.js"></script>
</body>
</html>