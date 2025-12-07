<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'dashboard';

$thirtyDaysAgoDateTime = date('Y-m-d H:i:s', strtotime('-30 days'));
$thirtyDaysAgoDate = date('Y-m-d', strtotime('-30 days'));

$metrics = [
  'revenue_30d' => 0.0,
  'gross_profit' => 0.0,
  'net_profit' => 0.0,
  'profit_margin' => 0.0,
  'cogs_30d' => 0.0,
  'receivables' => 0.0,
  'payables' => 0.0,
  'cash_flow' => 0.0,
  'expenses_30d' => 0.0,
  'inventory_value' => 0.0,
  'inventory_turnover' => 0.0,
  'customers' => 0,
  'active_staff' => 0,
];

$itemsSold30d = 0;
$cogs30d = 0.0;

// Ensure dependent tables exist so queries never fail
$conn->query("CREATE TABLE IF NOT EXISTS invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  total DECIMAL(12,2),
  items TEXT,
  status VARCHAR(32) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  description VARCHAR(255),
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  category VARCHAR(100) DEFAULT 'GENERAL',
  payment_status VARCHAR(20) DEFAULT 'PAID',
  incurred_on DATE DEFAULT (CURRENT_DATE),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(64),
  email VARCHAR(255),
  address TEXT,
  notes TEXT,
  status VARCHAR(32) DEFAULT 'ACTIVE',
  purchases_count INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS staff (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  role VARCHAR(60) NOT NULL DEFAULT 'Staff',
  status VARCHAR(30) NOT NULL DEFAULT 'ACTIVE',
  sales_today INT DEFAULT 0,
  products_added INT DEFAULT 0,
  joined_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure medicines table has cost_price to better estimate COGS
$costColumn = $conn->query("SHOW COLUMNS FROM medicines LIKE 'cost_price'");
if ($costColumn && $costColumn->num_rows === 0) {
  $conn->query("ALTER TABLE medicines ADD COLUMN cost_price DECIMAL(10,2) DEFAULT NULL AFTER price");
}
if ($costColumn instanceof mysqli_result) {
  $costColumn->free();
}

// Revenue (paid invoices) in the last 30 days
if ($stmt = $conn->prepare("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status = 'paid' AND created_at >= ?")) {
  $stmt->bind_param('s', $thirtyDaysAgoDateTime);
  $stmt->execute();
  $stmt->bind_result($metrics['revenue_30d']);
  $stmt->fetch();
  $stmt->close();
  $metrics['revenue_30d'] = (float)$metrics['revenue_30d'];
}

// Receivables (unpaid invoices)
if ($stmt = $conn->prepare("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status <> 'paid'")) {
  $stmt->execute();
  $stmt->bind_result($metrics['receivables']);
  $stmt->fetch();
  $stmt->close();
  $metrics['receivables'] = (float)$metrics['receivables'];
}

// Payables (expenses still pending)
if ($stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE payment_status <> 'PAID'")) {
  $stmt->execute();
  $stmt->bind_result($metrics['payables']);
  $stmt->fetch();
  $stmt->close();
  $metrics['payables'] = (float)$metrics['payables'];
}

// Expenses recorded in the last 30 days
if ($stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE incurred_on >= ?")) {
  $stmt->bind_param('s', $thirtyDaysAgoDate);
  $stmt->execute();
  $stmt->bind_result($metrics['expenses_30d']);
  $stmt->fetch();
  $stmt->close();
  $metrics['expenses_30d'] = (float)$metrics['expenses_30d'];
}

$expensesPaid30d = 0.0;
if ($stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE payment_status = 'PAID' AND incurred_on >= ?")) {
  $stmt->bind_param('s', $thirtyDaysAgoDate);
  $stmt->execute();
  $stmt->bind_result($expensesPaid30d);
  $stmt->fetch();
  $stmt->close();
  $expensesPaid30d = (float)$expensesPaid30d;
}

// Inventory valuation and stock
$totalStock = 0;
if ($res = $conn->query("SELECT COALESCE(SUM(price * stock),0) AS inventory_value, COALESCE(SUM(stock),0) AS total_stock FROM medicines")) {
  $row = $res->fetch_assoc();
  if ($row) {
    $metrics['inventory_value'] = (float)$row['inventory_value'];
    $totalStock = (float)$row['total_stock'];
  }
  $res->free();
}

// Build cost lookup for medicines to calculate COGS
$costLookup = [];
if ($res = $conn->query("SELECT id, price, cost_price FROM medicines")) {
  while ($row = $res->fetch_assoc()) {
    $id = (int)$row['id'];
    $fallbackPrice = isset($row['price']) ? (float)$row['price'] : 0.0;
    $costLookup[$id] = isset($row['cost_price']) && $row['cost_price'] !== null
      ? (float)$row['cost_price']
      : $fallbackPrice * 0.7; // approximate if cost not provided
  }
  $res->free();
}

if ($stmt = $conn->prepare("SELECT items FROM invoices WHERE status = 'paid' AND created_at >= ?")) {
  $stmt->bind_param('s', $thirtyDaysAgoDateTime);
  $stmt->execute();
  $stmt->bind_result($itemsJson);
  while ($stmt->fetch()) {
    $items = json_decode($itemsJson, true);
    if (!is_array($items)) {
      continue;
    }
    foreach ($items as $item) {
      $qty = isset($item['qty']) ? (int)$item['qty'] : 0;
      $itemId = isset($item['id']) ? (int)$item['id'] : 0;
      if ($qty <= 0) {
        continue;
      }
      $itemsSold30d += $qty;
      $costPerUnit = 0.0;
      if ($itemId && isset($costLookup[$itemId])) {
        $costPerUnit = $costLookup[$itemId];
      } elseif (isset($item['price'])) {
        $costPerUnit = (float)$item['price'] * 0.7;
      }
      $cogs30d += $costPerUnit * $qty;
    }
  }
  $stmt->close();
}

// Customers and staff counts
if ($stmt = $conn->prepare("SELECT COUNT(*) FROM customers")) {
  $stmt->execute();
  $stmt->bind_result($metrics['customers']);
  $stmt->fetch();
  $stmt->close();
  $metrics['customers'] = (int)$metrics['customers'];
}

if ($stmt = $conn->prepare("SELECT COUNT(*) FROM staff WHERE UPPER(status) = 'ACTIVE'")) {
  $stmt->execute();
  $stmt->bind_result($metrics['active_staff']);
  $stmt->fetch();
  $stmt->close();
  $metrics['active_staff'] = (int)$metrics['active_staff'];
}

// Derived metrics
$metrics['inventory_turnover'] = $totalStock > 0 ? ($itemsSold30d / $totalStock) * (365 / 30) : 0;
$metrics['cogs_30d'] = $cogs30d;
$metrics['gross_profit'] = max(0, $metrics['revenue_30d'] - $cogs30d);
$metrics['net_profit'] = $metrics['gross_profit'] - $expensesPaid30d;
$metrics['profit_margin'] = $metrics['revenue_30d'] > 0 ? ($metrics['net_profit'] / $metrics['revenue_30d']) * 100 : 0;
$metrics['cash_flow'] = $metrics['revenue_30d'] - $expensesPaid30d;
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
        <div class="dash-title">Dashboard</div>
      </div>
      <div class="metric-grid">
        <div class="metric">
          <div class="metric-info">
            <h5>Revenue (30d)</h5>
            <div class="value">BDT <?= number_format($metrics['revenue_30d'], 2); ?></div>
            <div class="muted">Last 30 days</div>
          </div>
          <div class="metric-icon mi-green"><i class="fas fa-arrow-trend-up"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info">
            <h5>Profit Margin</h5>
            <div class="value"><?= number_format($metrics['profit_margin'], 1); ?>%</div>
            <div class="muted">Net <?= $metrics['net_profit'] >= 0 ? 'gain' : 'loss'; ?> BDT <?= number_format(abs($metrics['net_profit']), 2); ?></div>
          </div>
          <div class="metric-icon mi-blue"><i class="fas fa-percent"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info">
            <h5>Gross Profit</h5>
            <div class="value">BDT <?= number_format($metrics['gross_profit'], 2); ?></div>
            <div class="muted">COGS BDT <?= number_format($metrics['cogs_30d'], 2); ?></div>
          </div>
          <div class="metric-icon mi-blue"><i class="fas fa-chart-line"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info">
            <h5>Net Profit</h5>
            <div class="value">BDT <?= number_format($metrics['net_profit'], 2); ?></div>
            <div class="muted">After expenses</div>
          </div>
          <div class="metric-icon mi-purple"><i class="fas fa-wave-square"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info">
            <h5>Receivables</h5>
            <div class="value">BDT <?= number_format($metrics['receivables'], 2); ?></div>
            <div class="muted">Outstanding receivables</div>
          </div>
          <div class="metric-icon mi-orange"><i class="fas fa-inbox"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info">
            <h5>Payables</h5>
            <div class="value">BDT <?= number_format($metrics['payables'], 2); ?></div>
            <div class="muted">Outstanding payables</div>
          </div>
          <div class="metric-icon mi-red"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info">
            <h5>Cash Flow</h5>
            <div class="value">BDT <?= number_format($metrics['cash_flow'], 2); ?></div>
            <div class="muted">Net cash position (30d)</div>
          </div>
          <div class="metric-icon mi-green"><i class="fas fa-arrow-up-right-dots"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info">
            <h5>Expenses (30d)</h5>
            <div class="value">BDT <?= number_format($metrics['expenses_30d'], 2); ?></div>
            <div class="muted">Total recorded expenses</div>
          </div>
          <div class="metric-icon mi-pink"><i class="fas fa-dollar-sign"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info">
            <h5>Inventory Value</h5>
            <div class="value">BDT <?= number_format($metrics['inventory_value'], 2); ?></div>
            <div class="muted">Stock value</div>
          </div>
          <div class="metric-icon mi-blue"><i class="fas fa-cubes"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info">
            <h5>Inventory Turnover</h5>
            <div class="value"><?= number_format($metrics['inventory_turnover'], 2); ?>x</div>
            <div class="muted">Annualized 30d rate</div>
          </div>
          <div class="metric-icon mi-green"><i class="fas fa-rotate"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info">
            <h5>Total Customers</h5>
            <div class="value"><?= number_format($metrics['customers']); ?></div>
            <div class="muted">Customer base</div>
          </div>
          <div class="metric-icon mi-yellow"><i class="fas fa-users"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info">
            <h5>Active Staff</h5>
            <div class="value"><?= number_format($metrics['active_staff']); ?></div>
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
