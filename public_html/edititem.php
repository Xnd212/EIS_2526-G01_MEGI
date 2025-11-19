<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Edit Item</title>
  <link rel="stylesheet" href="edititem.css" />
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
    </div>
  </header>

  <div class="main">
    <div class="content">
      <section class="item-creation-section">
        <h2 class="page-title">Edit Item</h2>
        <form id="itemForm" novalidate>
          <div class="form-group">
            <label>Collection <span class="required">*</span></label>
            <div class="custom-multiselect">
              <button type="button" id="dropdownBtn">Select Collections ⮟</button>
              <div class="dropdown-content" id="dropdownContent">
                <label><input type="checkbox" value="pokemon" checked> Pokémon Cards</label>
                <label><input type="checkbox" value="coins"> Rare Coins</label>
                <label><input type="checkbox" value="stickers"> Panini Stickers</label>
                <label><input type="checkbox" value="comics"> Comics</label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="itemName">Name <span class="required">*</span></label>
            <input type="text" id="itemName" name="itemName" value="Champion's Path Charizard V (PSA 10)" />
          </div>

          <div class="form-group">
            <label for="itemPrice">Price (€) <span class="required">*</span></label>
            <input type="number" id="itemPrice" name="itemPrice" value="950" step="0.01" min="0" />
          </div>

          <div class="form-group">
            <label for="itemType">Item Type <span class="required">*</span></label>
            <input type="text" id="itemType" name="itemType" value="Card" />
          </div>

          <div class="form-group">
            <label for="itemImportance">Importance <span class="required">*</span></label>
            <input type="text" id="itemImportance" name="itemImportance" value="High" />
          </div>

          <div class="form-group">
            <label for="acquisitionDate">Acquisition Date (DD-MM-YYYY) <span class="required">*</span></label>
            <input type="date" id="acquisitionDate" name="acquisitionDate" value="2025-10-03" />
          </div>

          <div class="form-group">
            <label for="acquisitionPlace">Acquisition Place</label>
            <input type="text" id="acquisitionPlace" name="acquisitionPlace" value="Comic Con 2025" />
          </div>

          <div class="form-group">
            <label for="itemDescription">Description</label>
            <input type="text" id="itemDescription" name="itemDescription" value="A rare and highly graded Charizard card from the Champion's Path set. This card was one of the highlights of my 2025 Comic Con haul. It holds sentimental value and is a key item in my collection." />
          </div>

          <div class="form-group">
            <label for="itemImage">Item Image (optional)</label>
            <input type="file" id="itemImage" name="itemImage" accept="image/*" />
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
  <script src="edititem.js"></script>
</body>
</html>