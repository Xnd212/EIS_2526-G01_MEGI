<?php
session_start();

// ---------- USER LOGADO ----------
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = (int) $_SESSION['user_id'];

$profileUserId = $currentUserId;  // ALWAYS your own user



// ---------- LIGAÇÃO À BD ----------
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

/* =====================================================
   1) DADOS DO PERFIL (user + image + contagens)
   ===================================================== */
$sqlUser = "
    SELECT 
        u.user_id,
        u.username,
        u.email,
        u.country,
        u.image_id,
        img.url AS profile_image,

        /* Contar items através das coleções do user (usando tabela contains) */
        (
            SELECT COUNT(*)
            FROM contains con
            INNER JOIN collection c2 ON con.collection_id = c2.collection_id
            WHERE c2.user_id = u.user_id
        ) AS total_items,

        /* Contar coleções do user */
        (SELECT COUNT(*) FROM collection c WHERE c.user_id = u.user_id) AS total_collections,

        /* Contar amigos do user */
        (SELECT COUNT(*) FROM friends f WHERE f.user_id = u.user_id) AS total_friends

    FROM user u
    LEFT JOIN image img ON u.image_id = img.image_id
    WHERE u.user_id = ?
";

$stmtUser = $conn->prepare($sqlUser);
if (!$stmtUser) {
    die("Prepare failed: " . $conn->error);
}
$stmtUser->bind_param("i", $profileUserId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$profile = $resultUser->fetch_assoc();
$stmtUser->close();

if (!$profile) {
    die("Utilizador não encontrado.");
}

$profileImgSrc = !empty($profile['profile_image'])
    ? $profile['profile_image']
    : "images/default_avatar.png";

/* ==========================================
   2) LISTA DE AMIGOS DO PERFIL (friends+user)
   ========================================== */
$sqlFriends = "
    SELECT 
        u.user_id,
        u.username,
        u.image_id,
        u.country,
        u.email,
        img.url AS friend_image
    FROM friends f
    INNER JOIN user u ON f.friend_id = u.user_id
    LEFT JOIN image img ON u.image_id = img.image_id
    WHERE f.user_id = ?
";
$stmtF = $conn->prepare($sqlFriends);
if (!$stmtF) {
    die("Prepare failed (friends): " . $conn->error);
}
$stmtF->bind_param("i", $profileUserId);
$stmtF->execute();
$resultF = $stmtF->get_result();

$friends = [];
while ($row = $resultF->fetch_assoc()) {
    $friends[] = $row;
}
$stmtF->close();

/* ==========================================
   2.5) CHECK IF CURRENT USER IS FRIENDS WITH PROFILE USER
   ========================================== */
$isFriend = false;
if ($currentUserId !== $profileUserId) {
    $sqlCheckFriend = "
        SELECT 1 
        FROM friends 
        WHERE user_id = ? AND friend_id = ?
        LIMIT 1
    ";
    $stmtCheck = $conn->prepare($sqlCheckFriend);
    if ($stmtCheck) {
        $stmtCheck->bind_param("ii", $currentUserId, $profileUserId);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $isFriend = ($resultCheck->num_rows > 0);
        $stmtCheck->close();
    }
}

/* ==========================================
   3) COLEÇÕES DO PERFIL (tabela collection)
   ========================================== */
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
if (!$stmtC) {
    die("Prepare failed (collections): " . $conn->error);
}
$stmtC->bind_param("i", $profileUserId);
$stmtC->execute();
$resultC = $stmtC->get_result();

$collections = [];
while ($row = $resultC->fetch_assoc()) {
    $collections[] = $row;
}
$stmtC->close();

/* ==========================================
   4) EVENTOS DO PERFIL (tabela event)
   ========================================== */
$sqlEvents = "
    SELECT 
        e.event_id,
        e.name,
        e.date,
        e.place,
        e.teaser_url,
        e.image_id,
        img.url AS event_image
    FROM event e
    LEFT JOIN image img ON e.image_id = img.image_id
    WHERE e.user_id = ?
    ORDER BY e.date DESC
";
$stmtE = $conn->prepare($sqlEvents);
if (!$stmtE) {
    die("Prepare failed (events): " . $conn->error);
}
$stmtE->bind_param("i", $profileUserId);
$stmtE->execute();
$resultE = $stmtE->get_result();

$events = [];
while ($row = $resultE->fetch_assoc()) {
    $events[] = $row;
}
$stmtE->close();


?>
<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | <?php echo htmlspecialchars($profile['username']); ?>'s Page</title>
  <link rel="stylesheet" href="homepage.css" />
  <link rel="stylesheet" href="userpage.css">
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
          <li><strong>Ana_Rita_Lopes</strong> added 1 new items to the Pokémon Champion's Path collection.</li>
          <li><strong>Telmo_Matos</strong> added 3 new items to the Premier League Stickers collection.</li>
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
    <div class="profile-container">
      <!-- ====================== PERFIL ====================== -->
      <section class="user-info">
        <div class="user-profile-box">
          <div class="user-box">
            <img src="<?php echo htmlspecialchars($profileImgSrc); ?>" 
                 alt="User Photo" class="user-photo" />

            <div class="user-info-wrapper">
              <div class="user-details-and-stats">
                <div class="user-details">
                  <h2 class="username">
                    <?php echo htmlspecialchars($profile['username']); ?>
                  </h2>
                  <p class="email">
                    <?php echo htmlspecialchars($profile['email']); ?>
                  </p>

                <?php if ($currentUserId !== $profileUserId): ?>
                  <?php if ($isFriend): ?>
                    <a
                      class="edit-btn active"
                      href="remove_friend.php?friend_id=<?php echo $profile['user_id']; ?>"
                      data-state="added"
                    >
                      ✔ Friend Added
                    </a>
                  <?php else: ?>
                    <a
                      class="edit-btn"
                      href="add_friend.php?friend_id=<?php echo $profile['user_id']; ?>"
                      data-state="default"
                    >
                      👥 Add Friend
                    </a>
                  <?php endif; ?>
                <?php else: ?>
                  <!-- Se for o próprio, mostra edit profile -->
                  <a href="editprofile.php" class="edit-btn">✏️ Edit profile</a>
                <?php endif; ?>


                </div>

                <!-- Stats à direita -->
                <div class="stats">
                  <div>
                    <strong><?php echo (int) $profile['total_items']; ?></strong><br>Items
                  </div>
                  <div>
                    <strong><?php echo (int) $profile['total_collections']; ?></strong><br>Collections
                  </div>
                  <div>
                    <strong><?php echo (int) $profile['total_friends']; ?></strong><br>Friends
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </section>
    
    
      <!-- ========== COLLECTIONS + FRIENDS ========== -->
      <div class="collections-and-friends">
        <!-- COLLECTIONS dinâmicas -->
        <section class="collections">
          <h3>Collections</h3>
          <div class="collection-grid">
            <?php if (empty($collections)): ?>
              <p>This user has no collections yet.</p>
            <?php else: ?>
              <?php foreach ($collections as $col): ?>
                <?php
                  $colImg = !empty($col['collection_image'])
                      ? $col['collection_image']
                      : 'images/default_collection.png';
                ?>
                <div class="collection-card">
                  <a href="collectionpage.php?id=<?php echo (int)$col['collection_id']; ?>">
                    <img src="<?php echo htmlspecialchars($colImg); ?>" 
                         alt="<?php echo htmlspecialchars($col['name']); ?>">
                    <p><strong><?php echo htmlspecialchars($col['name']); ?></strong></p>
                    <span class="last-updated">
                      Last updated:
                      <?php
                        if (!empty($col['starting_date'])) {
                            echo date('d/m/Y', strtotime($col['starting_date']));
                        } else {
                            echo '-';
                        }
                      ?>
                    </span>
                  </a>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>

            <!-- card para ver todas as coleções deste user -->
            <div class="collection-card">
              <a href="mycollectionspage.php" class="view-all">+ See more</a>

            </div>
          </div>
        </section>

        <!-- FRIENDS do perfil atual -->
        <section class="friends">
          <h3>Friends</h3>
          <div class="friends-grid">

            <?php if (empty($friends)): ?>

              <p>This user doesn't have any friends yet.</p>

            <?php else: ?>

              <?php foreach ($friends as $friend): ?>
                <?php
                  $friendImgSrc = !empty($friend['friend_image'])
                      ? $friend['friend_image']
                      : 'images/default_avatar.png';

                  if ((int)$friend['user_id'] === $currentUserId) {
                      $friendLink = "userpage.php";
                  } else {
                      $friendLink = "friendpage.php?user_id=" . (int)$friend['user_id'];
                  }
                ?>

                <div class="friend">
                  <a href="<?php echo $friendLink; ?>">
                    <img src="<?php echo htmlspecialchars($friendImgSrc); ?>"
                         alt="<?php echo htmlspecialchars($friend['username']); ?>">
                    <p class="friend-name">
                      <strong><?php echo htmlspecialchars($friend['username']); ?></strong>
                    </p>
                  </a>
                </div>

              <?php endforeach; ?>

            <?php endif; ?>

          </div>

          <a href="userfriendspage.php" class="view-all">+ See more</a>
        </section>

      </div>
    
      <!-- ====================== PAST EVENTS ====================== -->
      <section class="past-events">
        <h3>Past events</h3>
        <div class="past-events-grid">
          <?php if (empty($events)): ?>
            <p>This user has no past events.</p>
          <?php else: ?>
            <?php foreach ($events as $ev): ?>
              <?php
                if (!empty($ev['event_image'])) {
                    $eventImg = $ev['event_image'];
                } elseif (!empty($ev['teaser_url'])) {
                    $eventImg = $ev['teaser_url'];
                } else {
                    $eventImg = 'images/default_event.png';
                }

                $eventDate = !empty($ev['date'])
                    ? date('d/m/Y', strtotime($ev['date']))
                    : '-';
              ?>
              <div class="past-event-card">
                <img src="<?php echo htmlspecialchars($eventImg); ?>" 
                     alt="<?php echo htmlspecialchars($ev['name']); ?>">
                <p class="event-name"><?php echo htmlspecialchars($ev['name']); ?></p>
                <span class="event-date">
                  <?php echo $eventDate; ?>
                </span>
                <a href="pasteventpage.php?id=<?php echo (int)$ev['event_id']; ?>" class="view-all">
                  + See more
                </a>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>
        
        
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
  <script src="homepage.js"></script>
  <script src="friendpage.js"></script>
  <script src="logout.js"></script>

</body>
</html>
<?php
// close connection now that HTML is rendered
$conn->close();
?>
