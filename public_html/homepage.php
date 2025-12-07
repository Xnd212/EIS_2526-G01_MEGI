<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = (int) $_SESSION['user_id'];

require_once __DIR__ . "/db.php";

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
// TOP COLLECTORS (SEMANAL)
// =====================================
$sqlTopCollectors = "
    SELECT 
        u.user_id,
        u.username,
        COUNT(i.item_id) AS items_this_week
    FROM user u
    JOIN collection c ON c.user_id = u.user_id
    JOIN contains con ON con.collection_id = c.collection_id
    JOIN item i ON i.item_id = con.item_id
    WHERE YEARWEEK(i.registration_date, 1) = YEARWEEK(CURDATE(), 1)
    GROUP BY u.user_id, u.username
    ORDER BY items_this_week DESC
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

// ================== ITEM EM DESTAQUE PARA CADA TOP COLLECTION ==================
$featuredByValue    = null;
$featuredMostRecent = null;
$featuredByItems    = null;

$featuredSql = "
    SELECT 
        i.name,
        i.price,
        i.acc_date,
        i.acc_place,
        img.url AS item_image
    FROM contains con
    JOIN item i       ON i.item_id = con.item_id
    LEFT JOIN image img ON img.image_id = i.image_id
    WHERE con.collection_id = ?
    ORDER BY i.price DESC, i.item_id DESC   -- aqui escolhemos o mais caro dessa coleção
    LIMIT 1
";

if ($topByValue) {
    $stmtF = $conn->prepare($featuredSql);
    $stmtF->bind_param('i', $topByValue['collection_id']);
    $stmtF->execute();
    $resF = $stmtF->get_result();
    $featuredByValue = $resF->fetch_assoc() ?: null;
    $stmtF->close();
}

if ($topMostRecent) {
    $stmtF = $conn->prepare($featuredSql);
    $stmtF->bind_param('i', $topMostRecent['collection_id']);
    $stmtF->execute();
    $resF = $stmtF->get_result();
    $featuredMostRecent = $resF->fetch_assoc() ?: null;
    $stmtF->close();
}

if ($topByItems) {
    $stmtF = $conn->prepare($featuredSql);
    $stmtF->bind_param('i', $topByItems['collection_id']);
    $stmtF->execute();
    $resF = $stmtF->get_result();
    $featuredByItems = $resF->fetch_assoc() ?: null;
    $stmtF->close();
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
                <form action="search.php" method="GET">
                    <input type="text" name="q" placeholder="Search for friends, collections, events, items..." required>
                </form>
            </div>
            <div class="icons">
                <!-- Botão de notificações -->
                <?php include __DIR__ . '/notifications_popup.php'; ?>


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
    </header>


    <div class="main">
        <div class="content">

            <!-- ========== EVENTOS ========= -->
            <section class="events-section">
                <h2 class="section-title1">Events you might be interested in 👁</h2>

                <div class="events-carousel">
                    <div class="events-track" id="events-track">
                        <?php if (!empty($recommendedEvents)): ?>
                            <?php foreach ($recommendedEvents as $event): ?>
                                <div class="event-card">
                                    <a href="eventpage.php?id=<?= htmlspecialchars($event['event_id']) ?>" class="event-link">
                                        <div class="event-img-wrapper">
                                            <img
                                                src="<?= htmlspecialchars($event['event_image'] ?? 'images/default_event.png') ?>"
                                                alt="<?= htmlspecialchars($event['name']) ?>"
                                                >
                                        </div>

                                        <p class="event-title">
                                            <?= htmlspecialchars($event['name']) ?>
                                        </p>
                                    </a>

                                    <div class="see-more">
                                                <a href="eventpage.php?id=<?= htmlspecialchars($event['event_id']) ?>" class="see-more-link">
                                                    <span class="see-more-icon">+</span>
                                                    <span>See more</span>
                                                </a>
                                    </div>

                                </div>

                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
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
        <ol class="top-collector-list"
            id="top-collectors-list"
            data-current-user-id="<?php echo (int)$currentUserId; ?>">

            <?php foreach ($topCollectors as $idx => $collector): ?>
                <?php
                    $pos = $idx + 1;
                    if     ($pos === 1) { $medal = "🥇"; $medalClass = "gold"; }
                    elseif ($pos === 2) { $medal = "🥈"; $medalClass = "silver"; }
                    else                { $medal = "🥉"; $medalClass = "bronze"; }
                ?>
                <li data-user-id="<?php echo (int)$collector['user_id']; ?>">
                    <span class="medal <?php echo $medalClass; ?>">
                        <?php echo $medal; ?>
                    </span>

                    <div class="collector-info">
                        <span class="collector-name">
                            <?php echo htmlspecialchars($collector['username']); ?>
                        </span>

                        <span class="collector-items">
                            <?php echo (int)$collector['items_this_week']; ?> items
                        </span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php else: ?>
        <p style="padding: 0 1rem;">No collectors this week.</p>
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


<!-- ========== TOP COLLECTIONS ========= -->
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

                $feat      = $featuredByValue;
                $itemTitle = $feat['name'] ?? '';
                $itemPrice = isset($feat['price']) ? number_format($feat['price'], 2, ',', '') : '';
                $itemDate  = !empty($feat['acc_date']) ? date('d/m/Y', strtotime($feat['acc_date'])) : '';
                $itemPlace = $feat['acc_place'] ?? '';
                $itemImg   = !empty($feat['item_image']) ? $feat['item_image'] : 'images/default_item.png';
            ?>
            <div class="top-collection-block"
                 id="price-card"
                 onclick="window.location.href='collectionpage.php?id=<?php echo (int)$topByValue['collection_id']; ?>'"
                 data-id="price-card"
                 data-collection-name="<?php echo htmlspecialchars($topByValue['collection_name']); ?>"
                 data-collection-user="<?php echo htmlspecialchars($topByValue['username']); ?>"
                 data-collection-value="<?php echo number_format($topByValue['total_value'], 2, ',', ''); ?>"
                 data-collection-items="<?php echo (int)$topByValue['num_items']; ?>"
                 data-collection-lastupdated="<?php echo $lastUpdPrice; ?>"
                 data-item-title="<?php echo htmlspecialchars($itemTitle); ?>"
                 data-item-price="<?php echo $itemPrice; ?>"
                 data-item-date="<?php echo $itemDate; ?>"
                 data-item-place="<?php echo htmlspecialchars($itemPlace); ?>"
                 data-item-image="<?php echo htmlspecialchars($itemImg); ?>"
            >
                <h3 class="top-collection-title">Price</h3>
                <img src="<?php echo htmlspecialchars($imgPrice); ?>"
                     alt="<?php echo htmlspecialchars($topByValue['collection_name']); ?>">
                <p class="collection-name"><?php echo htmlspecialchars($topByValue['collection_name']); ?></p>
                <p class="collection-author"><?php echo htmlspecialchars($topByValue['username']); ?></p>
                <p class="collection-extra">
                    Value: <?php echo number_format($topByValue['total_value'], 2, ',', ''); ?>€
                </p>
                <p class="collection-extra">
                    Items: <?php echo (int)$topByValue['num_items']; ?>
                </p>
                <p class="collection-date">
                    Last updated: <?php echo $lastUpdPrice; ?>
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

                $featR      = $featuredMostRecent;
                $itemTitleR = $featR['name'] ?? '';
                $itemPriceR = isset($featR['price']) ? number_format($featR['price'], 2, ',', '') : '';
                $itemDateR  = !empty($featR['acc_date']) ? date('d/m/Y', strtotime($featR['acc_date'])) : '';
                $itemPlaceR = $featR['acc_place'] ?? '';
                $itemImgR   = !empty($featR['item_image']) ? $featR['item_image'] : 'images/default_item.png';
            ?>
            <div class="top-collection-block"
                 id="recent-card"
                 onclick="window.location.href='collectionpage.php?id=<?php echo (int)$topMostRecent['collection_id']; ?>'"
                 data-id="recent-card"
                 data-collection-name="<?php echo htmlspecialchars($topMostRecent['collection_name']); ?>"
                 data-collection-user="<?php echo htmlspecialchars($topMostRecent['username']); ?>"
                 data-collection-value="<?php echo number_format($topMostRecent['total_value'], 2, ',', ''); ?>"
                 data-collection-items="<?php echo (int)$topMostRecent['num_items']; ?>"
                 data-collection-lastupdated="<?php echo $lastUpdRecent; ?>"
                 data-item-title="<?php echo htmlspecialchars($itemTitleR); ?>"
                 data-item-price="<?php echo $itemPriceR; ?>"
                 data-item-date="<?php echo $itemDateR; ?>"
                 data-item-place="<?php echo htmlspecialchars($itemPlaceR); ?>"
                 data-item-image="<?php echo htmlspecialchars($itemImgR); ?>"
            >
                <h3 class="top-collection-title">Most recent</h3>
                <img src="<?php echo htmlspecialchars($imgRecent); ?>"
                     alt="<?php echo htmlspecialchars($topMostRecent['collection_name']); ?>">
                <p class="collection-name"><?php echo htmlspecialchars($topMostRecent['collection_name']); ?></p>
                <p class="collection-author"><?php echo htmlspecialchars($topMostRecent['username']); ?></p>
                <p class="collection-extra">
                    Value: <?php echo number_format($topMostRecent['total_value'], 2, ',', ''); ?>€
                </p>
                <p class="collection-extra">
                    Items: <?php echo (int)$topMostRecent['num_items']; ?>
                </p>
                <p class="collection-date">
                    Last updated: <?php echo $lastUpdRecent; ?>
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

                $featI      = $featuredByItems;
                $itemTitleI = $featI['name'] ?? '';
                $itemPriceI = isset($featI['price']) ? number_format($featI['price'], 2, ',', '') : '';
                $itemDateI  = !empty($featI['acc_date']) ? date('d/m/Y', strtotime($featI['acc_date'])) : '';
                $itemPlaceI = $featI['acc_place'] ?? '';
                $itemImgI   = !empty($featI['item_image']) ? $featI['item_image'] : 'images/default_item.png';
            ?>
            <div class="top-collection-block"
                 id="items-card"
                 onclick="window.location.href='collectionpage.php?id=<?php echo (int)$topByItems['collection_id']; ?>'"
                 data-id="items-card"
                 data-collection-name="<?php echo htmlspecialchars($topByItems['collection_name']); ?>"
                 data-collection-user="<?php echo htmlspecialchars($topByItems['username']); ?>"
                 data-collection-value="<?php echo number_format($topByItems['total_value'], 2, ',', ''); ?>"
                 data-collection-items="<?php echo (int)$topByItems['num_items']; ?>"
                 data-collection-lastupdated="<?php echo $lastUpdItems; ?>"
                 data-item-title="<?php echo htmlspecialchars($itemTitleI); ?>"
                 data-item-price="<?php echo $itemPriceI; ?>"
                 data-item-date="<?php echo $itemDateI; ?>"
                 data-item-place="<?php echo htmlspecialchars($itemPlaceI); ?>"
                 data-item-image="<?php echo htmlspecialchars($itemImgI); ?>"
            >
                <h3 class="top-collection-title">More items</h3>
                <img src="<?php echo htmlspecialchars($imgItems); ?>"
                     alt="<?php echo htmlspecialchars($topByItems['collection_name']); ?>">
                <p class="collection-name"><?php echo htmlspecialchars($topByItems['collection_name']); ?></p>
                <p class="collection-author"><?php echo htmlspecialchars($topByItems['username']); ?></p>
                <p class="collection-extra">
                    Value: <?php echo number_format($topByItems['total_value'], 2, ',', ''); ?>€
                </p>
                <p class="collection-extra">
                    Items: <?php echo (int)$topByItems['num_items']; ?>
                </p>
                <p class="collection-date">
                    Last updated: <?php echo $lastUpdItems; ?>
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