<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Tomás_Freitas's Page</title>
  <link rel="stylesheet" href="homepage.css" />
  <link rel="stylesheet" href="userpage.css">
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
    <div class="profile-container">
  <section class="user-info">
    <div class="user-profile-box">
      <div class="user-box">
        
        <img src="images/tomasfreitas.jpg" alt="User Photo" class="user-photo" />

        <div class="user-info-wrapper">
          
          <div class="user-details-and-stats">
            <div class="user-details">
              <h2 class="username">Tomás_Freitas</h2>
              <p class="email">tomas_freitas2003@gmail.com</p>
              <a class="edit-btn">👥️ Add Friend </a>
      
            </div>

            <!-- Stats à direita -->
            <div class="stats">
              <div><strong>153</strong><br>Items</div>
              <div><strong>3</strong><br>Collections</div>
              <div><strong>87</strong><br>Friends</div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
    
    
  <div class="collections-and-friends">
    <section class="collections">
      <h3>Collections</h3>
      <div class="collection-grid">
        <div class="collection-card">
          <a href="collectionpage.php">
          <img src="images/pokemon-pikachu.png" alt="Pokemon Cards">
          <p><strong>Pokemon Cards</strong></p>
          <span class="last-updated">Last updated: 03/10/2025</span>
          </a>
        </div>
        <div class="collection-card">
          <a href="collectionpage.php">
          <img src="images/carros.png" alt="Car Miniatures">
          <p><strong>Car Miniatures</strong></p>
          <span class="last-updated">Last updated: 23/09/2025</span>
          </a>
        </div>
        <div class="collection-card">
          <a href="collectionpage.php">
          <img src="images/panini.png" alt="Panini Stickers">
          <p><strong>Panini Stickers</strong></p>
          <span class="last-updated">Last updated: 17/05/2025</span>
          </a>
        </div>
        <div class="collection-card">
          <a href="friendscollectionspage.php" class="view-all">+ See more</a>
        </div>
      </div>
    </section>

    <section class="friends">
      <h3>Friends</h3>
      <div class="friends-grid">
        <div class="friend">
          <a href="friendpage.php">
          <img src="images/anaritalopes.jpg" alt="Ana Rita Lopes">
          <p class="friend-name"><strong>Ana_Rita_Lopes</strong></p>
          </a>
        </div>
        <div class="friend">
          <a href="friendpage.php">
          <img src="images/userimage.png" alt="Susana Andrade">
          <p class="friend-name"><strong>Susana_Andrade123</strong></p>
          </a>
        </div>
        <div class="friend">
          <a href="friendpage.php">
          <img src="images/davidramos.jpg" alt="David Ramos">
          <p class="friend-name"><strong>David_Ramos</strong></p>
          </a>
        </div>
        <div class="friend">
          <a href="friendpage.php">
          <img src="images/telmomatos.jpg" alt="Telmo Matos">
          <p class="friend-name"><strong>Telmo_Matos</strong></p>
          </a>
        </div>
        <div class="friend">
          <a href="friendpage.php">
          <img src="images/marcopereira.jpg" alt="Marco Pereira">
          <p class="friend-name"><strong>Marco_Pereira</strong></p>
          </a>
        </div>
      </div>
      <a href="userfriendspage.php" class="view-all">+ See more</a>
    </section>
  </div>
    
    <section class="past-events">
        <h3>Past events</h3>
        <div class="past-events-grid">
          
          <div class="past-event-card">
            <img src="images/market.png" alt="Vinyl Fair">
            <p class="event-name">Feira da Ladra Lisboa</p>
            <span class="event-date">06/12/2024</span>
            <a href="pasteventpage.php" class="view-all">+ See more</a>
          </div>
            
            <div class="past-event-card">
            <img src="images/iberanime24.png" alt="Iberanime 2024">
            <p class="event-name">Iberanime 2024</p>
            <span class="event-date">12/05/2024</span>
            <a href="pasteventpage.php" class="view-all">+ See more</a>
          </div>

            <div class="past-event-card">
            <img src="images/comic21.png" alt="Comic Con 2021">
            <p class="event-name">Comic Con 2021</p>
            <span class="event-date">10/12/2021</span>
            <a href="pasteventpage.php" class="view-all">+ See more</a>
          </div>
          
        </div>
    </section>
        
        
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
<script src="friendpage.js"></script>
</body>
</html>