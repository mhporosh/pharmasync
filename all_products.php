<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'inventory';
$activePage = 'all_products';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($search !== '') {
  $sql = 'SELECT * FROM medicines WHERE medicine_name = ? OR slug = ? OR generic_name = ? ORDER BY medicine_name ASC';
  $stmt = $conn->prepare($sql);
  if ($stmt) {
    $stmt->bind_param('sss', $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
  } else {
    $result = false;
  }
} else {
  $sql = 'SELECT * FROM medicines ORDER BY medicine_name ASC';
  $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Products • PharmaSync</title>
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
        <div class="dash-title">All Products</div>
      </div>
      <div class="panel">
        <form method="get" action="" class="inventory-search" style="max-width:420px; margin-bottom:18px; display:flex; gap:10px;">
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search medicine..." style="flex:1; padding:10px 14px; border-radius:8px; border:1px solid #d0d7de;">
          <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <div class="table-wrapper">
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
        </div>
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