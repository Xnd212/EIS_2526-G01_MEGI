<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Edit Collection</title>
  <link rel="stylesheet" href="editcollection.css" />
</head>

<body>
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
    </div>
  </header>

  <div class="main">
    <div class="content">
      <section class="collection-edit-section">
        <h2 class="page-title">Edit Collection</h2>
        <form id="collectionForm" novalidate>
            <div class="form-group">
                <label for="collectorName">Collector</label>
                <input type="text" id="collectorName" name="collectorName" value="Susana_Andrade123" disabled />
            </div>

            <div class="form-group">
                <label for="collectionTheme">Theme <span class="required">*</span></label>
                <input type="text" id="collectionTheme" name="collectionTheme" value="Pokémon Trading Cards" />
            </div>

            <div class="form-group">
                <label for="startDate">Start Date (DD/MM/YYYY)</label>
                <input type="date" id="startDate" name="startDate" value="2025-10-03" disabled />
            </div>

            <div class="form-group">
                <label for="mostRecentItem">Most Recent Item</label>
                <input type="text" id="mostRecentItem" name="mostRecentItem" value="Champion's Path Charizard V (PSA 10)" disabled />
            </div>

            <div class="form-group">
                <label for="collectionDescription">Description</label>
                <textarea id="collectionDescription" name="collectionDescription" rows="4">Pokémon cards from my childhood, rediscovered at home.</textarea>
            </div>

            <div class="form-group">
                <label for="collectionTags">Tags <span class="required">*</span></label>
                <input type="text" id="collectionTags" name="collectionTags" value="Pokemon, Cards, Anime, TCG" />
            </div>

            <div class="form-group">
                <label for="collectionImage">Collection Image (optional)</label>
                <input type="file" id="collectionImage" name="collectionImage" accept="image/*" />
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>


        <p id="formMessage" class="form-message"></p>
      </section>
    </div>

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
        <p><a href="userfriendspage.php"> Viem Friends</a></p>
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

  <script src="editcollection.js"></script>
  <script src="logout.js"></script>

</body>
</html>
