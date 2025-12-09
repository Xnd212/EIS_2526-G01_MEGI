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
    $user_id = null;
} else {
    $user_id = (int) $_SESSION['user_id'];
}

// ====== 4. GET INPUTS (Sort & Price) ======
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'recent-desc';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;

// Helper 1: Base URL for Sort Links (Keeps Price)
$baseUrl = "?";
if ($min_price !== null) $baseUrl .= "min_price=" . $min_price . "&";
if ($max_price !== null) $baseUrl .= "max_price=" . $max_price . "&";

// Helper 2: Reset URL for the Reset Button (Keeps Sort, Removes Price)
$resetUrl = "?sort=" . htmlspecialchars($sort);

// ====== 5. SORTING LOGIC ======
switch ($sort) {
    case 'alpha-asc':   $orderBy = "i.name ASC"; break;
    case 'alpha-desc':  $orderBy = "i.name DESC"; break;
    case 'price-asc':   $orderBy = "i.price ASC"; break;
    case 'price-desc':  $orderBy = "i.price DESC"; break;
    case 'importance-asc':  $orderBy = "i.importance ASC"; break;
    case 'importance-desc': $orderBy = "i.importance DESC"; break;
    case 'recent-desc': $orderBy = "i.item_id DESC"; break;
    case 'recent-asc':  $orderBy = "i.item_id ASC"; break;
    default:            $orderBy = "i.item_id DESC";
}

// ====== 6. FETCH ITEMS (Dynamic Query) ======
$items = [];

if ($user_id !== null) {
    // Base SQL
    $sql = "SELECT 
                i.item_id,
                i.name AS item_name,
                i.price,
                i.importance, 
                img.url AS item_url,
                c.name AS collection_name
            FROM item i
            JOIN contains cn ON i.item_id = cn.item_id
            JOIN collection c ON cn.collection_id = c.collection_id
            LEFT JOIN image img ON i.image_id = img.image_id
            WHERE c.user_id = ?";

    $types = "i";       
    $params = [$user_id]; 

    // Add Price Filters
    if ($min_price !== null) {
        $sql .= " AND i.price >= ?";
        $types .= "d";
        $params[] = $min_price;
    }
    if ($max_price !== null) {
        $sql .= " AND i.price <= ?";
        $types .= "d"; 
        $params[] = $max_price;
    }

    $sql .= " GROUP BY i.item_id ORDER BY $orderBy";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

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
        <style>
            /* Filter Menu Styles */
            .filter-menu a {
                display: block; width: 100%; text-align: left;
                padding: 0.5rem 1rem; font-size: 0.85rem;
                text-decoration: none; color: #333; cursor: pointer; box-sizing: border-box;
            }
            .filter-menu a:hover { background-color: #fbecec; color: #b54242; }
            .filter-menu hr { margin: 0.2rem 0; border: 0; border-top: 1px solid #eee; }

            /* Price Range Styles */
            .controls-wrapper {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .price-form {
                display: flex;
                align-items: center;
                gap: 5px;
                background: #fff;
                padding: 5px 10px;
                border-radius: 20px;
                border: 1px solid #ddd;
            }
            .price-form input {
                width: 60px;
                border: none;
                border-bottom: 1px solid #ccc;
                text-align: center;
                font-size: 0.9rem;
                outline: none;
            }
            .price-form span { font-size: 0.9rem; color: #666; }
            
            /* Apply Button (Arrow) */
            .price-form button {
                background: #b54242;
                color: white;
                border: none;
                border-radius: 50%;
                width: 24px;
                height: 24px;
                cursor: pointer;
                font-size: 0.8rem;
                display: flex; align-items: center; justify-content: center;
            }
            .price-form button:hover { background: #a03030; }

            /* Reset Button (Circle Arrow) */
            .reset-btn {
                background: #6c757d; /* Grey */
                color: white;
                border: none;
                border-radius: 50%;
                width: 24px;
                height: 24px;
                display: flex; align-items: center; justify-content: center;
                text-decoration: none;
                font-size: 0.9rem;
                margin-left: 2px;
                transition: background 0.2s;
            }
            .reset-btn:hover { background: #5a6268; }
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
                    <div class="popup-header"><h3>Logout</h3></div>
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

                            <div class="controls-wrapper">
                                
                                <form method="GET" class="price-form">
                                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                                    
                                    <span>€</span>
                                    <input type="number" name="min_price" placeholder="Min" step="0.01" 
                                           value="<?php echo $min_price; ?>">
                                    <span>-</span>
                                    <input type="number" name="max_price" placeholder="Max" step="0.01" 
                                           value="<?php echo $max_price; ?>">
                                    
                                    <button type="submit" title="Apply Price Filter">➤</button>

                                    <?php if($min_price !== null || $max_price !== null): ?>
                                        <a href="<?php echo $resetUrl; ?>" class="reset-btn" title="Reset Price">↺</a>
                                    <?php endif; ?>
                                </form>

                                <div style="position:relative;">
                                    <button class="filter-toggle" id="filterToggle" aria-haspopup="true" aria-expanded="false">
                                        &#128269; Sort ▾
                                    </button>

                                    <div class="filter-menu" id="filterMenu">
                                        <a href="<?php echo $baseUrl; ?>sort=recent-desc">Added: Newest</a>
                                        <a href="<?php echo $baseUrl; ?>sort=recent-asc">Added: Oldest</a>
                                        <hr>
                                        <a href="<?php echo $baseUrl; ?>sort=alpha-asc">Name: A–Z</a>
                                        <a href="<?php echo $baseUrl; ?>sort=alpha-desc">Name: Z–A</a>
                                        <hr>
                                        <a href="<?php echo $baseUrl; ?>sort=price-asc">Price: Low–High</a>
                                        <a href="<?php echo $baseUrl; ?>sort=price-desc">Price: High–Low</a>
                                        <hr>
                                        <a href="<?php echo $baseUrl; ?>sort=importance-desc">Importance: High–Low</a>
                                        <a href="<?php echo $baseUrl; ?>sort=importance-asc">Importance: Low–High</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="item-grid" id="itemGrid">
                            <?php if ($user_id === null): ?>
                                <p style="margin-top:20px; font-size:18px;">
                                    You are browsing as a guest. <a href="login.php" style="color:#7a1b24; font-weight:600;">Log in</a> to view items.
                                </p>
                            <?php elseif (empty($items)): ?>
                                <div style="margin-top:20px; font-size:18px;">
                                    <?php if($min_price !== null || $max_price !== null): ?>
                                        <p>No items found in this price range.</p>
                                        <a href="<?php echo $resetUrl; ?>" style="color:#7a1b24; font-weight:600;">Clear Filters</a>
                                    <?php else: ?>
                                        <p>You don’t have any items yet. <a href="itemcreation.php" style="color:#7a1b24; font-weight:600;">Create your first item</a>.</p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <?php foreach ($items as $row): ?>
                                    <?php
                                        $img   = !empty($row['item_url']) ? htmlspecialchars($row['item_url']) : 'images/default_item.png';
                                        $price = number_format($row['price'], 2);
                                    ?>
                                    <div class="item-card">
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
        <script src="homepage.js"></script>
        <script src="logout.js"></script>
    </body>
</html>