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

/* Which profile are we viewing? */
$profileUserId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

if (!$profileUserId) {
    // If no ?user_id given, show user's own friends (if logged)
    $profileUserId = $currentUserId;
}

/* DB CONNECTION */
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

/* FETCH PROFILE USERNAME */
$profileUsername = "User";

if ($profileUserId !== null) {
    $sqlUserName = "SELECT username FROM user WHERE user_id = ?";
    $stmtName = $conn->prepare($sqlUserName);
    $stmtName->bind_param("i", $profileUserId);
    $stmtName->execute();
    $resName = $stmtName->get_result();
    $rowName = $resName->fetch_assoc();
    $stmtName->close();

    if ($rowName) {
        $profileUsername = $rowName['username'];
    }
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
            img.url AS friend_image
        FROM friends f
        INNER JOIN user u ON f.friend_id = u.user_id
        LEFT JOIN image img ON u.image_id = img.image_id
        WHERE f.user_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $profileUserId);
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
  <title>Trall-E | User Friends Page</title>
  <link rel="stylesheet" href="userfriendspage.css">
</head>

<body>    

<!-- HEADER -->
<header>
    <a href="homepage.php" class="logo">
        <img src="images/TrallE_2.png" alt="logo" />
    </a>
    <div class="search-bar">
        <input type="search" placeholder="Search" />
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

        <section class="friends">
            <h2>
                <?php
                    if ($profileUserId === $currentUserId) {
                        echo "My Friends";
                    } else {
                        echo htmlspecialchars($profileUsername) . "'s Friends";
                    }
                ?>
            </h2>

            <div class="friends-grid">

                <?php if ($currentUserId === null): ?>

                    <!-- GUEST MODE MESSAGE -->
                    <p style="margin-top:20px; text-align:left; white-space:nowrap; font-size:18px;">
                        You are browsing as a guest.
                        <a href="login.php" style="color:#7a1b24; font-weight:600; text-decoration:none;">
                            Log in
                        </a>
                        to view and manage your friends list.
                    </p>

                <?php elseif (empty($friends)): ?>

                    <?php if ($profileUserId === $currentUserId): ?>
                        <p style="margin-top:20px; font-size:18px;">
                            You don't have any friends yet.
                        </p>
                    <?php else: ?>
                        <p style="margin-top:20px; font-size:18px;">
                            <?php echo htmlspecialchars($profileUsername); ?> does not have any friends to display.
                        </p>
                    <?php endif; ?>

                <?php else: ?>

                    <!-- FRIEND CARDS -->
                    <?php foreach ($friends as $friend): ?>
                        <?php
                            $imgSrc = !empty($friend['friend_image'])
                                ? $friend['friend_image']
                                : 'images/default_avatar.png';
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
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </section>

    </div>
</div>

<!-- SIDEBAR -->
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

<script src="userfriendspage.js"></script>
<script src="homepage.js"></script>
<script src="logout.js"></script>

</body>
</html>