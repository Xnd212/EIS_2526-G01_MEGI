<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

/* =========================================
  1. BLOCK GUEST AND NOT LOGGED IN
  ========================================= */
$isGuest = isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;

if (!isset($_SESSION['user_id']) || $isGuest) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once __DIR__ . "/db.php";

$user_id = (int) $_SESSION['user_id']; 

// ==========================================
// 1. FETCH USER COLLECTIONS FOR DROPDOWN
// ==========================================
$user_collections = [];
$coll_sql = "SELECT collection_id, name FROM collection WHERE user_id = ?";
$coll_stmt = $conn->prepare($coll_sql);
$coll_stmt->bind_param("i", $user_id);
$coll_stmt->execute();
$result = $coll_stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_collections[] = $row;
}
$coll_stmt->close();

$error = "";
$new_event_id = null;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = trim($_POST['eventName']);
    $start_date = $_POST['startDate'];
    $theme = trim($_POST['theme']);
    $place = trim($_POST['location']);
    $description = trim($_POST['description']);
    $teaser_url = !empty($_POST['youtube']) ? trim($_POST['youtube']) : null;
    $collection_id = isset($_POST['collection_id']) ? $_POST['collection_id'] : null;
    
    // Validate required fields
    if (empty($event_name) || empty($start_date) || empty($theme) || empty($place) || empty($description) || empty($collection_id)) {
        $error = "Please fill in all required fields, including the collection.";
    }
    // Validate date format (YYYY-MM-DD)
    elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
        $error = "Invalid date format";
    }
    // Validate date is not in the past
    elseif (strtotime($start_date) < strtotime(date('Y-m-d'))) {
        $error = "Event date cannot be in the past. Please select today or a future date.";
    }
    else {
        $image_id = 13; // Default placeholder image ID

        // Handle image upload (OPTIONAL)
        if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['coverImage']['type'];

            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['coverImage']['name'], PATHINFO_EXTENSION);
                $new_filename = 'event_' . time() . '_' . uniqid() . '.' . $file_extension;
                $target_path = __DIR__ . '/images/' . $new_filename;
                $url_path = 'images/' . $new_filename;

                if (!file_exists(__DIR__ . '/images')) {
                    mkdir(__DIR__ . '/images', 0777, true);
                }

                if (move_uploaded_file($_FILES['coverImage']['tmp_name'], $target_path)) {
                    $image_stmt = $conn->prepare("INSERT INTO image (url) VALUES (?)");
                    $image_stmt->bind_param("s", $url_path);

                    if ($image_stmt->execute()) {
                        $image_id = $image_stmt->insert_id;
                    } else {
                        $error = "Error saving image to database: " . $image_stmt->error;
                        unlink($target_path);
                    }
                    $image_stmt->close();
                } else {
                    $error = "Error uploading image file. Check folder permissions.";
                }
            } else {
                $error = "File type not allowed. Use JPEG, PNG, GIF or WEBP";
            }
        }
        // If no file uploaded or file error (other than UPLOAD_ERR_NO_FILE), use placeholder
        elseif (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] != UPLOAD_ERR_NO_FILE) {
            switch ($_FILES['coverImage']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error = "Image file is too large";
                    break;
                default:
                    $error = "Error uploading image";
            }
        }

        // Insert event if no errors
        if (empty($error)) {
            $event_stmt = $conn->prepare("INSERT INTO event (user_id, image_id, name, date, theme, place, description, teaser_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $event_stmt->bind_param("iissssss", $user_id, $image_id, $event_name, $start_date, $theme, $place, $description, $teaser_url);

            if ($event_stmt->execute()) {
                $new_event_id = $event_stmt->insert_id;

                // ==========================================
                // 2. INSERT INTO 'ATTENDS' TABLE
                // ==========================================
                if ($new_event_id && $collection_id) {
                    $attend_stmt = $conn->prepare("INSERT INTO attends (user_id, event_id, collection_id) VALUES (?, ?, ?)");
                    $attend_stmt->bind_param("iii", $user_id, $new_event_id, $collection_id);
                    if (!$attend_stmt->execute()) {
                        error_log("Failed to add creator to attends table: " . $attend_stmt->error);
                    }
                    $attend_stmt->close();
                }

                // === SUCCESS: REDIRECT IMMEDIATELY TO THE NEW EVENT PAGE ===
                header("Location: eventpage.php?id=" . $new_event_id);
                exit(); 

            } else {
                $error = "Error creating event: " . $event_stmt->error;
            }

            $event_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Trall-E | Create Event</title>
        <link rel="stylesheet" href="createevent.css" />
        <link rel="stylesheet" href="calendar_popup.css" />
    </head>

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
                        <button type="button" class="logout-btn cancel-btn" id="cancel-logout">
                            Cancel
                        </button>
                        <button type="button" class="logout-btn confirm-btn" id="confirm-logout">
                            Log out
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="main">
            <div class="content">
                <section class="item-creation-section">
                    <h2 class="page-title">Create a Event</h2>

                    <form id="eventForm" method="POST" action="" enctype="multipart/form-data" novalidate>
                        <div class="form-group">
                            <label for="eventName">Event Name <span class="required">*</span></label>
                            <input
                                type="text"
                                id="eventName"
                                name="eventName"
                                placeholder="e.g. Comic Con Portugal"
                                value="<?php echo isset($_POST['eventName']) ? htmlspecialchars($_POST['eventName']) : ''; ?>"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label for="startDate">Date <span class="required">*</span></label>
                            <input 
                                type="date" 
                                id="startDate" 
                                name="startDate" 
                                min="<?php echo date('Y-m-d'); ?>"
                                value="<?php echo isset($_POST['startDate']) ? htmlspecialchars($_POST['startDate']) : ''; ?>"
                                required 
                            />
                        </div>

                        <div class="form-group">
                            <label for="theme">Theme <span class="required">*</span></label>
                            <input
                                type="text"
                                id="theme"
                                name="theme"
                                placeholder="e.g. Anime, Cards, etc."
                                value="<?php echo isset($_POST['theme']) ? htmlspecialchars($_POST['theme']) : ''; ?>"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label>Collection to Bring <span class="required">*</span></label>

                            <div class="custom-multiselect">
                                <button type="button" id="collectionDropdownBtn">
                                    <?php
                                    $selectedName = "Select Collection ⮟";
                                    if (!empty($_POST['collection_id'])) {
                                        foreach ($user_collections as $c) {
                                            if ((int) $_POST['collection_id'] === (int) $c['collection_id']) {
                                                $selectedName = htmlspecialchars($c['name']);
                                                break;
                                            }
                                        }
                                    }
                                    echo $selectedName;
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

                                    <?php foreach ($user_collections as $c): ?>
                                        <label data-collection-name="<?= strtolower(htmlspecialchars($c['name'])) ?>">
                                            <input
                                                type="radio"
                                                name="collectionRadio"
                                                value="<?= (int) $c['collection_id'] ?>"
                                                <?= (isset($_POST['collection_id']) && (int) $_POST['collection_id'] === (int) $c['collection_id']) ? 'checked' : '' ?>
                                                >
                                                <?= htmlspecialchars($c['name']) ?>
                                        </label>
                                    <?php endforeach; ?>

                                </div>
                            </div>

                            <!-- valor real enviado para o PHP -->
                            <input
                                type="hidden"
                                id="collection_id"
                                name="collection_id"
                                value="<?= isset($_POST['collection_id']) ? (int) $_POST['collection_id'] : '' ?>"
                                >

                            <?php if (empty($user_collections)): ?>
                                <small style="color:red; margin-top: 5px;">You have no collections to bring. Please create one first.</small>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="location">Place <span class="required">*</span></label>
                            <input
                                type="text"
                                id="location"
                                name="location"
                                placeholder="e.g. Exponor – Porto"
                                value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label for="description">Description <span class="required">*</span></label>
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="Brief description about the event"
                                required
                            ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="youtube">YouTube video embed link</label>
                            <input
                                type="url"
                                id="youtube"
                                name="youtube"
                                placeholder="e.g. https://www.youtube.com/watch?v=..."
                                value="<?php echo isset($_POST['youtube']) ? htmlspecialchars($_POST['youtube']) : ''; ?>"
                            />
                        </div>

                        <div class="form-group">
                            <label for="coverImage">Cover Image (optional)</label>
                            <input
                                type="file"
                                id="coverImage"
                                name="coverImage"
                                accept="image/*"
                            />
                            <small style="color: #666; margin-top: 5px;">If no image is uploaded, a default placeholder will be used.</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Create Event</button>
                        </div>

                        <?php if (!empty($error)): ?>
                            <p class="form-message error"><?php echo htmlspecialchars($error); ?></p>
                        <?php endif; ?>
                    </form>

                </section>
            </div>

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
                    <p><a href="userfriendspage.php">View bubble</a></p>
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

        <!-- === JAVASCRIPT === -->
        <script src="homepage.js"></script>
        <script src="createevent.js"></script>
        <script src="logout.js"></script>

    </body>
</html>