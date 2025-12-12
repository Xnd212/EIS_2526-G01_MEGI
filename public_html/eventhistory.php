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
$sort = $_GET['sort'] ?? 'date-desc';

switch ($sort) {
    case 'alpha-asc':   $orderBy = "e.name ASC"; break;
    case 'alpha-desc':  $orderBy = "e.name DESC"; break;

    case 'date-desc':   $orderBy = "e.date DESC"; break;
    case 'date-asc':    $orderBy = "e.date ASC"; break;

    case 'role-org':    $orderBy = "role ASC, e.date DESC"; break;
    case 'role-part':   $orderBy = "role DESC, e.date DESC"; break;

    case 'rating-desc': $orderBy = "event_avg_rating DESC"; break;
    case 'rating-asc':  $orderBy = "event_avg_rating ASC"; break;

    default:            $orderBy = "e.date DESC";
}

/* ==========================================================
   2. FETCH EVENTS
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
            c.name AS collection_name,

            r.avg_rating   AS event_avg_rating,
            r.num_ratings  AS event_num_ratings

        FROM event e
        LEFT JOIN image img ON e.image_id = img.image_id
        LEFT JOIN attends a ON a.event_id = e.event_id AND a.user_id = ?
        LEFT JOIN collection c ON a.collection_id = c.collection_id
        LEFT JOIN (
            SELECT 
                event_id,
                AVG(rating) AS avg_rating,
                COUNT(DISTINCT collection_id) AS num_ratings
            FROM rating
            GROUP BY event_id
        ) r ON r.event_id = e.event_id

        WHERE (e.user_id = ? OR a.user_id IS NOT NULL)
          AND e.date <= CURDATE()

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
    <title>Trall-E | Event History</title>
    <link rel="stylesheet" href="eventhistory.css">
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
        <h2 class="page-title">Event History</h2>

        <?php if ($currentUserId !== null): ?>
            <button class="filter-toggle" id="filterToggle">&#128269; Sort ▾</button>

            <div class="filter-menu" id="filterMenu">
                <a href="?sort=date-desc">Date: Most Recent</a>
                <a href="?sort=date-asc">Date: Oldest</a>
                <hr>
                <a href="?sort=rating-desc">Rating: High–Low</a>
                <a href="?sort=rating-asc">Rating: Low–High</a>
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

            <p>You are browsing as a guest. <a href="login.php">Log in</a> to view your event history.</p>

        <?php elseif (empty($events)): ?>

            <p>You have no past events yet.</p>

        <?php else: ?>

            <?php foreach ($events as $ev): ?>

                <?php
                $eventImg = $ev["event_image"] ?: ($ev["teaser_url"] ?: "images/default_event.png");
                $avg   = $ev['event_avg_rating'];
                $nrat  = $ev['event_num_ratings'];
                $rounded = $avg ? round($avg) : 0;
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
                            <p><strong>Role:</strong> <?= $ev["role"] === "organizer" ? "Organizer" : "Participant" ?></p>

                            <?php if ($ev["collection_id"]): ?>
                                <p><strong>Collection brought:</strong>
                                    <a href="collectionpage.php?id=<?= $ev["collection_id"] ?>">
                                        <?= htmlspecialchars($ev["collection_name"]) ?>
                                    </a>
                                </p>
                            <?php endif; ?>

                        </div>

                        <!-- RATING -->
                        <div class="event-rating">

                            <?php if ($avg && $nrat > 0): ?>

                                <span class="event-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= ($i <= $rounded) ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </span>

                                <span class="event-rating-text">(<?= number_format($avg, 1); ?>/5, <?= $nrat ?> ratings)</span>

                            <?php else: ?>

                                <span class="event-rating-text no-ratings">No ratings</span>

                            <?php endif; ?>

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
<script src="eventhistory.js"></script>
<script src="logout.js"></script>

</body>
</html>
