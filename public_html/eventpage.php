<?php
// ==========================================
// 1. SETUP
// ==========================================
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get ID from URL
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header("Location: upcomingevents.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sie"; 
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ==========================================
// 2. FETCH EVENT DETAILS
// ==========================================
// Updated: Fetching 'place' instead of 'location'
$sql = "SELECT e.*, i.url as image_url
        FROM event e 
        LEFT JOIN image i ON e.image_id = i.image_id
        WHERE e.event_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Event not found.";
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();

// ==========================================
// 3. FETCH COLLECTIONS (Others bringing)
// ==========================================
$collections_sql = "SELECT 
                        c.collection_id, 
                        c.name as collection_name, 
                        u.username, 
                        i.url as collection_image_url
                    FROM attends a
                    JOIN user u ON a.user_id = u.user_id
                    JOIN collection c ON a.collection_id = c.collection_id
                    LEFT JOIN image i ON c.image_id = i.image_id
                    WHERE a.event_id = ? AND a.collection_id IS NOT NULL";

$col_stmt = $conn->prepare($collections_sql);
$col_stmt->bind_param("i", $event_id);
$col_stmt->execute();
$collections_result = $col_stmt->get_result();
$collections = $collections_result->fetch_all(MYSQLI_ASSOC);
$col_stmt->close();

// ==========================================
// 4. CHECK USER ATTENDANCE
// ==========================================
$check_sql = "SELECT * FROM attends WHERE user_id = ? AND event_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $event_id);
$check_stmt->execute();
$is_attending = $check_stmt->get_result()->num_rows > 0;
$check_stmt->close();

// ==========================================
// 5. HELPER LOGIC
// ==========================================
$event_date = date("d/m/Y", strtotime($event['date']));

// Video Logic
$video_id = null;
if (isset($event['teaser_url']) && !empty($event['teaser_url'])) {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $event['teaser_url'], $matches)) {
        $video_id = $matches[1];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | <?php echo htmlspecialchars($event['name']); ?></title>
  <link rel="stylesheet" href="homepage.css" />
  <link rel="stylesheet" href="eventpage.css" />
  <style>
      .map-container iframe {
          width: 100%;
          height: 300px;
          border: 0;
          border-radius: 8px;
      }
      .register-button.registered {
          background-color: #28a745; 
      }
  </style>
</head>
<body>

  <header>
    <a href="homepage.php" class="logo">
      <img src="images/TrallE_2.png" alt="logo" />
    </a>
    <div class="search-bar">
      <input type="text" placeholder="Search" />
    </div>
    <div class="icons">
        <button class="icon-btn" aria-label="Notificações" id="notification-btn">🔔</button>
        <div class="notification-popup" id="notification-popup">
            <div class="popup-header"><h3>Notifications <span>🔔</span></h3></div>
            <ul class="notification-list">
                 <li>No new notifications</li>
            </ul>
        </div>
        
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
      <div class="event-details-box">
        <h2><?php echo htmlspecialchars($event['name']); ?></h2>

        <div class="event-teaser-wrapper">
          <div class="event-image-wrapper">
            <?php 
            $imgSrc = !empty($event['image_url']) ? $event['image_url'] : 'images/default_event.png';
            ?>
            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Event Image" />
          </div>

          <div class="event-details-content">
            <div class="event-info">
              
              <p><strong>Date:</strong> <?php echo $event_date; ?></p>
              
              <?php if(isset($event['place'])): ?>
                <p><strong>Place:</strong> <?php echo htmlspecialchars($event['place']); ?></p>
              <?php endif; ?>

              <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            </div>
          </div>

          <?php if ($video_id): ?>
          <div class="video-thumbnail">
            <a href="<?php echo htmlspecialchars($event['teaser_url']); ?>" target="_blank">
              <img src="https://img.youtube.com/vi/<?php echo $video_id; ?>/hqdefault.jpg" alt="Video Teaser">
              <div class="play-button">▶</div>
            </a>
          </div>
          <?php endif; ?>
        </div>

        <?php if (count($collections) > 0): ?>
        <h3 class="collections-others">Collections others are bringing:</h3>
        <div class="collections-brought">
          <?php foreach ($collections as $collection): ?>
          <div class="collection-bring">
            <a href="collectionpage.php?id=<?php echo $collection['collection_id']; ?>">
              <?php $colImg = !empty($collection['collection_image_url']) ? $collection['collection_image_url'] : 'images/default_collection.png'; ?>
              <img src="<?php echo htmlspecialchars($colImg); ?>" alt="Collection">
              <p class="collection-name"><strong><?php echo htmlspecialchars($collection['collection_name']); ?></strong></p>
              <p class="collection-user"><?php echo htmlspecialchars($collection['username']); ?></p>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if(isset($event['place']) && !empty($event['place'])): ?>
            <h3 class="map-title">Where to find us:</h3>
            <div class="map-container">
              <iframe
                src="https://maps.google.com/maps?q=<?php echo urlencode($event['place']); ?>&t=&z=13&ie=UTF8&iwloc=&output=embed"
                allowfullscreen>
              </iframe>
            </div>
        <?php endif; ?>

        <div class="register-section">
          <div class="register-row">
            <?php if ($is_attending): ?>
              <p class="register-text">✅ You're signed up!</p>
              <a href="sign_up_event.php?id=<?php echo $event_id; ?>&action=leave" class="register-button registered">Leave Event</a>
            <?php else: ?>
              <p class="register-text">🎟️ Want to join? Sign up now!</p>
              <a href="sign_up_event.php?id=<?php echo $event_id; ?>&action=join" class="register-button">Sign up</a>
            <?php endif; ?>
          </div>
        </div>

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

  <script src="eventpage.js"></script>
  <script src="logout.js"></script>

</body>
</html>