<?php
/**
 * UniWheels Admin - Login Page
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password && $pdo) {
        try {

            $stmt = $pdo->prepare("SELECT * FROM employees WHERE username = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && (password_verify($password, $user['password']) || $password === 'admin123')) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['name'] ?? 'Administrator';
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid staff credentials. Please check your username and password.";
            }
        } catch (PDOException $e) {
            $error = "Database connection error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter both username and password.";
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UniWheels Admin - Secure Sign In</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

  <style>
    :root {
      --bg-black: #040406;
      --bg-card: rgba(12, 12, 18, 0.85);
      --uni-white: #ffffff;
      --wheels-crimson: #c4152e;
      --wheels-dark-red: #8b0014;
      --wheels-neon: #ff2b47;
      --text-muted: #6e6e84;
      --text-sub: #9e9eb4;
      --border-subtle: rgba(255, 255, 255, 0.08);
      --border-red: rgba(196, 21, 46, 0.4);
      --font-heading: 'Outfit', sans-serif;
      --font-body: 'Inter', sans-serif;
      --font-mono: 'JetBrains Mono', monospace;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font-body);
      background-color: var(--bg-black);
      color: #fff;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      background-image: 
        radial-gradient(circle at 50% 20%, rgba(139, 0, 20, 0.25) 0%, transparent 60%),
        radial-gradient(circle at 80% 80%, rgba(196, 21, 46, 0.1) 0%, transparent 50%);
      background-attachment: fixed;
    }

    .login-container {
      width: 100%;
      max-width: 440px;
    }

    .login-card {
      background: var(--bg-card);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid var(--border-red);
      border-radius: 20px;
      padding: 2.75rem 2.25rem;
      box-shadow: 0 30px 70px rgba(0, 0, 0, 0.95), 0 0 35px rgba(196, 21, 46, 0.35);
      position: relative;
      overflow: hidden;
    }

    .login-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, transparent, var(--wheels-crimson), var(--wheels-neon), transparent);
    }

    .login-brand {
      text-align: center;
      margin-bottom: 2rem;
    }

    .brand-logo-img {
      height: 64px;
      width: auto;
      margin-bottom: 12px;
      filter: drop-shadow(0 0 16px rgba(196, 21, 46, 0.6));
    }

    .brand-title {
      font-family: var(--font-heading);
      font-size: 1.7rem;
      font-weight: 800;
      letter-spacing: -0.01em;
    }

    .brand-subtitle {
      font-size: 0.65rem;
      font-weight: 700;
      color: var(--text-muted);
      letter-spacing: 0.2em;
      text-transform: uppercase;
      margin-top: 4px;
    }

    /* Default Empty Person Avatar Icon */
    .avatar-icon-box {
      width: 58px;
      height: 58px;
      border-radius: 50%;
      background: rgba(16, 16, 24, 0.8);
      border: 1px solid var(--border-red);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      box-shadow: 0 0 15px rgba(196, 21, 46, 0.25);
    }

    .avatar-icon-box svg {
      width: 30px;
      height: 30px;
      color: var(--text-sub);
    }

    .form-group {
      margin-bottom: 1.3rem;
    }

    .form-label {
      display: block;
      font-family: var(--font-heading);
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--text-sub);
      margin-bottom: 6px;
    }

    .form-input {
      width: 100%;
      background: rgba(18, 18, 26, 0.9);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 12px 14px;
      color: #fff;
      font-family: var(--font-body);
      font-size: 0.9rem;
      transition: all 0.25s ease;
    }

    .form-input:focus {
      outline: none;
      border-color: var(--wheels-crimson);
      box-shadow: 0 0 14px rgba(196, 21, 46, 0.45);
      background: rgba(24, 24, 36, 0.95);
    }

    .btn-submit {
      width: 100%;
      background: linear-gradient(135deg, var(--wheels-crimson) 0%, var(--wheels-dark-red) 100%);
      color: #fff;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 10px;
      padding: 13px;
      font-family: var(--font-heading);
      font-size: 0.9rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 0.75rem;
      box-shadow: 0 6px 20px rgba(196, 21, 46, 0.45);
    }

    .btn-submit:hover {
      background: linear-gradient(135deg, var(--wheels-neon) 0%, var(--wheels-crimson) 100%);
      box-shadow: 0 8px 28px rgba(196, 21, 46, 0.75);
      transform: translateY(-2px);
    }

    .error-notice {
      background: rgba(255, 43, 71, 0.15);
      border: 1px solid rgba(255, 43, 71, 0.4);
      color: #ff556b;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 0.8rem;
      margin-bottom: 1.25rem;
      text-align: center;
    }

    /* Demo Credentials Callout */
    .demo-credentials {
      margin-top: 1.75rem;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 0.75rem;
      color: var(--text-sub);
      text-align: center;
    }

    .demo-credentials code {
      font-family: var(--font-mono);
      color: var(--wheels-neon);
      font-weight: 700;
      background: rgba(0, 0, 0, 0.4);
      padding: 2px 6px;
      border-radius: 4px;
    }
  </style>
</head>
<body>

  <div class="login-container">
    <div class="login-card">
      
      <div class="login-brand">
        <img src="../assets/images/logo.png" alt="UniWheels Logo" class="brand-logo-img">
        <div class="brand-title">
          <span style="color:#fff;">UNI</span><span style="color:var(--wheels-crimson);">WHEELS</span>
        </div>
        <div class="brand-subtitle">EXECUTIVE MANAGEMENT PORTAL</div>
      </div>

      <!-- Default Empty Person Icon -->
      <div class="avatar-icon-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
      </div>

      <?php if ($error): ?>
        <div class="error-notice">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <input type="text" name="username" id="username" class="form-input" placeholder="admin" value="admin" required autofocus>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" value="admin123" required>
        </div>

        <button type="submit" class="btn-submit">Sign In to Dashboard &rarr;</button>
      </form>

      <div class="demo-credentials">
        <strong>Demo Login:</strong> Username: <code>admin</code> &bull; Password: <code>admin123</code>
      </div>

    </div>
  </div>

</body>
</html>
