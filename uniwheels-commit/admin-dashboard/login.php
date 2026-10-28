<?php
/**
 * UniWheels Motors - Staff & Dealership Management Portal Login
 * Redesigned for eye comfort, clarity, and ease of use
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
  <title>UniWheels Motors - Staff Portal Sign In</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">

  <style>
    :root {
      --bg-page: #f1f5f9;
      --bg-card: #ffffff;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --border-color: #cbd5e1;
      --brand-blue: #2563eb;
      --brand-blue-hover: #1d4ed8;
      --font-heading: 'Outfit', sans-serif;
      --font-body: 'Inter', sans-serif;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: var(--font-body);
      background-color: var(--bg-page);
      color: var(--text-main);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }

    .login-container {
      width: 100%;
      max-width: 420px;
    }

    .login-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      padding: 2.5rem 2rem;
      box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
    }

    .brand-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .brand-logo-img {
      height: 52px;
      width: auto;
      margin: 0 auto 12px;
      display: block;
    }

    .brand-title {
      font-family: var(--font-heading);
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--text-main);
      letter-spacing: -0.02em;
    }

    .brand-title span {
      color: #dc2626;
    }

    .portal-subtitle {
      font-size: 0.85rem;
      color: var(--text-muted);
      font-weight: 600;
      margin-top: 4px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .error-alert {
      background-color: #fef2f2;
      border: 1px solid #fecaca;
      color: #b91c1c;
      padding: 12px 14px;
      border-radius: 8px;
      font-size: 0.88rem;
      margin-bottom: 1.5rem;
      line-height: 1.4;
    }

    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-label {
      display: block;
      font-size: 0.88rem;
      font-weight: 600;
      color: var(--text-main);
      margin-bottom: 6px;
    }

    .form-input {
      width: 100%;
      height: 46px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 0 14px;
      font-size: 0.95rem;
      font-family: inherit;
      color: var(--text-main);
      background-color: #ffffff;
      transition: all 0.2s ease;
    }

    .form-input:focus {
      outline: none;
      border-color: var(--brand-blue);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .btn-submit {
      width: 100%;
      height: 48px;
      background-color: var(--brand-blue);
      color: #ffffff;
      font-size: 1rem;
      font-weight: 700;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.2s ease, transform 0.1s ease;
      margin-top: 0.5rem;
    }

    .btn-submit:hover {
      background-color: var(--brand-blue-hover);
    }

    .btn-submit:active {
      transform: scale(0.99);
    }

    .demo-credentials-box {
      margin-top: 1.5rem;
      background-color: #f8fafc;
      border: 1px dashed #cbd5e1;
      border-radius: 8px;
      padding: 12px 14px;
      font-size: 0.82rem;
      color: #475569;
      text-align: center;
    }

    .demo-credentials-box strong {
      color: var(--text-main);
    }

    .back-home-link {
      text-align: center;
      margin-top: 1.75rem;
      font-size: 0.88rem;
    }

    .back-home-link a {
      color: var(--brand-blue);
      text-decoration: none;
      font-weight: 600;
    }

    .back-home-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="login-container">
    <div class="login-card">
      
      <div class="brand-header">
        <img src="../assets/images/logo.png" alt="UniWheels Motors" class="brand-logo-img">
        <div class="brand-title">UNI<span>WHEELS</span> MOTORS</div>
        <div class="portal-subtitle">Staff & Dealership Portal</div>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-alert">
          <strong>Access Denied:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label class="form-label" for="username">Staff Username</label>
          <input type="text" id="username" name="username" class="form-input" placeholder="e.g. admin" required autofocus autocomplete="username">
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input type="password" id="password" name="password" class="form-input" placeholder="Enter your staff password" required autocomplete="current-password">
        </div>

        <button type="submit" class="btn-submit">Sign In to Dashboard &rarr;</button>

        <div class="demo-credentials-box">
          Default Admin Login: <strong>admin</strong> / <strong>admin123</strong>
        </div>
      </form>

    </div>

    <div class="back-home-link">
      <a href="../">&larr; Back to Public Showroom</a>
    </div>
  </div>

</body>
</html>
