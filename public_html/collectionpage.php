<?php
// ====== LIGAÇÃO À BASE DE DADOS ======
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

// ====== BUSCAR ID DA COLEÇÃO ======
$collection_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$collection_id) {
    die("Coleção inválida.");
}

// ====== BUSCAR DADOS DA COLEÇÃO ======
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

// ====== BUSCAR ITEM MAIS RECENTE (Novo Código) ======
// Ordena por data de aquisição (decrescente) e depois por ID (decrescente)
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

// ====== BUSCAR TODOS OS ITENS DA COLEÇÃO ======
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

// formatar data (opcional)
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
      <input type="text" placeholder="Search" />
    </div>

    <div class="icons">
      <button class="icon-btn" aria-label="Notificações" id="notification-btn">🔔</button>
      <div class="notification-popup" id="notification-popup">
        <div class="popup-header">
          <h3>Notifications <span>🔔</span></h3>
        </div>

        <hr class="popup-divider">
        <ul class="notification-list">
          <li><strong>Ana_Rita_Lopes</strong> added 3 new items...</li>
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
      <h2><?php echo htmlspecialchars($col['name']); ?></h2>

      <div class="collection-details">
        <div class="collection-logo-wrapper">
        <?php
        $image_src = !empty($col['url']) ? $col['url'] : 'images/default.png';
        ?>
        <img src="<?php echo htmlspecialchars($image_src); ?>" 
             alt="Collection Logo" class="collection-logo">
        <a href="editcollection.php?id=<?php echo $col['collection_id']; ?>" class="edit-link">✎ Edit</a>
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

          <p><strong>Tags:</strong> Pokemon, Cards, Anime, TCG</p>
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
          <div class="event-card">
            <a href="pasteventpage.php">
              <img src="images/amadoraBD.png" alt="Amadora BD">
              <p>Amadora BD - International Comic</p>
            </a>
          </div>
          <div class="event-card">
            <a href="pasteventpage.php">
              <img src="images/iberanime.png" alt="Iberanime">
              <p>Iberanime Porto</p>
            </a>
          </div>
          <div class="event-card">
            <a href="pasteventpage.php">
              <img src="images/lisbon.png" alt="Lisbon Games">
              <p>Lisbon Games</p>
            </a>
          </div>
        </div>

        <h3>Next Events</h3>
        <div class="next-event">
          <a href="eventpage.php">
            <img src="images/cardmadness.png" alt="Cardmadness 2026">
            <p><strong>CARDMADNESS 2026</strong></p>
          </a>
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
        <p>View upcoming events</p>
        <p><a href="eventhistory.php">Event history</a></p>
      </div>
    </aside>
  </div>

  <div id="hover-popup"></div>

  <script src="collectionpage.js"></script>
  <script src="logout.js"></script>

</body>
</html>