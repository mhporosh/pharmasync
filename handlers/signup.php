<?php
// handlers/signup.php
// Receive POST from ../signup.html, validate and save user to DB.

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signup.html');
    exit;
}

$first = trim($_POST['firstName'] ?? '');
$last = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$terms = isset($_POST['terms']) ? 1 : 0;

$errors = [];
if ($first === '') $errors[] = 'First name is required.';
if ($last === '') $errors[] = 'Last name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if ($password === '' || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
if (!$terms) $errors[] = 'You must accept the terms.';

if (!empty($errors)) {
    $msg = urlencode(implode(' ', $errors));
    header("Location: ../signup.html?error={$msg}");
    exit;
}

// Check if email already exists
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    $msg = urlencode('Server error (stmt).');
    header("Location: ../signup.html?error={$msg}");
    exit;
}
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $msg = urlencode('Email is already registered.');
    header("Location: ../signup.html?error={$msg}");
    exit;
}
$stmt->close();

// Insert new user
$hash = password_hash($password, PASSWORD_DEFAULT);
$ins = $conn->prepare('INSERT INTO users (first_name, last_name, email, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())');
if (!$ins) {
    $msg = urlencode('Server error (insert).');
    header("Location: ../signup.html?error={$msg}");
    exit;
}
$ins->bind_param('ssss', $first, $last, $email, $hash);
if ($ins->execute()) {
    $ins->close();
    $conn->close();
    header('Location: ../login.html?signup=success');
    exit;
} else {
    $ins->close();
    $msg = urlencode('Could not create account. Try again later.');
    header("Location: ../signup.html?error={$msg}");
    exit;
}

?>
