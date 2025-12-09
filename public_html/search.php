<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once __DIR__ . "/db.php";

$q = trim($_GET['q'] ?? '');
$searchTerm = "%" . $q . "%";

/* * LOGIC ADAPTED FROM HOMEPAGE.PHP:
 * We must LEFT JOIN the 'image' table to get the file URL.
 */

// =========================
// 1. FRIENDS SEARCH
// =========================
// Assuming 'user' table has 'image_id'. If it has 'profile_image_id', adjust accordingly.
// Based on homepage logic, we join image table to get the url.
$sqlFriends = "
    SELECT u.user_id, u.username, img.url AS user_image
    FROM user u
    LEFT JOIN image img ON u.image_id = img.image_id
    WHERE u.username LIKE ?
";
$stmt = $conn->prepare($sqlFriends);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$friends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// =========================
// 2. COLLECTIONS SEARCH
// =========================
$sqlCollections = "
    SELECT c.collection_id, c.name, img.url AS collection_image
    FROM collection c
    LEFT JOIN image img ON c.image_id = img.image_id
    WHERE c.name LIKE ?
";
$stmt = $conn->prepare($sqlCollections);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$collections = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// =========================
// 3. EVENTS SEARCH
// =========================
$sqlEvents = "
    SELECT e.event_id, e.name, e.date, e.place, img.url AS event_image
    FROM event e
    LEFT JOIN image img ON e.image_id = img.image_id
    WHERE e.name LIKE ?
";
$stmt = $conn->prepare($sqlEvents);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// =========================
// 4. ITEMS SEARCH
// =========================
$sqlItems = "
    SELECT i.item_id, i.name, i.price, img.url AS item_image
    FROM item i
    LEFT JOIN image img ON i.image_id = img.image_id
    WHERE i.name LIKE ?
";
$stmt = $conn->prepare($sqlItems);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Trall-E</title>
    <link rel="stylesheet" href="homepage.css"> 
    <link rel="stylesheet" href="search.css">
    <link rel="stylesheet" href="calendar_popup.css" />
</head>
<body>

    <header>
        <a href="homepage.php" class="logo">
            <img src="images/TrallE_2.png" alt="logo" />
        </a>

        <div class="search-bar">
            <form action="search.php" method="GET">
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search for friends, collections, events, items..." required>
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
            
            <div class="search-results-section">
                <h1 class="page-title">Search results for: "<span><?= htmlspecialchars($q) ?></span>"</h1>

                <?php if ($q === ''): ?>
                    <p class="no-results-text">Please enter a search term.</p>
                <?php else: ?>

                    <h2 class="section-header">People 👥</h2>
                    <div class="search-list">
                        <?php if (empty($friends)): ?>
                            <p class="no-results-text">No people found.</p>
                        <?php else: ?>
                            <?php foreach ($friends as $f): 
                                $link = ($f['user_id'] == $_SESSION['user_id']) ? "userpage.php" : "friendpage.php?user_id=" . $f['user_id'];
                                // Use DB image if exists, else default
                                $imgUrl = !empty($f['user_image']) ? $f['user_image'] : "images/placeholderuserpicture.png"; 
                            ?>
                                <a href="<?= $link ?>" class="search-card">
                                    <div class="search-card-img circle">
                                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($f['username']) ?>">
                                    </div>
                                    <div class="search-card-info">
                                        <h3><?= htmlspecialchars($f['username']) ?></h3>
                                        <p>User Profile</p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <h2 class="section-header">Collections 📚</h2>
                    <div class="search-list">
                        <?php if (empty($collections)): ?>
                            <p class="no-results-text">No collections found.</p>
                        <?php else: ?>
                            <?php foreach ($collections as $c): 
                                $imgUrl = !empty($c['collection_image']) ? $c['collection_image'] : "images/placeholdercollectionpicture.png";
                            ?>
                                <a href="collectionpage.php?id=<?= $c['collection_id'] ?>" class="search-card">
                                    <div class="search-card-img">
                                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($c['name']) ?>">
                                    </div>
                                    <div class="search-card-info">
                                        <h3><?= htmlspecialchars($c['name']) ?></h3>
                                        <p>Collection</p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <h2 class="section-header">Events 🗓️</h2>
                    <div class="search-list">
                        <?php if (empty($events)): ?>
                            <p class="no-results-text">No events found.</p>
                        <?php else: ?>
                            <?php foreach ($events as $e): 
                                $imgUrl = !empty($e['event_image']) ? $e['event_image'] : "images/placeholdereventpicture.png";
                            ?>
                                <a href="eventpage.php?id=<?= $e['event_id'] ?>" class="search-card">
                                    <div class="search-card-img">
                                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($e['name']) ?>">
                                    </div>
                                    <div class="search-card-info">
                                        <h3><?= htmlspecialchars($e['name']) ?></h3>
                                        <p><strong>Date:</strong> <?= date('d/m/Y', strtotime($e['date'])) ?></p>
                                        <p><strong>Place:</strong> <?= htmlspecialchars($e['place']) ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <h2 class="section-header">Items 💶</h2>
                    <div class="search-list">
                        <?php if (empty($items)): ?>
                            <p class="no-results-text">No items found.</p>
                        <?php else: ?>
                            <?php foreach ($items as $i): 
                                $imgUrl = !empty($i['item_image']) ? $i['item_image'] : "images/placeholderitempicture.png";
                            ?>
                                <a href="itempage.php?id=<?= $i['item_id'] ?>" class="search-card">
                                    <div class="search-card-img">
                                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($i['name']) ?>">
                                    </div>
                                    <div class="search-card-info">
                                        <h3><?= htmlspecialchars($i['name']) ?></h3>
                                        <p class="price-tag"><?= number_format($i['price'], 2) ?>€</p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>
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

    <div id="hover-popup"></div>

    <script src="homepage.js"></script> 
    <script src="logout.js"></script>

</body>
</html>