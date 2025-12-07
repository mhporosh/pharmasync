<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'sales';
$activePage = 'sales_overview';

// ensure invoices table exists
$conn->query("CREATE TABLE IF NOT EXISTS invoices (id INT AUTO_INCREMENT PRIMARY KEY, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, total DECIMAL(12,2), items TEXT, status VARCHAR(32) DEFAULT 'pending') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// helper date ranges
$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('monday this week'));
$month_start = date('Y-m-01');

// totals for paid invoices
function sumInvoices($conn, $from = null, $to = null)
{
  $sql = "SELECT total FROM invoices WHERE status = 'paid'";
  $params = [];
  if ($from && $to) {
    $sql .= " AND DATE(created_at) BETWEEN ? AND ?";
    $params = [$from, $to];
  } elseif ($from) {
    $sql .= " AND DATE(created_at) = ?";
    $params = [$from];
  }
  $stmt = $conn->prepare($sql);
  if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $res = $stmt->get_result();
  $sum = 0;
  while ($r = $res->fetch_assoc()) $sum += floatval($r['total']);
  $stmt->close();
  return $sum;
}

$todaySales = sumInvoices($conn, $today);
$thisWeekSales = sumInvoices($conn, $week_start, date('Y-m-d'));
$thisMonthSales = sumInvoices($conn, $month_start, date('Y-m-d'));

// recent paid invoices
$recent = $conn->query("SELECT * FROM invoices ORDER BY created_at DESC LIMIT 6");

// stock alerts - medicines with stock <= 10
$stockThreshold = 10;
$lowStockCount = 0;
$stockRes = $conn->query("SELECT id, medicine_name, stock FROM medicines WHERE stock <= $stockThreshold ORDER BY stock ASC LIMIT 6");
if ($stockRes) $lowStockCount = $stockRes->num_rows;

// top product - compute from paid invoices
$topProduct = ['name' => 'No sales yet', 'qty' => 0];
$paidRes = $conn->query("SELECT items FROM invoices WHERE status = 'paid'");
if ($paidRes && $paidRes->num_rows > 0) {
  $counts = [];
  while ($r = $paidRes->fetch_assoc()) {
    $items = json_decode($r['items'], true);
    if ($items) foreach ($items as $it) {
      $id = $it['id'] ?? $it['name'];
      $qty = intval($it['qty'] ?? 0);
      if (!isset($counts[$id])) $counts[$id] = ['name' => $it['name'] ?? 'Unknown', 'qty' => 0];
      $counts[$id]['qty'] += $qty;
    }
  }
  // find max
  foreach ($counts as $c) if ($c['qty'] > $topProduct['qty']) $topProduct = $c;
}

// trending items - top 5 sold in last 30 days
$trending = [];
$from30 = date('Y-m-d', strtotime('-30 days'));
$tRes = $conn->prepare("SELECT items FROM invoices WHERE status='paid' AND DATE(created_at) >= ?");
$tRes->bind_param('s', $from30);
$tRes->execute();
$tr = $tRes->get_result();
if ($tr && $tr->num_rows > 0) {
  $counts = [];
  while ($r = $tr->fetch_assoc()) {
    $items = json_decode($r['items'], true);
    if ($items) foreach ($items as $it) {
      $id = $it['id'] ?? $it['name'];
      $qty = intval($it['qty'] ?? 0);
      if (!isset($counts[$id])) $counts[$id] = ['name' => $it['name'] ?? 'Unknown', 'qty' => 0];
      $counts[$id]['qty'] += $qty;
    }
  }
  usort($counts, function ($a, $b) {
    return $b['qty'] - $a['qty'];
  });
  $trending = array_slice($counts, 0, 5);
}

// sales trend last 7 days
$sales7 = [];
for ($i = 6; $i >= 0; $i--) {
  $d = date('Y-m-d', strtotime("-$i days"));
  $sales7[$d] = sumInvoices($conn, $d);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Sales Overview • PharmaSync</title>
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
        <div class="dash-title">Sales</div>
        <div style="margin-left:auto;"></div>
      </div>

      <div style="display:flex; gap:14px; flex-wrap:wrap; margin-bottom:18px;">
        <div class="panel" style="flex:1; min-width:220px; padding:14px;">
          <div class="muted">Today's Sales</div>
          <div style="font-weight:700; color:var(--accent-2); font-size:20px;">BDT <?= number_format($todaySales, 2) ?></div>
          <div class="muted" style="font-size:13px;">0% from yesterday</div>
        </div>

        <div class="panel" style="flex:1; min-width:220px; padding:14px;">
          <div class="muted">This Week</div>
          <div style="font-weight:700; color:var(--accent-2); font-size:20px;">BDT <?= number_format($thisWeekSales, 2) ?></div>
          <div class="muted" style="font-size:13px;">0% from last week</div>
        </div>

        <div class="panel" style="flex:1; min-width:220px; padding:14px;">
          <div class="muted">This Month</div>
          <div style="font-weight:700; color:var(--accent-2); font-size:20px;">BDT <?= number_format($thisMonthSales, 2) ?></div>
          <div class="muted" style="font-size:13px;">0% from last month</div>
        </div>

        <div class="panel" style="flex:1; min-width:220px; padding:14px;">
          <div class="muted">Top Product</div>
          <div style="font-weight:700; color:#ff7043; font-size:18px;"><?= htmlspecialchars($topProduct['name']) ?></div>
          <div class="muted" style="font-size:13px;"><?= $topProduct['qty'] ?> units sold</div>
        </div>

        <div class="panel" style="flex:1; min-width:220px; padding:14px;">
          <div class="muted">Stock Alerts</div>
          <div style="font-weight:700; color:#d32f2f; font-size:18px;"><?= $lowStockCount ?></div>
          <div class="muted" style="font-size:13px;">0 critical</div>
        </div>

        <div class="panel" style="flex:1; min-width:220px; padding:14px;">
          <div class="muted">Trending Items</div>
          <div style="font-weight:700; color:#444; font-size:18px;"><?= count($trending) ?></div>
          <div class="muted" style="font-size:13px;">High growth products</div>
        </div>
      </div>

      <div class="panel" style="padding:12px; margin-bottom:18px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;"><strong>Recent Sales</strong><a href="pos_pending.php" class="btn" style="padding:6px 10px; text-decoration:none; border:1px solid #e5eef2;">View All</a></div>
        <?php if ($recent && $recent->num_rows > 0): ?>
          <div style="display:flex; flex-direction:column; gap:8px;">
            <?php while ($r = $recent->fetch_assoc()): ?>
              <div style="display:flex; justify-content:space-between; padding:8px; border-radius:6px; background:#fbfdff; border:1px solid #f1f6f7;">
                <div>
                  <div style="font-weight:600">Invoice #<?= $r['id'] ?></div>
                  <div class="muted" style="font-size:13px;"><?= $r['created_at'] ?></div>
                </div>
                <div style="text-align:right;">
                  <div style="font-weight:700">BDT <?= number_format($r['total'], 2) ?></div>
                  <div style="font-size:13px; color:<?= $r['status'] === 'paid' ? '#1b7e2a' : '#d32f2f' ?>;"><?= ucfirst($r['status']) ?></div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div style="padding:28px; text-align:center; color:#777;">No Recent Sales</div>
        <?php endif; ?>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:18px; margin-bottom:18px;">
        <div class="panel" style="padding:12px;">
          <strong>Sales Trend (Last 7 Days)</strong>
          <div style="height:180px; margin-top:8px; display:flex; align-items:end; gap:8px;">
            <?php foreach ($sales7 as $d => $v): $h = $v > 0 ? min(160, $v * 1) : 4; ?>
              <div style="flex:1; text-align:center;">
                <div style="height:<?= $h ?>px; background:linear-gradient(180deg,rgba(27,126,42,0.12),rgba(27,126,42,0.18)); border-radius:6px; margin-bottom:6px;"></div>
                <div style="font-size:11px; color:#777;"><?= date('M d', strtotime($d)) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="panel" style="padding:12px;">
          <strong>Sales by Category</strong>
          <div style="height:180px; display:flex; align-items:center; justify-content:center; color:#777;">No Category Data</div>
        </div>

        <div class="panel" style="padding:12px;">
          <strong>Trending Products</strong>
          <div style="height:180px; margin-top:8px;">
            <?php if ($trending): foreach ($trending as $t): ?>
                <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px dashed #f1f4f6;">
                  <div><?= htmlspecialchars($t['name']) ?></div>
                  <div style="font-weight:700;"><?= $t['qty'] ?></div>
                </div>
              <?php endforeach;
            else: ?>
              <div style="display:flex; align-items:center; justify-content:center; height:100%; color:#777;">No Trending Data</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
        <div class="panel" style="padding:12px; min-height:160px;">
          <strong>Stock Alerts</strong>
          <?php if ($stockRes && $stockRes->num_rows > 0): while ($s = $stockRes->fetch_assoc()): ?>
              <div style="padding:8px 0; border-bottom:1px dashed #f1f4f6;">
                <div style="font-weight:600"><?= htmlspecialchars($s['medicine_name']) ?></div>
                <div class="muted">Stock: <?= intval($s['stock']) ?></div>
              </div>
            <?php endwhile;
          else: ?>
            <div style="padding:18px; color:#777;">No Stock Alerts</div>
          <?php endif; ?>
        </div>

        <div class="panel" style="padding:12px; min-height:160px;">
          <strong>Top Suppliers by Value</strong>
          <div style="padding:18px; color:#777;">No Supplier Data</div>
        </div>
      </div>

    </main>
  </div>
</body>

</html>