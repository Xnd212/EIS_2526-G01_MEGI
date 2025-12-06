<?php
// ==========================================
// 1. SETUP & DATABASE
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check Session
if (isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
} else {
    // se quiseres mesmo obrigar login, podes redirecionar aqui
    // header("Location: login.php"); exit();
    $user_id = 1; // fallback de testes
}

// ==========================================
// 2. FETCH UPCOMING EVENTS (GLOBAL FEED)
// ==========================================
// Mostra TODOS os eventos futuros e calcula o papel do user logado
$sql = "
    SELECT 
        e.event_id,
        e.name AS event_name,
        e.date,
        i.url AS event_image,
        a.collection_id,
        c.name AS collection_name,
        CASE 
            WHEN e.user_id = ? THEN 'organizer'
            WHEN a.user_id IS NOT NULL THEN 'participant'
            ELSE 'none'
        END AS role
    FROM event e
    LEFT JOIN attends a 
        ON a.event_id = e.event_id
       AND a.user_id = ?
    LEFT JOIN collection c 
        ON a.collection_id = c.collection_id
    LEFT JOIN image i 
        ON e.image_id = i.image_id
    WHERE e.date >= CURDATE()
    ORDER BY e.date ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query error: " . $conn->error);
}
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Upcoming Events</title>
  <link rel="stylesheet" href="upcomingevents.css" />
  <style>
      .event-image {
          background-size: cover;
          background-position: center;
          background-color: #ddd;
      }
      .status-text {
          font-weight: bold;
      }
      .status-text.organizer {
          color: #b54242;
      }
      .status-text.going {
          color: green;
      }
      .status-text.none {
          color: #777;
      }
      .empty-state {
          text-align: center;
          padding: 40px;
          color: #666;
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
        <section class="event-history-section">
            <h2 class="page-title">Upcoming Events</h2>

            <div class="event-list">

                <?php if ($result && $result->num_rows > 0): ?>
                    
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php 
                            $dateFormatted = date("d/m/Y", strtotime($row['date']));
                            $bgImage = !empty($row['event_image']) ? $row['event_image'] : 'images/default_event.png';

                            // definir texto e classe do status
                            if ($row['role'] === 'organizer') {
                                $statusText  = 'Organizer';
                                $statusClass = 'organizer';
                            } elseif ($row['role'] === 'participant') {
                                $statusText  = 'Going';
                                $statusClass = 'going';
                            } else {
                                $statusText  = 'Not joined';
                                $statusClass = 'none';
                            }
                        ?>
                        
                        <div class="event-card">
                            <div class="event-image" 
                                 style="background-image: url('<?php echo htmlspecialchars($bgImage); ?>');"></div>

                            <div class="event-info">
                                <h3>
                                    <a href="eventpage.php?id=<?php echo $row['event_id']; ?>">
                                        <strong><?php echo htmlspecialchars($row['event_name']); ?></strong>
                                    </a>
                                </h3>

                                <p><strong>Date:</strong> <?php echo $dateFormatted; ?></p>

                                <p class="rating">
                                    <strong>Status:</strong>
                                    <span class="status-text <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </p>

                                <p><strong>Collection you are bringing:</strong>
                                    <?php if (!empty($row['collection_name'])): ?>
                                        <a href="collectionpage.php?id=<?php echo $row['collection_id']; ?>">
                                            <?php echo htmlspecialchars($row['collection_name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#777;">None</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="empty-state">
                        <p>There are no upcoming events.</p>
                        <p>
                            <a href="createevent.php" style="color:blue;">Create an event</a> 
                            or check back later.
                        </p>
                    </div>

                <?php endif; ?>

            </div>
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
            <p><a href="userfriendspage.php"> View Friends</a></p>
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

<script src="upcomingevents.js"></script>
<script src="logout.js"></script>

</body>
</html>
