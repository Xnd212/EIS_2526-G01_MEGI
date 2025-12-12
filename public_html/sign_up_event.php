<?php
session_start();

// 1. Enable Error Reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Check Session
if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['ajax']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit;
    }
    header("Location: login.php");
    exit();
}

// 3. Database Connection
require_once __DIR__ . "/db.php";

$user_id = $_SESSION['user_id'];
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$action = $_GET['action'] ?? '';

// =======================================================
// LOGIC BLOCK A: HANDLE "LEAVE EVENT" (AJAX)
// =======================================================
if ($action === 'leave' && $event_id) {
    header('Content-Type: application/json');
    $sql = "DELETE FROM attends WHERE user_id = ? AND event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $event_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Left event']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}

// =======================================================
// LOGIC BLOCK B: HANDLE FORM SUBMISSION (POST)
// =======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Get JSON input
    $data = json_decode(file_get_contents('php://input'), true);
    
    $p_event_id = isset($data['event_id']) ? intval($data['event_id']) : 0;
    // If "I'm not bringing a collection" (0), set to NULL for DB
    $p_coll_id = (!empty($data['collection_id']) && $data['collection_id'] != 0) ? intval($data['collection_id']) : NULL;

    if (!$p_event_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Event ID']);
        exit;
    }

    // Insert into 'attends' table
    // ON DUPLICATE KEY UPDATE handles cases where user re-joins with different data
    $sql = "INSERT INTO attends (user_id, event_id, collection_id) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE collection_id = VALUES(collection_id)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $p_event_id, $p_coll_id);

    if ($stmt->execute()) {
        // === FIX IS HERE: Changed 'event_id' to 'id' ===
        echo json_encode(['status' => 'success', 'redirect' => 'eventpage.php?id=' . $p_event_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// =======================================================
// LOGIC BLOCK C: PREPARE HTML VIEW
// =======================================================
if (!$event_id) {
    header("Location: upcomingevents.php");
    exit();
}

// 1. Get User Name (Using 'username' column)
$u_sql = "SELECT username FROM user WHERE user_id = ?"; 
$u_stmt = $conn->prepare($u_sql);
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$u_res = $u_stmt->get_result();

if ($u_res->num_rows > 0) {
    $row = $u_res->fetch_assoc();
    $username_display = !empty($row['username']) ? $row['username'] : "User";
} else {
    $username_display = "User"; 
}

// 2. Get User's Collections
$c_sql = "SELECT collection_id, name FROM collection WHERE user_id = ?";
$c_stmt = $conn->prepare($c_sql);
$c_stmt->bind_param("i", $user_id);
$c_stmt->execute();
$c_res = $c_stmt->get_result();
$user_collections = $c_res->fetch_all(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Sign up for event</title>
  <link rel="stylesheet" href="sign_up_event.css" />
  <link rel="stylesheet" href="homepage.css" /> 
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
      <section class="event-signup-section">
        <h2 class="page-title">Sign up for Event</h2>

        <form id="eventSignUpForm" novalidate data-event-id="<?php echo $event_id; ?>">
          
          <div class="form-group">
            <label for="userName">User Name</label>
            <input type="text" id="userName" name="userName" readonly value="<?php echo htmlspecialchars($username_display); ?>" />
          </div>

          <div class="form-group">
            <label for="collection">Collection <span class="required">*</span></label>
            <select id="collection" name="collection" required>
              <option value="">Select your collection</option>
              <option value="0">I'm not bringing a collection</option>
              <?php foreach($user_collections as $col): ?>
                <option value="<?php echo $col['collection_id']; ?>">
                    <?php echo htmlspecialchars($col['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="participants">Number of Participants</label>
            <input type="number" id="participants" name="participants" value="1" min="1" />
          </div>

          <div class="form-group">
            <label>Do you want to receive notifications by other means? <span class="required">*</span></label>
            <div class="radio-group">
              <label><input type="radio" name="notify" value="yes" /> Yes</label>
              <label><input type="radio" name="notify" value="no" checked /> No</label>
            </div>
          </div>

          <div id="notificationFields" class="conditional-section hidden" style="display:none; margin-top:10px;">
            <label>Preferred method: <span class="required">*</span></label>
            <div class="radio-group">
              <label><input type="radio" name="notifyMethod" value="email" /> Email</label>
              <label><input type="radio" name="notifyMethod" value="phone" /> Phone</label>
            </div>

            <div id="emailField" class="conditional-input hidden" style="display:none; margin-top:10px;">
              <label for="notifyEmail">Email</label>
              <input type="email" id="notifyEmail" name="notifyEmail" placeholder="Enter your email" style="width:100%;" />
            </div>

            <div id="phoneField" class="conditional-input hidden" style="display:none; margin-top:10px;">
              <label for="notifyPhone">Phone Number</label>
              <input type="tel" id="notifyPhone" name="notifyPhone" placeholder="Enter your phone number" style="width:100%;" />
            </div>
          </div>

          <div class="form-group">
            <label for="comments">Comments or Notes</label>
            <textarea id="comments" name="comments" rows="4" placeholder="Add any comments about your participation..."></textarea>
          </div>

          <div class="form-group">
            <label>
              <input type="checkbox" id="terms" name="terms" required />
              I accept the terms and conditions. <span class="required">*</span>
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">Confirm Registration</button>
          </div>
          <p id="formMessage" class="form-message"></p>
        </form>

        <div id="summarySection" class="summary-section hidden" style="display:none; margin-top:20px; border:1px solid #ccc; padding:20px; border-radius:8px;">
          <h3>Registration Summary</h3>
          <div id="summaryContent" style="margin-bottom:20px; line-height:1.6;"></div>
          <div class="summary-actions" style="display:flex; gap:10px;">
            <button id="editRegistration" class="btn-secondary" style="padding:10px 20px; background:#ddd; border:none; border-radius:5px; cursor:pointer;">Edit Registration</button>
            <button id="finalConfirm" class="btn-primary" style="padding:10px 20px; background:#28a745; color:white; border:none; border-radius:5px; cursor:pointer;">Confirm Final Registration</button>
          </div>
        </div>

      </section>
    </div>
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
  
  <script src="homepage.js"></script>
  <script src="sign_up_event.js"></script>
  <script src="logout.js"></script>

</body>
</html>