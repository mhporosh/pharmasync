<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit;
}
$admin_name = $_SESSION['user_name'] ?? '';
$user_role = $_SESSION['user_role'] ?? 'Admin';
if (isset($conn) && $conn instanceof mysqli) {
  $uid = (int) $_SESSION['user_id'];
  $stmt = $conn->prepare('SELECT first_name, last_name FROM users WHERE id = ? LIMIT 1');
  if ($stmt) {
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $stmt->bind_result($fn, $ln);
    if ($stmt->fetch()) {
      $fullName = trim(($fn ?? '') . ' ' . ($ln ?? ''));
      if ($fullName !== '') {
        $admin_name = $fullName;
      }
    }
    $stmt->close();
  }
}
if ($admin_name === '') {
  $admin_name = 'Admin';
}

if (!function_exists('should_show_footer')) {
  function should_show_footer(): bool
  {
    $footerPages = ['index.php', 'index.html', 'login.php', 'login.html', 'signup.php', 'signup.html'];
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '') ?: '';
    return in_array(strtolower($script), $footerPages, true);
  }
}
?>
