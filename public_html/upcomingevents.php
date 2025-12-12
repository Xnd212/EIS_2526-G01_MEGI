<?php
session_start();

// ===================== LOGIN / GUEST =====================
$isGuest = isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;

if (!$isGuest && isset($_SESSION['user_id'])) {
    $currentUserId = (int) $_SESSION['user_id'];
} else {
    $currentUserId = null;
}

// ---------------- BD ----------------
require_once __DIR__ . "/db.php";

/* ==========================================================
   1. SORTING LOGIC
   ========================================================== */
$sort = $_GET['sort'] ?? 'date-asc';

switch ($sort) {
    case 'alpha-asc':   $orderBy = "e.name ASC"; break;
    case 'alpha-desc':  $orderBy = "e.name DESC"; break;

    case 'date-asc':    $orderBy = "e.date ASC"; break;
    case 'date-desc':   $orderBy = "e.date DESC"; break;

    case 'role-org':    $orderBy = "role ASC, e.date ASC"; break;
    case 'role-part':   $orderBy = "role DESC, e.date ASC"; break;

    default:            $orderBy = "e.date ASC";
}

/* ==========================================================
   2. FETCH UPCOMING EVENTS
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
        LEFT JOIN attends a ON a.event_id = e.event_id AND a.user_id = ?
        LEFT JOIN collection c ON a.collection_id = c.collection_id

        WHERE (e.user_id = ? OR a.user_id IS NOT NULL)
          AND e.date >= CURDATE()

        GROUP BY e.event_id
        ORDER BY $orderBy
    ";

    $stmt = $conn->prepare($sqlEvents);
    $stmt->bind_param("iii", $currentUserId, $currentUserId, $currentUserId);
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trall-E | Upcoming Events</title>
    <link rel="stylesheet" href="upcomingevents.css">
    <link rel="stylesheet" href="calendar_popup.css" />
</head>

<body>

<header>
    <a href="homepage.php" class="logo">
        <img src="images/TrallE_2.png" alt="logo">
    </a>

    <div class="search-bar">
        <form action="search.php" method="GET">
            <input type="text" name="q" placeholder="Search for friends, collections, events, items..." required>
        </form>
    </div>

    <div class="icons">
        <?php include __DIR__ . '/calendar_popup.php'; ?>
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
        <h2 class="page-title">Upcoming Events</h2>

        <?php if ($currentUserId !== null): ?>
            <button class="filter-toggle" id="filterToggle">&#128269; Sort ▾</button>

            <div class="filter-menu" id="filterMenu">
                <a href="?sort=date-asc">Date: Sooner</a>
                <a href="?sort=date-desc">Date: Later</a>
                <hr>
                <a href="?sort=role-org">Organizer First</a>
                <a href="?sort=role-part">Participant First</a>
                <hr>
                <a href="?sort=alpha-asc">Name: A–Z</a>
                <a href="?sort=alpha-desc">Name: Z–A</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="event-list">

        <?php if ($currentUserId === null): ?>

            <p>You are browsing as a guest. 
                <a href="login.php">Log in</a> 
                to view your upcoming events.
            </p>

        <?php elseif (empty($events)): ?>

            <p>You have no upcoming events.</p>

        <?php else: ?>

            <?php foreach ($events as $ev): ?>

                <?php
                $eventImg = $ev["event_image"] ?: ($ev["teaser_url"] ?: "images/default_event.png");
                ?>

                <div class="event-card">
                    
                    <div class="event-content">

                        <!-- IMAGE -->
                        <div class="event-image"
                             style="background-image: url('<?= htmlspecialchars($eventImg); ?>');">
                        </div>

                        <!-- INFO -->
                        <div class="event-info">

                            <h3 class="event-title">
                                <a href="eventpage.php?id=<?= $ev['event_id']; ?>">
                                    <?= htmlspecialchars($ev['name']); ?>
                                </a>
                            </h3>

                            <p><strong>Date:</strong> <?= date("d/m/Y", strtotime($ev["date"])) ?></p>

                            <p><strong>Role:</strong> 
                                <?= $ev["role"] === "organizer" ? "Organizer" : "Participant" ?>
                            </p>

                            <p><strong>Collection you are bringing:</strong>
                                <?php if ($ev["collection_id"]): ?>
                                    <a href="collectionpage.php?id=<?= $ev["collection_id"] ?>">
                                        <?= htmlspecialchars($ev["collection_name"]) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color:#777;">None</span>
                                <?php endif; ?>
                            </p>

                        </div>

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

<script src="homepage.js"></script>
<script src="upcomingevents.js"></script>
<script src="logout.js"></script>

</body>
</html>
