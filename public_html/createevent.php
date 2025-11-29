<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Create Event</title>
  <link rel="stylesheet" href="createevent.css" />
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
      <input type="search" placeholder="Search" />
    </div>

    <div class="icons">
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
          <li><strong>Ana_Rita_Lopes</strong> added 1 new items to the Pokémon Champion’s Path collection.</li>
          <li><strong>Telmo_Matos</strong> added 3 new items to the Premier League Stickers collection.</li>
          <li><strong>Marco_Pereira</strong> created a new event: Card Madness Meetup.</li>
        </ul>
        <a href="#" class="see-more-link">+ See more</a>
      </div>

      <a href="userpage.php" class="icon-btn" aria-label="Perfil">👤</a>
    </div>
  </header>

  <!-- ===========================
       MAIN CONTENT
  ============================ -->
  <div class="main">
    <!-- Conteúdo principal (lado esquerdo) -->
    <div class="content">
      <section class="item-creation-section">
        <h2 class="page-title">Create a Event</h2>

        <form id="eventForm" novalidate>
          <!-- Name -->
          <div class="form-group">
            <label for="eventName">Event Name <span class="required">*</span></label>
            <input
              type="text"
              id="eventName"
              name="eventName"
              placeholder="e.g. Comic Con Portugal"
              required
            />
          </div>

          <!-- Date -->
          <div class="form-group">
            <label for="startDate">Date <span class="required">*</span></label>
            <input type="date" id="startDate" name="startDate" required />
          </div>

          <!-- Theme -->
          <div class="form-group">
            <label for="theme">Theme <span class="required">*</span></label>
            <input
              type="text"
              id="theme"
              name="theme"
              placeholder="e.g. Anime, Cards, etc."
              required
            />
          </div>

          <!-- Location -->
          <div class="form-group">
            <label for="location">Place <span class="required">*</span></label>
            <input
              type="text"
              id="location"
              name="location"
              placeholder="e.g. Exponor – Porto"
              required
            />
          </div>

          <!-- Description -->
          <div class="form-group">
            <label for="description">Description <span class="required">*</span></label>
            <textarea
              id="description"
              name="description"
              rows="4"
              placeholder="Brief description about the event"
              required
            ></textarea>
          </div>

          <!-- Tags -->
          <div class="form-group">
            <label for="tags">Tags <span class="required">*</span></label>
            <input
              type="text"
              id="tags"
              name="tags"
              placeholder="e.g. Pokemon, Anime, TCG"
              required
            />
          </div>

          <!-- YouTube Embed -->
          <div class="form-group">
            <label for="youtube">YouTube video embed link</label>
            <input
              type="url"
              id="youtube"
              name="youtube"
              placeholder="e.g. https://www.youtube.com/embed/..."
            />
          </div>

          <!-- Image Upload -->
          <div class="form-group">
            <label for="coverImage">Cover Image <span class="required">*</span></label>
            <input
              type="file"
              id="coverImage"
              name="coverImage"
              accept="image/*"
              required
            />
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">Create Event</button>
          </div>
        </form>

        <p id="formMessage" class="form-message"></p>

        <!-- ==== EVENT SUMMARY ==== -->
        <div id="eventSummarySection" class="summary-section hidden">
          <h3>Event Summary</h3>
          <div id="eventSummaryContent"></div>
          <div class="summary-actions">
            <button id="editEvent" class="btn-secondary" type="button">Edit Event</button>
            <button id="finalEventConfirm" class="btn-primary" type="button">Confirm and Create</button>
          </div>
        </div>
        
        <!-- ==== SUCCESS MODAL ==== -->
        <div id="eventSuccessModal" class="event-success-modal">
      <div class="success-box">

        <h2>Event created successfully</h2>
        <p>Your event has been created.</p>

        <!-- RESUMO DO EVENTO APÓS CRIAÇÃO -->
        <div id="modalEventSummary" class="modal-event-summary"></div>

        <div class="success-buttons">
          <button id="goToEventBtn" class="btn-primary">Go to event page</button>
          <button id="goToHomeBtn" class="btn-secondary">Go to homepage</button>
        </div>
      </div>
        </div>
        
      </section>
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
        <p><a href="userfriendspage.php">Viem Friends</a></p>
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

  <!-- ========== SUCCESS MODAL ========== -->
  <div id="eventSuccessModal" class="event-success-modal">
    <div class="success-box">
      <h2>Event created successfully ✅</h2>
      <p>Your event has been created.</p>
      <div class="success-buttons">
        <button id="goToEventBtn" class="btn-primary">Go to event page</button>
        <button id="goToHomeBtn" class="btn-secondary">Go to homepage</button>
      </div>
    </div>
  </div>

  <!-- === JAVASCRIPT === -->
  <script src="createevent.js"></script>
</body>
</html>
