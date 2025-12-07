<?php
require_once __DIR__ . '/../config/db.php';
// Show medicines expiring within 30 days or already expired
$sql = "SELECT *, DATEDIFF(expiry_date, CURDATE()) AS days_left FROM medicines WHERE expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY expiry_date ASC";
$result = $conn->query($sql);
?>

<?php
require_once __DIR__ . '/../config/db.php';
// Show medicines expiring within 30 days or already expired
$sql = "SELECT *, DATEDIFF(expiry_date, CURDATE()) AS days_left FROM medicines WHERE expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY expiry_date ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Expiry Management • PharmaSync</title>
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
    <h2 style="margin-bottom:18px;">Expiry Management</h2>
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
      <?php while($row = $result->fetch_assoc()): ?>
      <tr class="<?= $row['days_left'] < 0 ? 'expired' : ($row['days_left'] <= 7 ? 'expiring-soon' : '') ?>">
        <td><?= htmlspecialchars($row['medicine_name']) ?></td>
        <td><?= htmlspecialchars($row['generic_name']) ?></td>
        <td><?= htmlspecialchars($row['unit_size']) ?></td>
        <td class="price"><?= htmlspecialchars($row['price']) ?></td>
        <td class="stock"><?= htmlspecialchars($row['stock']) ?></td>
        <td class="expiry"><?= htmlspecialchars($row['expiry_date']) ?></td>
        <td><?= $row['days_left'] ?></td>
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