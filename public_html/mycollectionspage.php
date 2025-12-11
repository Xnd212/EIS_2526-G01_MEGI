<?php
session_start();

/* ================================
   CHECK LOGIN STATUS
   ================================ */
$isGuest = isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;

if ($isGuest || !isset($_SESSION['user_id'])) {
    // guest or not logged in -> no own collections
    $currentUserId = null;
} else {
    $currentUserId = (int) $_SESSION['user_id'];
}

// ====== DATABASE CONNECTION ======
require_once __DIR__ . "/db.php";

// ====== SORTING LOGIC ======
// 1. Get sort parameter from URL (default to Date Newest)
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'updated-desc';

// 2. Define SQL Order based on parameter
switch ($sort) {
    case 'alpha-asc':   $orderBy = "c.name ASC"; break;
    case 'alpha-desc':  $orderBy = "c.name DESC"; break;
    
    // Sort by Total Price (Calculated in Query)
    case 'price-asc':   $orderBy = "total_price ASC"; break;
    case 'price-desc':  $orderBy = "total_price DESC"; break;
    
    // Sort by Collection Date
    case 'updated-asc': 
    case 'created-asc': $orderBy = "c.starting_date ASC"; break;
    case 'updated-desc':
    case 'created-desc':$orderBy = "c.starting_date DESC"; break;
    
    // Sort by Item Count (Calculated in Query)
    case 'items-asc':   $orderBy = "item_count ASC"; break;
    case 'items-desc':  $orderBy = "item_count DESC"; break;

    // NEW: Sort by Recency of Item Addition (using Item ID)
    case 'recent-item-desc': $orderBy = "latest_item_id DESC"; break; 
    case 'recent-item-asc':  $orderBy = "latest_item_id ASC"; break;
    
    default:            $orderBy = "c.starting_date DESC";
}

// ====== FETCH COLLECTIONS ======
// We use LEFT JOIN to calculate item counts, sum prices, and find newest item ID
$sql = "SELECT 
            c.collection_id,
            c.name,
            c.starting_date,
            i.url,
            COUNT(ct.item_id) AS item_count,
            COALESCE(SUM(it.price), 0) AS total_price,
            COALESCE(MAX(ct.item_id), 0) AS latest_item_id
        FROM collection c
        LEFT JOIN image i ON c.image_id = i.image_id
        LEFT JOIN contains ct ON c.collection_id = ct.collection_id
        LEFT JOIN item it ON ct.item_id = it.item_id
        WHERE c.user_id = ?
        GROUP BY c.collection_id
        ORDER BY $orderBy";

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
    <link rel="stylesheet" href="calendar_popup.css" />
    <style>
        /* Inline styles for filter menu links */
        .filter-menu a {
            display: block;
            width: 100%;
            text-align: left;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            text-decoration: none;
            color: #333;
            cursor: pointer;
            box-sizing: border-box;
        }
        .filter-menu a:hover {
            background-color: #fbecec;
            color: #b54242;
        }
        .filter-menu hr {
            margin: 0.2rem 0;
            border: 0;
            border-top: 1px solid #eee;
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
            <div class="collections-and-friends">
                <section class="collections">
                    
                    <div class="collections-header">
                        <h2>My Collections</h2>
                        <button class="filter-toggle" id="filterToggle" aria-haspopup="true" aria-expanded="false">
                            &#128269; Sort ▾
                        </button>
                        
                        <div class="filter-menu" id="filterMenu">
                            <a href="?sort=alpha-asc">Name: A–Z</a>
                            <a href="?sort=alpha-desc">Name: Z–A</a>
                            <hr>
                            <a href="?sort=price-asc">Price: Low–High</a>
                            <a href="?sort=price-desc">Price: High–Low</a>
                            <hr>
                            <a href="?sort=updated-desc">Date: Newest</a>
                            <a href="?sort=updated-asc">Date: Oldest</a>
                            <hr>
                            <a href="?sort=items-desc">Items: Most</a>
                            <a href="?sort=items-asc">Items: Fewest</a>
                            <hr>
                            <a href="?sort=recent-item-desc">Item Added: Recent</a>
                            <a href="?sort=recent-item-asc">Item Added: Oldest</a>
                        </div>
                    </div>

                    <div class="collection-grid" id="collectionGrid">
                        <?php if ($currentUserId === null): ?>
                            <p style="margin-top:20px; font-size:18px;">
                                You are browsing as a guest.
                                <a href="login.php" style="color:#7a1b24; font-weight:600;">Log in</a>
                                to view collections.
                            </p>

                        <?php elseif (empty($collections)): ?>
                            <div style="margin-top:20px; font-size:18px;">
                                <p>
                                    You don’t have any collections yet.
                                    <a href="collectioncreation.php" style="color:#7a1b24; font-weight:600;">
                                        Create your first collection
                                    </a>.
                                </p>
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
    
    <script src="homepage.js"></script>
    <script src="mycollectionspage.js"></script>
    <script src="logout.js"></script>
</body>
</html>
