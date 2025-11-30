<?php
session_start();

// --- ID do utilizador logado ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // só para testes
}
$currentUserId = (int) $_SESSION['user_id'];

// --- Ligação à base de dados ---
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

// --- Buscar lista de amigos ---
$sql = "
    SELECT 
        u.user_id,
        u.username,
        u.image_id,
        u.country,
        u.email,
        img.url AS friend_image   -- AQUI ESTÁ A TUA COLUNA CERTA
    FROM friends f
    INNER JOIN user u ON f.friend_id = u.user_id
    LEFT JOIN image img ON u.image_id = img.image_id
    WHERE f.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

$friends = [];
while ($row = $result->fetch_assoc()) {
    $friends[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | User Friends Page</title>
  <link rel="stylesheet" href="userfriendspage.css">
</head>

<body>    
  <!-- HEADER -->
  <header>
    <a href="homepage.php" class="logo">
      <img src="images/TrallE_2.png" alt="logo" />
    </a>

    <div class="search-bar">
      <input type="text" placeholder="Search" />
    </div>

    <div class="icons">
      <button class="icon-btn" id="notification-btn">🔔</button>

      <a href="userpage.php" class="icon-btn">👤</a>
      <button class="icon-btn" id="logout-btn">🚪</button>
    </div>
  </header>

  <div class="main">
    <div class="content">    

      <section class="friends">
        <h2>Friends</h2>

        <div class="friends-grid">

          <?php if (empty($friends)): ?>
            <p>You don't have any friends yet.</p>

          <?php else: ?>
            <?php foreach ($friends as $friend): ?>

              <?php
                // usar imagem BD se existir, senão imagem default
                $imgSrc = (!empty($friend['friend_image']))
                    ? $friend['friend_image']
                    : 'images/default_avatar.png';
              ?>

              <div class="friend">
                <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                     alt="<?php echo htmlspecialchars($friend['username']); ?>">

                <p class="friend-name">
                  <strong>
                    <a href="friendpage.php?user_id=<?php echo $friend['user_id']; ?>">
                      <?php echo htmlspecialchars($friend['username']); ?>
                    </a>
                  </strong>
                </p>
              </div>

            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </section>

    </div>
  </div>

  <!-- SIDEBAR -->
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

  <script src="homepage.js"></script>
  <script src="logout.js"></script>

</body>
</html>
