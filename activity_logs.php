<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'staff';
$activePage = 'activity_logs';

// Ensure audit_logs table exists
$ensure = "CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    user_name VARCHAR(255) DEFAULT NULL,
    action VARCHAR(255) DEFAULT NULL,
    details TEXT,
    ip VARCHAR(64) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($ensure);

// Filters from GET
$rangeDays = isset($_GET['range']) ? intval($_GET['range']) : 7; // default last 7 days
$actionFilter = isset($_GET['action']) ? trim($_GET['action']) : '';

// Build where clause
$where = [];
$params = [];
$types = '';
$where[] = "created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
$params[] = $rangeDays;
$types .= 'i';
if ($actionFilter !== '' && strtolower($actionFilter) !== 'all') {
    $where[] = 'action = ?';
    $params[] = $actionFilter;
    $types .= 's';
}
$whereSql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Export CSV if requested
if (isset($_GET['export']) && $_GET['export'] == '1') {
    $sql = "SELECT id,user_id,user_name,action,details,ip,created_at FROM audit_logs $whereSql ORDER BY created_at DESC LIMIT 5000";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="activity_logs.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id','user_id','user_name','action','details','ip','created_at']);
        while ($row = $res->fetch_assoc()) fputcsv($out, $row);
        fclose($out);
        exit;
    }
}

// Fetch logs
$sql = "SELECT id,user_id,user_name,action,details,ip,created_at FROM audit_logs $whereSql ORDER BY created_at DESC LIMIT 500";
$stmt = $conn->prepare($sql);
$logs = [];
if ($stmt) {
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $logs[] = $r;
}

// Metrics: total logs in range, distinct active users in range
$metricSql = "SELECT COUNT(*) AS total, COUNT(DISTINCT user_name) AS users FROM audit_logs $whereSql";
$stmtm = $conn->prepare($metricSql);
$totalLogs = 0; $activeUsers = 0;
if ($stmtm) {
    if (!empty($params)) $stmtm->bind_param($types, ...$params);
    $stmtm->execute();
    $rm = $stmtm->get_result()->fetch_assoc();
    $totalLogs = intval($rm['total'] ?? 0);
    $activeUsers = intval($rm['users'] ?? 0);
}

// Render page
ob_start();
?>
<main class="dash-wrap fullwidth" data-page="activity_logs" data-page-title="Activity Monitor">
  <div class="dash-header">
    <div class="dash-title">Activity Monitor (<?php echo $totalLogs; ?>)</div>
  </div>

  <div class="panel activity-panel">
    <div class="activity-metrics">
      <div class="activity-card">
        <div>
          <h4>Total Audit Logs</h4>
          <div class="big"><?php echo $totalLogs; ?></div>
          <div class="note">Last <?php echo $rangeDays; ?> days</div>
        </div>
        <div class="icon"><i class="fas fa-list"></i></div>
      </div>

      <div class="activity-card">
        <div>
          <h4>Active Users</h4>
          <div class="big"><?php echo $activeUsers; ?></div>
          <div class="note">Last <?php echo $rangeDays; ?> days</div>
        </div>
        <div class="icon"><i class="fas fa-users"></i></div>
      </div>
    </div>

    <div class="activity-filters">
      <select class="date-select" name="range" id="rangeSel">
        <option value="7"<?php echo $rangeDays==7?' selected':''; ?>>Last 7 days</option>
        <option value="30"<?php echo $rangeDays==30?' selected':''; ?>>Last 30 days</option>
        <option value="90"<?php echo $rangeDays==90?' selected':''; ?>>Last 90 days</option>
      </select>

      <select class="select" id="actionSel" name="action">
        <option value="all">All Actions</option>
        <?php
          // list distinct actions for filter
          $acts = $conn->query('SELECT DISTINCT action FROM audit_logs WHERE action IS NOT NULL AND action <> "" LIMIT 200');
          if ($acts) while ($a = $acts->fetch_assoc()) {
            $val = $a['action'];
            echo '<option value="'.htmlspecialchars($val).'"'.($actionFilter===$val?' selected':'').'>'.htmlspecialchars($val).'</option>';
          }
        ?>
      </select>

      <div class="tools">
        <button class="btn secondary" id="refreshBtn" title="Refresh"><i class="fas fa-sync-alt"></i></button>
        <button class="btn" id="exportBtn" title="Export CSV"><i class="fas fa-file-export"></i></button>
      </div>
    </div>

    <?php if (empty($logs)): ?>
      <div class="activity-empty">
        <div class="icon"><i class="fas fa-list"></i></div>
        <h4>No Logs available</h4>
      </div>
    <?php else: ?>
      <div id="activity-logs" class="table-wrapper">
        <table class="activity-table">
          <thead>
            <tr><th>User</th><th>Action</th><th>Details</th><th>Date &amp; Time</th></tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['user_name']?:'System'); ?></td>
                <td class="activity-action"><?php echo htmlspecialchars($row['action']); ?></td>
                <td><?php echo htmlspecialchars($row['details']); ?></td>
                <td class="activity-time"><?php echo htmlspecialchars($row['created_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php
$mainContent = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Activity Monitor • PharmaSync</title>
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

  <script>
    (function(){
      var rangeSel = document.getElementById('rangeSel');
      var actionSel = document.getElementById('actionSel');
      var refreshBtn = document.getElementById('refreshBtn');
      var exportBtn = document.getElementById('exportBtn');
      function reloadWithFilters() {
        var r = rangeSel ? rangeSel.value : '';
        var a = actionSel ? actionSel.value : '';
        var url = new URL(window.location.href);
        url.searchParams.set('range', r);
        if (a && a !== 'all') url.searchParams.set('action', a); else url.searchParams.delete('action');
        window.location.href = url.toString();
      }
      if (rangeSel) rangeSel.addEventListener('change', reloadWithFilters);
      if (actionSel) actionSel.addEventListener('change', reloadWithFilters);
      if (refreshBtn) refreshBtn.addEventListener('click', function(e){ e.preventDefault(); window.location.reload(); });
      if (exportBtn) exportBtn.addEventListener('click', function(e){ e.preventDefault(); var url = new URL(window.location.href); url.searchParams.set('export','1'); window.location.href = url.toString(); });
    })();
  </script>
</body>
</html>
