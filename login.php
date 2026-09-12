<?php
require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $users = new User($db);
    $user = $users->attemptLogin($email, $password);

    if ($user) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];

        header('Location: dashboard.php');
        exit;
    } else {
        
        $error = true;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style/global.css" />
    <link rel="stylesheet" href="style/login.css" />
    <title>Login</title>
  </head>
  <body>
    <div class="login-container">
      <div class="left-panel"></div>

      <!-- Right Side -->
      <div class="right-panel">
        <div class="login-box">
          <h1>WELCOME!</h1>
          <br>
          <br>

          <p class="subtitle">SIGN IN TO YOUR ACCOUNT</p>

          <?php if ($error): ?>
            <p class="login-error" style="color: red">Invalid email or password.</p>
          <?php endif; ?>

          <form action="login.php" method="POST">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="you@email.com" required />

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required />

            <div class="options">
              <label class="remember">
                <input type="checkbox" name="remember" /> Remember Me
              </label>

              <a href="#">Forgot Password?</a>
            </div>

            <button type="submit" class="signin-btn">SIGN IN</button>
          </form>
        </div>
      </div>
    </div>
  </body>
</html>