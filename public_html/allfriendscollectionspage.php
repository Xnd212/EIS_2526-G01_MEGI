<?php
session_start();

// ---------- USER LOGADO (para testes, caso ainda não tenhas login) ----------
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // muda depois quando tiveres login real
}
$currentUserId = (int) $_SESSION['user_id'];

// ---------- LIGAÇÃO À BD ----------
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

/*
   Queremos: todas as coleções dos friends do user atual.

   friends: user_id (quem tem o amigo) / friend_id (o amigo)
   collection: user_id (dono da coleção)
   user: username do dono
   image: imagem da coleção
*/

$sql = "
    SELECT
        c.collection_id,
        c.name AS collection_name,
        c.starting_date,
        c.image_id,
        u.user_id      AS owner_id,
        u.username     AS owner_username,
        img.url        AS collection_image
    FROM friends f
    INNER JOIN user u
        ON f.friend_id = u.user_id
    INNER JOIN collection c
        ON c.user_id = u.user_id
    LEFT JOIN image img
        ON c.image_id = img.image_id
    WHERE f.user_id = ?
    ORDER BY c.starting_date DESC, c.name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

$collections = [];
while ($row = $result->fetch_assoc()) {
    $collections[] = $row;
}
$stmt->close();
// $conn->close(); // se quiseres, no fim da página
?>

<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Friends' Collections</title>
  <link rel="stylesheet" href="mycollectionspage.css">
</head>


<body>    
  <!-- ===========================
       HEADER
  ============================ -->
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
      <div class="collections-and-friends">
        <section class="collections">
          <div class="collections-header">
            <h2>Friends' Collections</h2>
              
            <!-- Botão de filtro -->
            <button class="filter-toggle" id="filterToggle" aria-haspopup="true" aria-expanded="false">
              &#128269; Filter ▾
            </button>

            <!-- Menu de filtros -->
            <div class="filter-menu" id="filterMenu">
              <!-- Nome -->
              <button type="button" data-sort="alpha-asc">Name: A–Z</button>
              <button type="button" data-sort="alpha-desc">Name: Z–A</button>
              <hr>

              <!-- Preço (quando implementarem) -->
              <button type="button" data-sort="price-asc">Price: Low–High</button>
              <button type="button" data-sort="price-desc">Price: High–Low</button>
              <hr>

              <!-- Last updated (quando implementarem) -->
              <button type="button" data-sort="updated-desc">Last updated: New</button>
              <button type="button" data-sort="updated-asc">Last updated: Old</button>
              <hr>

              <!-- Creation date -->
              <button type="button" data-sort="created-desc">Created: New</button>
              <button type="button" data-sort="created-asc">Created: Old</button>
              <hr>

              <!-- Nº itens -->
              <button type="button" data-sort="items-desc">Items: Most</button>
              <button type="button" data-sort="items-asc">Items: Fewest</button>
            </div>
          </div>
              
          <div class="collection-grid" id="collectionGrid">
            <?php if (empty($collections)): ?>
              <p>Your friends don't have any collections yet.</p>
            <?php else: ?>
              <?php foreach ($collections as $col): ?>
                <?php
                  $imgSrc = !empty($col['collection_image'])
                      ? $col['collection_image']
                      : 'images/default_collection.png';

                  $owner  = $col['owner_username'];
                ?>
                <div class="collection-card">
                  <a href="collectionpage.php?id=<?php echo $col['collection_id']; ?>">
                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                         alt="<?php echo htmlspecialchars($col['collection_name']); ?>">
                    <p class="collection-name">
                      <?php echo htmlspecialchars($col['collection_name']); ?>
                    </p>
                    <span class="collection-author">
                      <?php echo htmlspecialchars($owner); ?>
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

   
  <!-- ===== Right Sidebar (Under Header) ===== -->
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
      <p><a href="userfriendspage.php"> Viem Friends</a></p>
      <p><a href="allfriendscollectionspage.php">View collections</a></p>
      <p><a href="teampage.php"> Team Page</a></p>
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
