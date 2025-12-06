<?php
require_once __DIR__ . '/config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit;
}
$admin_name = '';
$user_role = 'Admin';
$uid = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT first_name, last_name FROM users WHERE id = ? LIMIT 1');
if ($stmt) {
  $stmt->bind_param('i', $uid);
  $stmt->execute();
  $stmt->bind_result($fn, $ln);
  if ($stmt->fetch()) {
    $admin_name = trim($fn . ' ' . $ln);
  }
  $stmt->close();
}
if ($admin_name === '') {
  $admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';
}
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
  <header>
    <div class="nav">
      <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false" title="Menu"><i class="fas fa-bars"></i></button>
      <div class="logo"><span class="brand-logo"><i class="fas fa-plus"></i></span><h1>PharmaSync</h1></div>
      <div class="nav-actions">
        <button id="themeToggle" class="icon-btn" aria-label="Toggle theme"><i class="fas fa-moon"></i></button>
        <button id="refreshBtn" class="icon-btn" aria-label="Refresh"><i class="fas fa-sync-alt"></i></button>
        <button id="notifBtn" class="icon-btn" aria-label="Notifications"><i class="fas fa-bell"></i></button>
        <a href="#" class="icon-btn" aria-label="Settings"><i class="fas fa-cog"></i></a>
      </div>
      <div class="profile">
        <button id="profileBtn" class="admin-pill" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-user-shield"></i>
          <div>
            <span class="admin-name"><?php echo htmlspecialchars($admin_name); ?></span>
            <div class="admin-role"><?php echo htmlspecialchars($user_role); ?></div>
          </div>
        </button>
        <div id="profileMenu" class="dd-menu" aria-hidden="true">
          <a href="#" class="dd-item" id="editInfoBtn"><i class="fas fa-user-edit"></i> Edit Info</a>
          <a href="handlers/logout.php" class="dd-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
    </div>
  </header>
  <div class="layout">
    <aside class="sidebar">
      <div class="side-brand">
        <div class="side-header">
          <div style="display:flex; align-items:center; gap:10px;">
            <div class="badge">PP</div>
            <div class="brand-text">
              <div class="brand-title">PharmaSync</div>
              <div class="brand-tier">STARTER</div>
            </div>
          </div>
          <button id="sidebarToggle" class="side-toggle" aria-expanded="true" title="Collapse sidebar"><i class="fas fa-angle-left"></i></button>
        </div>
      </div>
      <nav class="menu">
        <a class="menu-item" href="dashboard.php"><span><i class="fas fa-home"></i></span><span>Dashboard</span></a>



        <button class="menu-item has-sub" data-target="sales-sub" aria-expanded="false"><span><i class="fas fa-dollar-sign"></i></span><span>Sales</span><i class="fas fa-chevron-down chevron"></i></button>
        <div id="sales-sub" class="submenu">
          <a href="#" class="submenu-item"><span><i class="fas fa-cash-register"></i></span><span>Point of Sale</span></a>
          <a href="#" class="submenu-item"><span><i class="fas fa-chart-bar"></i></span><span>Sales Overview</span></a>
          <a href="#" class="submenu-item"><span><i class="fas fa-history"></i></span><span>Sales History</span></a>
        </div>

        <button class="menu-item has-sub" data-target="inventory-sub" aria-expanded="false"><span><i class="fas fa-boxes"></i></span><span>Inventory</span><i class="fas fa-chevron-down chevron"></i></button>
        <div id="inventory-sub" class="submenu">
          <a href="handlers/all_products.php" class="submenu-item"><span><i class="fas fa-pills"></i></span><span>All Products</span></a>
          <a href="handlers/expiry_management.php" class="submenu-item"><span><i class="fas fa-hourglass-end"></i></span><span>Expiry Management</span></a>
          <a href="handlers/low_stock_alerts.php" class="submenu-item"><span><i class="fas fa-exclamation-triangle"></i></span><span>Low Stock Alerts</span></a>
        </div>

        <button class="menu-item has-sub" data-target="purchases-sub" aria-expanded="false"><span><i class="fas fa-shopping-cart"></i></span><span>Purchases</span><i class="fas fa-chevron-down chevron"></i></button>
        <div id="purchases-sub" class="submenu">
          <a href="#" class="submenu-item"><span><i class="fas fa-file-alt"></i></span><span>Requisitions</span></a>
          <a href="#" class="submenu-item"><span><i class="fas fa-file-invoice-dollar"></i></span><span>Purchase Orders</span></a>
          <a href="#" class="submenu-item"><span><i class="fas fa-truck"></i></span><span>Receiving Hub</span></a>
          <a href="#" class="submenu-item"><span><i class="fas fa-industry"></i></span><span>Suppliers</span></a>
        </div>

        <button class="menu-item has-sub" data-target="customers-sub" aria-expanded="false"><span><i class="fas fa-user-friends"></i></span><span>Customers</span><i class="fas fa-chevron-down chevron"></i></button>
        <div id="customers-sub" class="submenu">
          <a href="#" class="submenu-item"><span><i class="fas fa-address-book"></i></span><span>Customers List</span></a>
        </div>

        <button class="menu-item has-sub" data-target="staff-sub" aria-expanded="false"><span><i class="fas fa-users-cog"></i></span><span>Staff</span><i class="fas fa-chevron-down chevron"></i></button>
        <div id="staff-sub" class="submenu">
          <a href="#" class="submenu-item"><span><i class="fas fa-id-badge"></i></span><span>Staff Directory</span></a>
          <a href="#" class="submenu-item"><span><i class="fas fa-wave-square"></i></span><span>Activity Logs</span></a>
        </div>

        <a class="menu-item" href="#"><span><i class="fas fa-cog"></i></span><span>Settings</span></a>

        <a class="menu-item" href="#"><span><i class="fas fa-credit-card"></i></span><span>Billing</span><span class="upgrade">Upgrade</span></a>
      </nav>
    </aside>
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
