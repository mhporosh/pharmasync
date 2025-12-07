$php_debug = true;
<?php
if (!empty($php_debug)) {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
}
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'purchases';
$activePage = 'purchase_orders';
$currentAdminName = $_SESSION['user_name'] ?? 'Primary Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Purchase Orders • PharmaSync</title>
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
        <div>
          <div class="dash-title">Purchase Orders</div>
          <div class="purchase-sub">Manage and track your purchase orders</div>
        </div>
        <div class="po-actions">
          <button class="btn-new-po" type="button"><i class="fas fa-plus" style="margin-right:8px"></i> New Purchase Order</button>
        </div>
      </div>

      <div class="panel">
        <div class="po-tabs" role="tablist" aria-label="Purchase order filters">
          <button class="active" role="tab" aria-selected="true">All</button>
          <button role="tab">Pending</button>
          <button role="tab">Active</button>
          <button role="tab">Receiving</button>
          <button role="tab">Completed</button>
        </div>

        <div class="po-empty" aria-live="polite">
          <div class="icon"><i class="far fa-file-alt"></i></div>
          <h3>Purchase Orders</h3>
          <p>No purchase orders yet. Create one to get started.</p>
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
