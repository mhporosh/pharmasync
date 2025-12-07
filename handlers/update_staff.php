<?php
// handlers/update_staff.php
// Updates staff accounts (or the primary admin profile) from the Staff Directory action menu.

require_once __DIR__ . '/../config/db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please sign in again.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$staffId = intval($_POST['staff_id'] ?? 0);
$isPrimary = intval($_POST['is_primary_admin'] ?? 0) === 1;
$fullName = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = strtoupper(trim($_POST['role'] ?? 'STAFF'));
$status = strtoupper(trim($_POST['status'] ?? 'ACTIVE'));

if ($fullName === '') {
    echo json_encode(['success' => false, 'error' => 'Full name is required.']);
    exit;
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'A valid email is required.']);
    exit;
}

$allowedRoles = ['ADMIN','MANAGER','PHARMACIST','SALES','INVENTORY','CASHIER','STAFF'];
if (!in_array($role, $allowedRoles, true)) {
    $role = 'STAFF';
}
$allowedStatuses = ['ACTIVE','INACTIVE','SUSPENDED'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'ACTIVE';
}

if ($isPrimary) {
    $primaryId = $staffId > 0 ? $staffId : intval($_SESSION['user_id']);
    $check = $conn->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    if (!$check) {
        echo json_encode(['success' => false, 'error' => 'Server error (check admin email).']);
        exit;
    }
    $check->bind_param('si', $email, $primaryId);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        echo json_encode(['success' => false, 'error' => 'Email already in use by another user.']);
        exit;
    }
    $check->close();

    $parts = preg_split('/\s+/', $fullName, 2, PREG_SPLIT_NO_EMPTY);
    $first = $parts[0] ?? 'Admin';
    $last = $parts[1] ?? ($parts[0] ?? 'User');

    $stmt = $conn->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ? LIMIT 1');
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Server error (update admin).']);
        exit;
    }
    $stmt->bind_param('sssi', $first, $last, $email, $primaryId);
    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['user_name'] = $first . ' ' . $last;
        echo json_encode(['success' => true]);
        exit;
    }
    $stmt->close();
    echo json_encode(['success' => false, 'error' => 'Could not update admin profile.']);
    exit;
}

if ($staffId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid staff record.']);
    exit;
}

$check = $conn->prepare('SELECT id FROM staff WHERE email = ? AND id <> ? LIMIT 1');
if (!$check) {
    echo json_encode(['success' => false, 'error' => 'Server error (check email).']);
    exit;
}
$check->bind_param('si', $email, $staffId);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close();
    echo json_encode(['success' => false, 'error' => 'Email already in use by another staff member.']);
    exit;
}
$check->close();

$stmt = $conn->prepare('UPDATE staff SET name = ?, email = ?, role = ?, status = ? WHERE id = ? LIMIT 1');
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Server error (update staff).']);
    exit;
}
$stmt->bind_param('ssssi', $fullName, $email, $role, $status, $staffId);
if ($stmt->execute()) {
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
}
$stmt->close();
echo json_encode(['success' => false, 'error' => 'Could not update staff record.']);
exit;

?>
