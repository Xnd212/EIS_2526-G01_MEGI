<?php
session_start();

// Check if browsing as guest
if (isset($_GET['guest']) && $_GET['guest'] == 'true') {
    // Set guest session
    $_SESSION['user_id'] = 0;
    $_SESSION['username'] = 'Guest';
    $_SESSION['email'] = '';
    $_SESSION['logged_in'] = false;
    $_SESSION['is_guest'] = true;
    
    // Redirect to homepage
    header("Location: homepage.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sie"; 
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

$error = "";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    $input = trim($_POST['username']);
    
    // Prepare statement to check both username and email
    $stmt = $conn->prepare("SELECT user_id, username, email FROM user WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $input, $input);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        $_SESSION['is_guest'] = false;
        
        // Redirect to homepage
        header("Location: homepage.php");
        exit();
    } else {
        $error = "Username ou email não encontrado";
    }
    
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trall-E </title>
  <link rel="stylesheet" href="login.css" />
</head>
<body>
    <!-- TOP BAR IGUAL AO DA HOME (MESMAS CORES) -->
    <header class="login-header">
    <a href="homepage.php" class="logo">
        <img src="images/TrallE_2.png" alt="logo" />
    </a>
    <!-- Placeholder da search bar (invisível mas mantém layout) -->
    <div class="search-bar placeholder"></div>
    <!-- Placeholder dos icons (invisível mas mantém layout) -->
    <div class="icons placeholder"></div>
    </header>
    <main class="auth-page">
        <div class="auth-wrapper">
            <span class="or-label">OR</span>
            <!-- LOGIN (ESQUERDA) -->
            <section class="login-section">
                <h2>Login to your profile</h2>
                <?php if (!empty($error)): ?>
                    <p style="color: red; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <form method="post" action="">
                    <label for="username">Username or email</label>
                    <input type="text" id="username" name="username" required>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <button type="submit" class="login-btn">Login</button>
                </form>
            </section>
            <!-- SIGN UP / GUEST (DIREITA) -->
            <section class="alternative-section">
                <h2>Not a collector yet ?</h2>
                <a href="signup.php" class="full-btn primary-outline">
                    Join other collectors
                </a>
                <a href="?guest=true" class="full-btn secondary-outline">
                    browse as guest
                </a>
            </section>
        </div>
    </main>
</body>
</html>