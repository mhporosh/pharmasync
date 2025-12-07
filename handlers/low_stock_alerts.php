<?php
require_once __DIR__ . '/../config/db.php';
// Show medicines with stock less than or equal to 10
$sql = "SELECT * FROM medicines WHERE stock <= 10 ORDER BY stock ASC, medicine_name ASC";
$result = $conn->query($sql);
?>

<?php
require_once __DIR__ . '/../config/db.php';
// Show medicines with stock less than or equal to 10
$sql = "SELECT * FROM medicines WHERE stock <= 10 ORDER BY stock ASC, medicine_name ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Low Stock Alerts • PharmaSync</title>
  <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../responsive.css">
</head>
<body>
  <header>
    <div class="nav">
      <div class="logo"><span class="brand-logo"><i class="fas fa-plus"></i></span><h1>PharmaSync</h1></div>
    </div>
  </header>
  <main class="container">
    <a href="../dashboard.php" class="btn btn-secondary" style="float:right; margin-bottom:12px;">Back to Dashboard</a>
    <h2 style="margin-bottom:18px;">Low Stock Alerts</h2>
    <table class="inventory-table">
      <tr>
        <th>Medicine Name</th>
        <th>Generic Name</th>
        <th>Unit Size</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Expiry Date</th>
      </tr>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr class="low-stock">
        <td><?= htmlspecialchars($row['medicine_name']) ?></td>
        <td><?= htmlspecialchars($row['generic_name']) ?></td>
        <td><?= htmlspecialchars($row['unit_size']) ?></td>
        <td class="price"><?= htmlspecialchars($row['price']) ?></td>
        <td class="stock"><?= htmlspecialchars($row['stock']) ?></td>
        <td class="expiry"><?= htmlspecialchars($row['expiry_date']) ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
    <!-- Back button moved to top -->
  </main>
  <footer>
    <div class="footer">
      <p>&copy; 2025 PharmaSync Ltd. All rights reserved.</p>
      <p>Contact us: contact.pharmasync@gmail.com | +8801716008149</p>
    </div>
  </footer>
</body>
</html>
<?php $conn->close(); ?>