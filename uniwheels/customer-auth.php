<?php
/**
 * UniWheels Customer Authentication API
 * Handles Customer Registration, Login, Session Verification, and Logout
 * Operates on the `users` table.
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/connection.php';

$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if JSON body or standard form POST
    $raw_input = file_get_contents('php://input');
    $json = json_decode($raw_input, true);
    $input = is_array($json) ? $json : $_POST;
} else {
    $input = $_GET;
}

$action = $input['action'] ?? 'check';

// 1. Check current customer session
if ($action === 'check') {
    if (!empty($_SESSION['customer_id'])) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['customer_id'],
                'name' => $_SESSION['customer_name'] ?? 'Customer',
                'email' => $_SESSION['customer_email'] ?? '',
                'username' => $_SESSION['customer_username'] ?? ''
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
    }
    exit;
}

// 2. Customer Registration
if ($action === 'register') {
    $full_name = trim($input['full_name'] ?? '');
    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required registration fields.'
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide a valid email address.'
        ]);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters long.'
        ]);
        exit;
    }

    // Check if email or username already taken
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'An account with this username or email already exists.'
        ]);
        exit;
    }

    // Hash password and insert
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role, status) VALUES (?, ?, ?, ?, 'customer', 'active')");
        $stmt->execute([$username, $hashed, $full_name, $email]);
        $new_id = $pdo->lastInsertId();

        // Establish session
        $_SESSION['customer_id'] = $new_id;
        $_SESSION['customer_name'] = $full_name;
        $_SESSION['customer_email'] = $email;
        $_SESSION['customer_username'] = $username;

        echo json_encode([
            'success' => true,
            'message' => "Welcome to UniWheels, {$full_name}! Your account has been registered.",
            'user' => [
                'id' => $new_id,
                'name' => $full_name,
                'email' => $email,
                'username' => $username
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Registration error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// 3. Customer Sign In
if ($action === 'login') {
    $identifier = trim($input['identifier'] ?? $input['username'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($identifier) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter your username/email and password.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Establish customer session
        $_SESSION['customer_id'] = $user['id'];
        $_SESSION['customer_name'] = $user['full_name'];
        $_SESSION['customer_email'] = $user['email'];
        $_SESSION['customer_username'] = $user['username'];

        echo json_encode([
            'success' => true,
            'message' => "Welcome back, {$user['full_name']}!",
            'user' => [
                'id' => $user['id'],
                'name' => $user['full_name'],
                'email' => $user['email'],
                'username' => $user['username']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email/username or password. Please try again.'
        ]);
    }
    exit;
}

// 4. Customer Sign Out
if ($action === 'logout') {
    unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email'], $_SESSION['customer_username']);
    echo json_encode([
        'success' => true,
        'message' => 'You have been signed out safely.'
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Unrecognized authentication action.'
]);
