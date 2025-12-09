<?php
// ==========================================
// 1. SETUP & AUTH
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Database connection
require_once __DIR__ . "/db.php";

// ==========================================
// 2. FETCH EVENT DATA (GET)
// ==========================================
// We need the ID to know what to edit
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// If ID is missing, we might be submitting the form (POST), so check hidden input
if (!$event_id && isset($_POST['event_id'])) {
    $event_id = (int) $_POST['event_id'];
}

if (!$event_id) {
    header("Location: upcomingevents.php");
    exit();
}

// Fetch existing event details AND ensure the current user is the creator
$sqlEvent = "SELECT e.*, i.url as image_url 
             FROM event e
             LEFT JOIN image i ON e.image_id = i.image_id
             WHERE e.event_id = ? AND e.user_id = ?";
$stmtEvent = $conn->prepare($sqlEvent);
$stmtEvent->bind_param("ii", $event_id, $user_id);
$stmtEvent->execute();
$resEvent = $stmtEvent->get_result();

if ($resEvent->num_rows === 0) {
    die("Event not found or you do not have permission to edit it.");
}

$eventData = $resEvent->fetch_assoc();
$stmtEvent->close();

// Fetch the collection the user is CURRENTLY bringing to this event
$current_collection_id = null;
$sqlAttends = "SELECT collection_id FROM attends WHERE event_id = ? AND user_id = ?";
$stmtAttends = $conn->prepare($sqlAttends);
$stmtAttends->bind_param("ii", $event_id, $user_id);
$stmtAttends->execute();
$resAttends = $stmtAttends->get_result();
if ($row = $resAttends->fetch_assoc()) {
    $current_collection_id = $row['collection_id'];
}
$stmtAttends->close();

// ==========================================
// 3. FETCH USER COLLECTIONS FOR DROPDOWN
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

// ==========================================
// 4. HANDLE FORM SUBMISSION (UPDATE)
// ==========================================
$error = "";
$success = false;

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
        $error = "Please fill in all required fields.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
        $error = "Invalid date format";
    } else {
        // Keep the old image ID by default
        $image_id = $eventData['image_id'];

        // Handle NEW image upload (Optional during edit)
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
                    // Insert NEW image
                    $image_stmt = $conn->prepare("INSERT INTO image (url) VALUES (?)");
                    $image_stmt->bind_param("s", $url_path);
                    if ($image_stmt->execute()) {
                        $image_id = $image_stmt->insert_id; // Update variable to new ID
                    }
                    $image_stmt->close();
                } else {
                    $error = "Error uploading image file.";
                }
            } else {
                $error = "File type not allowed. Use JPEG, PNG, GIF or WEBP";
            }
        }

        // UPDATE Event if no errors
        if (empty($error)) {
            $update_sql = "UPDATE event 
                           SET image_id = ?, name = ?, date = ?, theme = ?, place = ?, description = ?, teaser_url = ?
                           WHERE event_id = ? AND user_id = ?";

            $stmtUpd = $conn->prepare($update_sql);
            $stmtUpd->bind_param("issssssii", $image_id, $event_name, $start_date, $theme, $place, $description, $teaser_url, $event_id, $user_id);

            if ($stmtUpd->execute()) {
                // UPDATE Attendance (Collection choice)
                $att_sql = "UPDATE attends SET collection_id = ? WHERE event_id = ? AND user_id = ?";
                $stmtAtt = $conn->prepare($att_sql);
                $stmtAtt->bind_param("iii", $collection_id, $event_id, $user_id);
                $stmtAtt->execute();
                $stmtAtt->close();

                $success = true;

                // Refetch data so the form shows new values
                $eventData['name'] = $event_name;
                $eventData['date'] = $start_date;
                $eventData['theme'] = $theme;
                $eventData['place'] = $place;
                $eventData['description'] = $description;
                $eventData['teaser_url'] = $teaser_url;
                $current_collection_id = $collection_id;
                // If image changed, we'd need to refetch URL, but success message usually redirects anyway.
            } else {
                $error = "Error updating event: " . $stmtUpd->error;
            }
            $stmtUpd->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Trall-E | Edit Event</title>
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
                    <input type="text" name="q" placeholder="Search for friends, collections, events, items..." required>
                </form>
            </div>

            <div class="icons">
                <?php include __DIR__ . '/calendar_popup.php'; ?>
                <?php include __DIR__ . '/notifications_popup.php'; ?>

                <a href="userpage.php" class="icon-btn" aria-label="Perfil">👤</a>
                <button class="icon-btn" id="logout-btn" aria-label="Logout">🚪</button>

                <div class="notification-popup logout-popup" id="logout-popup">
                    <div class="popup-header"><h3>Logout</h3></div>
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
                    <h2 class="page-title">Edit Event</h2>

                    <?php if ($success): ?>
                        <div id="eventSuccessModal" class="event-success-modal" style="display: flex;">
                            <div class="success-box">
                                <h2>Event Updated Successfully ✅</h2>
                                <p>Your event details have been saved.</p>
                                <div class="success-buttons">
                                    <a href="eventpage.php?id=<?php echo $event_id; ?>" class="btn-primary">Go to event page</a>
                                    <a href="homepage.php" class="btn-secondary">Go to homepage</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>

                        <?php if (!empty($error)): ?>
                            <p class="form-message error" style="color: red; padding: 10px; background: #ffe6e6; border-radius: 5px; margin-bottom: 15px;">
                                <?php echo htmlspecialchars($error); ?>
                            </p>
                        <?php endif; ?>

                        <form id="eventForm" method="POST" action="" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">

                            <div class="form-group">
                                <label for="eventName">Event Name <span class="required">*</span></label>
                                <input
                                    type="text"
                                    id="eventName"
                                    name="eventName"
                                    value="<?php echo htmlspecialchars($eventData['name']); ?>"
                                    required
                                    />
                            </div>

                            <div class="form-group">
                                <label for="startDate">Date <span class="required">*</span></label>
                                <input 
                                    type="date" 
                                    id="startDate" 
                                    name="startDate" 
                                    value="<?php echo htmlspecialchars($eventData['date']); ?>"
                                    required 
                                    />
                            </div>

                            <div class="form-group">
                                <label for="theme">Theme <span class="required">*</span></label>
                                <input
                                    type="text"
                                    id="theme"
                                    name="theme"
                                    value="<?php echo htmlspecialchars($eventData['theme']); ?>"
                                    required
                                    />
                            </div>

                            <div class="form-group">
                                <label for="collection_id">Collection to Bring <span class="required">*</span></label>
                                <select id="collection_id" name="collection_id" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                                    <option value="">-- Select one of your collections --</option>
                                    <?php foreach ($user_collections as $coll): ?>
                                        <option value="<?php echo $coll['collection_id']; ?>" 
                                                <?php echo ($coll['collection_id'] == $current_collection_id) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($coll['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="location">Place <span class="required">*</span></label>
                                <input
                                    type="text"
                                    id="location"
                                    name="location"
                                    value="<?php echo htmlspecialchars($eventData['place']); ?>"
                                    required
                                    />
                            </div>

                            <div class="form-group">
                                <label for="description">Description <span class="required">*</span></label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows="4"
                                    required
                                    ><?php echo htmlspecialchars($eventData['description']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="youtube">YouTube video embed link</label>
                                <input
                                    type="url"
                                    id="youtube"
                                    name="youtube"
                                    value="<?php echo htmlspecialchars($eventData['teaser_url'] ?? ''); ?>"
                                    />
                            </div>

                            <div class="form-group">
                                <label for="coverImage">Cover Image (Optional)</label>
                                <?php if (!empty($eventData['image_url'])): ?>
                                    <div style="margin-bottom:10px;">
                                        <small>Current Image:</small><br>
                                        <img src="<?php echo htmlspecialchars($eventData['image_url']); ?>" alt="Current Event Image" style="max-height: 100px; border-radius: 5px;">
                                    </div>
                                <?php endif; ?>
                                <input
                                    type="file"
                                    id="coverImage"
                                    name="coverImage"
                                    accept="image/*"
                                    />
                                <small style="color:#666;">Leave empty to keep the current image.</small>
                            </div>

                            <div class="form-actions" style="display: flex; gap: 15px; align-items: center; justify-content: space-between;">

                                <div style="display: flex; gap: 15px;">
                                    <button type="submit" class="btn-primary">Update Event</button>

                                    <a href="eventpage.php?id=<?php echo $event_id; ?>" 
                                       class="btn-secondary" 
                                       style="text-decoration:none; padding: 12px 24px; background-color: #6c757d; color: white; border-radius: 5px; font-weight: bold; font-size: 16px; border: none; cursor: pointer;">
                                        Cancel
                                    </a>
                                </div>

                                <a href="delete_event.php?id=<?php echo $event_id; ?>" 
                                   onclick="return confirm('Are you sure you want to PERMANENTLY delete this event? This cannot be undone.');"
                                   style="text-decoration:none; padding: 12px 24px; background-color: #dc3545; color: white; border-radius: 5px; font-weight: bold; font-size: 16px; border: none; cursor: pointer;">
                                    🗑️ Delete Event
                                </a>

                            </div>
                        </form>

                    <?php endif; ?>
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
                    <h3>My friends</h3>
                    <p><a href="userfriendspage.php">View Friends</a></p>
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

        <script src="createevent.js"></script>
        <script src="logout.js"></script>

    </body>
</html>