<?php
session_start();

// ---------------------- USER LOGADO (para fallback) ----------------------
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // só para testes; depois tiras quando tiveres login
}
$currentUserId = (int) $_SESSION['user_id'];

// ---------------------- PERFIL CUJAS COLEÇÕES VAMOS VER ----------------------
$profileUserId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if (!$profileUserId) {
    // se não vier user_id no URL, mostra as coleções do próprio user logado
    $profileUserId = $currentUserId;
}

// ---------------------- LIGAÇÃO À BD ----------------------
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

// ---------------------- 1) BUSCAR DADOS DO UTILIZADOR ----------------------
$sqlUser = "
    SELECT 
        u.user_id,
        u.username
    FROM user u
    WHERE u.user_id = ?
";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $profileUserId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$profile = $resultUser->fetch_assoc();
$stmtUser->close();

if (!$profile) {
    die("Utilizador não encontrado.");
}

// ---------------------- 2) BUSCAR COLEÇÕES DO UTILIZADOR ----------------------
$sqlCollections = "
    SELECT 
        c.collection_id,
        c.name,
        c.starting_date,
        c.image_id,
        c.Theme,
        img.url AS collection_image
    FROM collection c
    LEFT JOIN image img ON c.image_id = img.image_id
    WHERE c.user_id = ?
    ORDER BY c.starting_date DESC
";
$stmtC = $conn->prepare($sqlCollections);
$stmtC->bind_param("i", $profileUserId);
$stmtC->execute();
$resultC = $stmtC->get_result();

$collections = [];
while ($row = $resultC->fetch_assoc()) {
    $collections[] = $row;
}
$stmtC->close();

// (podes fechar a ligação no fim da página, se quiseres)
// $conn->close();
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Trall-E | <?php echo htmlspecialchars($profile['username']); ?>'s Collections</title>
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
                <form action="search.php" method="GET">
                    <input type="text" name="q" placeholder="Search for friends, collections, events, items..." required>
                </form>
            </div>

            <div class="icons">
                <!-- Botão de notificações -->
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
                <div class="collections-and-friends">
                    <section class="collections">
                        <div class="collections-header">
                            <h2><?php echo htmlspecialchars($profile['username']); ?>'s Collections</h2>

                            <!-- Botão de filtro -->
                            <button class="filter-toggle" id="filterToggle" aria-haspopup="true" aria-expanded="false">
                                &#128269; Filter ▾
                            </button>

                            <!-- Menu de filtros (fica igual, o JS trata do resto) -->
                            <div class="filter-menu" id="filterMenu">

                                <!-- Nome -->
                                <button type="button" data-sort="alpha-asc">Name: A–Z</button>
                                <button type="button" data-sort="alpha-desc">Name: Z–A</button>
                                <hr>

                                <!-- Preço -->
                                <button type="button" data-sort="price-asc">Price: Low–High</button>
                                <button type="button" data-sort="price-desc">Price: High–Low</button>
                                <hr>

                                <!-- Last updated -->
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
                                <p>This user has no collections yet.</p>
                            <?php else: ?>
                                <?php foreach ($collections as $col): ?>
                                    <?php
                                        $imgSrc = !empty($col['collection_image'])
                                            ? $col['collection_image']
                                            : 'images/default_collection.png';

                                        $lastUpdated = !empty($col['starting_date'])
                                            ? date('d/m/Y', strtotime($col['starting_date']))
                                            : '-';
                                    ?>
                                    <div class="collection-card">
                                        <a href="collectionpage.php?id=<?php echo $col['collection_id']; ?>">
                                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                                                 alt="<?php echo htmlspecialchars($col['name']); ?>">
                                            <p><strong><?php echo htmlspecialchars($col['name']); ?></strong></p>
                                            <span class="last-updated">
                                                Last updated: <?php echo $lastUpdated; ?>
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


        <!-- === JAVASCRIPT === -->
        <script src="friendscollectionspage.js"></script>
        <script src="logout.js"></script>
    </body>
</html>
