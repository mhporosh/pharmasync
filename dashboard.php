<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'dashboard';
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard • PharmaSync</title>
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
        <div class="dash-title">Dashboard</div>
      </div>
      <div class="metric-grid">
      <div class="metric">
        <div class="metric-info">
          <h5>Revenue (30d)</h5>
          <div class="value">BDT 0</div>
          <div class="muted">Last 30 days</div>
        </div>
        <div class="metric-icon mi-green"><i class="fas fa-arrow-trend-up"></i></div>
      </div>
      <div class="metric">
        <div class="metric-info">
          <h5>Profit Margin</h5>
          <div class="value">0%</div>
          <div class="muted">BDT 0</div>
        </div>
        <div class="metric-icon mi-blue"><i class="fas fa-percent"></i></div>
      </div>
      <div class="metric">
        <div class="metric-info">
          <h5>Gross Profit</h5>
          <div class="value">BDT 0</div>
          <div class="muted">Before expenses</div>
        </div>
        <div class="metric-icon mi-blue"><i class="fas fa-chart-line"></i></div>
      </div>
      <div class="metric">
        <div class="metric-info">
          <h5>Net Profit</h5>
          <div class="value">BDT 0</div>
          <div class="muted">After expenses</div>
        </div>
        <div class="metric-icon mi-purple"><i class="fas fa-wave-square"></i></div>
      </div>
      <div class="metric">
        <div class="metric-info">
          <h5>Receivables</h5>
          <div class="value">BDT 0</div>
          <div class="muted">Outstanding receivables</div>
        </div>
        <div class="metric-icon mi-orange"><i class="fas fa-inbox"></i></div>
      </div>
      <div class="metric">
        <div class="metric-info">
          <h5>Payables</h5>
          <div class="value">BDT 0</div>
          <div class="muted">Outstanding payables</div>
        </div>
        <div class="metric-icon mi-red"><i class="fas fa-file-invoice-dollar"></i></div>
      </div>
      <div class="metric">
        <div class="metric-info">
          <h5>Cash Flow</h5>
          <div class="value">BDT 0</div>
          <div class="muted">Net cash position</div>
        </div>
        <div class="metric-icon mi-green"><i class="fas fa-arrow-up-right-dots"></i></div>
      </div>
      <div class="metric">
        <div class="metric-info">
          <h5>Expenses (30d)</h5>
          <div class="value">BDT 0</div>
          <div class="muted">Total expenses</div>
        </div>
        <div class="metric-icon mi-pink"><i class="fas fa-dollar-sign"></i></div>
      </div>
      <div class="metric">
        <div class="metric-info">
          <h5>Inventory Value</h5>
          <div class="value">BDT 0</div>
          <div class="muted">Stock value</div>
        </div>
        <div class="metric-icon mi-blue"><i class="fas fa-cubes"></i></div>
      </div>
      <div class="metric">
        <div class="metric-info">
          <h5>Inventory Turnover</h5>
          <div class="value">0x</div>
          <div class="muted">Annual turnover rate</div>
        </div>
        <div class="metric-icon mi-green"><i class="fas fa-rotate"></i></div>
      </div>
      <div class="metric">
        <div class="metric-info">
          <h5>Total Customers</h5>
          <div class="value">0</div>
          <div class="muted">Customer base</div>
        </div>
        <div class="metric-icon mi-yellow"><i class="fas fa-users"></i></div>
      </div>
      <div class="metric">
        <div class="metric-info">
          <h5>Active Staff</h5>
          <div class="value">0</div>
          <div class="muted">Team members</div>
        </div>
        <div class="metric-icon mi-blue"><i class="fas fa-user-check"></i></div>
      </div>
      </div>
      <div class="charts">
        <div class="panel"><strong>Daily Revenue Trend (30d)</strong></div>
        <div class="panel"><strong>Monthly Comparison</strong></div>
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
