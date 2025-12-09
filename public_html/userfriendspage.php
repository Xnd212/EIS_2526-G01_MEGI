<?php
session_start();

/* ================================
   CHECK LOGIN STATUS / GUEST MODE
   ================================ */
$isGuest = isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;

if ($isGuest || !isset($_SESSION['user_id'])) {
    $currentUserId = null; // guest: no friends list
} else {
    $currentUserId = (int) $_SESSION['user_id'];
}

/* DB CONNECTION */
require_once __DIR__ . "/db.php";

/* ==========================================
   SORTING LOGIC
   ========================================== */
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'alpha-asc';

switch ($sort) {
    case 'alpha-asc':   $orderBy = "u.username ASC"; break;
    case 'alpha-desc':  $orderBy = "u.username DESC"; break;
    
    // Sort by Friendship Date (start_date in friends table)
    case 'date-desc':   $orderBy = "f.start_date DESC"; break; // Newest friends first
    case 'date-asc':    $orderBy = "f.start_date ASC"; break;  // Oldest friends first

    default:            $orderBy = "u.username ASC";
}

/* FETCH FRIENDS — only if not guest */
$friends = [];

if ($currentUserId !== null) {

    $sql = "
        SELECT 
            u.user_id,
            u.username,
            u.image_id,
            u.country,
            u.email,
            img.url AS friend_image,
            f.start_date
        FROM friends f
        INNER JOIN user u ON f.friend_id = u.user_id
        LEFT JOIN image img ON u.image_id = img.image_id
        WHERE f.user_id = ?
        ORDER BY $orderBy
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $friends[] = $row;
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | My Friends</title>
  <link rel="stylesheet" href="userfriendspage.css">
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

        <section class="friends">
            <div class="friends-header">
                <h2>My Friends</h2>

                <button class="filter-toggle" id="filterToggle">
                    &#128269; Sort ▾
                </button>

                <div class="filter-menu" id="filterMenu">
                    <a href="?sort=alpha-asc">Name: A–Z</a>
                    <a href="?sort=alpha-desc">Name: Z–A</a>
                    <hr>
                    <a href="?sort=date-desc">Newest Friends</a>
                    <a href="?sort=date-asc">Oldest Friends</a>
                </div>
            </div>

            <div class="friends-grid">
                <?php if ($currentUserId === null): ?>

                    <!-- ESTADO: GUEST (mesmo estilo da collections) -->
                    <p style="text-align:left; margin-top:20px; margin-left:0; white-space:nowrap; font-size:18px;">
                        You are browsing as a guest.
                        <a href="login.php" style="color:#7a1b24; font-weight:600; text-decoration:none;">
                            Log in
                        </a>
                        to view friends.
                    </p>

                <?php elseif (empty($friends)): ?>

                    <!-- ESTADO: LOGADO MAS SEM AMIGOS (igual à collections) -->
                    <p style="text-align:left; margin-top:20px; margin-left:0; white-space:nowrap; font-size:18px;">
                        You don’t have any friends yet.
                        <a href="login.php" style="color:#7a1b24; font-weight:600; text-decoration:none;">
                            Find new friends
                        </a>.
                    </p>

                <?php else: ?>

                    <?php foreach ($friends as $friend): ?>
                        <?php
                            $imgSrc = !empty($friend['friend_image'])
                                ? $friend['friend_image']
                                : 'images/default_avatar.png';
                            
                            $since = !empty($friend['start_date']) 
                                ? date("M Y", strtotime($friend['start_date'])) 
                                : '';
                        ?>
                        <div class="friend">
                            <a href="friendpage.php?user_id=<?php echo $friend['user_id']; ?>">
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                                     alt="<?php echo htmlspecialchars($friend['username']); ?>">
                            </a>

                            <p class="friend-name">
                                <strong>
                                    <a href="friendpage.php?user_id=<?php echo $friend['user_id']; ?>">
                                        <?php echo htmlspecialchars($friend['username']); ?>
                                    </a>
                                </strong>
                            </p>
                            
                            <?php if ($since): ?>
                                <p style="font-size:0.8rem; color:#666;">Since <?php echo $since; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
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

<script>
    // Inline Script to handle the Filter Toggle
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
<script src="userfriendspage.js"></script>
<script src="homepage.js"></script>
<script src="logout.js"></script>

</body>
</html>
