<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Trall-E | My Collections</title>
        <link rel="stylesheet" href="mycollectionspage.css">
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
                    <section class="collections">
                        <div class="collections-header">
                            <h2>My Collections</h2>

                            <!-- Botão de filtro -->
                            <button class="filter-toggle" id="filterToggle" aria-haspopup="true" aria-expanded="false">
                                <!-- símbolo simples de funil + texto -->
                                &#128269; Filter ▾
                            </button>

                            <!-- Menu de filtros -->
                            <div class="filter-menu" id="filterMenu">

                                <!-- Nome -->
                                <button type="button" data-sort="alpha-asc">Name: A–Z</button>
                                <button type="button" data-sort="alpha-desc">Name: Z–A</button>
                                <hr>

                                <!-- Preço -->
                                <button type="button" data-sort="price-asc">Price: Low–High</button>
                                <button type="button" data-sort="price-desc">Price: High–Low</button>
                                <hr>

                                <!-- Last updated (quando implementarmos) -->
                                <button type="button" data-sort="updated-desc">Last updated: New</button>
                                <button type="button" data-sort="updated-asc">Last updated: Old</button>
                                <hr>

                                <!-- Creation date -->
                                <button type="button" data-sort="created-desc">Created: New</button>
                                <button type="button" data-sort="created-asc">Created: Old</button>
                                <hr>

                                <!-- Nº itens -->
                                <button type="button" data-sort="items-desc">Items: Most</button>
                                <button type="button" data-sort="items-asc">Items: Fewest</button>
                            </div>
                        </div>



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
                                <a href="collectionpage.php">
                                    <img src="images/funkopop.png" alt="Funko Pop">
                                    <p><strong>Funko Pop</strong></p>
                                    <span class="last-updated">Last updated: 27/01/2025</span>
                                </a>
                            </div>
                            <div class="collection-card">
                                <a href="collectionpage.php">
                                    <img src="images/vinil.png" alt="Vinyl Collection">
                                    <p><strong>Vinyl collection</strong></p>
                                    <span class="last-updated">Last updated: 24/12/2024</span>
                                </a>
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
                <p><a href="itemcreation.php">Create item</a></p>
                <p><a href="mycollectionspage.php">View collections</a></p>
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
        <script src="mycollectionspage.js"></script>

    </body>
</html>
