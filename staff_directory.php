<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'staff';
$activePage = 'staff_directory';
$currentAdminName = $_SESSION['user_name'] ?? 'Primary Admin';
// Fetch staff from database (table `staff`) and compute overview metrics
$staff_rows = [];
$totalStaff = 0;
$activeStaff = 0;
$admins = 0;
$salesToday = 0;
$tableMissing = false;
 $partial = isset($_GET['partial']);

function renderStaffCard(array $s): string {
    $initials = '';
    if (!empty($s['name'])) {
        $parts = preg_split('/\s+/', trim($s['name']));
        $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
    }
    $joined = !empty($s['joined_date']) ? date('n/j/Y', strtotime($s['joined_date'])) : date('n/j/Y');
    $role_raw = $s['role'] ?? 'Staff';
    $status_raw = $s['status'] ?? 'INACTIVE';
    $role_class = strtolower(preg_replace('/[^a-z0-9_-]+/i', '', $role_raw));
    $status_class = strtolower(preg_replace('/[^a-z0-9_-]+/i', '', $status_raw));
    $staffId = intval($s['id'] ?? 0);
    $staffName = $s['name'] ?? 'Unnamed';
    $staffEmail = $s['email'] ?? '';
    $roleDisplay = strtoupper($role_raw);
    $statusDisplay = strtoupper($status_raw);
    $isPrimaryAdmin = !empty($s['is_primary_admin']);

    ob_start();
    ?>
    <div class="staff-card compact" data-staff-id="<?php echo $staffId; ?>" data-primary-admin="<?php echo $isPrimaryAdmin ? '1' : '0'; ?>">
      <div class="staff-card-left">
        <div class="avatar"><?php echo htmlspecialchars($initials ?: 'US'); ?></div>
      </div>
      <div class="staff-card-body">
        <div class="staff-top-row">
          <div class="staff-name"><?php echo htmlspecialchars($staffName); ?></div>
          <?php if ($isPrimaryAdmin): ?><span class="primary-admin-pill">Primary Admin</span><?php endif; ?>
          <div class="staff-email"><?php echo htmlspecialchars($staffEmail); ?></div>
        </div>
        <div class="staff-info-grid">
          <div class="info-label">Role:</div>
          <div class="info-value"><span class="badge role <?php echo htmlspecialchars($role_class); ?>"><?php echo htmlspecialchars($roleDisplay); ?></span></div>

          <div class="info-label">Status:</div>
          <div class="info-value"><span class="badge status <?php echo htmlspecialchars($status_class); ?>"><?php echo htmlspecialchars($statusDisplay); ?></span></div>

          <div class="info-label">Sales Today:</div>
          <div class="info-value"><?php echo intval($s['sales_today'] ?? 0); ?></div>

          <div class="info-label">Products Added:</div>
          <div class="info-value"><?php echo intval($s['products_added'] ?? 0); ?></div>

          <div class="info-label">Joined:</div>
          <div class="info-value"><?php echo $joined; ?></div>
        </div>
      </div>
      <div class="staff-card-actions">
        <button class="icon-btn staff-action-trigger" type="button" aria-haspopup="true" aria-expanded="false" title="More actions">
          <i class="fas fa-ellipsis-v"></i>
        </button>
        <div class="staff-action-menu" role="menu">
          <button type="button" class="staff-action-btn" data-action="edit" data-staff-id="<?php echo $staffId; ?>" data-staff-name="<?php echo htmlspecialchars($staffName, ENT_QUOTES, 'UTF-8'); ?>" data-staff-email="<?php echo htmlspecialchars($staffEmail, ENT_QUOTES, 'UTF-8'); ?>" data-staff-role="<?php echo htmlspecialchars($roleDisplay, ENT_QUOTES, 'UTF-8'); ?>" data-staff-status="<?php echo htmlspecialchars($statusDisplay, ENT_QUOTES, 'UTF-8'); ?>" data-primary-admin="<?php echo $isPrimaryAdmin ? '1' : '0'; ?>">Edit Info</button>
          <button type="button" class="staff-action-btn" data-action="role" data-staff-id="<?php echo $staffId; ?>" data-staff-name="<?php echo htmlspecialchars($staffName, ENT_QUOTES, 'UTF-8'); ?>" data-staff-email="<?php echo htmlspecialchars($staffEmail, ENT_QUOTES, 'UTF-8'); ?>" data-staff-role="<?php echo htmlspecialchars($roleDisplay, ENT_QUOTES, 'UTF-8'); ?>" data-staff-status="<?php echo htmlspecialchars($statusDisplay, ENT_QUOTES, 'UTF-8'); ?>" data-primary-admin="<?php echo $isPrimaryAdmin ? '1' : '0'; ?>">Change Role</button>
          <?php if (!$isPrimaryAdmin): ?>
          <button type="button" class="staff-action-btn" data-action="status" data-staff-id="<?php echo $staffId; ?>" data-staff-name="<?php echo htmlspecialchars($staffName, ENT_QUOTES, 'UTF-8'); ?>" data-staff-email="<?php echo htmlspecialchars($staffEmail, ENT_QUOTES, 'UTF-8'); ?>" data-staff-role="<?php echo htmlspecialchars($roleDisplay, ENT_QUOTES, 'UTF-8'); ?>" data-staff-status="<?php echo htmlspecialchars($statusDisplay, ENT_QUOTES, 'UTF-8'); ?>" data-primary-admin="0">Update Status</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
  // Normalize row keys from legacy staff tables so UI can rely on consistent fields
  function normalizeStaffRow(array $row): array {
    $resolvedName = $row['name']
      ?? $row['full_name']
      ?? $row['fullname']
      ?? $row['staff_name']
      ?? '';
    $resolvedEmail = $row['email']
      ?? $row['email_address']
      ?? $row['contact_email']
      ?? '';
    $resolvedRole = $row['role']
      ?? $row['position']
      ?? $row['title']
      ?? 'Staff';
    $resolvedStatus = $row['status']
      ?? $row['state']
      ?? $row['account_status']
      ?? 'INACTIVE';
    $resolvedJoined = $row['joined_date']
      ?? $row['joining_date']
      ?? $row['created_at']
      ?? $row['created_on']
      ?? $row['date_created']
      ?? date('Y-m-d');
    $resolvedSales = $row['sales_today']
      ?? $row['today_sales']
      ?? $row['sales']
      ?? 0;
    $resolvedProducts = $row['products_added']
      ?? $row['items_added']
      ?? $row['products_added_today']
      ?? 0;
    return [
      'id' => $row['id'] ?? $row['staff_id'] ?? $row['user_id'] ?? 0,
      'name' => $resolvedName !== '' ? $resolvedName : 'Unnamed',
      'email' => $resolvedEmail,
      'role' => $resolvedRole,
      'status' => $resolvedStatus,
      'joined_date' => $resolvedJoined,
      'sales_today' => intval($resolvedSales),
      'products_added' => intval($resolvedProducts),
      'is_primary_admin' => !empty($row['is_primary_admin']),
    ];
  }

  function buildPrimaryAdminRow(mysqli $conn, int $userId): ?array {
    $stmt = $conn->prepare('SELECT id, first_name, last_name, email, created_at FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) {
      return null;
    }
    $stmt->bind_param('i', $userId);
    if (!$stmt->execute()) {
      $stmt->close();
      return null;
    }
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    if (!$user) {
      return null;
    }
    $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    if ($fullName === '') {
      $fullName = 'Primary Admin';
    }
    return [
      'id' => $user['id'] ?? $userId,
      'name' => $fullName,
      'email' => $user['email'] ?? 'admin@example.com',
      'role' => 'ADMIN',
      'status' => 'ACTIVE',
      'sales_today' => 0,
      'products_added' => 0,
      'joined_date' => $user['created_at'] ?? date('Y-m-d'),
      'is_primary_admin' => true,
    ];
  }
// Attempt to query the `staff` table. If the table does not exist or query fails,
// catch the exception and set a flag so the UI can show a helpful message instead
// of a fatal error.
if (isset($conn)) {
    $sql = "SELECT * FROM staff";
    try {
        $result = $conn->query($sql);
    } catch (mysqli_sql_exception $e) {
        // Table probably doesn't exist or there was a permission error.
        $tableMissing = true;
        $result = false;
    }

    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
      $normalized = normalizeStaffRow($row);
      $staff_rows[] = $normalized;
        $totalStaff++;
      if (!empty($normalized['status']) && strtolower($normalized['status']) === 'active') $activeStaff++;
      if (!empty($normalized['role']) && strtolower($normalized['role']) === 'admin') $admins++;
      $salesToday += intval($normalized['sales_today']);
      }
    }
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        $primaryAdminRow = buildPrimaryAdminRow($conn, (int)$userId);
        if ($primaryAdminRow) {
            $adminEmail = strtolower($primaryAdminRow['email']);
            $alreadyIncluded = false;
            foreach ($staff_rows as &$row) {
                if (!empty($row['email']) && strtolower($row['email']) === $adminEmail) {
                    $alreadyIncluded = true;
                    $row['is_primary_admin'] = true;
                    break;
                }
            }
            unset($row);
            if (!$alreadyIncluded) {
                array_unshift($staff_rows, $primaryAdminRow);
                $totalStaff++;
                if (strtolower($primaryAdminRow['status']) === 'active') {
                    $activeStaff++;
                }
                if (strtolower($primaryAdminRow['role']) === 'admin') {
                    $admins++;
                }
            }
        }
    }
}
ob_start();
?>
<main class="dash-wrap fullwidth" data-page="staff_directory" data-page-title="Staff Management • PharmaSync">
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
          <?php if (!empty($staff_rows)): ?>
          <?php endif; ?>
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
              <div class="panel" style="padding:18px; border-radius:8px; background:#fbfbfb; margin-bottom:12px;">
                <p style="margin:0;">No staff accounts yet. Click <strong>Add Staff</strong> to create a new staff record.</p>
              </div>
            <?php endif; ?>

            <?php foreach ($staff_rows as $s): echo renderStaffCard($s); endforeach; ?>
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

      <!-- Manage Staff Modal -->
      <div id="manageStaffModal" class="modal" aria-hidden="true">
        <div class="modal-dialog">
          <header>
            <h3 id="manageStaffTitle">Manage Staff Member</h3>
            <button class="modal-close" aria-label="Close">&times;</button>
          </header>
          <div class="modal-body">
            <form id="manageStaffForm">
              <input type="hidden" name="staff_id" value="">
              <input type="hidden" name="is_primary_admin" value="0">
              <label>Full Name<input type="text" name="fullname" required></label>
              <label>Email<input type="email" name="email" required></label>
              <label>Role
                <select name="role" id="manageRole">
                  <option value="ADMIN">ADMIN</option>
                  <option value="MANAGER">MANAGER</option>
                  <option value="PHARMACIST">PHARMACIST</option>
                  <option value="SALES">SALES</option>
                  <option value="INVENTORY">INVENTORY</option>
                  <option value="CASHIER">CASHIER</option>
                  <option value="STAFF">STAFF</option>
                </select>
              </label>
              <label>Status
                <select name="status" id="manageStatus">
                  <option value="ACTIVE">ACTIVE</option>
                  <option value="INACTIVE">INACTIVE</option>
                  <option value="SUSPENDED">SUSPENDED</option>
                </select>
              </label>
              <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" class="add-staff-btn">Save Changes</button>
                <button type="button" class="modal-close btn-secondary">Cancel</button>
              </div>
            </form>
          </div>
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
  <title>Staff Management • PharmaSync</title>
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

  <?php if (!function_exists('should_show_footer') || should_show_footer()): ?>
  <footer class="site-footer">
    <div class="footer">
      <p>&copy; 2025 PharmaSync Ltd. All rights reserved.</p>
    </div>
  </footer>
  <?php endif; ?>

</body>
</html>
