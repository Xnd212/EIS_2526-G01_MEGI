<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get event_id from URL
if (!isset($_GET['event_id'])) {
    header("Location: upcomingevents.php");
    exit();
}

$event_id = intval($_GET['event_id']);

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sie"; 
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

// Get event details
$stmt = $conn->prepare("SELECT e.*, u.username as creator_username, i.url as image_url
                        FROM event e 
                        JOIN user u ON e.user_id = u.user_id 
                        LEFT JOIN image i ON e.image_id = i.image_id
                        WHERE e.event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Evento não encontrado";
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();

// Get collections being brought to this event
$collections_stmt = $conn->prepare("SELECT a.collection_id, c.name as collection_name, 
                                            u.username, i.url as collection_image_url
                                     FROM attends a
                                     JOIN user u ON a.user_id = u.user_id
                                     JOIN collection c ON a.collection_id = c.collection_id
                                     LEFT JOIN image i ON c.image_id = i.image_id
                                     WHERE a.event_id = ?");
$collections_stmt->bind_param("i", $event_id);
$collections_stmt->execute();
$collections_result = $collections_stmt->get_result();
$collections = $collections_result->fetch_all(MYSQLI_ASSOC);
$collections_stmt->close();

// Check if current user is already signed up
$check_attendance = $conn->prepare("SELECT * FROM attends WHERE user_id = ? AND event_id = ?");
$check_attendance->bind_param("ii", $_SESSION['user_id'], $event_id);
$check_attendance->execute();
$is_attending = $check_attendance->get_result()->num_rows > 0;
$check_attendance->close();

// Format date
$event_date = date("d/m/Y", strtotime($event['date']));

// Get video ID from teaser_url if it's a YouTube link
$video_id = null;
if (!empty($event['teaser_url'])) {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $event['teaser_url'], $matches)) {
        $video_id = $matches[1];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | <?php echo htmlspecialchars($event['name']); ?></title>
  <link rel="stylesheet" href="homepage.css" />
  <link rel="stylesheet" href="eventpage.css" />
</head>
<body>

  <!-- ===== Header ===== -->
  <header>
    <a href="homepage.php" class="logo">
      <img src="images\TrallE_2.png" alt="logo" />
    </a>
    <div class="search-bar">
      <input type="text" placeholder="Search" />
    </div>
    <div class="icons">
            <!-- Botão de notificações -->
        <button class="icon-btn" aria-label="Notificações" id="notification-btn">🔔</button>
            <div class="notification-popup" id="notification-popup">
                
            <div class="popup-header">
            <h3>Notifications <span>🔔</span></h3>
            </div>
                
            <hr class="popup-divider">
            <ul class="notification-list">
                <li><strong>Ana_Rita_Lopes</strong> added 3 new items to the Pokémon Cards collection.</li>
                <li><strong>Tomás_Freitas</strong> created a new collection: Vintage Stamps.</li>
                <li><strong>David_Ramos</strong> updated his Funko Pop inventory.</li>
                <li><strong>Telmo_Matos</strong> joined the event: Iberanime Porto 2025.</li>
                
                <li><strong>Marco_Pereira</strong> started following your Panini Stickers collection.</li>
                <li><strong>Ana_Rita_Lopes</strong> added 1 new items to the Pokémon Champion’s Path collection.</li>
                <li><strong>Telmo_Matos</strong> added added 3 new items to the Premier League Stickers collection.</li>
                <li><strong>Marco_Pereira</strong> created a new event: Card Madness Meetup.</li>
            </ul>

            <a href="#" class="see-more-link">+ See more</a>
            </div>
           
        <a href="userpage.php" class="icon-btn" aria-label="Perfil">👤</a>
        
            <!-- Logout -->
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

  <!-- ===== Main Content ===== -->
  <div class="main">
    <div class="content">
      <div class="event-details-box">
        <h2><?php echo htmlspecialchars($event['name']); ?></h2>

        <div class="event-teaser-wrapper">

          <div class="event-image-wrapper">
            <?php if (!empty($event['image_url'])): ?>
              <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['name']); ?>" />
            <?php else: ?>
              <img src="images/default_event.png" alt="Event Image" />
            <?php endif; ?>
          </div>

          <div class="event-details-content">
            <div class="event-info">
              <p><strong>Creator:</strong> <?php echo htmlspecialchars($event['creator_username']); ?></p>
              <p><strong>Theme:</strong> <?php echo htmlspecialchars($event['theme']); ?></p>
              <p><strong>Date:</strong> <?php echo $event_date; ?></p>
              <p><strong>Place:</strong> <?php echo htmlspecialchars($event['place']); ?></p>
              <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description']); ?></p>
            </div>
          </div>

          <!-- TEASER VÍDEO -->
          <?php if ($video_id): ?>
          <div class="video-thumbnail">
            <a href="<?php echo htmlspecialchars($event['teaser_url']); ?>" target="_blank">
              <img src="https://img.youtube.com/vi/<?php echo $video_id; ?>/hqdefault.jpg" alt="Video Teaser">
              <div class="play-button">▶</div>
            </a>
          </div>
          <?php endif; ?>

        </div>

        <!-- COLEÇÕES -->
        <?php if (count($collections) > 0): ?>
        <h3 class="collections-others">Collections others are bringing:</h3>
        <div class="collections-brought">
          <?php foreach ($collections as $collection): ?>
          <div class="collection-bring">
            <a href="collectionpage.php?collection_id=<?php echo $collection['collection_id']; ?>">
              <?php if (!empty($collection['collection_image_url'])): ?>
                <img src="<?php echo htmlspecialchars($collection['collection_image_url']); ?>" alt="<?php echo htmlspecialchars($collection['collection_name']); ?>">
              <?php else: ?>
                <img src="images/default_collection.png" alt="Collection">
              <?php endif; ?>
              <p class="collection-name"><strong><?php echo htmlspecialchars($collection['collection_name']); ?></strong></p>
              <p class="collection-user"><?php echo htmlspecialchars($collection['username']); ?></p>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- MAPA -->
        <h3 class="map-title">Where to find us:</h3>
        <div class="map-container">
          <iframe
            src="https://www.google.com/maps?q=<?php echo urlencode($event['place']); ?>&output=embed"
            allowfullscreen
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>

        <!-- INSCRIÇÃO -->
        <div class="register-section">
          <div class="register-row">
            <?php if ($is_attending): ?>
              <p class="register-text">✅ You're signed up for this event!</p>
              <a href="sign_up_event.php?event_id=<?php echo $event_id; ?>" class="register-button registered">Manage Registration</a>
            <?php else: ?>
              <p class="register-text">🎟️ Want to join? Sign up now!</p>
              <a href="sign_up_event.php?event_id=<?php echo $event_id; ?>" class="register-button">Sign up</a>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>

    <!-- ===== Sidebar ===== -->
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
