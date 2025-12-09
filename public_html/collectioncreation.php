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
require_once __DIR__ . "/db.php";

// ==========================================
// 3. USER ID
// ==========================================
$user_id = (int) $_SESSION['user_id'];

$message = "";
$messageType = "";

// ==========================================
// 4. CSV IMPORT HANDLER
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['import_csv'])) {

    if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] == 0) {

        $handle = fopen($_FILES['csvFile']['tmp_name'], "r");

        if ($handle !== FALSE) {
            $imported   = 0;
            $failed     = 0;
            $errors     = [];
            $row_number = 0;

            // Skip header row
            $first_row = fgetcsv($handle);

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row_number++;

                if (!$conn->ping()) {
                    $conn->close();
                    require_once __DIR__ . "/db.php";
                }

                if (count($data) < 2) {
                    $failed++;
                    $errors[] = "Row $row_number: Not enough columns";
                    continue;
                }

                if (empty(trim($data[0])) || empty(trim($data[1]))) {
                    $failed++;
                    $errors[] = "Row $row_number: Missing required field (name or theme)";
                    continue;
                }

                $name          = trim($data[0]);
                $theme         = trim($data[1]);
                $starting_date = isset($data[2]) && !empty($data[2]) ? $data[2] : date('Y-m-d');
                $description   = isset($data[3]) ? trim($data[3]) : '';

                $stmt_coll = $conn->prepare("
                    INSERT INTO collection (user_id, Theme, name, starting_date, description) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt_coll->bind_param("issss", $user_id, $theme, $name, $starting_date, $description);

                if ($stmt_coll->execute()) {
                    $imported++;
                } else {
                    $failed++;
                    $errors[] = "Row $row_number: Failed to insert collection '$name'";
                }
                $stmt_coll->close();

                if ($row_number % 50 == 0) {
                    usleep(10000);
                }
            }

            fclose($handle);

            $message = "Import complete! Successfully imported $imported collections.";
            if ($failed > 0) {
                $message .= " $failed collections failed.";
                $messageType = "error";
                if (!empty($errors) && count($errors) <= 5) {
                    $message .= "<br><small>" . implode("<br>", $errors) . "</small>";
                } elseif (!empty($errors)) {
                    $message .= "<br><small>Showing first 5 errors:<br>" . implode("<br>", array_slice($errors, 0, 5)) . "</small>";
                }
            } else {
                $messageType = "success";
            }
        } else {
            $message = "Error opening CSV file.";
            $messageType = "error";
        }
    } else {
        $message = "Please upload a valid CSV file.";
        $messageType = "error";
    }
}

// ==========================================
// 5. HANDLE NORMAL FORM SUBMISSION
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['import_csv'])) {

    // A. Inputs
    $name          = $conn->real_escape_string($_POST['collectionName']);
    $theme         = $conn->real_escape_string($_POST['collectionTheme']);
    $description   = $conn->real_escape_string($_POST['collectionDescription']);
    $starting_date = $_POST['creationDate']; // YYYY-MM-DD

    // Tags seleccionadas (IDs da tabela tags)
    $selectedTags = isset($_POST['tags'])
        ? array_map('intval', (array)$_POST['tags'])
        : [];

    // B. Image Upload
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

    $sql_coll = "
        INSERT INTO collection (user_id, Theme, image_id, name, starting_date, description) 
        VALUES ('$user_id', '$theme', $img_val, '$name', '$starting_date', '$description')
    ";

    if ($conn->query($sql_coll) === TRUE) {
        $new_collection_id = $conn->insert_id;

        if ($new_collection_id == 0) {
            $message     = "Error: Collection created but ID is 0. Check DB Auto_Increment.";
            $messageType = "error";
        } else {
            // D. ligar items
            if (!empty($_POST['selectedItems'])) {
                foreach ($_POST['selectedItems'] as $item_id) {
                    $item_id = (int)$item_id;
                    $sql_contains = "
                        INSERT INTO contains (collection_id, item_id) 
                        VALUES ('$new_collection_id', '$item_id')
                    ";
                    $conn->query($sql_contains);
                }
            }

            // E. ligar TAGS (tabela collection_tags)
            if (!empty($selectedTags)) {
                $stmtTag = $conn->prepare("
                    INSERT INTO collection_tags (collection_id, tag_id) 
                    VALUES (?, ?)
                ");
                foreach ($selectedTags as $tid) {
                    $tid = (int)$tid;
                    $stmtTag->bind_param("ii", $new_collection_id, $tid);
                    $stmtTag->execute();
                }
                $stmtTag->close();
            }

            $message     = "Collection created successfully!";
            $messageType = "success";
        }
    } else {
        $message     = "Database Error: " . $conn->error;
        $messageType = "error";
    }
}

// ==========================================
// 6. FETCH TAGS (para o dropdown)
// ==========================================
$allTags = [];
$sqlTags = "SELECT tag_id, name FROM tags ORDER BY name ASC";
$resTags = $conn->query($sqlTags);
if ($resTags && $resTags->num_rows > 0) {
    while ($row = $resTags->fetch_assoc()) {
        $allTags[] = $row;
    }
}

// ==========================================
// 7. FETCH ITEMS (para o dropdown de items)
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
    <link rel="stylesheet" href="calendar_popup.css" />
</head>

<body>

<!-- POPUP CSV (overlay + modal) -->
<div class="popup-overlay" id="csv-overlay"></div>

<div class="csv-import-popup" id="csv-import-popup">
    <div class="popup-header">
        <h3>Bulk Import Collections</h3>
        <button class="close-popup" id="close-csv-popup" type="button">✕</button>
    </div>

    <div class="popup-content">
        <h4>CSV Format Instructions</h4>
        <p>Your CSV file must have columns in this <strong>exact order</strong>:</p>

        <div class="csv-format-box">
            <code>name, theme, starting_date, description</code>
        </div>

        <h4>Example CSV:</h4>
        <div class="csv-example">
<pre>name,theme,starting_date,description
Pokemon Cards,Trading Cards,2024-01-15,My Pokemon card collection
Action Figures,Toys,2024-02-20,Collection of superhero action figures
Vintage Comics,Comics,2024-03-10,Classic Marvel and DC comics</pre>
        </div>

        <h4>Field Details:</h4>
        <ul>
            <li><strong>name</strong> <span class="required">*</span> - Collection name (required)</li>
            <li><strong>theme</strong> <span class="required">*</span> - Collection theme (required)</li>
            <li><strong>starting_date</strong> - Date in format YYYY-MM-DD (defaults to today if empty)</li>
            <li><strong>description</strong> - Collection description (optional)</li>
        </ul>

        <p><strong>Important Notes:</strong></p>
        <ul>
            <li>The first row should be the header (it will be skipped)</li>
            <li>Required fields (name and theme) must not be empty</li>
            <li>Cover images will use the database default value</li>
            <li>Items can be added to collections later</li>
        </ul>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="csvFile">Upload CSV File <span class="required">*</span></label>
                <input type="file" id="csvFile" name="csvFile" accept=".csv" required />
            </div>

            <div class="form-actions">
                <button type="submit" name="import_csv" class="btn-primary">Import Collections</button>
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

                <!-- TAGS – MESMO ESTILO DO EDIT -->
                <div class="form-group">
                    <label>Tags</label>

                    <div class="tag-header">
                        <button type="button" id="openTagModal" class="btn-small">Criar tags</button>
                    </div>

                    <div class="custom-multiselect">
                        <button type="button" id="dropdownBtn">Select Tags ⮟</button>
                        <div class="dropdown-content" id="tagDropdown">
                            <?php if (empty($allTags)): ?>
                                <div style="padding:0.4rem 0.6rem; color:#777;">No tags created yet.</div>
                            <?php else: ?>
                                <?php foreach ($allTags as $tag): ?>
                                    <label>
                                        <input type="checkbox" name="tags[]" value="<?php echo (int)$tag['tag_id']; ?>">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ITENS EXISTENTES -->
                <div class="form-group">
                    <label>Select Existing Items (optional)</label>
                    <div class="custom-multiselect">
                        <button type="button" id="itemsDropdownBtn">Select from existing items ⮟</button>
                        <div class="dropdown-content" id="itemsDropdownContent">

                            <?php
                            if (empty($user_items)) {
                                echo "<div style='padding:10px; color:#555;'>No items found in your inventory.</div>";
                            } else {
                                foreach ($user_items as $item) {
                                    echo '<label>';
                                    echo '<input type="checkbox" name="selectedItems[]" value="' . (int)$item['item_id'] . '"> ';
                                    echo htmlspecialchars($item['name']);
                                    echo '</label>';
                                }
                            }
                            ?>

                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="collectionImage">Cover Image (optional)</label>
                    <input type="file" id="collectionImage" name="collectionImage" accept="image/*" />
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Collection</button>
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


<!-- MODAL DE TAGS -->
<div id="tagModalOverlay" class="modal-overlay hidden"></div>

<div id="tagModal" class="modal hidden">
    <h3>Criar nova tag</h3>
    <input type="text" id="newTagInput" placeholder="Nome da tag...">
    <p id="tagFeedback" class="tag-feedback"></p>
    <div class="modal-buttons">
        <button id="createTagBtn" class="btn-primary">Criar tag</button>
        <button id="closeTagModal" class="btn-secondary">Close</button>
    </div>
</div>

<script src="collectioncreation.js"></script>
<script src="homepage.js"></script>
<script src="logout.js"></script>

</body>
</html>




