<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$currentUserId = (int) $_SESSION['user_id'];

// ====== LIGAÇÃO À BD ======
require_once __DIR__ . "/db.php";

// ====== OBTÉM ID DO ITEM ======
$item_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$item_id || $item_id <= 0) {
    die("Item inválido.");
}

// ====== QUERY AO ITEM (com collector, type e image_id) usando contains ======
$sql = "
    SELECT 
        i.*,
        c.collection_id,
        c.name      AS collection_name,
        c.image_id  AS collection_image_id,
        c.user_id   AS owner_id,
        u.username  AS collector_name,
        t.name      AS type_name
    FROM item i
    INNER JOIN contains con ON i.item_id = con.item_id
    INNER JOIN collection c ON con.collection_id = c.collection_id
    JOIN user u       ON c.user_id      = u.user_id
    LEFT JOIN type t  ON i.type_id      = t.type_id
    WHERE i.item_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Item não encontrado.");
}

// ====== IMAGEM DO ITEM (via item.image_id) ======
$item_img_url = "images/placeholder.png";
if (!empty($item['image_id'])) {
    $sqlImg = "SELECT url FROM image WHERE image_id = ? LIMIT 1";
    $stmtImg = $conn->prepare($sqlImg);
    $stmtImg->bind_param("i", $item['image_id']);
    $stmtImg->execute();
    $resImg = $stmtImg->get_result();
    if ($row = $resImg->fetch_assoc()) {
        $item_img_url = $row['url'];
    }
}

// ====== IMAGEM DA COLEÇÃO (via collection.image_id) ======
$collection_img_url = "images/placeholder_collection.png";
if (!empty($item['collection_image_id'])) {
    $sqlColImg = "SELECT url FROM image WHERE image_id = ? LIMIT 1";
    $stmtColImg = $conn->prepare($sqlColImg);
    $stmtColImg->bind_param("i", $item['collection_image_id']);
    $stmtColImg->execute();
    $resColImg = $stmtColImg->get_result();
    if ($colImg = $resColImg->fetch_assoc()) {
        $collection_img_url = $colImg['url'];
    }
}

function fmtDate($d) {
    return $d ? date("d/m/Y", strtotime($d)) : "-";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Item Page</title>
  <link rel="stylesheet" href="homepage.css" />
  <link rel="stylesheet" href="itempage.css" />
  <link rel="stylesheet" href="mycollectionspage.css" />
  <link rel="stylesheet" href="calendar_popup.css" />
</head>

<body>
  <!-- ===== HEADER ===== -->
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
      
          <!-- Logout -->
    <button class="icon-btn" id="logout-btn" aria-label="Logout">🚪</button>

    <div class="notification-popup logout-popup" id="logout-popup">
      <div class="popup-header">
        <h3>Logout</h3>
      </div>

      <p>Are you sure you want to log out?</p>

      <div class="logout-btn-wrapper">
        <button type="button" class="logout-btn cancel-btn" id="cancel-logout">
          Cancel
        </button>
        <button type="button" class="logout-btn confirm-btn" id="confirm-logout">
          Log out
        </button>
      </div>
    </div>
    </div>
  </header>

  <div class="main">
    <div class="content">
      <!-- TÍTULO DO ITEM -->
      <h2><?php echo htmlspecialchars($item['name']); ?></h2>

      <div class="item-details">
        <div class="item-image-wrapper">
          <img src="<?php echo htmlspecialchars($item_img_url); ?>" 
               alt="<?php echo htmlspecialchars($item['name']); ?>" 
               class="item-image">

          <?php if ((int)$item['owner_id'] === $currentUserId): ?>
              <div class="item-actions">
                <a href="edititem.php?id=<?php echo $item_id; ?>" 
                   id="editRedirectBtn" class="edit-link">✎ Edit</a>
                <button id="deleteItemBtn" 
                        class="delete-link" 
                        data-item-id="<?php echo $item_id; ?>">🗑️ Delete</button>
              </div>
          <?php endif; ?>
        </div>

        <div class="item-info">
          <p><strong>Collector:</strong> 
            <?php 
                // Check if the current user owns this item
                if ((int)$item['owner_id'] === $currentUserId) {
                    $profileLink = "userpage.php";
                } else {
                    $profileLink = "friendpage.php?user_id=" . $item['owner_id'];
                }
            ?>
            <a href="<?php echo $profileLink; ?>" class="collector-link">
                <?php echo htmlspecialchars($item['collector_name']); ?>
            </a>
          </p>
          <p><strong>Price:</strong> 
            <?php echo htmlspecialchars($item['price']); ?>€
          </p>
          <p><strong>Item Type:</strong> 
            <?php echo htmlspecialchars($item['type_name'] ?? '—'); ?>
          </p>
          <p><strong>Importance:</strong> 
            <?php echo htmlspecialchars($item['importance']); ?>
          </p>
          <p><strong>Acquisition Date:</strong> 
            <?php echo fmtDate($item['acc_date']); ?>
          </p>
          <p><strong>Acquisition Place:</strong> 
            <?php echo htmlspecialchars($item['acc_place']); ?>
          </p>
          <p><strong>Description:</strong> 
            <?php echo htmlspecialchars($item['description']); ?>
          </p>
          <?php if (!empty($item['registration_date'])): ?>
          <p><strong>Registration Date:</strong> 
            <?php echo fmtDate($item['registration_date']); ?>
          </p>
          <?php endif; ?>
        </div>
      </div>

      <div class="collections-and-friends">
        <section class="collections">
          <h3>Collections it belongs to</h3>
          <div class="collection-grid">
            <div class="collection-card">
              <a href="collectionpage.php?id=<?php echo $item['collection_id']; ?>">
                
                <img src="<?php echo htmlspecialchars($collection_img_url); ?>" 
                alt="<?php echo htmlspecialchars($item['collection_name']); ?>">

                <p class="collection-name">
                  <?php echo htmlspecialchars($item['collection_name']); ?>
                </p>
              </a>
            </div>
          </div>
        </section>
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

      <div class="sidebar-section">
        <h3>Events</h3>
        <p><a href="createevent.php">Create event</a></p>
        <p>View upcoming events</p>
        <p><a href="eventhistory.php">Event history</a></p>
      </div>
    </aside>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="delete-popup" id="delete-popup" style="display: none;">
    <div class="popup-header">
      <h3>Delete Item</h3>
    </div>

    <p>Are you sure you want to delete this item? This action cannot be undone.</p>

    <div class="logout-btn-wrapper">
      <button type="button" class="logout-btn cancel-btn" id="cancel-delete">
        Cancel
      </button>
      <button type="button" class="logout-btn confirm-btn" id="confirm-delete">
        Delete
      </button>
    </div>
  </div>

  <script src="itempage.js"></script>
  <script src="logout.js"></script>
  <script src="deleteitem.js"></script>
</body>
</html>