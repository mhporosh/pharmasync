<?php
// handlers/login.php
// Receives POST from ../login.html, validates credentials and starts session.

require_once __DIR__ . '/../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    $msg = urlencode('Enter valid email and password.');
    header("Location: ../login.html?error={$msg}");
    exit;
}

$stmt = $conn->prepare('SELECT id, first_name, last_name, password_hash FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    $msg = urlencode('Server error.');
    header("Location: ../login.html?error={$msg}");
    exit;
}
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    $msg = urlencode('No account found with that email.');
    header("Location: ../login.html?error={$msg}");
    exit;
}
$stmt->bind_result($id, $first_name, $last_name, $hash);
$stmt->fetch();
$stmt->close();

if (!password_verify($password, $hash)) {
    $msg = urlencode('Incorrect email or password.');
    header("Location: ../login.html?error={$msg}");
    exit;
}

// Successful login
session_regenerate_id(true);
$_SESSION['user_id'] = $id;
$_SESSION['user_name'] = $first_name . ' ' . $last_name;

$conn->close();
header('Location: ../dashboard.php');
exit;
