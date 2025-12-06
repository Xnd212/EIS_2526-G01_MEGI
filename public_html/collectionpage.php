<?php
session_start();

// se quiseres garantir login obrigatório:
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = (int) $_SESSION['user_id'];

// ====== LIGAÇÃO À BASE DE DADOS ======
require_once __DIR__ . "/db.php";

// ======  ID DA COLEÇÃO ======
$collection_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$collection_id) {
    die("Coleção inválida.");
}

// ====== 1. DADOS DA COLEÇÃO ======
$sql = "SELECT 
            c.collection_id,
            c.user_id,
            c.name,
            c.Theme,
            c.image_id,
            c.starting_date,
            c.description,
            u.username,
            i.url
        FROM collection c
        LEFT JOIN user u ON c.user_id = u.user_id
        LEFT JOIN image i ON c.image_id = i.image_id
        WHERE c.collection_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $collection_id);
$stmt->execute();
$result = $stmt->get_result();
$col = $result->fetch_assoc();

if (!$col) {
    die("Coleção não encontrada.");
}

// ====== 2. ITEM MAIS RECENTE ======
$recent_sql = "SELECT i.item_id, i.name 
               FROM item i
               JOIN contains cn ON i.item_id = cn.item_id
               WHERE cn.collection_id = ?
               ORDER BY i.acc_date DESC, i.item_id DESC 
               LIMIT 1";

$recent_stmt = $conn->prepare($recent_sql);
$recent_stmt->bind_param("i", $collection_id);
$recent_stmt->execute();
$recent_result = $recent_stmt->get_result();
$most_recent_item = $recent_result->fetch_assoc();

// ====== 3. TODOS OS ITENS ======
$item_sql = "SELECT 
                it.item_id,
                it.name,
                it.image_id,
                img.url AS item_url
            FROM contains con
            INNER JOIN item it ON con.item_id = it.item_id
            LEFT JOIN image img ON it.image_id = img.image_id
            WHERE con.collection_id = ?";

$item_stmt = $conn->prepare($item_sql);
$item_stmt->bind_param("i", $collection_id);
$item_stmt->execute();
$item_result = $item_stmt->get_result();

// ====== 4. TAGS ======
$tags_sql = "SELECT t.name
             FROM collection_tags ct
             INNER JOIN tags t ON ct.tag_id = t.tag_id
             WHERE ct.collection_id = ?";

$tags_stmt = $conn->prepare($tags_sql);
$tags_stmt->bind_param("i", $collection_id);
$tags_stmt->execute();
$tags_result = $tags_stmt->get_result();

$tags = [];
while ($row = $tags_result->fetch_assoc()) {
    $tags[] = $row['name'];
}

// ====== 5. EVENTOS PASSADOS (Past Events) ======
$past_sql = "SELECT e.event_id, e.name, e.date, i.url 
             FROM event e
             JOIN attends a ON e.event_id = a.event_id
             LEFT JOIN image i ON e.image_id = i.image_id
             WHERE a.collection_id = ? AND e.date < CURDATE()
             ORDER BY e.date DESC"; 

$past_stmt = $conn->prepare($past_sql);
$past_stmt->bind_param("i", $collection_id);
$past_stmt->execute();
$past_events = $past_stmt->get_result();

// ====== 6. EVENTOS FUTUROS (Next Events) ======
$future_sql = "SELECT e.event_id, e.name, e.date, i.url 
               FROM event e
               JOIN attends a ON e.event_id = a.event_id
               LEFT JOIN image i ON e.image_id = i.image_id
               WHERE a.collection_id = ? AND e.date >= CURDATE()
               ORDER BY e.date ASC"; 

$future_stmt = $conn->prepare($future_sql);
$future_stmt->bind_param("i", $collection_id);
$future_stmt->execute();
$future_events = $future_stmt->get_result();


// Formatar data (opcional)
$starting_date_fmt = "";
if (!empty($col['starting_date'])) {
    $starting_date_fmt = date("d/m/Y", strtotime($col['starting_date']));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Collection Page</title>
  <link rel="stylesheet" href="homepage.css" />
  <link rel="stylesheet" href="collectionpage.css">
  <link rel="stylesheet" href="userpage.css">
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
      <h2><?php echo htmlspecialchars($col['name']); ?></h2>

      <div class="collection-details">
        <div class="collection-logo-wrapper">
          <?php
          $image_src = !empty($col['url']) ? $col['url'] : 'images/default.png';
          ?>
          <img src="<?php echo htmlspecialchars($image_src); ?>" 
               alt="Collection Logo" class="collection-logo">

          <?php if ((int)$col['user_id'] === $currentUserId): ?>
              <a href="editcollection.php?id=<?php echo $col['collection_id']; ?>" class="edit-link">
                ✎ Edit
              </a>
          <?php endif; ?>
        </div>


        <div class="collection-info">
          <p>
            <strong>Collector:</strong>
            <?php echo htmlspecialchars($col['username'] ?? 'Unknown'); ?>
          </p>

          <?php if (!empty($col['Theme'])): ?>
            <p><strong>Theme:</strong> <?php echo htmlspecialchars($col['Theme']); ?></p>
          <?php endif; ?>

          <?php if (!empty($starting_date_fmt)): ?>
            <p><strong>Start Date:</strong> <?php echo $starting_date_fmt; ?></p>
          <?php endif; ?>

          <p><strong>Most recent Item:</strong> 
            <?php if ($most_recent_item): ?>
                <a href="itempage.php?id=<?php echo $most_recent_item['item_id']; ?>">
                    <?php echo htmlspecialchars($most_recent_item['name']); ?>
                </a>
            <?php else: ?>
                <span style="color:#777;">No items yet</span>
            <?php endif; ?>
          </p>

          <?php if (!empty($col['description'])): ?>
            <p><strong>Description:</strong>
              <?php echo nl2br(htmlspecialchars($col['description'])); ?>
            </p>
          <?php endif; ?>

            
          <?php if (!empty($tags)): ?>
            <p><strong>Tags:</strong>
              <?php echo htmlspecialchars(implode(', ', $tags)); ?>
            </p>
          <?php else: ?>
            <p><strong>Tags:</strong> <span style="color:#777;">No tags yet</span></p>
          <?php endif; ?>

        </div>
      </div>

        <div class="items-section">
          <h3>Collection Items</h3>
          <div class="items-grid">
            <?php if ($item_result->num_rows > 0): ?>
              <?php while ($item = $item_result->fetch_assoc()): ?>
                <?php
                  $item_img = !empty($item['item_url']) ? $item['item_url'] : 'images/default_item.png';
                ?>
                <div class="item-card">
                  <a href="itempage.php?id=<?php echo $item['item_id']; ?>">
                    <img src="<?php echo htmlspecialchars($item_img); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <p class="item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                    <p class="edit-btn">View Item</p>
                  </a>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <p>No items in this collection yet.</p>
            <?php endif; ?>
          </div>
        </div>


      <div class="events-section">
        <h3>Previous Events</h3>
        <div class="event-cards">
          <?php if ($past_events->num_rows > 0): ?>
            <?php while($p_event = $past_events->fetch_assoc()): ?>
              <?php 
                 $p_img = !empty($p_event['url']) ? $p_event['url'] : 'images/default_event.png';
              ?>
              <div class="event-card">
                <a href="pasteventpage.php?id=<?php echo $p_event['event_id']; ?>">
                  <img src="<?php echo htmlspecialchars($p_img); ?>" alt="<?php echo htmlspecialchars($p_event['name']); ?>">
                  <p><?php echo htmlspecialchars($p_event['name']); ?></p>
                </a>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p style="color: #777;">No previous events.</p>
          <?php endif; ?>
        </div>

        <h3>Next Events</h3>
        <div class="next-event">
          <?php if ($future_events->num_rows > 0): ?>
            <?php while($f_event = $future_events->fetch_assoc()): ?>
              <?php 
                 $f_img = !empty($f_event['url']) ? $f_event['url'] : 'images/default_event.png';
              ?>
              <a href="eventpage.php?id=<?php echo $f_event['event_id']; ?>">
                <img src="<?php echo htmlspecialchars($f_img); ?>" alt="<?php echo htmlspecialchars($f_event['name']); ?>">
                <p><strong><?php echo htmlspecialchars($f_event['name']); ?></strong></p>
              </a>
            <?php endwhile; ?>
          <?php else: ?>
            <p style="color: #777;">No upcoming events.</p>
          <?php endif; ?>
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

      <div class="sidebar-section">
        <h3>Events</h3>
        <p><a href="createevent.php">Create event</a></p>
        <p><a href="upcomingevents.php">View upcoming events</a></p>
        <p><a href="eventhistory.php">Event history</a></p>
      </div>
    </aside>
  </div>

  <div id="hover-popup"></div>

  <script src="collectionpage.js"></script>
  <script src="logout.js"></script>

</body>
</html>