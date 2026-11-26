<?php
/**
 * UniWheels staff sign-in.
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$suppress_db_die = true;
require_once __DIR__ . '/../connection.php';

$error = '';
$username = '';

// Slow down password guessing: after 5 wrong tries, wait a minute.
$attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'since' => time()];
if (time() - $attempts['since'] > 60) {
    $attempts = ['count' => 0, 'since' => time()];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($attempts['count'] >= 5) {
        $error = 'Too many tries. Please wait one minute and try again.';
    } elseif ($username === '' || $password === '') {
        $error = 'Please type your username and password.';
    } elseif (!$pdo) {
        $error = 'Cannot reach the database. Please make sure XAMPP (Apache and MySQL) is running.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE username = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            unset($_SESSION['login_attempts']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['name'] ?: 'Staff';
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit;
        }
        $attempts['count']++;
        $error = 'That username or password is not right. Please try again.';
    }
    $_SESSION['login_attempts'] = $attempts;
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Sign In - UniWheels</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="login-page">
  <main class="login-box">
    <p class="login-brand">UNI<span>WHEELS</span> MOTORS</p>
    <h1>Staff sign in</h1>
    <p class="hint">Sign in to update the cars on the website.</p>

    <?php if ($error): ?>
      <div class="notice notice-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="login.php" class="car-form">
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" required autofocus autocomplete="username" autocapitalize="none">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
        <label class="choice"><input type="checkbox" id="show-password"> Show password</label>
      </div>
      <button type="submit" class="btn btn-primary btn-big btn-full">Sign in</button>
    </form>

    <p class="login-footer"><a href="../" class="link">&larr; Back to the website</a></p>
  </main>
  <script>
    document.getElementById('show-password').addEventListener('change', function () {
      document.getElementById('password').type = this.checked ? 'text' : 'password';
    });
  </script>
</body>
</html>
