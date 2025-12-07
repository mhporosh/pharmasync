<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'inventory';
$activePage = 'expiry_management';
// Show medicines expiring within 30 days or already expired
$sql = 'SELECT *, DATEDIFF(expiry_date, CURDATE()) AS days_left FROM medicines WHERE expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY expiry_date ASC';
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Expiry Management • PharmaSync</title>
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
      <div class="dash-header">
        <div class="dash-title">Expiry Management</div>
      </div>
      <div class="panel">
        <table class="inventory-table">
          <tr>
            <th>Medicine Name</th>
            <th>Generic Name</th>
            <th>Unit Size</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Expiry Date</th>
            <th>Days Left</th>
          </tr>
          <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
          <tr class="<?= $row['days_left'] < 0 ? 'expired' : ($row['days_left'] <= 7 ? 'expiring-soon' : '') ?>">
            <td><?= htmlspecialchars($row['medicine_name']) ?></td>
            <td><?= htmlspecialchars($row['generic_name']) ?></td>
            <td><?= htmlspecialchars($row['unit_size']) ?></td>
            <td class="price"><?= htmlspecialchars($row['price']) ?></td>
            <td class="stock"><?= htmlspecialchars($row['stock']) ?></td>
            <td class="expiry"><?= htmlspecialchars($row['expiry_date']) ?></td>
            <td><?= $row['days_left'] ?></td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="7" style="text-align:center; color:#b00020;">No expiring medicines found.</td></tr>
          <?php endif; ?>
        </table>
      </div>
    </main>
  </div>
  <footer>
    <div class="footer">
      <p>&copy; 2025 PharmaSync Ltd. All rights reserved.</p>
      <p>Contact us: contact.pharmasync@gmail.com | +8801716008149</p>
    </div>
  </footer>
</body>
</html>
<?php $conn->close(); ?>