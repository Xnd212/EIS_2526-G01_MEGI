<?php
session_start();
require "db.php";

// =============================================
// 1. VALIDAR COLLECTION ID
// =============================================
if (!isset($_GET['id'])) {
    die("Erro: Nenhuma coleção especificada.");
}
$collectionId = intval($_GET['id']);

// =============================================
// 2. BUSCAR DADOS DA COLEÇÃO
// =============================================
$stmt = $pdo->prepare("
    SELECT c.*, u.username
    FROM collection c
    JOIN user u ON c.user_id = u.user_id
    WHERE c.collection_id = ?
");
$stmt->execute([$collectionId]);
$collection = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$collection) {
    die("Erro: Coleção não encontrada.");
}

$message = "";
$redirectAfterSuccess = false;

// =============================================
// 3. PROCESSAR UPDATE
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['collectionName'];
    $theme = $_POST['collectionTheme'];
    $description = $_POST['collectionDescription'];

    // --- UPDATE PRINCIPAL ---
    $stmtUpdate = $pdo->prepare("
        UPDATE collection
        SET name = ?, Theme = ?, description = ?
        WHERE collection_id = ?
    ");
    $stmtUpdate->execute([$name, $theme, $description, $collectionId]);

    // --- UPLOAD DE IMAGEM ---
    if (!empty($_FILES['collectionImage']['name'])) {

        $file = $_FILES['collectionImage'];
        $uploadDir = "images/";
        $safeName = time() . "_" . basename($file['name']);
        $targetPath = $uploadDir . $safeName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {

            $stmtImg = $pdo->prepare("INSERT INTO image (url) VALUES (?)");
            $stmtImg->execute([$targetPath]);
            $newImageId = $pdo->lastInsertId();

            $stmtImgUpdate = $pdo->prepare("
                UPDATE collection
                SET image_id = ?
                WHERE collection_id = ?
            ");
            $stmtImgUpdate->execute([$newImageId, $collectionId]);

        } else {
            $message = "⚠ Erro ao carregar a imagem.";
        }
    }

    // --- SE TUDO CORREU BEM ---
    if ($message === "") {
        $message = "✓ Alterações guardadas com sucesso! A redirecionar...";
        $redirectAfterSuccess = true;
    }

    // --- RECARREGAR DADOS ---
    $stmt2 = $pdo->prepare("
        SELECT c.*, u.username
        FROM collection c
        JOIN user u ON c.user_id = u.user_id
        WHERE c.collection_id = ?
    ");
    $stmt2->execute([$collectionId]);
    $collection = $stmt2->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E | Edit Collection</title>

  <link rel="stylesheet" href="editcollection.css" />
</head>

<body>

    <!-- HEADER COMPLETO -->
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
                <?php include __DIR__ . '/notifications_popup.php'; ?>

            <a href="userpage.php" class="icon-btn" aria-label="Perfil">👤</a>

            <button class="icon-btn" id="logout-btn" aria-label="Logout">🚪</button>

            <div class="notification-popup logout-popup" id="logout-popup">
                <div class="popup-header"><h3>Logout</h3></div>
                <p>Are you sure you want to log out?</p>
                <div class="logout-btn-wrapper">
                    <button type="button" class="logout-btn cancel-btn" id="cancel-logout">Cancel</button>
                    <button type="button" class="logout-btn confirm-btn" id="confirm-logout">Log out</button>
                </div>
            </div>
        </div>
    </header>

    <div class="main">
        <div class="content">
            <section class="collection-edit-section">

                <h2 class="page-title">Edit Collection</h2>

                <!-- ANCHOR PARA EVITAR SCROLL AO TOPO -->
                <a id="stay-here"></a>

                <form id="collectionForm" method="POST" action="" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>Collector</label>
                        <input type="text" disabled value="<?= htmlspecialchars($collection['username']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="collectionName">Name *</label>
                        <input type="text" id="collectionName" name="collectionName"
                               value="<?= htmlspecialchars($collection['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="collectionTheme">Theme *</label>
                        <input type="text" id="collectionTheme" name="collectionTheme"
                               value="<?= htmlspecialchars($collection['Theme']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" disabled value="<?= htmlspecialchars($collection['starting_date']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="collectionDescription">Description</label>
                        <textarea id="collectionDescription" name="collectionDescription" rows="4"><?= 
                            htmlspecialchars($collection['description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="collectionImage">Upload New Image</label>
                        <input type="file" id="collectionImage" name="collectionImage" accept="image/*">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            Save Changes
                        </button>
                    </div>

                    <!-- MENSAGEM NO FUNDO DO FORM -->
                    <?php if ($message): ?>
                        <p id="form-message" class="form-message <?= $redirectAfterSuccess ? 'success' : 'error' ?>">
                            <?= htmlspecialchars($message) ?>
                        </p>
                    <?php endif; ?>

                </form>

            </section>
        </div>

        <!-- SIDEBAR COMPLETA -->
        <aside class="sidebar">
            <div class="sidebar-section collections-section">
                <h3>My collections</h3>
                <p><a href="collectioncreation.php">Create collection</a></p>
                <p><a href="itemcreation.php"> Create Item</a></p>
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

    <script>
    window.redirectAfterSuccess = <?= $redirectAfterSuccess ? 'true' : 'false' ?>;
    window.collectionId = <?= json_encode($collectionId) ?>;
    </script>
    
    <script src="editcollection.js"></script>
    <script src="logout.js"></script>


</body>
</html>
