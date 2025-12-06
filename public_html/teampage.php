<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Team Page</title>
  <link rel="stylesheet" href="teampage.css" />
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


  <!-- ===========================
       MAIN CONTENT
  ============================ -->
  <div class="main">
    <div class="content">

      <section class="team-section">
        <div class="team-header">
          <h2 class="page-title">Team Page 🔍</h2>
          <h2 class="page-subtitle">SIE 25/26 - MEGI</h2>
        </div>

        <div class="team-content">
          <div class="team-group">
            <h3 class="group-title">Students</h3>
            <div class="team-grid">
              <div class="team-member">
                <div class="member-photo ana-rita"></div>
                <p class="member-name"><a href="https://sigarra.up.pt/feup/pt/fest_geral.cursos_list?pv_num_unico=202403069" title="View Sigarra"> Ana Rita Lopes</a></p>
                <p class="member-email">up202043069@edu.fe.up.pt</p>
              </div>
              <div class="team-member">
                <div class="member-photo david"></div>
                <p class="member-name"><a href="https://sigarra.up.pt/feup/pt/fest_geral.cursos_list?pv_num_unico=202105976" title="View Sigarra"> David Ramos</a></p>
                <p class="member-email">up202105976@edu.fe.up.pt</p>
              </div>
              <div class="team-member">
                <div class="member-photo marco"></div>
                <p class="member-name"><a href="https://sigarra.up.pt/feup/pt/fest_geral.cursos_list?pv_num_unico=202107320" title="View Sigarra"> Marco Pereira</a></p>
                <p class="member-email">up202107320@edu.fe.up.pt</p>
              </div>
              <div class="team-member">
                <div class="member-photo tomas"></div>
                <p class="member-name"><a href="https://sigarra.up.pt/feup/pt/fest_geral.cursos_list?pv_num_unico=202107550" title="View Sigarra"> Tomás Freitas</a></p>
                <p class="member-email">up202107550@edu.fe.up.pt</p>
              </div>
            </div>
          </div>

          <div class="team-group">
            <h3 class="group-title">Professor</h3>
            <div class="team-grid professor">
              <div class="team-member">
                <div class="member-photo telmo"></div>
                <p class="member-name"><a href="https://sigarra.up.pt/feup/pt/func_geral.formview?p_codigo=665168" title="View Sigarra"> Telmo Matos</a></p>
                <p class="member-email">tpm@fe.up.pt</p>
              </div>
            </div>
          </div>

          <div class="university-logo">
            <img src="images/feup.png" alt="U.Porto logo" />
          </div>
        </div>
      </section>

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
        <p><a href="userfriendspage.php"> View Friends</a></p>
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
<script src="teampage.js"></script>
<script src="logout.js"></script>

</body>
</html>

