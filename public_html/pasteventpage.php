<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Comic Con Portugal</title>
  <link rel="stylesheet" href="homepage.css" />
  <link rel="stylesheet" href="eventpage.css" />
</head>

<body>

  <!-- ===== Header ===== -->
  <header>
    <a href="homepage.php" class="logo">
      <img src="images/TrallE_2.png" alt="logo" />
    </a>
    <div class="search-bar">
      <input type="text" placeholder="Search" />
    </div>
    <div class="icons">
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
  </header>

  <!-- ===== Main Content ===== -->
  <div class="main">
    <div class="content">
      <div class="event-details-box">
        <h2>Comic Con Portugal</h2>

        <div class="event-teaser-wrapper">

          <div class="event-image-wrapper">
            <img src="images/comiccon.png" alt="Comic Con Portugal" />
          </div>

          <div class="event-details-content">
            <div class="event-info">
              <p><strong>Creator:</strong> Alex.Mendes147</p>
              <p><strong>Theme:</strong> Card</p>
              <p><strong>Date:</strong> 03/10/2025</p>
              <p><strong>Place:</strong> EXPONOR – Porto</p>
              <p><strong>Description:</strong> The biggest pop culture event in Portugal.</p>
              <p><strong>Tags:</strong> Pokemon, Cards, Anime, TCG</p>
            </div>
          </div>

          <!-- TEASER VÍDEO -->
          <div class="video-thumbnail">
            <a href="https://www.youtube.com/watch?v=6mw8rvBWbYE" target="_blank">
              <img src="https://img.youtube.com/vi/6mw8rvBWbYE/hqdefault.jpg" alt="Video Teaser">
              <div class="play-button">▶</div>
            </a>
          </div>

        </div>

        <!-- COLEÇÕES -->
        <h3 class="collections-others"> Collections others are bringing:</h3>
        <div class="collections-brought">
          <div class="collection-bring">
            <a href="collectionpage.php">
            <img src="images/pokémon_logo.png" alt="Pokemon Cards">
            <p class="collection-name"><strong>Pokemon Cards</strong></p>
            <p class="collection-user">Rafael_Ameida123</p>
            </a>
          </div>
          <div class="collection-bring">
            <a href="collectionpage.php">
            <img src="images/championspath.png" alt="Pokemon Champion's Path">
            <p class="collection-name"><strong>Pokemon Champion's Path</strong></p>
            <p class="collection-user">Andre_SS123</p>
            </a>
          </div>
        </div>

        <!-- MAPA -->
        <h3 class="map-title">Where to find us:</h3>
        <div class="map-container">
          <iframe
            src="https://www.google.com/maps?q=EXPONOR%20%E2%80%93%20Porto&output=embed"
            allowfullscreen
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>
    </div>

    <!-- ===== Sidebar ===== -->
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
        <p><a href="userfriendspage.php"> View Friends</a></p>
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
  </div>

  <script src="eventpage.js"></script>
  <script src="logout.js"></script>
</body>
</html>
