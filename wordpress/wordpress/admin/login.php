<?php
session_start();
include("connection.php");

$conn = new mysqli($servername, $username, $password, $dbname);

$inputUsername = $_POST['username'];
$inputPassword = $_POST['password'];

$sql = "SELECT * FROM users";

$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
    $dbusername = $row['username'];
    $dbpassword = $row['password'];

    if($inputUsername == $dbusername && $inputPassword == $dbpassword) {
        $_SESSION['username'] = $inputUsername;
        header('location: dashboard.php');
        exit;
    }
}

$_SESSION['login_error'] = "Invalid username or password.";
header('location: index.php');
exit;
?>