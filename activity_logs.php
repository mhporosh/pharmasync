<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'staff';
$activePage = 'activity_logs';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Activity Logs • PharmaSync</title>
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
        <div class="dash-title">Activity Logs</div>
      </div>

      <div class="panel">
        <h3>Recent Activity</h3>
        <div id="activity-logs">
          <table border="0" cellpadding="8" style="width:100%">
            <thead>
              <tr style="background:#f1f5f4;"><th style="text-align:left; padding:10px">Staff Name</th><th style="text-align:left; padding:10px">Action</th><th style="text-align:left; padding:10px">Date & Time</th></tr>
            </thead>
            <tbody>
              <tr><td>Mehedi Hasan Porosh</td><td>Added new product</td><td>2025-12-07 09:15</td></tr>
              <tr><td>Jane Smith</td><td>Updated stock levels</td><td>2025-12-07 10:02</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
