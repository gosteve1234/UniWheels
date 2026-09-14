<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit;
}
?>

<html>
    <head>
        <title>Dashboard</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <h1>Hello Dashboard</h1>

        <h1>Hello <?php echo $_SESSION['username']; ?></h1>
        <h2>This is only the mockup result page for CRUD</h2>
    </body>
</html>