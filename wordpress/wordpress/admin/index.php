<?php
session_start();
?>

<html>
    <head>
        <title>My Login Page</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <h1>Welcome to the Admin Panel</h1>

        <form action="login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>

            <?php if (isset($_SESSION['login_error'])): ?>
            <p class="error"><?php echo $_SESSION['login_error'];
             unset($_SESSION['login_error']); ?></p>
            <?php endif; ?>
            
            <input type="submit" value="Login">
        </form>
    </body>
</html>