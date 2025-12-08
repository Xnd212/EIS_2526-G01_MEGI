<?php
session_start();

// ===================== LOGIN / GUEST =====================
$isGuest = isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;

if (!$isGuest && isset($_SESSION['user_id'])) {
    $currentUserId = (int) $_SESSION['user_id'];
} else {
    $currentUserId = null; // guest
}

// ---------------- BD ----------------
require_once __DIR__ . "/db.php";

/* ==========================================================
   1. SORTING LOGIC
   ========================================================== */
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date-asc';

switch ($sort) {
    case 'alpha-asc':   $orderBy = "e.name ASC"; break;
    case 'alpha-desc':  $orderBy = "e.name DESC"; break;
    
    // Date sorting
    case 'date-asc':    $orderBy = "e.date ASC"; break;  // Sooner first (default)
    case 'date-desc':   $orderBy = "e.date DESC"; break; // Later first
    
    // Role sorting
    case 'role-org':    $orderBy = "role ASC, e.date ASC"; break; 
    case 'role-part':   $orderBy = "role DESC, e.date ASC"; break;

    default:            $orderBy = "e.date ASC";
}

/* ==========================================================
   2. BUSCAR PRÓXIMOS EVENTOS (SÓ SE HOUVER USER LOGADO)
   ========================================================== */

$events = [];

if ($currentUserId !== null) {

    $sqlEvents = "
        SELECT 
            e.event_id,
            e.name,
            e.date,
            e.theme,
            e.place,
            e.description,
            e.teaser_url,
            e.image_id,
            img.url AS event_image,

            CASE 
                WHEN e.user_id = ? THEN 'organizer'
                ELSE 'participant'
            END AS role,

            c.collection_id,
            c.name AS collection_name

        FROM event e
        LEFT JOIN image img ON e.image_id = img.image_id

        LEFT JOIN attends a 
            ON a.event_id = e.event_id
           AND a.user_id = ?

        LEFT JOIN collection c 
            ON a.collection_id = c.collection_id

        WHERE 
              (e.user_id = ? OR a.user_id IS NOT NULL)
          AND e.date >= CURDATE()   -- FUTUROS (inclui hoje)

        GROUP BY e.event_id
        ORDER BY $orderBy
    ";

    $stmt = $conn->prepare($sqlEvents);
    if (!$stmt) {
        die("Erro na query (prepare): " . $conn->error);
    }

    // 1. CASE (role), 2. JOIN attends, 3. WHERE (organizer)
    $stmt->bind_param("iii", $currentUserId, $currentUserId, $currentUserId);
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
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

            <a href="userpage.php" class="icon-btn">👤</a>

            <button class="icon-btn" id="logout-btn">🚪</button>

            <div class="notification-popup logout-popup" id="logout-popup">
                <div class="popup-header"><h3>Logout</h3></div>
                <p>Are you sure you want to log out?</p>
                <div class="logout-btn-wrapper">
                    <button class="logout-btn cancel-btn" id="cancel-logout">Cancel</button>
                    <button class="logout-btn confirm-btn" id="confirm-logout">Log out</button>
                </div>
            </div>
        </div>
    </header>

    <div class="main">
        <div class="content">

            <section class="event-history-section">
                
                <div class="events-header">
                    <h2 class="page-title" style="margin:0;">Upcoming Events</h2>

                    <?php if ($currentUserId !== null): ?>
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

                    <?php if ($currentUserId === null): ?>

                        <p style="margin-top:20px; font-size:18px;">
                            You are browsing as a guest.
                            <a href="login.php" style="color:#7a1b24; font-weight:600; text-decoration:none;">
                                Log in
                            </a>
                            to view and manage your upcoming events.
                        </p>

                    <?php elseif (empty($events)): ?>

                        <p>You have no upcoming events.</p>

                    <?php else: ?>

                        <?php foreach ($events as $ev): ?>

                            <?php
                            if (!empty($ev['event_image'])) {
                                $eventImg = $ev['event_image'];
                            } elseif (!empty($ev['teaser_url'])) {
                                $eventImg = $ev['teaser_url'];
                            } else {
                                $eventImg = "images/default_event.png";
                            }
                            ?>

                            <div class="event-card">

                                <div class="event-image" 
                                     style="background-image: url('<?php echo htmlspecialchars($eventImg); ?>');">
                                </div>

                                <div class="event-info">

                                    <div class="event-header-row">
                                        <h3 class="event-title">
                                            <strong>
                                                <a href="eventpage.php?id=<?php echo $ev['event_id']; ?>">
                                                    <?php echo htmlspecialchars($ev['name']); ?>
                                                </a>
                                            </strong>
                                        </h3>
                                    </div>

                                    <p><strong>Date:</strong> 
                                        <?php echo date('d/m/Y', strtotime($ev['date'])); ?>
                                    </p>

                                    <p><strong>Role:</strong> 
                                        <?php echo $ev['role'] === 'organizer' ? 'Organizer' : 'Participant'; ?>
                                    </p>

                                    <p><strong>Collection you are bringing:</strong>
                                        <?php if (!empty($ev['collection_id']) && !empty($ev['collection_name'])): ?>
                                            <a href="collectionpage.php?id=<?php echo (int)$ev['collection_id']; ?>">
                                                <?php echo htmlspecialchars($ev['collection_name']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#777;">None</span>
                                        <?php endif; ?>
                                    </p>

                                </div>
                            </div>

                        <?php endforeach; ?>
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
