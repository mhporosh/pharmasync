<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'staff';
$activePage = 'staff_directory';
// Fetch staff from database (table `staff`) and compute overview metrics
$staff_rows = [];
$totalStaff = 0;
$activeStaff = 0;
$admins = 0;
$salesToday = 0;
$tableMissing = false;
// Attempt to query the `staff` table. If the table does not exist or query fails,
// catch the exception and set a flag so the UI can show a helpful message instead
// of a fatal error.
if (isset($conn)) {
    $sql = "SELECT id, name, email, role, status, joined_date, COALESCE(sales_today,0) AS sales_today, COALESCE(products_added,0) AS products_added FROM staff";
    try {
        $result = $conn->query($sql);
    } catch (mysqli_sql_exception $e) {
        // Table probably doesn't exist or there was a permission error.
        $tableMissing = true;
        $result = false;
    }

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $staff_rows[] = $row;
            $totalStaff++;
            if (isset($row['status']) && strtolower($row['status']) === 'active') $activeStaff++;
            if (isset($row['role']) && strtolower($row['role']) === 'admin') $admins++;
            $salesToday += intval($row['sales_today']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Staff Management • PharmaSync</title>
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
    <main class="dash-wrap fullwidth">
      <div class="dash-header" style="align-items: flex-start; gap:12px;">
        <div>
          <h1 class="dash-title">Staff Management</h1>
          <div class="muted" style="margin-top:6px;">Manage your team, profiles and activity logs</div>
        </div>
        <div class="nav-actions" style="margin-left:auto;">
          <button id="addStaffBtn" class="add-staff-btn"><i class="fas fa-plus"></i> Add Staff</button>
        </div>
      </div>

      <div class="staff-tabs">
        <button class="tab active" data-tab="overview">Overview</button>
        <button class="tab" data-tab="scheduling">Scheduling</button>
        <button class="tab" data-tab="attendance">Attendance</button>
        <button class="tab" data-tab="leaves">Leaves</button>
        <button class="tab" data-tab="tasks">Tasks</button>
        <button class="tab" data-tab="payroll">Payroll</button>
        <button class="tab" data-tab="settings">Settings</button>
      </div>

      <section id="overview" class="tab-panel active">
        <div class="metric-grid staff-metrics">
          <div class="stat-card">
            <div class="stat-label">Total Staff</div>
            <div class="stat-value" id="totalStaff"><?php echo intval($totalStaff); ?></div>
            <div class="stat-icon"><i class="fas fa-users"></i></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Active Staff</div>
            <div class="stat-value" id="activeStaff"><?php echo intval($activeStaff); ?></div>
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Admins</div>
            <div class="stat-value" id="admins"><?php echo intval($admins); ?></div>
            <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Sales Today</div>
            <div class="stat-value" id="salesToday"><?php echo intval($salesToday); ?></div>
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
          </div>
        </div>

        <div class="panel" style="margin-top:18px;">
          <h3 style="margin:0 0 12px 0;">Accounts</h3>
          <div class="staff-list-grid">
            <?php if ($tableMissing): ?>
                <div class="panel" style="padding:18px; border-radius:8px; background:#fff6f0; border:1px solid #ffd6b3;">
                <p style="margin:0 0 8px 0; color:#7a3b00;"><strong>Staff table not found.</strong> The application could not find the `staff` table in the `pharmasync` database.</p>
                <p style="margin:0; font-size:90%;">Run the provided SQL to create the table, then reload this page.</p>
                <pre style="margin-top:8px; padding:8px; background:#fff; border:1px solid #eee; overflow:auto; font-size:12px;">CREATE TABLE IF NOT EXISTS staff (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  role VARCHAR(60) NOT NULL DEFAULT 'Staff',
  status VARCHAR(30) NOT NULL DEFAULT 'ACTIVE',
  sales_today INT DEFAULT 0,
  products_added INT DEFAULT 0,
  joined_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);</pre>
              </div>
            <?php elseif (count($staff_rows) === 0): ?>
              <div class="panel" style="padding:18px; border-radius:8px; background:#fbfbfb;">
                <p style="margin:0;">No staff accounts yet. Click <strong>Add Staff</strong> to create a new staff record.</p>
              </div>
            <?php else: ?>
              <?php foreach ($staff_rows as $s): ?>
                <?php
                  $initials = '';
                  if (!empty($s['name'])) {
                      $parts = preg_split('/\s+/', trim($s['name']));
                      $initials = strtoupper(substr($parts[0],0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : ''));
                  }
                  $joined = !empty($s['joined_date']) ? date('n/j/Y', strtotime($s['joined_date'])) : date('n/j/Y');
                  // Build sanitized CSS classes for role and status so modifiers like
                  // `.badge.role.admin` and `.badge.status.active` match correctly.
                  $role_raw = $s['role'] ?? 'Staff';
                  $status_raw = $s['status'] ?? 'INACTIVE';
                  $role_class = strtolower(preg_replace('/[^a-z0-9_-]+/i', '', $role_raw));
                  $status_class = strtolower(preg_replace('/[^a-z0-9_-]+/i', '', $status_raw));
                ?>
                <div class="staff-card compact">
                  <div class="staff-card-left">
                    <div class="avatar"><?php echo htmlspecialchars($initials ?: 'US'); ?></div>
                  </div>
                  <div class="staff-card-body">
                    <div class="staff-top-row">
                      <div class="staff-name"><?php echo htmlspecialchars($s['name'] ?? 'Unnamed'); ?></div>
                      <div class="staff-email"><?php echo htmlspecialchars($s['email'] ?? ''); ?></div>
                    </div>
                    <div class="staff-info-grid">
                      <div class="info-label">Role:</div>
                      <div class="info-value"><span class="badge role <?php echo htmlspecialchars($role_class); ?>"><?php echo htmlspecialchars(strtoupper($s['role'] ?? 'Staff')); ?></span></div>

                      <div class="info-label">Status:</div>
                      <div class="info-value"><span class="badge status <?php echo htmlspecialchars($status_class); ?>"><?php echo htmlspecialchars(strtoupper($s['status'] ?? 'INACTIVE')); ?></span></div>

                      <div class="info-label">Sales Today:</div>
                      <div class="info-value"><?php echo intval($s['sales_today'] ?? 0); ?></div>

                      <div class="info-label">Products Added:</div>
                      <div class="info-value"><?php echo intval($s['products_added'] ?? 0); ?></div>

                      <div class="info-label">Joined:</div>
                      <div class="info-value"><?php echo $joined; ?></div>
                    </div>
                  </div>
                  <div class="staff-card-actions">
                    <button class="icon-btn" title="More"><i class="fas fa-ellipsis-v"></i></button>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- placeholder panels for other tabs -->
      <section id="scheduling" class="tab-panel">
        <div class="panel"><em>Scheduling module placeholder.</em></div>
      </section>
      <section id="attendance" class="tab-panel">
        <div class="panel"><em>Attendance module placeholder.</em></div>
      </section>
      <section id="leaves" class="tab-panel">
        <div class="panel"><em>Leaves module placeholder.</em></div>
      </section>
      <section id="tasks" class="tab-panel">
        <div class="panel"><em>Tasks module placeholder.</em></div>
      </section>
      <section id="payroll" class="tab-panel">
        <div class="panel"><em>Payroll module placeholder.</em></div>
      </section>
      <section id="settings" class="tab-panel">
        <div class="panel"><em>Staff settings placeholder.</em></div>
      </section>

    </main>
  </div>

  <footer class="site-footer">
    <div class="footer">
      <p>&copy; 2025 PharmaSync Ltd. All rights reserved.</p>
    </div>
  </footer>

  <!-- Add Staff Modal -->
  <div id="addStaffModal" class="modal" aria-hidden="true">
    <div class="modal-dialog">
      <header>
        <h3>Add New Staff</h3>
        <button class="modal-close" aria-label="Close">&times;</button>
      </header>
      <div class="modal-body">
        <form id="addStaffForm">
          <label>Full Name<input type="text" name="fullname" required></label>
          <label>Email<input type="email" name="email" required></label>
          <label>Role<select name="role"><option>Pharmacist</option><option>Manager</option><option>Admin</option></select></label>
          <div style="margin-top:12px;"><button type="submit" class="add-staff-btn">Add Staff</button> <button type="button" class="modal-close btn-secondary">Cancel</button></div>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
