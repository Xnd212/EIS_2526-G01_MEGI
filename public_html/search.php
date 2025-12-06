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

if ($q === '') {
    echo "<h2>No search term provided.</h2>";
    exit();
}

$searchTerm = "%" . $q . "%";

// =========================
// FRIENDS SEARCH
// =========================
$sqlFriends = "
    SELECT user_id, username 
    FROM user 
    WHERE username LIKE ?
";

$stmt = $conn->prepare($sqlFriends);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$friends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// =========================
// COLLECTIONS SEARCH
// =========================
$sqlCollections = "
    SELECT collection_id, name, image_id
    FROM collection
    WHERE name LIKE ?
";

$stmt = $conn->prepare($sqlCollections);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$collections = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// =========================
// EVENTS SEARCH
// =========================
$sqlEvents = "
    SELECT event_id, name, date, place, image_id
    FROM event
    WHERE name LIKE ?
";

$stmt = $conn->prepare($sqlEvents);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// =========================
// ITEMS SEARCH
// =========================
$sqlItems = "
    SELECT item_id, name, price, image_id
    FROM item
    WHERE name LIKE ?
";

$stmt = $conn->prepare($sqlItems);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Search results</title>
    <link rel="stylesheet" href="search.css">
</head>
<body>

<h1>Search results for: "<?= htmlspecialchars($q) ?>"</h1>

<!-- FRIENDS -->
<h2>Friends</h2>
<?php if (empty($friends)): ?>
    <p>No friends found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($friends as $f): ?>
            <li>
                <?php
                    if ($f['user_id'] == $_SESSION['user_id']) {
                        // Searching for yourself → go to your own profile page
                        $link = "userpage.php";
                    } else {
                        // Searching for another user → go to their friendpage
                        $link = "friendpage.php?user_id=" . $f['user_id'];
                    }
                ?>
                <a href="<?= $link ?>">
                    <?= htmlspecialchars($f['username']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>


<!-- COLLECTIONS -->
<h2>Collections</h2>
<?php if (empty($collections)): ?>
    <p>No collections found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($collections as $c): ?>
            <li>
                <a href="collectionpage.php?id=<?= $c['collection_id'] ?>">
                    <?= htmlspecialchars($c['name']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- EVENTS -->
<h2>Events</h2>
<?php if (empty($events)): ?>
    <p>No events found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($events as $e): ?>
            <li>
                <a href="eventpage.php?id=<?= $e['event_id'] ?>">
                    <?= htmlspecialchars($e['name']) ?> — <?= htmlspecialchars($e['date']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- ITEMS -->
<h2>Items</h2>
<?php if (empty($items)): ?>
    <p>No items found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($items as $i): ?>
            <li>
                <a href="itempage.php?id=<?= $i['item_id'] ?>">
                    <?= htmlspecialchars($i['name']) ?> — <?= $i['price'] ?>€
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

</body>
</html>
