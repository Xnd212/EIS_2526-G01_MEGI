<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Trall-E | Event History</title>
        <link rel="stylesheet" href="eventhistory.css" />
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

        <!-- ===========================
             MAIN CONTENT
        ============================ -->
        <div class="main">
            <div class="content">
                <section class="event-history-section">
                    <h2 class="page-title">Event history</h2>

                    <div class="event-list">

                        <!-- === EVENT CARD 1 === -->
                        <div class="event-card">
                            <div class="event-image img1"></div>
                            <div class="event-info">
                                <h3><strong><a href="pasteventpage.php">Comic Con Portugal</a></strong></h3>
                                <p><strong>Date:</strong> 03/10/2025</p>
                                <p class="rating">
                                    <strong>Rating:</strong>
                                    <span class="stars">⭐ ⭐ ⭐ ⭐ ☆</span>
                                </p>
                                <p><strong>Collection brought:</strong>
                                    <a href="collectionpage.php">Pokemon Cards</a>
                                </p>
                            </div>
                        </div>

                        <!-- === EVENT CARD 2 === -->
                        <div class="event-card">
                            <div class="event-image img2"></div>
                            <div class="event-info">
                                <h3><strong><a href="pasteventpage.php">Amadora BD - International Comic Festival</a></strong></h3>
                                <p><strong>Date:</strong> 23/10/2025</p>
                                <p class="rating">
                                    <strong>Rating:</strong>
                                    <span class="stars">⭐ ⭐ ⭐ ☆ ☆</span>
                                </p>
                                <p><strong>Collection brought:</strong>
                                    <a href="collectionpage.php">Panini Stickers</a>
                                </p>
                            </div>
                        </div>

                    </div>
                </section>
            </div>

            <!-- ===========================
                 SIDEBAR
            ============================ -->
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

        <!-- === JAVASCRIPT === -->
        <script src="eventhistory.js"></script>
        <script src="logout.js"></script>
    </body>
</html>
