<?php
// handlers/add_staff.php
// Accepts POST to create a new staff account and insert into `staff` table.

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? '');

$errors = [];
if ($fullname === '') $errors[] = 'Full name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

// Check existing email in `staff` table
$parts = preg_split('/\s+/', $fullname, 2, PREG_SPLIT_NO_EMPTY);
$first = $parts[0] ?? '';
$last = $parts[1] ?? '';

$check = $conn->prepare('SELECT id FROM staff WHERE email = ? LIMIT 1');
if (!$check) {
    echo json_encode(['success' => false, 'error' => 'Server error (prepare).']);
    exit;
}
$check->bind_param('s', $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close();
    echo json_encode(['success' => false, 'error' => 'Email is already registered.']);
    exit;
}
$check->close();
// Insert into `staff` table (minimal fields only)
$ins = $conn->prepare('INSERT INTO staff (name, email, role, status, sales_today, products_added, joined_date) VALUES (?, ?, ?, ?, 0, 0, NOW())');
if (!$ins) {
    echo json_encode(['success' => false, 'error' => 'Server error (insert prepare).']);
    exit;
}
$name_final = $fullname;
$role_final = $role ?: 'Staff';
$status_default = 'ACTIVE';
$ins->bind_param('ssss', $name_final, $email, $role_final, $status_default);
if ($ins->execute()) {
    $newId = $ins->insert_id;
    $ins->close();
    $conn->close();
    echo json_encode(['success' => true, 'id' => $newId]);
    exit;
} else {
    $ins->close();
    echo json_encode(['success' => false, 'error' => 'Could not create account.']);
    exit;
}

?>
