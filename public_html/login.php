<?php
session_start();

$loginError  = $_SESSION['login_error']  ?? "";
$signupError = $_SESSION['signup_error'] ?? "";

// limpa imediatamente depois de ler
unset($_SESSION['login_error'], $_SESSION['signup_error']);


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
$success = "";

// Check if user wants to sign up
$showSignup = isset($_GET['action']) && $_GET['action'] == 'signup';

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$showSignup && isset($_POST['username']) && isset($_POST['password'])) {
    $input = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepare statement to check both username and email
    $stmt = $conn->prepare("SELECT user_id, username, email, password FROM user WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $input, $input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password (plain text comparison)
        if ($password === $user['password']) {
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
            $_SESSION['login_error'] = "Password incorreta";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "Username ou email não encontrado";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
}

// Handle signup
// Handle signup
if ($_SERVER["REQUEST_METHOD"] == "POST" && $showSignup && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate all fields are filled
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['signup_error'] = "Todos os campos são obrigatórios";
        header("Location: login.php?action=signup");
        exit();
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['signup_error'] = "Por favor, insira um email válido";
        header("Location: login.php?action=signup");
        exit();
    }
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $_SESSION['signup_error'] = "Username deve ter 3-20 caracteres (apenas letras, números e _)";
        header("Location: login.php?action=signup");
        exit();
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        $_SESSION['signup_error'] = "Este username já está em uso";
        header("Location: login.php?action=signup");
        exit();
    }
    $stmt->close();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        $_SESSION['signup_error'] = "Este email já está registado";
        header("Location: login.php?action=signup");
        exit();
    }
    $stmt->close();

    // Insert new user
    $insert_stmt = $conn->prepare("INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("sss", $username, $email, $password);

    if ($insert_stmt->execute()) {
        $new_user_id = $insert_stmt->insert_id;

        $_SESSION['user_id']   = $new_user_id;
        $_SESSION['username']  = $username;
        $_SESSION['email']     = $email;
        $_SESSION['logged_in'] = true;
        $_SESSION['is_guest']  = false;

        header("Location: homepage.php");
        exit();
    } else {
        $_SESSION['signup_error'] = "Erro ao criar conta. Tente novamente.";
        header("Location: login.php?action=signup");
        exit();
    }
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
                <?php if (!$showSignup): ?>
                    <!-- DEFAULT VIEW: LOGIN LEFT, BUTTONS RIGHT -->
                    <span class="or-label">OR</span>
                    <!-- LOGIN (ESQUERDA) -->
                    <section class="login-section">
                        <h2>Login to your profile</h2>
                        <form method="post" action="">
                            <label for="username">Username or email</label>
                            <input type="text" id="username" name="username" required>
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                            <div class="login-row">
                                    <button type="submit" class="login-btn">Login</button>

                                    <?php if (!empty($loginError)): ?>
                                        <span class="error-msg"><?php echo htmlspecialchars($loginError); ?></span>
                                    <?php endif; ?>
                            </div>
                            
                        </form>
                    </section>
                    <!-- SIGN UP / GUEST (DIREITA) -->
                    <section class="alternative-section">
                        <h2>Not a collector yet ?</h2>
                        <a href="?action=signup" class="full-btn primary-outline">
                            Join other collectors
                        </a>
                        <a href="?guest=true" class="full-btn secondary-outline">
                            Browse as guest
                        </a>
                    </section>
                <?php else: ?>
                    <!-- SIGNUP VIEW: ONLY SIGNUP FORM CENTERED -->
                    <section class="login-section signup-centered">
                        <h2>Join other collectors</h2>
                        <form method="post" action="?action=signup">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                            <label for="email">Email</label>
                            <input type="text" id="email" name="email" required>
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                            <div class="login-row">
                                    <button type="submit" class="login-btn">Sign Up</button>

                                    <?php if (!empty($signupError)): ?>
                                        <span class="error-msg"><?php echo htmlspecialchars($signupError); ?></span>
                                    <?php endif; ?>
                            </div>

                        </form>
                        <p style="margin-top: 20px; text-align: center;">
                            Already have an account? <a href="login.php" style="color: #007bff;">Login here</a>
                        </p>
                        </p>
                    </section>
                <?php endif; ?>
            </div>
        </main>
    </body>
</html>