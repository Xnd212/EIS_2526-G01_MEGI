<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Trall-E | User Page</title>
        <link rel="stylesheet" href="editprofile.css">
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
                <form action="search.php" method="GET">
                    <input type="text" name="q" placeholder="Search for friends, collections, events, items..." required>
                </form>
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
            </div>
        </header>

        <!-- ==== EDIT PROFILE ==== -->
        <main class="edit-profile-page">
            <h2>Edit Profile</h2>

            <form id="editProfileForm" novalidate>
                <!-- Profile Photo -->
                <div class="photo-section">
                    <img id="profilePreview" src="images/userimage.png" alt="User photo" />
                    <div class="photo-actions">
                        <label for="profilePhoto" class="btn-secondary small-btn">Change Photo</label>
                        <input type="file" id="profilePhoto" accept="image/*" hidden />
                    </div>
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" required />
                    <small class="input-hint">3–10 characters, letters, numbers, underscores only</small>
                </div>

                <!-- Date of Birth -->
                <div class="form-group">
                    <label for="dob">Date of birth </label>
                    <input type="date" id="dob" name="dob" required />
                </div>

                <!-- Email -->
                <div class="form-group email-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <div class="email-row">
                        <input type="email" id="email" name="email" placeholder="Enter email" required />
                        <button type="button" id="verifyEmail" class="btn-secondary small-btn">
                            Send verification email
                        </button>
                    </div>
                    <small id="emailStatus" class="verification-status"></small>
                </div>

                <!-- Country -->
                <div class="form-group">
                    <label for="country">Country </label>
                    <select id="country" name="country" required>
                        <option value="">Select your country</option>
                        <option value="Portugal">Portugal</option>
                        <option value="Spain">Spain</option>
                        <option value="France">France</option>
                        <option value="Germany">Germany</option>
                        <option value="Italy">Italy</option>
                        <option value="United Kingdom">United Kingdom</option>
                        <option value="United States">United States</option>
                        <option value="Brazil">Brazil</option>
                    </select>
                </div>

                <!-- Notifications -->
                <div class="form-group">
                    <label>Do you want to receive notifications? <span class="required">*</span></label>
                    <div class="radio-group">
                        <label><input type="radio" name="notify" value="yes" /> Yes</label>
                        <label><input type="radio" name="notify" value="no" /> No</label>
                    </div>
                </div>

                <!-- opções de notificação -->
                <div id="notificationFields" class="conditional-section hidden">
                    <label>Preferred method: <span class="required">*</span></label>
                    <div class="radio-group">
                        <label><input type="radio" name="notifyMethod" value="email" /> Email</label>
                        <label><input type="radio" name="notifyMethod" value="phone" /> Phone</label>
                    </div>

                    <div id="emailField" class="conditional-input hidden">
                        <label for="notifyEmail">Email</label>
                        <input type="email" id="notifyEmail" name="notifyEmail" placeholder="Enter your email" />
                    </div>

                    <div id="phoneField" class="conditional-input hidden">
                        <label for="notifyPhone">Phone Number</label>
                        <input type="tel" id="notifyPhone" name="notifyPhone" placeholder="Enter your phone number" />
                    </div>
                </div>


                <!-- Custom Multi-Select for Favourite Collections -->
                <div class="form-group">
                    <label>Select Favourite Collections (up to 5) </label>
                    <div class="custom-multiselect">
                        <button type="button" id="dropdownBtn">Select from existing collections ⮟</button>
                        <div class="dropdown-content" id="dropdownContent">
                            <label><input type="checkbox" value="pokemon"> Pokemon Cards</label>
                            <label><input type="checkbox" value="coins"> Moedas</label>
                            <label><input type="checkbox" value="stickers"> Paninin stickers</label>
                            <label><input type="checkbox" value="comics"> Comics</label>    
                            <label><input type="checkbox" value="vinyl"> Vinyl</label>  
                            <label><input type="checkbox" value="carros"> Carrões</label> 
                        </div>
                    </div>
                </div>

                <!-- Theme -->
                <div class="form-group theme-group">
                    <label>Theme</label>
                    <div class="theme-options">
                        <label><input type="radio" name="theme" value="light" checked /> Light</label>
                        <label><input type="radio" name="theme" value="dark" /> Dark</label>
                    </div>
                </div>

                <!-- Feedback -->
                <p id="formFeedback" class="form-feedback"></p>

                <!-- Buttons -->
                <div class="form-actions">
                    <a href="userpage.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </main>


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


        <!-- === JAVASCRIPT === -->
        <script src="theme.js"></script>
        <script src="editprofile.js"></script>
        <script src="logout.js"></script>


    </body>
</html>