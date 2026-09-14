<?php
session_start();
include("connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    $sql = "SELECT * FROM users";

    $result = $connection->query($sql);

    while($row = $result->fetch_assoc()) {
        $dbusername = $row['username'];
        $dbpassword = $row['password'];

        if($inputUsername == $dbusername && $inputPassword == $dbpassword) {
            $_SESSION['username'] = $inputUsername;
            header('location: index.php');
            exit;
        }
    }

    $_SESSION['login_error'] = "Invalid username or password.";
    header('location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log In &mdash; POS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>POS Login</h1>

    <form method="POST" action="login.php">
        <?php if (!empty($_SESSION['login_error'])): ?>
            <span class="error"><?= htmlspecialchars($_SESSION['login_error']) ?></span>
        <?php unset($_SESSION['login_error']); endif; ?>

        <p>
            <label for="username">Username</label><br>
            <input type="text" id="username" name="username" required>
        </p>
        <p>
            <label for="password">Password</label><br>
            <input type="password" id="password" name="password" required>
        </p>
        <input type="submit" value="Log In">
    </form>
</body>
</html>