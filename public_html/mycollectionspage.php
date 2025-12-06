<?php
session_start();

/* ================================
   CHECK LOGIN STATUS
   ================================ */
$isGuest = isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;

if ($isGuest || !isset($_SESSION['user_id'])) {
    // guest ou não logado -> não tem coleções próprias
    $currentUserId = null;
} else {
    $currentUserId = (int) $_SESSION['user_id'];
}

// ====== LIGAÇÃO À BASE DE DADOS ======
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

// ====== BUSCAR COLEÇÕES DO USER LOGADO ======
$sql = "SELECT 
            c.collection_id,
            c.name,
            c.starting_date,
            i.url
        FROM collection c
        LEFT JOIN image i ON c.image_id = i.image_id
        WHERE c.user_id = ?
        ORDER BY c.starting_date DESC";

$collections = [];

if ($currentUserId !== null) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $collections[] = $row;
    }

    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Trall-E | My Collections</title>
    <link rel="stylesheet" href="mycollectionspage.css">

</head>

<body>    
    <!-- =========================== HEADER ============================ -->
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
            <div class="collections-and-friends">
                <section class="collections">
                    <div class="collections-header">
                        <h2>My Collections</h2>
                        <button class="filter-toggle" id="filterToggle" aria-haspopup="true" aria-expanded="false">
                            &#128269; Filter ▾
                        </button>
                        <div class="filter-menu" id="filterMenu">
                            <button type="button" data-sort="alpha-asc">Name: A–Z</button>
                            <button type="button" data-sort="alpha-desc">Name: Z–A</button>
                            <hr>
                            <button type="button" data-sort="price-asc">Price: Low–High</button>
                            <button type="button" data-sort="price-desc">Price: High–Low</button>
                            <hr>
                            <button type="button" data-sort="updated-desc">Last updated: New</button>
                            <button type="button" data-sort="updated-asc">Last updated: Old</button>
                            <hr>
                            <button type="button" data-sort="created-desc">Created: New</button>
                            <button type="button" data-sort="created-asc">Created: Old</button>
                            <hr>
                            <button type="button" data-sort="items-desc">Items: Most</button>
                            <button type="button" data-sort="items-asc">Items: Fewest</button>
                        </div>
                    </div>

                    <div class="collection-grid">
                        <?php if ($currentUserId === null): ?>
                            <p style="text-align:left; margin-top:20px; margin-left:0; white-space:nowrap; font-size:18px;">
                                You are browsing as a guest. 
                                <a href="login.php" style="color:#7a1b24; font-weight:600; text-decoration:none;">
                                    Log in
                                </a>
                                to create and view your own collections.
                            </p>

                        <?php elseif (empty($collections)): ?>
                            <div class="empty-message">
                                <h3>No collections yet</h3>
                                <p>You haven’t created any collections so far.</p>
                                <a href="collectioncreation.php" class="btn-primary">Create your first collection</a>
                            </div>

                        <?php else: ?>
                            <?php foreach ($collections as $row): ?>
                                <?php
                                    $img = !empty($row['url']) ? $row['url'] : 'images/default.png';
                                    $date = !empty($row['starting_date'])
                                          ? date("d/m/Y", strtotime($row['starting_date']))
                                          : '';
                                ?>
                                <div class="collection-card">
                                    <a href="collectionpage.php?id=<?php echo (int)$row['collection_id']; ?>">
                                        <img src="<?php echo htmlspecialchars($img); ?>" 
                                             alt="<?php echo htmlspecialchars($row['name']); ?>">
                                        <p><strong><?php echo htmlspecialchars($row['name']); ?></strong></p>
                                        <?php if ($date): ?>
                                            <span class="last-updated">Last updated: <?php echo $date; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- ===== Right Sidebar ===== -->
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

    <script src="mycollectionspage.js"></script>
    <script src="logout.js"></script>
</body>
</html>
