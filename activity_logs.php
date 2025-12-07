<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'staff';
$activePage = 'activity_logs';
$partial = isset($_GET['partial']);
ob_start();
?>
<main class="dash-wrap" data-page="activity_logs" data-page-title="Activity Logs • PharmaSync">
      <div class="dash-header">
        <div class="dash-title">Activity Logs</div>
      </div>

      <div class="panel activity-panel">
        <h3>Recent Activity</h3>
        <div id="activity-logs" class="table-wrapper">
          <table class="activity-table">
            <thead>
              <tr><th>Staff Name</th><th>Action</th><th>Date &amp; Time</th></tr>
            </thead>
            <tbody>
              <tr><td>Mehedi Hasan Porosh</td><td class="activity-action">Added new product</td><td class="activity-time">2025-12-07 09:15</td></tr>
              <tr><td>Jane Smith</td><td class="activity-action">Updated stock levels</td><td class="activity-time">2025-12-07 10:02</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
<?php
$mainContent = ob_get_clean();
if ($partial) {
    echo $mainContent;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Activity Logs • PharmaSync</title>
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
    <?php echo $mainContent; ?>
  </div>
</body>
</html>
