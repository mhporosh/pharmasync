<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'inventory';
$activePage = 'low_stock_alerts';
// Show medicines with stock less than or equal to 10
$sql = 'SELECT * FROM medicines WHERE stock <= 10 ORDER BY stock ASC, medicine_name ASC';
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Low Stock Alerts • PharmaSync</title>
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="stylesheet" href="style.css?v=20251205">
  <link rel="stylesheet" href="responsive.css?v=20251205">
  <link rel="stylesheet" href="dashboard.css?v=20251205">
  <script src="https://kit.fontawesome.com/d3e9fb9ce3.js" crossorigin="anonymous"></script>
  <script src="script.js?v=20251205" defer></script>
</head>
<body>
  <?php require __DIR__ . '/partials/nav.php'; ?>
  <div class="layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <main class="dash-wrap">
      <div class="dash-header">
        <div class="dash-title">Low Stock Alerts</div>
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
          </tr>
          <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
          <tr class="low-stock">
            <td><?= htmlspecialchars($row['medicine_name']) ?></td>
            <td><?= htmlspecialchars($row['generic_name']) ?></td>
            <td><?= htmlspecialchars($row['unit_size']) ?></td>
            <td class="price"><?= htmlspecialchars($row['price']) ?></td>
            <td class="stock"><?= htmlspecialchars($row['stock']) ?></td>
            <td class="expiry"><?= htmlspecialchars($row['expiry_date']) ?></td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="6" style="text-align:center; color:#b00020;">No low-stock medicines found.</td></tr>
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