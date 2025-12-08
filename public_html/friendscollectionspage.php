<?php
session_start();

// ---------------------- USER LOGADO (para fallback) ----------------------
if (!isset($_SESSION['user_id'])) {
    // Redirect if not logged in (optional, based on your logic)
    header("Location: login.php");
    exit();
}
$currentUserId = (int) $_SESSION['user_id'];

// ---------------------- PERFIL CUJAS COLEÇÕES VAMOS VER ----------------------
$profileUserId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if (!$profileUserId) {
    // se não vier user_id no URL, mostra as coleções do próprio user logado
    $profileUserId = $currentUserId;
}

// ---------------------- LIGAÇÃO À BD ----------------------
require_once __DIR__ . "/db.php";

// ---------------------- 1) BUSCAR DADOS DO UTILIZADOR ----------------------
$sqlUser = "SELECT user_id, username FROM user WHERE user_id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $profileUserId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$profile = $resultUser->fetch_assoc();
$stmtUser->close();

if (!$profile) {
    die("Utilizador não encontrado.");
}

// ---------------------- 2) SORTING LOGIC ----------------------
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'updated-desc';

switch ($sort) {
    case 'alpha-asc':   $orderBy = "c.name ASC"; break;
    case 'alpha-desc':  $orderBy = "c.name DESC"; break;
    
    case 'price-asc':   $orderBy = "total_price ASC"; break;
    case 'price-desc':  $orderBy = "total_price DESC"; break;
    
    case 'updated-asc': 
    case 'created-asc': $orderBy = "c.starting_date ASC"; break;
    case 'updated-desc':
    case 'created-desc':$orderBy = "c.starting_date DESC"; break;
    
    case 'items-asc':   $orderBy = "item_count ASC"; break;
    case 'items-desc':  $orderBy = "item_count DESC"; break;

    case 'recent-item-desc': $orderBy = "latest_item_id DESC"; break; 
    case 'recent-item-asc':  $orderBy = "latest_item_id ASC"; break;
    
    default:            $orderBy = "c.starting_date DESC";
}

// Helper: Build the Base URL to preserve the 'user_id' parameter
$baseUrl = "?";
if ($profileUserId) {
    $baseUrl .= "user_id=" . $profileUserId . "&";
}

// ---------------------- 3) BUSCAR COLEÇÕES DO UTILIZADOR ----------------------
$sqlCollections = "
    SELECT 
        c.collection_id,
        c.name,
        c.starting_date,
        c.image_id,
        c.Theme,
        img.url AS collection_image,
        COUNT(ct.item_id) AS item_count,
        COALESCE(SUM(it.price), 0) AS total_price,
        COALESCE(MAX(ct.item_id), 0) AS latest_item_id
    FROM collection c
    LEFT JOIN image img ON c.image_id = img.image_id
    LEFT JOIN contains ct ON c.collection_id = ct.collection_id
    LEFT JOIN item it ON ct.item_id = it.item_id
    WHERE c.user_id = ?
    GROUP BY c.collection_id
    ORDER BY $orderBy
";

$stmtC = $conn->prepare($sqlCollections);
$stmtC->bind_param("i", $profileUserId);
$stmtC->execute();
$resultC = $stmtC->get_result();

$collections = [];
while ($row = $resultC->fetch_assoc()) {
    $collections[] = $row;
}
$stmtC->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Trall-E | <?php echo htmlspecialchars($profile['username']); ?>'s Collections</title>
        <link rel="stylesheet" href="mycollectionspage.css">
        <style>
            /* Ensure links look correct in the menu */
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
                            <h2><?php echo htmlspecialchars($profile['username']); ?>'s Collections</h2>

                            <button class="filter-toggle" id="filterToggle" aria-haspopup="true" aria-expanded="false">
                                &#128269; Sort ▾
                            </button>

                            <div class="filter-menu" id="filterMenu">
                                <a href="<?php echo $baseUrl; ?>sort=alpha-asc">Name: A–Z</a>
                                <a href="<?php echo $baseUrl; ?>sort=alpha-desc">Name: Z–A</a>
                                <hr>
                                <a href="<?php echo $baseUrl; ?>sort=price-asc">Price: Low–High</a>
                                <a href="<?php echo $baseUrl; ?>sort=price-desc">Price: High–Low</a>
                                <hr>
                                <a href="<?php echo $baseUrl; ?>sort=updated-desc">Date: Newest</a>
                                <a href="<?php echo $baseUrl; ?>sort=updated-asc">Date: Oldest</a>
                                <hr>
                                <a href="<?php echo $baseUrl; ?>sort=items-desc">Items: Most</a>
                                <a href="<?php echo $baseUrl; ?>sort=items-asc">Items: Fewest</a>
                                <hr>
                                <a href="<?php echo $baseUrl; ?>sort=recent-item-desc">Item Added: Recent</a>
                                <a href="<?php echo $baseUrl; ?>sort=recent-item-asc">Item Added: Oldest</a>
                            </div>
                        </div>

                        <div class="collection-grid" id="collectionGrid">
                            <?php if (empty($collections)): ?>
                                <p>This user has no collections yet.</p>
                            <?php else: ?>
                                <?php foreach ($collections as $col): ?>
                                    <?php
                                        $imgSrc = !empty($col['collection_image'])
                                            ? $col['collection_image']
                                            : 'images/default_collection.png';

                                        $lastUpdated = !empty($col['starting_date'])
                                            ? date('d/m/Y', strtotime($col['starting_date']))
                                            : '-';
                                    ?>
                                    <div class="collection-card">
                                        <a href="collectionpage.php?id=<?php echo $col['collection_id']; ?>">
                                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                                                 alt="<?php echo htmlspecialchars($col['name']); ?>">
                                            <p><strong><?php echo htmlspecialchars($col['name']); ?></strong></p>
                                            <span class="last-updated">
                                                Last updated: <?php echo $lastUpdated; ?>
                                            </span>
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


        <!-- === JAVASCRIPT === -->
        <script src="friendscollectionspage.js"></script>
        <script src="logout.js"></script>
    </body>
</html>