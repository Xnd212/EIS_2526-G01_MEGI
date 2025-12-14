<?php
session_start();

/* LOGIN / GUEST */
$isGuest = isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;
$currentUserId = isset($_SESSION['user_id']) && !$isGuest ? (int)$_SESSION['user_id'] : null;

/* PERFIL CUJAS COLEÇÕES DOS AMIGOS VAMOS VER */
$profileUserId = null;
if (isset($_GET['user_id']) && ctype_digit($_GET['user_id'])) {
    $profileUserId = (int)$_GET['user_id'];
} elseif ($currentUserId !== null) {
    $profileUserId = $currentUserId;
}

/* BD */
require_once __DIR__ . "/db.php";

/* NOME DO PERFIL */
$profileUsername = 'User';
if ($profileUserId !== null) {
    $sqlUserName = "SELECT username FROM user WHERE user_id = ?";
    $stmtName = $conn->prepare($sqlUserName);
    $stmtName->bind_param("i", $profileUserId);
    $stmtName->execute();
    $resName = $stmtName->get_result();
    if ($rowName = $resName->fetch_assoc()) {
        $profileUsername = $rowName['username'];
    }
    $stmtName->close();
}

/* ==========================================
   SORTING LOGIC
   ========================================== */
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

switch ($sort) {
    case 'alpha-asc':   $orderBy = "c.name ASC"; break;
    case 'alpha-desc':  $orderBy = "c.name DESC"; break;
    
    case 'date-desc':   $orderBy = "c.starting_date DESC"; break;
    case 'date-asc':    $orderBy = "c.starting_date ASC"; break;
    
    case 'items-desc':  $orderBy = "item_count DESC"; break;
    case 'items-asc':   $orderBy = "item_count ASC"; break;

    // NEW: Sort by Recency of Item Addition
    case 'recent-item-desc': $orderBy = "latest_item_id DESC"; break; 
    case 'recent-item-asc':  $orderBy = "latest_item_id ASC"; break;

    default:            $orderBy = "owner_username ASC, c.name ASC"; 
}

/* ==========================================
   FETCH COLLECTIONS
   ========================================== */
$friendCollections = [];

if ($profileUserId !== null) {
    // Added MAX(ct.item_id) AS latest_item_id to support sorting by recent items
    $sql = "
        SELECT 
            c.collection_id,
            c.name AS collection_name,
            c.theme,
            c.description,
            c.starting_date,
            img_col.url AS collection_image,
            u.user_id AS owner_id,
            u.username AS owner_username,
            COUNT(ct.item_id) AS item_count,
            COALESCE(MAX(ct.item_id), 0) AS latest_item_id
        FROM friends f
        INNER JOIN user u 
            ON f.friend_id = u.user_id          -- amigo
        INNER JOIN collection c 
            ON c.user_id = u.user_id            -- coleção do amigo
        LEFT JOIN image img_col 
            ON c.image_id = img_col.image_id    -- imagem da coleção
        LEFT JOIN contains ct 
            ON c.collection_id = ct.collection_id -- items para contagem e recência
        WHERE f.user_id = ?
        GROUP BY c.collection_id
        ORDER BY $orderBy
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $profileUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $friendCollections[] = $row;
    }

    $stmt->close();
}

// Helper to preserve user_id in sort links
$baseUrl = "?";
if (isset($_GET['user_id'])) {
    $baseUrl .= "user_id=" . htmlspecialchars($_GET['user_id']) . "&";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | My Bubble Collections</title>
  <link rel="stylesheet" href="allfriendscollectionspage.css">
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
        
        <div class="collections-header">
            <h2>
                <?php
                  if ($profileUserId !== null && $currentUserId !== null && $profileUserId === $currentUserId) {
                      echo "My bubble's collections";
                  } elseif ($profileUserId !== null) {
                      echo htmlspecialchars($profileUsername) . "'s bubble's collections";
                  } else {
                      echo "Bubble's collections";
                  }
                ?>
            </h2>

            <button class="filter-toggle" id="filterToggle">
                &#128269; Sort ▾
            </button>

            <div class="filter-menu" id="filterMenu">
                <a href="<?php echo $baseUrl; ?>sort=alpha-asc">Name: A–Z</a>
                <a href="<?php echo $baseUrl; ?>sort=alpha-desc">Name: Z–A</a>
                <hr>
                <a href="<?php echo $baseUrl; ?>sort=date-desc">Date: Newest</a>
                <a href="<?php echo $baseUrl; ?>sort=date-asc">Date: Oldest</a>
                <hr>
                <a href="<?php echo $baseUrl; ?>sort=items-desc">Items: Most</a>
                <a href="<?php echo $baseUrl; ?>sort=items-asc">Items: Fewest</a>
                <hr>
                <a href="<?php echo $baseUrl; ?>sort=recent-item-desc">Item Added: Recent</a>
                <a href="<?php echo $baseUrl; ?>sort=recent-item-asc">Item Added: Oldest</a>
            </div>
        </div>

        <div class="friends-grid">

          <?php if ($isGuest || ($profileUserId === null && $currentUserId === null)): ?>

              <p style="margin-top:20px; text-align:left; white-space:nowrap; font-size:18px;">
                  You are browsing as a guest.
                  <a href="login.php" style="color:#7a1b24; font-weight:600; text-decoration:none;">
                      Log in
                  </a>
                  to view your friends' collections.
              </p>

          <?php elseif (empty($friendCollections)): ?>

              <p style="margin-top:20px; font-size:18px; width: 500px;">
                  <?php echo htmlspecialchars($profileUsername); ?> has no friends' collections to display.
              </p>

          <?php else: ?>

              <?php foreach ($friendCollections as $col): ?>
                  <?php
                    $imgSrc = !empty($col['collection_image'])
                        ? $col['collection_image']
                        : 'images/default_collection.png';
                  ?>
                  <div class="friend">
                      <a href="collectionpage.php?id=<?php echo $col['collection_id']; ?>">
                          <img
                              src="<?php echo htmlspecialchars($imgSrc); ?>"
                              alt="<?php echo htmlspecialchars($col['collection_name']); ?>"
                          >
                      </a>

                      <p class="friend-name">
                          <strong>
                              <a href="collectionpage.php?id=<?php echo $col['collection_id']; ?>">
                                  <?php echo htmlspecialchars($col['collection_name']); ?>
                              </a>
                          </strong>
                      </p>

                      <p class="friend-owner">
                          by
                          <a href="friendpage.php?user_id=<?php echo $col['owner_id']; ?>">
                              <?php echo htmlspecialchars($col['owner_username']); ?>
                          </a>
                      </p>
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