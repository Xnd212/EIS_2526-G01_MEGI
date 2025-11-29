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
    <button class="icon-btn" id="logout-btn" aria-label="Logout">🚪</button>
    
    <!-- Popup de Logout -->
    <div class="logout-popup" id="logout-popup">
      <h3>Logout</h3>
      <p>Are you sure you want to log out?</p>
      <div class="logout-actions">
        <button class="cancel-btn" id="cancel-logout">Cancel</button>
        <button class="logout-btn" id="confirm-logout">Log out</button>
      </div>
    </div>
  </div>
</header>




  <div class="main">
    <div class="content">
        
<!-- ========== EVENTOS ========= -->
<section class="events-section">
  <h2 class="section-title1">Events you might be interested in 👁️</h2>
  <div class="events-scroll">
    <div class="event-card">
      <img src="images/comiccon.png" alt="Comic Con Portugal">
      <p>Comic Con Portugal</p>
      <div class="see-more">
        <a href="eventpage.php" class="see-more-link">
          <span class="see-more-icon">+️</span> See more
        </a>
      </div>
    </div>

    <div class="event-card">
      <img src="images/amadoraBD.png" alt="Amadora BD">
      <p>Amadora BD</p>
      <div class="see-more">
        <a href="eventpage.php" class="see-more-link">
          <span class="see-more-icon">+️</span> See more
        </a>
      </div>
    </div>

    <div class="event-card">
      <img src="images/iberanime.png" alt="Iberanime Porto">
      <p>Iberanime Porto</p>
      <div class="see-more">
        <a href="eventpage.php" class="see-more-link">
          <span class="see-more-icon">+️</span> See more
        </a>
      </div>
    </div>

    <div class="event-card">
      <img src="images/lisbon.png" alt="Lisbon Games Week">
      <p>Lisbon Games Week</p>
      <div class="see-more">
        <a href="eventpage.php" class="see-more-link">
          <span class="see-more-icon">+️</span> See more
        </a>
      </div>
    </div>

    <div class="event-card">
      <img src="images/cardmadness.png" alt="Cardmadness 2026">
      <p>Cardmadness 2026</p>
      <div class="see-more">
        <a href="eventpage.php" class="see-more-link">
          <span class="see-more-icon">+️</span> See more
        </a>
      </div>
    </div>
  </div>
</section>


 <!-- ========== COLEÇÕES ========= -->       
      <div class="collections-and-collectors">
        <section class="collections">
          <h2 class="section-title1">Recently edited collections 📚</h2>
          <div class="collections-grid">
            <div class="collection-card">
              <a href="collectionpage.php">
                <img src="images\pokémon_logo.png" alt="Pokemon Cards">
                <p class="collection-name">Pokemon Cards</p>
                <span class="collection-author">Rafael_Ameida123</span>
              </a>
            </div>
              
            <div class="collection-card">
              <a href="collectionpage.php">
                <img src="images\championspath.png" alt="Champion's Path">
                <p class="collection-name">Champion's Path</p>
                <span class="collection-author">Paul_Perez1697</span>
              </a>
            </div>
              
            <div class="collection-card">
              <a href="collectionpage.php">
                <img src="images\Funko.png" alt="Funko Pop" >
                <p class="collection-name">Funko Pop</p>
                <span class="collection-author">Rafa_Silva147</span>
              </a>
            </div>
             
            <div class="collection-card">
              <a href="collectionpage.php">
                <img src="images\pokemon-pikachu.png" alt="Pokemon Cards" >
                <p class="collection-name">Pokemon Cards</p>
                <span class="collection-author">Marco_Alex4865</span>
              </a>
            </div>
              
            <div class="collection-card">
              <a href="collectionpage.php">
                <img src="images\SWSH.png" alt="Pokémon SWSH Set">
                <p class="collection-name">Pokémon SWSH Set</p>
                <span class="collection-author">Ana_SSilva7812</span>
              </a>
            </div>
              
            <div class="collection-card">
              <a href="collectionpage.php">
                <img src="images\stamps.png" alt="Stamp collection">
                <p class="collection-name">Stamp collection</p>
                <span class="collection-author">John_VVV.741</span>
              </a>
            </div>
                        
            <div class="collection-card">
              <a href="collectionpage.php">
                <img src="images\Coins.png" alt="Coins collection" >
                <p class="collection-name">Coins collection</p>
                <span class="collection-author">Tomás.Freitas_3366</span>
              </a>
            </div>
              
            <div class="collection-card">
              <a href="collectionpage.php">
                <img src="images\funko2.png" alt="Funko Pop" >
                <p class="collection-name">Funko Pop</p>
                <span class="collection-author">Rafa_Silva147</span>
              </a>
            </div>
              
            <div class="collection-card">
              <a href="collectionpage.php">
                <img src="images\panini.png" alt="Panini Cards" >
                <p class="collection-name">Panini Cards</p>
                <span class="collection-author">Anne_Land.4826</span>
              </a>
            </div>
              
          </div>
          
          <!-- Botão Ver mais -->
        <div class="see-more">
          <a href="allfriendscollectionspage.php" class="see-more-link">
            <span class="see-more-icon">+️</span> See more
          </a>
        </div>                   
        </section>

<!-- ========== TOP COLECIONADORES ========= -->
    <div class="side-ranking">

  <section class="top-collectors-card">
    <h2 class="section-title1">Top collectors of the week 🤝</h2>
    <ol class="top-collector-list">
      <li>
        <span class="medal gold">🥇</span>
        <div class="collector-info">
          <span class="collector-name">Rafael_Ameida123</span>
          <span class="collector-items">101 items</span>
        </div>
      </li>
      <li>
        <span class="medal silver">🥈</span>
        <div class="collector-info">
          <span class="collector-name">AnaAlmeida.889</span>
          <span class="collector-items">87 items</span>
        </div>
      </li>
      <li>
        <span class="medal bronze">🥉</span>
        <div class="collector-info">
          <span class="collector-name">Gaby_Soares97</span>
          <span class="collector-items">84 items</span>
        </div>
      </li>
    </ol>
  </section>

        
<!-- ========== TOP ITENS ========= -->
  <section class="top-items-card">
    <h2 class="section-title1">Top 3 Items 💶</h2>

    <div class="top-item">
      <img src="images\1.png" alt="One Piece Card Game">
      <div class="item-info">
        <p class="item-name">One Piece Card Game <br><span>SR Super Parallel</span></p>
        <p class="item-user">Rafael_Ameida123</p>
        <p class="item-price">999,90€</p>
      </div>
    </div>

    <div class="top-item">
      <img src="images\2.png" alt="Magic Spider-Man">
      <div class="item-info">
        <p class="item-name">Magic the Gathering Marvel's <br><span>Spider-Man Collector Booster</span></p>
        <p class="item-user">AnaAlmeida.889</p>
        <p class="item-price">662,97€</p>
      </div>
    </div>

    <div class="top-item">
      <img src="images\3.png" alt="Charizard 1st Edition Holo">
      <div class="item-info">
        <p class="item-name">Charizard 1st <br><span>Edition Holo</span></p>
        <p class="item-user">Gaby_Soares97</p>
        <p class="item-price">651,17€</p>
      </div>
    </div>
  </section>
</div>
</div>


<!-- ========== TOP COLLECTIONS (POPUP INCL.) ========= -->
<section class="top-collections-section">
  <h2 class="section-title2">Top Collections ⭐</h2>
  
  <div class="top-collections-grid">
      
    <!-- Preço -->
    <div class="top-collection-block" id="price-card" data-id="price-card">
      <h3 class="top-collection-title">Price</h3>
      <img src="images\pokémon_logo.png" alt="Pokemon Cards">
      <p class="collection-name">Pokemon Cards</p>
      <p class="collection-author">Rafael_Ameida123</p>
      <p class="collection-extra">Value: 5312€</p>
      <p class="collection-extra">Items: 51</p>
      <p class="collection-date">Last updated: 27/03/2025</p>
      </div>

    <!-- Mais recente -->
    <div class="top-collection-block" id="recent-card" data-id="recent-card">
      <h3 class="top-collection-title">Most recent</h3>
      <img src="images\championspath.png" alt="Champion's Path">
      <p class="collection-name">Pokemon Champion's Path</p>
      <p class="collection-author">Paul_Perez1697</p>
      <p class="collection-extra">Value: 875€</p>
      <p class="collection-extra">Items: 14</p>
      <p class="collection-date">Last updated: 31/10/2025</p>

    </div>

    <!-- Mais itens -->
    <div class="top-collection-block" id="items-card" data-id="items-card">
      <h3 class="top-collection-title">More items</h3>
      <img src="images\funko3.png" alt="Funko Pop">
      <p class="collection-name">Funko Pop</p>
      <p class="collection-author">Ana_SSilva7812</p>
      <p class="collection-extra">Value: 103€</p>
      <p class="collection-extra">Items: 152</p>     
      <p class="collection-date">Last updated: 01/10/2025</p>

    </div>
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
  </div>
 </div>
    


    
 <!-- === POPUP DINÂMICO === -->
<div id="hover-popup"></div>

<!-- === JAVASCRIPT === -->
<script src="homepage.js"></script>

</body>
</html>
