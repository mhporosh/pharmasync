<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
session_start();
$activeMenu = 'sales';
$activePage = 'pos';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['prescription'])) {
  $uploaddir = __DIR__ . '/uploads/prescriptions';
  if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
  $file = $_FILES['prescription'];
  if ($file['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe = uniqid('pres_').'.'.($ext?:'jpg');
    $dest = $uploaddir . '/' . $safe;
    if (move_uploaded_file($file['tmp_name'],$dest)) {
      // create prescriptions table
      $conn->query("CREATE TABLE IF NOT EXISTS prescriptions (id INT AUTO_INCREMENT PRIMARY KEY, filename VARCHAR(255), uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP, status VARCHAR(32) DEFAULT 'new') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
      $stmt = $conn->prepare('INSERT INTO prescriptions (filename, status) VALUES (?, ?)');
      $status = 'new';
      $stmt->bind_param('ss', $safe, $status);
      $stmt->execute();
      $pid = $stmt->insert_id;
      $stmt->close();
      $message = 'Uploaded successfully. Reference: #'.$pid;
    } else $message = 'Failed to move uploaded file.';
  } else $message = 'Upload error: '.$file['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Prescription • PharmaSync</title>
  <link rel="stylesheet" href="style.css?v=20251205">
  <link rel="stylesheet" href="dashboard.css?v=20251205">
</head>
<body>
  <?php require __DIR__ . '/partials/nav.php'; ?>
  <div class="layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <main class="dash-wrap">
      <div class="dash-header"><div class="dash-title">Upload Prescription</div></div>
      <div class="panel">
        <?php if ($message): ?><div style="padding:10px; background:#e8f5e9; border-radius:6px; margin-bottom:12px;"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" style="display:flex; gap:12px; align-items:center;">
          <input type="file" name="prescription" accept="image/*,application/pdf" required>
          <button class="btn" type="submit" style="background:#117a2b; color:#fff;">Upload</button>
          <a href="pos.php" class="btn" style="padding:8px 12px; border:1px solid #e5eef2; text-decoration:none;">Back</a>
        </form>
        <div style="margin-top:18px; color:#666;">Uploaded prescriptions will be stored for review and invoicing.</div>
      </div>
    </main>
  </div>
</body>
</html>
