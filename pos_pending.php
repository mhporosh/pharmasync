<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'sales';
$activePage = 'pos';

// ensure invoices table exists (if created by pos_create.php this is fine)
$conn->query("CREATE TABLE IF NOT EXISTS invoices (id INT AUTO_INCREMENT PRIMARY KEY, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, total DECIMAL(12,2), items TEXT, status VARCHAR(32) DEFAULT 'pending') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// handle pay action
if (isset($_GET['pay'])) {
  $id = intval($_GET['pay']);
  $stmt = $conn->prepare('UPDATE invoices SET status = ? WHERE id = ?');
  $s = 'paid';
  $stmt->bind_param('si', $s, $id);
  $stmt->execute();
  $stmt->close();
  header('Location: pos_pending.php'); exit;
}

// fetch invoices
$result = $conn->query('SELECT * FROM invoices ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pending Invoices • PharmaSync</title>
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="stylesheet" href="style.css?v=20251205">
  <link rel="stylesheet" href="responsive.css?v=20251205">
  <link rel="stylesheet" href="dashboard.css?v=20251207">
  <script src="https://kit.fontawesome.com/d3e9fb9ce3.js" crossorigin="anonymous"></script>
  <script src="script.js?v=20251207" defer></script>
</head>
<body>
  <?php require __DIR__ . '/partials/nav.php'; ?>
  <div class="layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <main class="dash-wrap">
      <div class="dash-header"><div class="dash-title">Invoices</div></div>
      <div class="panel">
        <div style="margin-bottom:12px;">
          <a href="pos.php" class="btn" style="padding:8px 12px; border:1px solid #e5eef2; text-decoration:none;">Back</a>
        </div>
        <div style="background:#fff; border:1px solid #eef2f3; border-radius:8px; padding:10px;">
          <table style="width:100%; border-collapse:collapse;">
            <tr style="border-bottom:1px solid #f1f4f6;"><th>#</th><th>Date</th><th>Total</th><th>Status</th><th>Items</th><th></th></tr>
            <?php if ($result && $result->num_rows>0): while($row = $result->fetch_assoc()): ?>
            <tr style="border-bottom:1px solid #f6f8f9;">
              <td style="padding:8px;">#<?= $row['id'] ?></td>
              <td><?= $row['created_at'] ?></td>
              <td>BDT <?= number_format($row['total'],2) ?></td>
              <td style="text-transform:capitalize;"><?= htmlspecialchars($row['status']) ?></td>
              <td style="max-width:320px; overflow:auto; font-size:13px; color:#444;">
                <?php $items = json_decode($row['items'], true); if ($items): foreach($items as $it) echo htmlspecialchars($it['name']).' × '.intval($it['qty']).'<br>'; endif; ?>
              </td>
              <td style="width:160px; text-align:right;">
                <?php if ($row['status'] !== 'paid'): ?>
                  <a href="pos_pending.php?pay=<?= $row['id'] ?>" class="btn" style="background:#117a2b; color:#fff; padding:8px 12px; border-radius:6px; text-decoration:none;">Mark Paid</a>
                <?php else: ?>
                  <span style="color:#1b7e2a; font-weight:600;">Paid</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="6" style="text-align:center; padding:18px; color:#777;">No invoices yet.</td></tr>
            <?php endif; ?>
          </table>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
