<?php
require_once __DIR__ . '/../config/db.php';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($search !== '') {
  $sql = "SELECT * FROM medicines WHERE medicine_name = ? OR slug = ? OR generic_name = ? ORDER BY medicine_name ASC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sss', $search, $search, $search);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $sql = "SELECT * FROM medicines ORDER BY medicine_name ASC";
  $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Products • PharmaSync</title>
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
    <h2 style="margin-bottom:18px;">All Products</h2>
    <form method="get" action="" style="max-width:400px; margin:0 auto 18px; display:flex; gap:8px;">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search medicine..." style="flex:1; padding:8px 12px; border-radius:6px; border:1px solid #ccc;">
      <button type="submit" class="btn btn-primary">Search</button>
    </form>
    <table class="inventory-table">
      <tr>
        <th>Medicine Name</th>
        <th>Slug</th>
        <th>Generic Name</th>
        <th>Unit Size</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Expiry Date</th>
      </tr>
      <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['medicine_name']) ?></td>
        <td><?= htmlspecialchars($row['slug']) ?></td>
        <td><?= htmlspecialchars($row['generic_name']) ?></td>
        <td><?= htmlspecialchars($row['unit_size']) ?></td>
        <td class="price"><?= htmlspecialchars($row['price']) ?></td>
        <td class="stock"><?= htmlspecialchars($row['stock']) ?></td>
        <td class="expiry"><?= htmlspecialchars($row['expiry_date']) ?></td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="7" style="text-align:center; color:#b00020;">No medicines found.</td></tr>
      <?php endif; ?>
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