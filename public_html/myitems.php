<?php
// ====== 1. SETUP & DEBUG ======
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// ====== 2. DATABASE CONNECTION ======
require_once __DIR__ . "/db.php";

// ====== 3. CHECK USER SESSION / GUEST ======
$isGuest = isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;

if ($isGuest || !isset($_SESSION['user_id'])) {
    // guest ou não logado
    $user_id = null;
} else {
    $user_id = (int) $_SESSION['user_id'];
}

// ====== 4. FETCH ITEMS (Using 'contains' table) ======
$items = [];

if ($user_id !== null) {
    $sql = "SELECT 
                i.item_id,
                i.name AS item_name,
                i.price,
                img.url AS item_url,
                c.name AS collection_name
            FROM item i
            JOIN contains cn ON i.item_id = cn.item_id
            JOIN collection c ON cn.collection_id = c.collection_id
            LEFT JOIN image img ON i.image_id = img.image_id
            WHERE c.user_id = ?
            GROUP BY i.item_id
            ORDER BY i.name ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Trall-E | My Items</title>
        <link rel="stylesheet" href="myitems.css">
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
                <button class="icon-btn" aria-label="Notificações" id="notification-btn">🔔</button>
                <div class="notification-popup" id="notification-popup">
                    <div class="popup-header">
                        <h3>Notifications <span>🔔</span></h3>
                    </div>
                    <hr class="popup-divider">
                    <ul class="notification-list">
                        <li><strong>Ana_Rita</strong> added 3 new items...</li>
                    </ul>
                    <a href="#" class="see-more-link">+ See more</a>
                </div>

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
                            <h2>My Items</h2>

                            <button class="filter-toggle" id="filterToggle" aria-haspopup="true" aria-expanded="false">
                                &#128269; Filter ▾
                            </button>

                            <div class="filter-menu" id="filterMenu">
                                <button type="button" data-sort="alpha-asc">Name: A–Z</button>
                                <button type="button" data-sort="alpha-desc">Name: Z–A</button>
                                <hr>
                                <button type="button" data-sort="price-asc">Price: Low–High</button>
                                <button type="button" data-sort="price-desc">Price: High–Low</button>
                            </div>
                        </div>

                        <div class="item-grid" id="itemGrid">
                            <?php if ($user_id === null): ?>

                                <p style="margin-top:20px; text-align:left; white-space:nowrap; font-size:18px;">
                                    You are browsing as a guest. 
                                    <a href="login.php" style="color:#7a1b24; font-weight:600; text-decoration:none;">
                                        Log in
                                    </a>
                                    to view and manage your own items.
                                </p>

                            <?php elseif (empty($items)): ?>

                                <p style="margin-top:20px; text-align:left; white-space:nowrap; font-size:18px;">
                                    You don’t have any items yet. 
                                    <a href="itemcreation.php" style="color:#7a1b24; font-weight:600; text-decoration:none;">
                                        Create your first item
                                    </a>.
                                </p>

                            <?php else: ?>
                                <?php foreach ($items as $row): ?>
                                    <?php
                                        $img   = !empty($row['item_url']) ? htmlspecialchars($row['item_url']) : 'images/default_item.png';
                                        $price = number_format($row['price'], 2);
                                    ?>
                                    <div class="item-card"
                                         data-name="<?php echo htmlspecialchars($row['item_name']); ?>"
                                         data-price="<?php echo $row['price']; ?>">
                                        <a href="itempage.php?id=<?php echo $row['item_id']; ?>" style="text-decoration:none; color:inherit;">
                                            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($row['item_name']); ?>">

                                            <p class="item-title" style="margin-top:10px;">
                                                <strong><?php echo htmlspecialchars($row['item_name']); ?></strong>
                                            </p>

                                            <p class="item-price" style="color:#007bff;">
                                                €<?php echo $price; ?>
                                            </p>

                                            <?php if (!empty($row['collection_name'])): ?>
                                                <span class="item-collection" style="font-size:0.8em; color:#666;">
                                                    Collection: <?php echo htmlspecialchars($row['collection_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </a>
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
        </div>

        <script src="myitems.js"></script>
        <script src="logout.js"></script>
    </body>
</html>
