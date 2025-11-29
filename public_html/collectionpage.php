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
// Ex: collectionpage.php?id=1
$collection_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$collection_id) {
    die("Coleção inválida.");
}

// ====== BUSCAR DADOS DA COLEÇÃO ======
// assumo que tens uma tabela `user` com campo `username`
$sql = "SELECT 
            c.collection_id,
            c.user_id,
            c.name,
            c.Theme,
            c.image_id,
            c.starting_date,
            c.description,
            u.username,
            i.*
        FROM collection c
        LEFT JOIN user u ON c.user_id = u.user_id
        LEFT JOIN image i ON c.image_id = i.image_id
        WHERE c.collection_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $collection_id);
$stmt->execute();
$result = $stmt->get_result();
$col = $result->fetch_assoc();

// ====== BUSCAR ITENS DA COLEÇÃO ======
$item_sql = "SELECT 
                it.item_id,
                it.name,
                it.image_id,
                img.url AS item_url
            FROM item it
            LEFT JOIN image img ON it.image_id = img.image_id
            WHERE it.collection_id = ?";

$item_stmt = $conn->prepare($item_sql);
$item_stmt->bind_param("i", $collection_id);
$item_stmt->execute();
$item_result = $item_stmt->get_result();


if (!$col) {
    die("Coleção não encontrada.");
}

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
  <!-- ===== HEADER ===== -->
  <header>
    <a href="homepage.php" class="logo">
      <img src="images/TrallE_2.png" alt="logo" />
    </a>

    <div class="search-bar">
      <input type="text" placeholder="Search" />
    </div>

    <div class="icons">
      <!-- Botão de notificações -->
      <button class="icon-btn" aria-label="Notificações" id="notification-btn">🔔</button>
      <div class="notification-popup" id="notification-popup">
        <div class="popup-header">
          <h3>Notifications <span>🔔</span></h3>
        </div>

        <hr class="popup-divider">
        <ul class="notification-list">
          <li><strong>Ana_Rita_Lopes</strong> added 3 new items to the Pokémon Cards collection.</li>
          <li><strong>Tomás_Freitas</strong> created a new collection: Vintage Stamps.</li>
          <li><strong>David_Ramos</strong> updated his Funko Pop inventory.</li>
          <li><strong>Telmo_Matos</strong> joined the event: Iberanime Porto 2025.</li>
          <li><strong>Marco_Pereira</strong> started following your Panini Stickers collection.</li>
          <li><strong>Ana_Rita_Lopes</strong> added 1 new items to the Pokémon Champion’s Path collection.</li>
          <li><strong>Telmo_Matos</strong> added added 3 new items to the Premier League Stickers collection.</li>
          <li><strong>Marco_Pereira</strong> created a new event: Card Madness Meetup.</li>
        </ul>

        <a href="#" class="see-more-link">+ See more</a>
      </div>

      <a href="userpage.php" class="icon-btn" aria-label="Perfil">👤</a>
    </div>
  </header>

  <div class="main">
    <div class="content">
      <!-- NOME DA COLEÇÃO VINDO DA BD -->
      <h2><?php echo htmlspecialchars($col['name']); ?></h2>

      <div class="collection-details">
        <div class="collection-logo-wrapper">

        <?php
        // vai buscar o campo 'url' da tabela image
        $image_src = !empty($col['url']) 
                     ? $col['url'] 
                     : 'images/default.png';  // fallback
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

          <!-- Por enquanto estático, até ligares à tabela de itens -->
          <p><strong>Most recent Item:</strong> <a href="itempage.php">Champion's Path Charizard V (PSA 10)</a></p>

          <?php if (!empty($col['description'])): ?>
            <p><strong>Description:</strong>
              <?php echo nl2br(htmlspecialchars($col['description'])); ?>
            </p>
          <?php endif; ?>

          <!-- Tags ainda estão estáticas -->
          <p><strong>Tags:</strong> Pokemon, Cards, Anime, TCG</p>
        </div>
      </div>

        <div class="items-section">
          <h3>Collection Items</h3>
          <div class="items-grid">
            <?php if ($item_result->num_rows > 0): ?>
              <?php while ($item = $item_result->fetch_assoc()): ?>
                <?php
                  $item_img = !empty($item['item_url']) ? $item['item_url'] : 'images/default.png';
                ?>
                <div class="item-card">
                  <a href="itempage.php?id=<?php echo $item['item_id']; ?>">
                    <img src="<?php echo htmlspecialchars($item_img); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <p class="item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                    <p class="edit-btn">Remove item</p>
                  </a>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <p>No items in this collection yet.</p>
            <?php endif; ?>
          </div>
        </div>


      <!-- ===== EVENTOS (ainda estáticos) ===== -->
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
        <p><a href="userfriendspage.php">Viem Friends</a></p>
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
</body>
</html>



