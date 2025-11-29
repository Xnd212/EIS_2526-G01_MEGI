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
    </div>
  </header>

  
   <div class="main">
    <div class="content">    
  <div class="collections-and-friends">
    <section class="friends">
      <h2>Friends</h2>
      <div class="friends-grid">
        <div class="friend">
          <img src="images/anaritalopes.jpg" alt="Ana Rita Lopes">
          <p class="friend-name"><strong><a href="friendpage.php">Ana_Rita_Lopes</a></strong></p>
        </div>
        <div class="friend">
          <img src="images/tomasfreitas.jpg" alt="Tomás Freitas">
          <p class="friend-name"><strong><a href="friendpage.php">Tomás_Freitas</a></strong></p>
        </div>
        <div class="friend">
          <img src="images/davidramos.jpg" alt="David Ramos">
          <p class="friend-name"><strong><a href="friendpage.php">David_Ramos</a></strong></p>
        </div>
        <div class="friend">
          <img src="images/telmomatos.jpg" alt="Telmo Matos">
          <p class="friend-name"><strong><a href="friendpage.php">Telmo_Matos</a></strong></p>
        </div>
        <div class="friend">
          <img src="images/marcopereira.jpg" alt="Marco Pereira">
          <p class="friend-name"><strong><a href="friendpage.php">Marco_Pereira</a></strong></p>
        </div>
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

</body>
</html>
