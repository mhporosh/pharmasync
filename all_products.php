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
  <link rel="stylesheet" href="dashboard.css?v=20251207">
  <script src="https://kit.fontawesome.com/d3e9fb9ce3.js" crossorigin="anonymous"></script>
  <script src="script.js?v=20251207" defer></script>
</head>

<body>
  <?php require __DIR__ . '/partials/nav.php'; ?>
  <div class="layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <main class="dash-wrap">
      <!-- Dashboard Summary Cards -->
      <?php
      // Calculate summary values
      $totalItems = 0;
      $expiringSoon = 0;
      $lowStock = 0;
      $totalValue = 0;
      $today = date('Y-m-d');
      $soon = date('Y-m-d', strtotime('+30 days'));
      if ($result && $result->num_rows > 0) {
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
          $totalItems++;
          if ($row['expiry_date'] <= $soon && $row['expiry_date'] >= $today) $expiringSoon++;
          if ($row['stock'] <= 10) $lowStock++;
          $totalValue += floatval($row['price']) * intval($row['stock']);
        }
        $result->data_seek(0); // Reset pointer for table
      }
      ?>
      <div class="dashboard-cards" style="display:flex; gap:18px; margin-bottom:22px;">
        <div class="card" style="border-radius:12px; box-shadow:0 2px 8px #0001; padding:18px 28px; min-width:170px; display:flex; flex-direction:column; justify-content:center;">
          <div style="font-weight:600; color:var(--accent-2); font-size:15px;">Total Items</div>
          <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
            <span style="color:var(--accent-2); font-size:22px; font-weight:600;"><?= $totalItems ?></span>
            <i class="fas fa-cube" style="color: #673c00ff; font-size:22px;"></i>
          </div>
        </div>
        <div class="card" style="border-radius:12px; box-shadow:0 2px 8px #0001; padding:18px 28px; min-width:170px; display:flex; flex-direction:column; justify-content:center;">
          <div style="font-weight:600; color:var(--accent-2); font-size:15px;">Expiring Soon</div>
          <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
            <span style="color:var(--accent-2); font-size:22px; font-weight:600;"><?= $expiringSoon ?></span>
            <i class="fas fa-clock" style="color: black; font-size:22px;"></i>
          </div>
        </div>
        <div class="card" style="border-radius:12px; box-shadow:0 2px 8px #0001; padding:18px 28px; min-width:170px; display:flex; flex-direction:column; justify-content:center;">
          <div style="font-weight:600; color:var(--accent-2); font-size:15px;">Low Stock</div>
          <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
            <span style="color:var(--accent-2); font-size:22px; font-weight:600;"><?= $lowStock ?></span>
            <i class="fas fa-exclamation-triangle" style="color: red; font-size:22px;"></i>
          </div>
        </div>
        <div class="card" style="border-radius:12px; box-shadow:0 2px 8px #0001; padding:18px 28px; min-width:170px; display:flex; flex-direction:column; justify-content:center;">
          <div style="font-weight:600; color:var(--accent-2); font-size:15px;">Total Value</div>
          <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
            <span style="color:var(--accent-2); font-size:22px; font-weight:600;">BDT <?= number_format($totalValue, 0) ?></span>
            <i class="fas fa-money-bill-wave" style="color: green; font-size:22px;"></i>
          </div>
        </div>
      </div>
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
            <?php if ($result && $result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['medicine_name']) ?></td>
                  <td><?= htmlspecialchars($row['slug']) ?></td>
                  <td><?= htmlspecialchars($row['generic_name']) ?></td>
                  <td><?= htmlspecialchars($row['unit_size']) ?></td>
                  <td class="price"><?= htmlspecialchars($row['price']) ?></td>
                  <td class="stock"><?= htmlspecialchars($row['stock']) ?></td>
                  <td class="expiry"><?= htmlspecialchars($row['expiry_date']) ?></td>
                </tr>
              <?php endwhile;
            else: ?>
              <tr>
                <td colspan="7" style="text-align:center; color:#b00020;">No medicines found.</td>
              </tr>
            <?php endif; ?>
          </table>
        </div>
      </div>
    </main>
  </div>
  <?php if (!function_exists('should_show_footer') || should_show_footer()): ?>
  <footer>
    <div class="footer">
      <p>&copy; 2025 PharmaSync Ltd. All rights reserved.</p>
      <p>Contact us: contact.pharmasync@gmail.com | +8801716008149</p>
    </div>
  </footer>
  <?php endif; ?>
</body>

</html>
<?php $conn->close(); ?>