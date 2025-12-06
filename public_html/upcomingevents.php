<?php
// ==========================================
// 1. SETUP & DATABASE
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/db.php";

// ==========================================
// 2. CHECK SESSION (GUEST VS LOGGED)
// ==========================================

// Se tiver user_id → logado
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $isGuest = false;
    $user_id = (int) $_SESSION['user_id'];
} else {
    // Sem user_id → guest
    $isGuest = true;
    $user_id = null;
}

// ==========================================
// 3. FETCH UPCOMING EVENTS (SÓ PARA LOGADO)
// ==========================================
$result = null;

if (!$isGuest) {
    $sql = "SELECT 
                e.event_id,
                e.name AS event_name,
                e.date,
                i.url AS event_image,
                c.collection_id,
                c.name AS collection_name,
                CASE 
                    WHEN e.user_id = ? THEN 'organizer'
                    ELSE 'participant'
                END AS role
            FROM event e
            LEFT JOIN attends a 
                ON a.event_id = e.event_id
               AND a.user_id = ?
            LEFT JOIN collection c 
                ON a.collection_id = c.collection_id
            LEFT JOIN image i 
                ON e.image_id = i.image_id
            WHERE 
                  (e.user_id = ? OR a.user_id = ?)
              AND e.date >= CURDATE()
            GROUP BY e.event_id
            ORDER BY e.date ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erro na query: " . $conn->error);
    }

    $stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
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
          color: green; 
          font-weight: bold;
      }
      .empty-state {
          text-align: center;
          padding: 40px;
          color: #666;
      }
      .empty-state a {
          color: #7a1b24;
          font-weight: 600;
          text-decoration: none;
      }
      .empty-state a:hover {
          text-decoration: underline;
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
      <section class="event-history-section">
        <h2 class="page-title">Upcoming Events</h2>

        <div class="event-list">

          <?php if ($isGuest): ?>
              <!-- GUEST VIEW -->
              <div class="empty-state">
                  <p>You are browsing as a guest.</p>
                  <p>
                      <a href="login.php">Log in</a> to view, join, or create upcoming events.
                  </p>
              </div>

          <?php else: ?>
              <?php if (!$result || $result->num_rows === 0): ?>
                  <!-- USER LOGADO MAS SEM EVENTOS -->
                  <div class="empty-state">
                      <p>You have no upcoming events.</p>
                      <p>
                          <a href="createevent.php">Create an event</a> or browse existing ones.
                      </p>
                  </div>
              <?php else: ?>
                  <!-- USER LOGADO COM EVENTOS -->
                  <?php while ($row = $result->fetch_assoc()): ?>
                      <?php 
                          $dateFormatted = date("d/m/Y", strtotime($row['date']));
                          $bgImage = !empty($row['event_image']) ? $row['event_image'] : 'images/default_event.png';
                      ?>
                      
                      <div class="event-card">
                          <div class="event-image" style="background-image: url('<?php echo htmlspecialchars($bgImage); ?>');"></div>
                          
                          <div class="event-info">
                              <h3>
                                  <a href="eventpage.php?id=<?php echo $row['event_id']; ?>">
                                      <strong><?php echo htmlspecialchars($row['event_name']); ?></strong>
                                  </a>
                              </h3>
                              
                              <p><strong>Date:</strong> <?php echo $dateFormatted; ?></p>
                              
                              <p class="rating">
                                  <strong>Status:</strong>
                                  <span class="status-text">
                                      <?php echo ($row['role'] === 'organizer') ? 'Organizer' : 'Going'; ?>
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
              <?php endif; ?>
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

  <script src="upcomingevents.js"></script>
  <script src="logout.js"></script>

</body>
</html>
