<?php
// ==========================================
// 1. DEBUG SETTINGS
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$isGuest = !empty($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;

if (!isset($_SESSION['user_id']) || $isGuest) {
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

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ==========================================
// 3. USER ID HANDLING
// ==========================================

// aqui já sabemos que existe user_id, porque acima fizemos o check
$user_id = (int)$_SESSION['user_id'];

$message = "";
$messageType = "";

// ==========================================
// 4. HANDLE FORM SUBMISSION
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // A. Sanitize Inputs
    $name        = $conn->real_escape_string($_POST['collectionName']);
    $theme       = $conn->real_escape_string($_POST['collectionTheme']);
    $description = $conn->real_escape_string($_POST['collectionDescription']);
    $starting_date = $_POST['creationDate']; // YYYY-MM-DD

    // B. Image Upload Logic
    $image_id = "NULL";

    if (isset($_FILES['collectionImage']) && $_FILES['collectionImage']['error'] == 0) {
        $target_dir = "images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name   = basename($_FILES["collectionImage"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["collectionImage"]["tmp_name"], $target_file)) {

            $url    = "images/" . $file_name;
            $url_db = $conn->real_escape_string($url);

            $sql_img = "INSERT INTO image (url) VALUES ('$url_db')";

            if ($conn->query($sql_img) === TRUE) {
                $image_id = $conn->insert_id;
            } else {
                $message     = "Image DB Error: " . $conn->error;
                $messageType = "error";
            }
        }
    }

    // C. Insert Collection
    $img_val = ($image_id === "NULL") ? "NULL" : $image_id;

    $sql_coll = "INSERT INTO collection (user_id, Theme, image_id, name, starting_date, description) 
                 VALUES ('$user_id', '$theme', $img_val, '$name', '$starting_date', '$description')";

    if ($conn->query($sql_coll) === TRUE) {
        $new_collection_id = $conn->insert_id;

        if ($new_collection_id == 0) {
            $message     = "Error: Collection created but ID is 0. Check DB Auto_Increment.";
            $messageType = "error";
        } else {
            $message     = "Collection created successfully!";
            $messageType = "success";

            // D. Link Selected Items
            if (!empty($_POST['selectedItems'])) {
                foreach ($_POST['selectedItems'] as $item_id) {
                    $item_id = (int)$item_id;
                    $sql_contains = "INSERT INTO contains (collection_id, item_id) 
                                     VALUES ('$new_collection_id', '$item_id')";
                    $conn->query($sql_contains);
                }
            }
        }
    } else {
        $message     = "Database Error: " . $conn->error;
        $messageType = "error";
    }
}

// ==========================================
// 5. FETCH ITEMS (For Dropdown)
// ==========================================
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
    while ($row = $result_items->fetch_assoc()) {
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
                padding: 10px;
                margin-top: 15px;
                border-radius: 5px;
            }
            .form-message.success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .form-message.error {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
        </style>
    </head>

    <body>

        <header>
            <a href="homepage.php" class="logo">
                <img src="images/TrallE_2.png" alt="logo" />
            </a>
            <div class="search-bar">
                <input type="search" placeholder="Search" />
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
                <section class="collection-creation-section">
                    <h2 class="page-title">Create a Collection</h2>

                    <form id="collectionForm" method="POST" action="" enctype="multipart/form-data">

                        <div class="form-group">
                            <label for="collectionName">Name <span class="required">*</span></label>
                            <input type="text" id="collectionName" name="collectionName" placeholder="Enter collection name" required />
                        </div>

                        <div class="form-group">
                            <label for="collectionTheme">Theme <span class="required">*</span></label>
                            <input type="text" id="collectionTheme" name="collectionTheme" placeholder="Enter collection theme" required />
                        </div>

                        <div class="form-group">
                            <label for="creationDate">Starting Date (DD/MM/YYYY) <span class="required">*</span></label>
                            <input type="date" id="creationDate" name="creationDate" required />
                        </div>

                        <div class="form-group">
                            <label for="collectionDescription">Description</label>
                            <textarea id="collectionDescription" name="collectionDescription" rows="4" placeholder="Add details"></textarea>
                        </div>    

                        <div class="form-group">
                            <label for="collectionTags">Tags</label>
                            <input type="text" id="collectionTags" name="collectionTags" placeholder="Enter collection tags" />
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
    echo "<div style='padding:10px; color:#555;'>No items found in your inventory.</div>";
} else {
    foreach ($user_items as $item) {
        echo '<label>';
        echo '<input type="checkbox" name="selectedItems[]" value="' . $item['item_id'] . '"> ';
        echo htmlspecialchars($item['name']);
        echo '</label>';
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

        <script src="collectioncreation.js"></script>
        <script src="homepage.js"></script>
        <script src="logout.js"></script>

    </body>
</html>