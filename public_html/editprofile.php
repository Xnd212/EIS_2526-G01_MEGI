<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = (int) $_SESSION['user_id'];

require_once __DIR__ . '/db.php';

$errors = [];
$successMessage = "";

/* =========================================
   1) BUSCAR DADOS ATUAIS DO USER
========================================= */
$sqlUser = "
    SELECT 
        u.user_id,
        u.username,
        u.email,
        u.dob,
        u.country,
        u.image_id,
        img.url AS image_url
    FROM user u
    LEFT JOIN image img ON img.image_id = u.image_id
    WHERE u.user_id = ?
";

$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $currentUserId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();
$stmtUser->close();

if (!$user) {
    die("User not found.");
}

/* Defaults */
$currentUsername = $user['username'] ?? "";
$currentEmail    = $user['email'] ?? "";
$currentDob      = $user['dob'] ?? "";
$currentCountry  = $user['country'] ?? "";
$currentImageUrl = $user['image_url'] ?: 'images/placeholderuserpicture.png';

/* =========================================
   2) COLEÇÕES DO USER & FAVORITOS
========================================= */
$allCollections = [];
$userFavourites = [];

/* Coleções do próprio user */
$sqlCollections = "
    SELECT collection_id, name
    FROM collection
    WHERE user_id = ?
    ORDER BY name ASC
";
$stmtCol = $conn->prepare($sqlCollections);
$stmtCol->bind_param("i", $currentUserId);
$stmtCol->execute();
$resCol = $stmtCol->get_result();
while ($row = $resCol->fetch_assoc()) {
    $allCollections[] = $row;
}
$stmtCol->close();

/* Favoritos atuais */
$sqlFav = "
    SELECT collection_id
    FROM favourite
    WHERE user_id = ?
";
$stmtFav = $conn->prepare($sqlFav);
$stmtFav->bind_param("i", $currentUserId);
$stmtFav->execute();
$resFav = $stmtFav->get_result();
while ($row = $resFav->fetch_assoc()) {
    $userFavourites[] = (int)$row['collection_id'];
}
$stmtFav->close();

/* =========================================
   3) PROCESSAR FORM (POST)
========================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Ler campos */
    $newUsername = trim($_POST['username'] ?? "");
    $newEmail    = trim($_POST['email'] ?? "");
    $newDob      = $_POST['dob'] ?? "";
    $newCountry  = $_POST['country'] ?? "";

    /* Validações */
    if ($newUsername === "") {
        $errors[] = "Username is required.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $newUsername)) {
        $errors[] = "Username must be 3–20 characters and use only letters, numbers and underscores.";
    }

    if ($newEmail === "") {
        $errors[] = "Email is required.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    $dobValue     = $newDob !== "" ? $newDob : null;
    $countryValue = $newCountry !== "" ? $newCountry : null;

    /* =====================================
       4) FOTO DE PERFIL (UPLOAD)
    ===================================== */
    $newImageId = null;
    $relativePath = null;

    if (!empty($_FILES['profilePhoto']['name'])) {
        $file = $_FILES['profilePhoto'];

        if ($file['error'] === UPLOAD_ERR_OK) {

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 2 * 1024 * 1024;

            if (!in_array($file['type'], $allowedTypes)) {
                $errors[] = "Profile photo must be an image (JPG, PNG, GIF, WEBP).";
            } elseif ($file['size'] > $maxSize) {
                $errors[] = "Profile photo is too large. Max size is 2MB.";
            } else {

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $ext = 'jpg';
                }

                $uploadDir = __DIR__ . '/uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = 'profile_' . $currentUserId . '_' . time() . '.' . $ext;
                $relativePath = 'uploads/' . $newFileName;
                $fullPath = $uploadDir . '/' . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $fullPath)) {

                    $sqlImg = "INSERT INTO image (url) VALUES (?)";
                    $stmtImg = $conn->prepare($sqlImg);
                    $stmtImg->bind_param("s", $relativePath);
                    $stmtImg->execute();
                    $newImageId = $stmtImg->insert_id;
                    $stmtImg->close();

                } else {
                    $errors[] = "Error saving the profile photo.";
                }
            }
        }
    }

    /* =====================================
       5) FAVORITE COLLECTIONS
    ===================================== */
    $selectedFavs = $_POST['favourites'] ?? [];
    $selectedFavs = array_unique(array_map('intval', $selectedFavs));
    if (count($selectedFavs) > 5) {
        $selectedFavs = array_slice($selectedFavs, 0, 5);
    }

    /* =====================================
       6) UPDATE FINAL
    ===================================== */
    if (empty($errors)) {

        $conn->begin_transaction();

        try {

            if ($newImageId !== null) {
                $sqlUpdate = "
                    UPDATE user
                    SET username = ?, email = ?, dob = ?, country = ?, image_id = ?
                    WHERE user_id = ?
                ";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param(
                    "ssssii",
                    $newUsername,
                    $newEmail,
                    $dobValue,
                    $countryValue,
                    $newImageId,
                    $currentUserId
                );
            } else {
                $sqlUpdate = "
                    UPDATE user
                    SET username = ?, email = ?, dob = ?, country = ?
                    WHERE user_id = ?
                ";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param(
                    "ssssi",
                    $newUsername,
                    $newEmail,
                    $dobValue,
                    $countryValue,
                    $currentUserId
                );
            }

            $stmtUpdate->execute();
            $stmtUpdate->close();

            /* Limpar favoritos antigos */
            $stmtDelFav = $conn->prepare("DELETE FROM favourite WHERE user_id = ?");
            $stmtDelFav->bind_param("i", $currentUserId);
            $stmtDelFav->execute();
            $stmtDelFav->close();

            /* Inserir novos favoritos */
            if (!empty($selectedFavs)) {
                $stmtInsFav = $conn->prepare(
                    "INSERT INTO favourite (user_id, collection_id) VALUES (?, ?)"
                );
                foreach ($selectedFavs as $cid) {
                    $stmtInsFav->bind_param("ii", $currentUserId, $cid);
                    $stmtInsFav->execute();
                }
                $stmtInsFav->close();
            }

            $conn->commit();

            /* Atualizar variáveis locais */
            $currentUsername = $newUsername;
            $currentEmail    = $newEmail;
            $currentDob      = $dobValue;
            $currentCountry  = $countryValue;
            $userFavourites = $selectedFavs;

            if ($relativePath) {
                $currentImageUrl = $relativePath;
            }

            $successMessage = "✅ Profile updated successfully!";

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "An error occurred while saving your profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trall-E | Edit Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="editprofile.css">
    <link rel="stylesheet" href="calendar_popup.css" />
</head>

<!-- Download Popup -->
<div class="download-overlay" id="download-overlay"></div>
<div class="download-popup" id="download-popup">
    <div class="popup-header">
        <h3>Download Your Data</h3>
        <button class="close-popup" id="close-download-popup" type="button">✕</button>
    </div>
    
    <p>Choose what you want to download:</p>
    
    <div class="download-options">
        <a href="download_data.php?type=items" class="download-option-btn">
            Download Items Only
        </a>
        
        <a href="download_data.php?type=collections" class="download-option-btn">
            Download Collections Only
        </a>
        
        <a href="download_data.php?type=both" class="download-option-btn">
            Download Both (Items + Collections)
        </a>
    </div>
    
    <div class="download-info">
        <strong>What's included:</strong>
        <ul>
            <li><strong>Items:</strong> Name, Price, Type, Importance, Dates, Description, Collection</li>
            <li><strong>Collections:</strong> Name, Theme, Starting Date, Description, Item Count</li>
        </ul>
        <p><em>Note: IDs are not included for better readability.</em></p>
    </div>
</div>

<body>

<header>
    <a href="homepage.php" class="logo">
        <img src="images/TrallE_2.png" alt="logo" />
    </a>
    <div class="search-bar">
        <form action="search.php" method="GET">
            <input type="text" name="q" placeholder="Search for friends, collections, events, items..." required>
        </form>
    </div>
    <div class="icons">
        <?php include __DIR__ . '/calendar_popup.php'; ?>
        <?php include __DIR__ . '/notifications_popup.php'; ?>

        <a href="userpage.php" class="icon-btn" aria-label="Perfil">👤</a>

        <button class="icon-btn" id="logout-btn" aria-label="Logout">🚪</button>

        <div class="notification-popup logout-popup" id="logout-popup">
            <div class="popup-header">
                <h3>Logout</h3>
            </div>
            <p>Are you sure you want to log out?</p>
            <div class="logout-btn-wrapper">
                <button type="button" class="logout-btn cancel-btn" id="cancel-logout">Cancel</button>
                <button type="button" class="logout-btn confirm-btn" id="confirm-logout">Log out</button>
            </div>
        </div>
    </div>
</header>

<main class="edit-profile-wrapper">
    <section class="edit-profile-page">
        <h2>Edit Profile</h2>

        <?php if (!empty($errors)): ?>
            <div class="form-feedback error">
                <?php foreach ($errors as $err): ?>
                    <p><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
        <?php elseif ($successMessage): ?>
            <div class="form-feedback success">
                    <span class="success-main">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </span>

                    <span class="redirect-msg">
                        A redirecionar...
                    </span>
            </div>
        <?php endif; ?>

        <form id="editProfileForm" method="post" enctype="multipart/form-data" novalidate>
            <!-- Profile Photo -->
            <div class="photo-section">
                <img id="profilePreview" src="<?php echo htmlspecialchars($currentImageUrl); ?>" alt="User photo" />
                <div class="photo-actions">
                    <label for="profilePhoto" class="btn-secondary small-btn">Change Photo</label>
                    <input type="file" id="profilePhoto" name="profilePhoto" accept="image/*" hidden>
                </div>
            </div>

            <!-- Username -->
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlspecialchars($currentUsername); ?>"
                    required
                >
                <small class="input-hint input-hint--indented">
                    3–20 characters, letters, numbers, underscores only
                </small>
            </div>

            <!-- Date of Birth (optional) -->
            <div class="form-group">
                <label for="dob">Date of birth</label>
                <input
                    type="date"
                    id="dob"
                    name="dob"
                    value="<?php echo $currentDob ? htmlspecialchars($currentDob) : ''; ?>"
                >
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($currentEmail); ?>"
                    required
                >
            </div>

            <!-- Country (optional) -->
            <div class="form-group">
                <label for="country">Country</label>
                <select id="country" name="country">
                    <option value="">Select your country</option>
                    <?php
                    $countries = [
                        "Portugal", "Spain", "France", "Germany", "Italy",
                        "United Kingdom", "United States", "Brazil"
                    ];
                    foreach ($countries as $country):
                        $selected = ($currentCountry === $country) ? 'selected' : '';
                        ?>
                        <option value="<?php echo htmlspecialchars($country); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($country); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Favourite Collections -->
            <div class="form-group">
                <label>Favourite Collections (up to 5)</label>
                <div class="custom-multiselect">
                    <button type="button" id="dropdownBtn">Select from existing collections ⮟</button>
                    <div class="dropdown-content" id="dropdownContent">
                        <?php if (!empty($allCollections)): ?>
                            <?php foreach ($allCollections as $col): ?>
                                <?php
                                $cid = (int)$col['collection_id'];
                                $isChecked = in_array($cid, $userFavourites, true) ? 'checked' : '';
                                ?>
                                <label>
                                    <input
                                        type="checkbox"
                                        name="favourites[]"
                                        value="<?php echo $cid; ?>"
                                        <?php echo $isChecked; ?>
                                        data-name="<?php echo htmlspecialchars($col['name']); ?>"
                                    >
                                    <?php echo htmlspecialchars($col['name']); ?>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-collections-text">You don't have any collections yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <small id="collectionCounter" class="input-hint input-hint--indented">
                    <?php echo count($userFavourites); ?> / 5 selected
                </small>
            </div>

            <!-- Buttons -->
            <div class="form-actions">
                <a href="userpage.php" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
            
            <!-- Download Data Section -->
            <div class="download-data-section">
                <h3>Download Your Data</h3>
                <p>Export your collections and items data to a CSV file for backup or external use.</p>
                <button type="button" class="download-btn" id="download-data-btn">
                    📥 Download My Data
                </button>
            </div>
        </form>
    </section>
</main>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-section collections-section">
        <h3>My collections</h3>
        <p><a href="collectioncreation.php">Create collection</a></p>
        <p><a href="itemcreation.php">Create item</a></p>
        <p><a href="mycollectionspage.php">View collections</a></p>
        <p><a href="myitems.php">View items</a></p>
    </div>

    <div class="sidebar-section friends-section">
        <h3>My bubble</h3>
        <p><a href="userfriendspage.php">View bubble</a></p>
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

<?php if ($successMessage): ?>
<script>
    setTimeout(function () {
        window.location.href = 'userpage.php';
    }, 2000);
</script>
<?php endif; ?>

<script src="homepage.js"></script>
<script src="editprofile.js"></script>
<script src="logout.js"></script>
<script src="download_data.js"></script>
</body>
</html>
