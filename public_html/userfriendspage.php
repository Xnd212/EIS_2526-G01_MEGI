<?php
session_start();

// --- ID do utilizador logado ---
// Aqui assumo que já tens o user_id guardado na sessão.
// Se ainda não tiveres login feito, podes testar com um valor fixo, por ex: 1
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // <-- só para testes
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
// friends(user_id, friend_id)
// queremos os dados do utilizador que É amigo (tabela user)
$sql = "
    SELECT 
        u.user_id,
        u.username,
        u.image_id,
        u.country,
        u.email
    FROM friends f
    INNER JOIN user u ON f.friend_id = u.user_id
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
// $conn->close();  // se quiseres, mas só no fim da página
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
  <!-- ===========================
       HEADER
  ============================ -->
  <header>
    <a href="homepage.php" class="logo">
      <img src="images\TrallE_2.png" alt="logo" />
     
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
    <section class="friends">
        <h2>Friends</h2>
        <div class="friends-grid">
          <?php if (empty($friends)): ?>
            <p>You don't have any friends yet.</p>
          <?php else: ?>
            <?php foreach ($friends as $friend): ?>
              <div class="friend">
                <!-- Imagem: por agora uso um avatar genérico.
                     Quando tiveres a tabela 'image', faz um JOIN e muda o src. -->
                <img src="images/default_avatar.png"
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
</div>

   
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
<script src="homepage.js"></script>
<script src="logout.js"></script>

</body>
</html>
