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
            $error = "Password incorreta";
        }
    } else {
        $error = "Username ou email não encontrado";
    }

    $stmt->close();
}

// Handle signup
if ($_SERVER["REQUEST_METHOD"] == "POST" && $showSignup && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate all fields are filled
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Todos os campos são obrigatórios";
    }
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, insira um email válido";
    }
    // Validate username (alphanumeric and underscores only, 3-20 chars)
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = "Username deve ter 3-20 caracteres (apenas letras, números e _)";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Este username já está em uso";
            $stmt->close();
        } else {
            $stmt->close();

            // Check if email already exists
            $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Este email já está registado";
                $stmt->close();
            } else {
                $stmt->close();

                // Insert new user
                $insert_stmt = $conn->prepare("INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("sss", $username, $email, $password);

                if ($insert_stmt->execute()) {
                    $new_user_id = $insert_stmt->insert_id;

                    // Set session variables
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $_SESSION['logged_in'] = true;
                    $_SESSION['is_guest'] = false;

                    // Redirect to homepage
                    header("Location: homepage.php");
                    exit();
                } else {
                    $error = "Erro ao criar conta. Tente novamente.";
                }

                $insert_stmt->close();
            }
        }
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
                        <a href="?action=signup" class="full-btn primary-outline">
                            Join other collectors
                        </a>
                        <a href="?guest=true" class="full-btn secondary-outline">
                            browse as guest
                        </a>
                    </section>
                <?php else: ?>
                    <!-- SIGNUP VIEW: ONLY SIGNUP FORM CENTERED -->
                    <section class="login-section signup-centered">
                        <h2>Join other collectors</h2>
                        <?php if (!empty($error)): ?>
                            <p style="color: red; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></p>
                        <?php endif; ?>
                        <form method="post" action="?action=signup">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                            <label for="email">Email</label>
                            <input type="text" id="email" name="email" required>
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                            <button type="submit" class="login-btn">Sign Up</button>
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