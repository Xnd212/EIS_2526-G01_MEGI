<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = (int) $_SESSION['user_id'];

// ===== LIGAÇÃO À BD =====
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

/*
 * EVENTOS RECOMENDADOS
 * - Tags das coleções do user atual (collection + collection_tags)
 * - Eventos futuros (event.date >= CURDATE())
 * - Eventos em que exista pelo menos uma coleção (de qualquer user)
 *   com uma dessas tags (attends + collection + collection_tags)
 */
$sqlRecommended = "
    SELECT DISTINCT
        e.event_id,
        e.name,
        e.date,
        e.theme,
        e.place,
        e.teaser_url,
        e.image_id,
        img.url AS event_image
    FROM event e
    JOIN attends a              ON a.event_id = e.event_id
    JOIN collection c_evt       ON c_evt.collection_id = a.collection_id
    JOIN collection_tags ct_evt ON ct_evt.collection_id = c_evt.collection_id
    LEFT JOIN image img         ON e.image_id = img.image_id
    WHERE e.date >= CURDATE()
      AND e.user_id <> ?              -- NÃO mostrar eventos criados pelo user logado
      AND ct_evt.tag_id IN (
          SELECT DISTINCT ct_user.tag_id
          FROM collection c_user
          JOIN collection_tags ct_user
                ON ct_user.collection_id = c_user.collection_id
          WHERE c_user.user_id = ?     -- tags das coleções do user logado
      )
    ORDER BY e.date ASC
    LIMIT 5
";

$stmt = $conn->prepare($sqlRecommended);
$stmt->bind_param("ii", $currentUserId, $currentUserId);

$stmt->execute();
$result = $stmt->get_result();

$recommendedEvents = [];
while ($row = $result->fetch_assoc()) {
    $recommendedEvents[] = $row;
}
$stmt->close();

/* Fallback: se não houver nenhum evento com tags em comum,
   mostra simplesmente os próximos eventos futuros. */
if (empty($recommendedEvents)) {
    $sqlFallback = "
        SELECT
            e.event_id,
            e.name,
            e.date,
            e.theme,
            e.place,
            e.teaser_url,
            e.image_id,
            img.url AS event_image
        FROM event e
        LEFT JOIN image img ON e.image_id = img.image_id
        WHERE e.date >= CURDATE()
          AND e.user_id <> ?      -- também aqui só eventos de outros users
        ORDER BY e.date ASC
        LIMIT 5
    ";

    $stmt2 = $conn->prepare($sqlFallback);
    $stmt2->bind_param("i", $currentUserId);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($result2) {
        while ($row = $result2->fetch_assoc()) {
            $recommendedEvents[] = $row;
        }
    }

    $stmt2->close();
}

// =====================================
// RECENTLY EDITED COLLECTIONS (GLOBAL)
// =====================================
$sqlRecentCollections = "
    SELECT
        c.collection_id,
        c.name            AS collection_name,
        c.starting_date,
        c.image_id,
        img.url           AS collection_image,
        u.username
    FROM collection c
    LEFT JOIN image img ON c.image_id = img.image_id
    LEFT JOIN user  u   ON c.user_id = u.user_id
    WHERE c.user_id <> ?        -- NÃO mostrar coleções do user atual
    ORDER BY c.starting_date DESC
    LIMIT 9
";

$stmt3 = $conn->prepare($sqlRecentCollections);
$stmt3->bind_param("i", $currentUserId);
$stmt3->execute();
$resultCollections = $stmt3->get_result();

$recentCollections = [];
if ($resultCollections) {
    while ($row = $resultCollections->fetch_assoc()) {
        $recentCollections[] = $row;
    }
}
$stmt3->close();

// =====================================
// TOP COLLECTORS (GLOBAL)
// =====================================
$sqlTopCollectors = "
    SELECT 
        u.user_id,
        u.username,
        COUNT(DISTINCT con.item_id) AS total_items
    FROM user u
    JOIN collection c  ON c.user_id = u.user_id
    JOIN contains  con ON con.collection_id = c.collection_id
    GROUP BY u.user_id, u.username
    ORDER BY total_items DESC
    LIMIT 3
";
$resTopCollectors = $conn->query($sqlTopCollectors);

$topCollectors = [];
if ($resTopCollectors) {
    while ($row = $resTopCollectors->fetch_assoc()) {
        $topCollectors[] = $row;
    }
}

// =====================================
// TOP 3 ITEMS MAIS CAROS (GLOBAL)
// =====================================
$sqlTopItems = "
    SELECT 
        i.item_id,
        i.name,
        i.price,
        img.url      AS item_image,
        u.username   AS owner_name
    FROM item i
    JOIN contains con   ON con.item_id = i.item_id
    JOIN collection c   ON c.collection_id = con.collection_id
    JOIN user u         ON u.user_id = c.user_id
    LEFT JOIN image img ON img.image_id = i.image_id
    WHERE i.price IS NOT NULL
    GROUP BY i.item_id, i.name, i.price, img.url, u.username
    ORDER BY i.price DESC
    LIMIT 3
";
$resTopItems = $conn->query($sqlTopItems);

$topItems = [];
if ($resTopItems) {
    while ($row = $resTopItems->fetch_assoc()) {
        $topItems[] = $row;
    }
}

// ================= TOP COLLECTIONS (VALUE, MOST RECENT, MORE ITEMS) =================
$sqlCollectionsStats = "
    SELECT
        c.collection_id,
        c.name                         AS collection_name,
        c.image_id,
        u.username,
        img.url                        AS collection_image,
        COALESCE(SUM(i.price), 0)      AS total_value,
        COUNT(i.item_id)               AS num_items,
        MAX(i.registration_date)       AS last_updated
    FROM collection c
    JOIN user u           ON u.user_id      = c.user_id
    LEFT JOIN image img   ON img.image_id   = c.image_id
    LEFT JOIN contains con ON con.collection_id = c.collection_id
    LEFT JOIN item i      ON i.item_id      = con.item_id
    GROUP BY c.collection_id
";

$resStats = $conn->query($sqlCollectionsStats);

$collectionsStats = [];
if ($resStats) {
    while ($row = $resStats->fetch_assoc()) {
        $collectionsStats[] = $row;
    }
}

$topByValue    = null;
$topMostRecent = null;
$topByItems    = null;

foreach ($collectionsStats as $col) {
    if ($topByValue === null || $col['total_value'] > $topByValue['total_value']) {
        $topByValue = $col;
    }
    if (!empty($col['last_updated'])) {
        if ($topMostRecent === null || $col['last_updated'] > $topMostRecent['last_updated']) {
            $topMostRecent = $col;
        }
    }
    if ($topByItems === null || $col['num_items'] > $topByItems['num_items']) {
        $topByItems = $col;
    }
}

?>



<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Trall-E | Homepage</title>
        <link rel="stylesheet" href="homepage.css" />

    </head>
    <!-- comment -->
    <!-- comment 2 -->
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
                    <ul class="notification-list">
                        <li><strong>Ana_Rita_Lopes</strong> added 3 new items to the Pokémon Cards collection.</li>
                        <li><strong>Tomás_Freitas</strong> created a new collection: Vintage Stamps.</li>
                        <li><strong>David_Ramos</strong> updated his Funko Pop inventory.</li>
                        <li><strong>Telmo_Matos</strong> joined the event: Iberanime Porto 2025.</li>
                        <li><strong>Marco_Pereira</strong> started following your Panini Stickers collection.</li>
                        <li><strong>Ana_Rita_Lopes</strong> added 1 new items to the Pokémon Champion's Path collection.</li>
                        <li><strong>Telmo_Matos</strong> added 3 new items to the Premier League Stickers collection.</li>
                        <li><strong>Marco_Pereira</strong> created a new event: Card Madness Meetup.</li>
                    </ul>
                    <a href="#" class="see-more-link">+ See more</a>
                </div>

                <!-- Perfil -->
                <a href="userpage.php" class="icon-btn" aria-label="Perfil">👤</a>


                <!-- Logout -->
                <button class="icon-btn" id="logout-btn" type="button" aria-label="Logout">🚪</button>
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

        </div>
    </header>


    <div class="main">
        <div class="content">

            <!-- ========== EVENTOS ========= -->
        <section class="events-section">
            <h2 class="section-title1">Events you might be interested in 👁</h2>

            <div class="events-scroll">
                <?php if (!empty($recommendedEvents)): ?>
                    <?php foreach ($recommendedEvents as $event): ?>
                        <div class="event-card">
                            <!-- imagem + nome também levam link para o evento -->
                            <a href="eventpage.php?id=<?= htmlspecialchars($event['event_id']) ?>">
                                <img
                                    src="<?= htmlspecialchars($event['event_image'] ?? 'images/default_event.png') ?>"
                                    alt="<?= htmlspecialchars($event['name']) ?>"
                                >
                                <p><?= htmlspecialchars($event['name']) ?></p>
                            </a>

                            <div class="see-more">
                                <a
                                    href="eventpage.php?id=<?= htmlspecialchars($event['event_id']) ?>"
                                    class="see-more-link"
                                >
                                    <span class="see-more-icon">+</span> See more
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No recommended events found for you yet.</p>
                <?php endif; ?>
            </div>
        </section>

            

            <!-- ========== COLEÇÕES ========= -->       
            <div class="collections-and-collectors">
        <section class="collections">
            <h2 class="section-title1">Recently edited collections 📚</h2>

            <div class="collections-grid">
                <?php if (!empty($recentCollections)): ?>
                    <?php foreach ($recentCollections as $col): ?>
                        <div class="collection-card">
                            <a href="collectionpage.php?id=<?= htmlspecialchars($col['collection_id']) ?>">
                                <img
                                    src="<?= htmlspecialchars($col['collection_image'] ?? 'images/default_collection.png') ?>"
                                    alt="<?= htmlspecialchars($col['collection_name']) ?>"
                                >
                                <p class="collection-name">
                                    <?= htmlspecialchars($col['collection_name']) ?>
                                </p>
                                <span class="collection-author">
                                    <?= htmlspecialchars($col['username'] ?? 'Unknown user') ?>
                                </span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No collections found yet.</p>
                <?php endif; ?>
            </div>
        </section>

                <!-- ========== TOP COLECIONADORES ========= -->
                <div class="side-ranking">
                <section class="top-collectors-card">
                    <h2 class="section-title1">Top collectors of the week 🤝</h2>

                    <?php if (!empty($topCollectors)): ?>
                        <ol class="top-collector-list">
                            <?php foreach ($topCollectors as $idx => $collector): ?>
                                <?php
                                    $pos = $idx + 1;
                                    if     ($pos === 1) { $medal = "🥇"; $medalClass = "gold"; }
                                    elseif ($pos === 2) { $medal = "🥈"; $medalClass = "silver"; }
                                    else                { $medal = "🥉"; $medalClass = "bronze"; }
                                ?>
                                <li>
                                    <span class="medal <?php echo $medalClass; ?>">
                                        <?php echo $medal; ?>
                                    </span>
                                    <div class="collector-info">
                                        <span class="collector-name">
                                            <?php echo htmlspecialchars($collector['username']); ?>
                                        </span>
                                        <span class="collector-items">
                                            <?php echo (int)$collector['total_items']; ?> items
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <p style="padding: 0 1rem;">No collectors found yet.</p>
                    <?php endif; ?>
                </section>


                    <!-- ========== TOP ITENS ========= -->
            <section class="top-items-card">
                <h2 class="section-title1">Top 3 Items 💶</h2>

                <?php if (!empty($topItems)): ?>
                    <?php foreach ($topItems as $top): ?>
                        <?php
                            $img = !empty($top['item_image'])
                                ? $top['item_image']
                                : 'images/default_item.png';
                        ?>
                        <div class="top-item">
                            <a href="itempage.php?id=<?php echo (int)$top['item_id']; ?>">
                                <img src="<?php echo htmlspecialchars($img); ?>"
                                     alt="<?php echo htmlspecialchars($top['name']); ?>">
                            </a>
                            <div class="item-info">
                                <p class="item-name">
                                    <?php echo htmlspecialchars($top['name']); ?>
                                </p>
                                <p class="item-user">
                                    <?php echo htmlspecialchars($top['owner_name']); ?>
                                </p>
                                <p class="item-price">
                                    <?php echo number_format((float)$top['price'], 2, ',', ''); ?>€
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="padding: 0 1rem;">No items found yet.</p>
                <?php endif; ?>
            </section>

                </div>
            </div>


            <!-- ========== TOP COLLECTIONS (POPUP INCL.) ========= -->
           <section class="top-collections-section">
               <h2 class="section-title2">Top Collections ⭐</h2>

               <div class="top-collections-grid">

                   <!-- ======= Price (coleção com maior valor total) ======= -->
                   <?php if ($topByValue): ?>
                       <?php
                           $imgPrice = !empty($topByValue['collection_image'])
                               ? $topByValue['collection_image']
                               : 'images/default_collection.png';

                           $lastUpdPrice = $topByValue['last_updated']
                               ? date('d/m/Y', strtotime($topByValue['last_updated']))
                               : '—';
                       ?>
                       <div class="top-collection-block"
                            id="price-card"
                            data-id="price-card"
                            data-collection-name="<?= htmlspecialchars($topByValue['collection_name']); ?>"
                            data-collection-user="<?= htmlspecialchars($topByValue['username']); ?>"
                            data-collection-value="<?= number_format($topByValue['total_value'], 2, ',', ''); ?>€"
                            data-collection-items="<?= (int)$topByValue['num_items']; ?>"
                            data-collection-lastupdated="<?= $lastUpdPrice; ?>"
                       >
                           <h3 class="top-collection-title">Price</h3>
                           <img src="<?= htmlspecialchars($imgPrice); ?>"
                                alt="<?= htmlspecialchars($topByValue['collection_name']); ?>">
                           <p class="collection-name"><?= htmlspecialchars($topByValue['collection_name']); ?></p>
                           <p class="collection-author"><?= htmlspecialchars($topByValue['username']); ?></p>
                           <p class="collection-extra">
                               Value: <?= number_format($topByValue['total_value'], 2, ',', ''); ?>€
                           </p>
                           <p class="collection-extra">
                               Items: <?= (int)$topByValue['num_items']; ?>
                           </p>
                           <p class="collection-date">
                               Last updated: <?= $lastUpdPrice; ?>
                           </p>
                       </div>
                   <?php endif; ?>


                   <!-- ======= Most recent (coleção mais recentemente atualizada) ======= -->
                   <?php if ($topMostRecent): ?>
                       <?php
                           $imgRecent = !empty($topMostRecent['collection_image'])
                               ? $topMostRecent['collection_image']
                               : 'images/default_collection.png';

                           $lastUpdRecent = $topMostRecent['last_updated']
                               ? date('d/m/Y', strtotime($topMostRecent['last_updated']))
                               : '—';
                       ?>
                       <div class="top-collection-block"
                            id="recent-card"
                            data-id="recent-card"
                            data-collection-name="<?= htmlspecialchars($topMostRecent['collection_name']); ?>"
                            data-collection-user="<?= htmlspecialchars($topMostRecent['username']); ?>"
                            data-collection-value="<?= number_format($topMostRecent['total_value'], 2, ',', ''); ?>€
                            data-collection-items="<?= (int)$topMostRecent['num_items']; ?>"
                            data-collection-lastupdated="<?= $lastUpdRecent; ?>"
                       >
                           <h3 class="top-collection-title">Most recent</h3>
                           <img src="<?= htmlspecialchars($imgRecent); ?>"
                                alt="<?= htmlspecialchars($topMostRecent['collection_name']); ?>">
                           <p class="collection-name"><?= htmlspecialchars($topMostRecent['collection_name']); ?></p>
                           <p class="collection-author"><?= htmlspecialchars($topMostRecent['username']); ?></p>
                           <p class="collection-extra">
                               Value: <?= number_format($topMostRecent['total_value'], 2, ',', ''); ?>€
                           </p>
                           <p class="collection-extra">
                               Items: <?= (int)$topMostRecent['num_items']; ?>
                           </p>
                           <p class="collection-date">
                               Last updated: <?= $lastUpdRecent; ?>
                           </p>
                       </div>
                   <?php endif; ?>


                   <!-- ======= More items (coleção com mais items) ======= -->
                   <?php if ($topByItems): ?>
                       <?php
                           $imgItems = !empty($topByItems['collection_image'])
                               ? $topByItems['collection_image']
                               : 'images/default_collection.png';

                           $lastUpdItems = $topByItems['last_updated']
                               ? date('d/m/Y', strtotime($topByItems['last_updated']))
                               : '—';
                       ?>
                       <div class="top-collection-block"
                            id="items-card"
                            data-id="items-card"
                            data-collection-name="<?= htmlspecialchars($topByItems['collection_name']); ?>"
                            data-collection-user="<?= htmlspecialchars($topByItems['username']); ?>"
                            data-collection-value="<?= number_format($topByItems['total_value'], 2, ',', ''); ?>€
                            data-collection-items="<?= (int)$topByItems['num_items']; ?>"
                            data-collection-lastupdated="<?= $lastUpdItems; ?>"
                       >
                           <h3 class="top-collection-title">More items</h3>
                           <img src="<?= htmlspecialchars($imgItems); ?>"
                                alt="<?= htmlspecialchars($topByItems['collection_name']); ?>">
                           <p class="collection-name"><?= htmlspecialchars($topByItems['collection_name']); ?></p>
                           <p class="collection-author"><?= htmlspecialchars($topByItems['username']); ?></p>
                           <p class="collection-extra">
                               Value: <?= number_format($topByItems['total_value'], 2, ',', ''); ?>€
                           </p>
                           <p class="collection-extra">
                               Items: <?= (int)$topByItems['num_items']; ?>
                           </p>
                           <p class="collection-date">
                               Last updated: <?= $lastUpdItems; ?>
                           </p>
                       </div>
                   <?php endif; ?>

               </div>
           </section>



            <!-- ===== Right Sidebar (Under Header) ===== -->
            <aside class="sidebar">
                <div class="sidebar-section collections-section">
                    <h3>My collections</h3>
                    <p><a href="collectioncreation.php">Create collection</a></p>
                    <p><a href="itemcreation.php"> Create item</a></p>
                    <p><a href="mycollectionspage.php">View collections</a></p>
                    <p><a href="myitems.php">View items</a></p>
                </div>

                <div class="sidebar-section friends-section">
                    <h3>My friends</h3>
                    <p><a href="userfriendspage.php"> View Friends</a></p>
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
        </div>
    </div>




    <!-- === POPUP DINÂMICO === -->
    <div id="hover-popup"></div>

    <!-- === JAVASCRIPT === -->
    <script src="homepage.js"></script>
    <script src="logout.js"></script>

</body>
</html>