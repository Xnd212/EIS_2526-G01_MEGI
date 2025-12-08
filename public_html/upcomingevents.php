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
// 2. CHECK SESSION
// ==========================================
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $isGuest = false;
    $user_id = (int) $_SESSION['user_id'];
} else {
    $isGuest = true;
    $user_id = null;
}

// ==========================================
// 3. SORTING LOGIC
// ==========================================
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date-asc';

switch ($sort) {
    case 'alpha-asc':   $orderBy = "e.name ASC"; break;
    case 'alpha-desc':  $orderBy = "e.name DESC"; break;
    
    // Date sorting
    case 'date-asc':    $orderBy = "e.date ASC"; break;  // Sooner first (Default)
    case 'date-desc':   $orderBy = "e.date DESC"; break; // Later first
    
    // Role sorting (Organizer first or Participant first)
    case 'role-org':    $orderBy = "role ASC, e.date ASC"; break; // 'organizer' comes before 'participant' alphabetically? No, O comes before P.
    case 'role-part':   $orderBy = "role DESC, e.date ASC"; break;

    default:            $orderBy = "e.date ASC";
}

// ==========================================
// 4. FETCH UPCOMING EVENTS
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
              AND e.date > CURDATE()  -- '>= include today' logic'
            GROUP BY e.event_id
            ORDER BY $orderBy";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erro na query: " . $conn->error);
    }

    // Parameters: 1. Role Check, 2. Attends Join, 3. Where Clause (Creator), 4. Where Clause (Attendee)
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
          text-align: center; padding: 40px; color: #666;
      }
      .empty-state a {
          color: #7a1b24; font-weight: 600; text-decoration: none;
      }
      .empty-state a:hover { text-decoration: underline; }

      /* Filter Styles */
      .events-header {
          display: flex; align-items: center; justify-content: space-between;
          margin-bottom: 20px; position: relative;
      }
      .filter-toggle {
          display: inline-flex; align-items: center; gap: 0.25rem;
          padding: 0.35rem 0.8rem; border-radius: 999px;
          border: 1px solid #b54242; background-color: #fbecec;
          font-size: 0.9rem; cursor: pointer; color: #b54242;
      }
      .filter-menu {
          position: absolute; top: 100%; right: 0; margin-top: 5px;
          background: white; border-radius: 10px; border: 1px solid #ddd;
          box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 200px;
          display: none; z-index: 1000;
      }
      .filter-menu.show { display: block; }
      .filter-menu a {
          display: block; padding: 10px 15px; text-decoration: none;
          color: #333; font-size: 0.9rem;
      }
      .filter-menu a:hover { background: #fbecec; color: #b54242; }
      .filter-menu hr { margin: 0; border: 0; border-top: 1px solid #eee; }
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
        
        <div class="events-header">
            <h2 class="page-title" style="margin:0;">Upcoming Events</h2>

            <?php if (!$isGuest): ?>
                <button class="filter-toggle" id="filterToggle">
                    &#128269; Sort ▾
                </button>

                <div class="filter-menu" id="filterMenu">
                    <a href="?sort=date-asc">Date: Sooner</a>
                    <a href="?sort=date-desc">Date: Later</a>
                    <hr>
                    <a href="?sort=role-org">Show: Organizer First</a>
                    <a href="?sort=role-part">Show: Participant First</a>
                    <hr>
                    <a href="?sort=alpha-asc">Name: A–Z</a>
                    <a href="?sort=alpha-desc">Name: Z–A</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="event-list">

          <?php if ($isGuest): ?>
              <div class="empty-state">
                  <p>You are browsing as a guest.</p>
                  <p>
                      <a href="login.php">Log in</a> to view, join, or create upcoming events.
                  </p>
              </div>

          <?php else: ?>
              <?php if (!$result || $result->num_rows === 0): ?>
                  <div class="empty-state">
                      <p>You have no upcoming events.</p>
                      <p>
                          <a href="createevent.php">Create an event</a> or browse existing ones.
                      </p>
                  </div>
              <?php else: ?>
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

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('filterToggle');
        const menu = document.getElementById('filterMenu');
        
        if(toggle && menu) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('show');
            });
            
            document.addEventListener('click', function(e) {
                if(!menu.contains(e.target) && !toggle.contains(e.target)) {
                    menu.classList.remove('show');
                }
            });
        }
    });
  </script>
    <script src="upcomingevents.js"></script>
  <script src="logout.js"></script>

</body>
</html>