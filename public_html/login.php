<?php
// login_signup.php
// Aqui no topo podes no futuro tratar de sessões, mensagens de erro, etc.
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

                <form method="post" action="process_login.php">
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

                <a href="browse_guest.php" class="full-btn secondary-outline">
                    browse as guest
                </a>
            </section>
        </div>
    </main>
</body>
</html>
