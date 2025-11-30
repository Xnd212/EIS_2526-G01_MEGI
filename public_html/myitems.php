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

// ====== DEFINIR O USER (ajusta depois para sessão) ======
$user_id = 1; // TODO: trocar por $_SESSION['user_id'] quando tiveres login

// ====== BUSCAR ITENS DESSE UTILIZADOR ======
// item -> collection -> user
$sql = "SELECT 
            it.item_id,
            it.name AS item_name,
            img.url AS item_url,
            c.name AS collection_name
        FROM item it
        INNER JOIN collection c ON it.collection_id = c.collection_id
        LEFT JOIN image img ON it.image_id = img.image_id
        WHERE c.user_id = ?
        ORDER BY it.name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Trall-E | My Items</title>
    <link rel="stylesheet" href="myitems.css">

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
    </header>

    <div class="main">
        <div class="content">
            <div class="collections-and-friends">
                <section class="collections">
                    <div class="collections-header">
                        <h2>My Items</h2>

                        <!-- Botão de filtro (mantido igual) -->
                        <button class="filter-toggle" id="filterToggle" aria-haspopup="true" aria-expanded="false">
                            &#128269; Filter ▾
                        </button>

                        <!-- Menu de filtros (mantido igual, mesmo texto) -->
                        <div class="filter-menu" id="filterMenu">
                            <button type="button" data-sort="alpha-asc">Name: A–Z</button>
                            <button type="button" data-sort="alpha-desc">Name: Z–A</button>
                            <hr>
                            <button type="button" data-sort="price-asc">Price: Low–High</button>
                            <button type="button" data-sort="price-desc">Price: High–Low</button>
                            <hr>
                            <button type="button" data-sort="updated-desc">Last updated: New</button>
                            <button type="button" data-sort="updated-asc">Last updated: Old</button>
                            <hr>
                            <button type="button" data-sort="created-desc">Created: New</button>
                            <button type="button" data-sort="created-asc">Created: Old</button>
                            <hr>
                            <button type="button" data-sort="items-desc">Items: Most</button>
                            <button type="button" data-sort="items-asc">Items: Fewest</button>
                        </div>
                    </div>

                    <!-- ====== GRID DE ITENS (DINÂMICO) ====== -->
                    <div class="item-grid">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php $img = !empty($row['item_url']) ? $row['item_url'] : 'images/default.png'; ?>
                                <div class="item-card">
                                    <a href="itempage.php?id=<?php echo $row['item_id']; ?>">
                                        <img src="<?php echo htmlspecialchars($img); ?>" 
                                             alt="<?php echo htmlspecialchars($row['item_name']); ?>">
                                        <p class="item-title"><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></p>
                                        <?php if (!empty($row['collection_name'])): ?>
                                            <span class="item-collection">
                                                From collection: <?php echo htmlspecialchars($row['collection_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>You don't have any items yet.</p>
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
            <p><a href="mycollection.php">View collections</a></p>
            <p><a href="myitems.php">View items</a></p>
        </div>

        <div class="sidebar-section friends-section">
            <h3>My friends</h3>
            <p><a href="userfriendspage.php">Viem Friends</a></p>
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
    <script src="mycollectionspage.js"></script>
    <script src="logout.js"></script>
</body>
</html>




