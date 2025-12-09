<?php
session_start();
require_once __DIR__ . "/db.php";

// ==============================
// 1. VALIDAR COLLECTION ID
// ==============================
if (!isset($_GET['id'])) {
    die("Erro: Nenhuma coleção especificada.");
}
$collectionId = intval($_GET['id']);

// ==============================
// 2. FUNÇÃO PARA BUSCAR A COLEÇÃO (REUTILIZADA)
// ==============================
function loadCollection($conn, $collectionId)
{
    $sql = "
        SELECT c.*, u.username
        FROM collection c
        JOIN user u ON c.user_id = u.user_id
        WHERE c.collection_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $collectionId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $data;
}

// Carregar coleção inicialmente
$collection = loadCollection($conn, $collectionId);
if (!$collection) {
    die("Erro: Coleção não encontrada.");
}

// ==============================
// 3. BUSCAR TODAS AS TAGS
// ==============================
$sqlTags = "SELECT tag_id, name FROM tags ORDER BY name ASC";
$resTags = $conn->query($sqlTags);
$allTags = $resTags->fetch_all(MYSQLI_ASSOC);

// Tags da coleção
$sqlColTags = "SELECT tag_id FROM collection_tags WHERE collection_id = ?";
$stmtCT = $conn->prepare($sqlColTags);
$stmtCT->bind_param("i", $collectionId);
$stmtCT->execute();
$resCT = $stmtCT->get_result();
$currentTagIds = array_column($resCT->fetch_all(MYSQLI_ASSOC), "tag_id");
$stmtCT->close();

$message = "";
$redirectAfterSuccess = false;

// ==============================
// 4. PROCESSAR SUBMISSÃO
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['collectionName']);
    $theme = trim($_POST['collectionTheme']);
    $description = trim($_POST['collectionDescription']);
    $selectedTags = $_POST['tags'] ?? [];

    // Atualizar info principal
    $sqlUp = "
        UPDATE collection
        SET name = ?, Theme = ?, description = ?
        WHERE collection_id = ?
    ";
    $stmtUp = $conn->prepare($sqlUp);
    $stmtUp->bind_param("sssi", $name, $theme, $description, $collectionId);
    $stmtUp->execute();
    $stmtUp->close();

    // Upload imagem
    if (!empty($_FILES['collectionImage']['name'])) {

        $file = $_FILES['collectionImage'];
        $uploadDir = "images/";
        $safeName = time() . "_" . basename($file['name']);
        $targetPath = $uploadDir . $safeName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {

            $sqlImg = "INSERT INTO image (url) VALUES (?)";
            $stmtImg = $conn->prepare($sqlImg);
            $stmtImg->bind_param("s", $targetPath);
            $stmtImg->execute();
            $newImageId = $stmtImg->insert_id;
            $stmtImg->close();

            $sqlUpdImg = "UPDATE collection SET image_id = ? WHERE collection_id = ?";
            $stmtUpdImg = $conn->prepare($sqlUpdImg);
            $stmtUpdImg->bind_param("ii", $newImageId, $collectionId);
            $stmtUpdImg->execute();
            $stmtUpdImg->close();

        } else {
            $message = "⚠ Erro ao carregar a imagem.";
        }
    }

    // Atualizar tags
    $conn->query("DELETE FROM collection_tags WHERE collection_id = $collectionId");

    if (!empty($selectedTags)) {
        $sqlInsTag = $conn->prepare("INSERT INTO collection_tags (collection_id, tag_id) VALUES (?, ?)");
        foreach ($selectedTags as $tid) {
            $tid = (int)$tid;
            $sqlInsTag->bind_param("ii", $collectionId, $tid);
            $sqlInsTag->execute();
        }
        $sqlInsTag->close();
    }

    // Mensagem final
    if ($message === "") {
        $message = "✓ Alterações guardadas com sucesso! <span class='redirect-msg'>A redirecionar...</span>";
        $redirectAfterSuccess = true;
    }

    // 🔥 Aqui está a correção principal — recarregar os novos dados
    $collection = loadCollection($conn, $collectionId);

    // Recarregar tags selecionadas
    $stmtCT = $conn->prepare("SELECT tag_id FROM collection_tags WHERE collection_id = ?");
    $stmtCT->bind_param("i", $collectionId);
    $stmtCT->execute();
    $resCT = $stmtCT->get_result();
    $currentTagIds = array_column($resCT->fetch_all(MYSQLI_ASSOC), "tag_id");
    $stmtCT->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Collection</title>
    <link rel="stylesheet" href="editcollection.css">
    <link rel="stylesheet" href="calendar_popup.css" />
</head>
<body>

<header>
    <a href="homepage.php" class="logo"><img src="images/TrallE_2.png" alt="logo"></a>

    <div class="search-bar">
        <form action="search.php" method="GET">
            <input type="text" name="q" placeholder="Search for friends, collections, events, items..." required>
        </form>
    </div>

    <div class="icons">
        <?php include __DIR__ . '/calendar_popup.php'; ?>
        <?php include __DIR__ . '/notifications_popup.php'; ?>
        <a href="userpage.php" class="icon-btn">👤</a>
        <button class="icon-btn" id="logout-btn">🚪</button>
    </div>
</header>

<div class="main">
    <div class="content">

        <!-- SIDEBAR -->
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

        <!-- FORMULÁRIO -->
        <section class="collection-edit-section">

            <h2 class="page-title">Edit Collection</h2>

            <form id="collectionForm" method="POST" enctype="multipart/form-data">

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

                <!-- TAGS -->
                <div class="form-group">
                    <label>Tags</label>

                    <div class="tag-header">
                        <button type="button" id="openTagModal" class="btn-small">Criar tags</button>
                    </div>

                    <div class="custom-multiselect">
                        <button type="button" id="dropdownBtn">Select Tags ⮟</button>
                        <div class="dropdown-content" id="tagDropdown">

                            <?php foreach ($allTags as $tag): ?>
                                <?php $checked = in_array($tag['tag_id'], $currentTagIds) ? "checked" : ""; ?>

                                <label>
                                    <input type="checkbox" name="tags[]"
                                           value="<?= $tag['tag_id'] ?>" <?= $checked ?>>
                                    <?= htmlspecialchars($tag['name']) ?>
                                </label>

                            <?php endforeach; ?>

                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="collectionDescription"
                              name="collectionDescription"
                              rows="4"><?= htmlspecialchars($collection['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="collectionImage">Upload New Image</label>
                    <input type="file" id="collectionImage" name="collectionImage" accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>

                <!-- MENSAGEM NO FUNDO -->
                <?php if ($message): ?>
                    <a id="msg-anchor"></a>   <!-- âncora invisível -->
                    <p id="form-message" class="form-message <?= $redirectAfterSuccess ? 'success' : 'error' ?>">
                        <?= $message ?>
                    </p>
                <?php endif; ?>

            </form>
        </section>
    </div>
</div>

<!-- MODAL DE TAGS -->
<div id="tagModalOverlay" class="modal-overlay hidden"></div>

<div id="tagModal" class="modal hidden">
    <h3>Criar nova tag</h3>

    <input type="text" id="newTagInput" placeholder="Nome da tag...">

    <p id="tagFeedback" class="tag-feedback"></p>

    <div class="modal-buttons">
        <button id="createTagBtn" class="btn-primary">Criar tag</button>
        <button id="closeTagModal" class="btn-secondary">Close</button>
    </div>
</div>

<script>
    window.redirectAfterSuccess = <?= $redirectAfterSuccess ? 'true' : 'false' ?>;
    window.collectionId = <?= json_encode($collectionId) ?>;
</script>

<script src="editcollection.js"></script>
<script src="logout.js"></script>

</body>
</html>
