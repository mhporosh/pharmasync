<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'staff';
$activePage = 'staff_directory';
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
            <div class="stat-value" id="totalStaff">1</div>
            <div class="stat-icon"><i class="fas fa-users"></i></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Active Staff</div>
            <div class="stat-value" id="activeStaff">1</div>
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Admins</div>
            <div class="stat-value" id="admins">1</div>
            <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Sales Today</div>
            <div class="stat-value" id="salesToday">0</div>
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
          </div>
        </div>

        <div class="panel" style="margin-top:18px;">
          <h3 style="margin:0 0 12px 0;">Accounts</h3>
          <div class="staff-list-grid">
            <div class="staff-card compact">
              <div class="staff-card-left">
                <div class="avatar">MP</div>
              </div>
              <div class="staff-card-body">
                <div class="staff-top-row">
                  <div class="staff-name">Mehedi Hasan Porosh</div>
                  <div class="staff-email">porosh.diu@gmail.com</div>
                </div>
                <div class="staff-info-grid">
                  <div class="info-label">Role:</div>
                  <div class="info-value"><span class="badge role admin">ADMIN</span></div>

                  <div class="info-label">Status:</div>
                  <div class="info-value"><span class="badge status active">ACTIVE</span></div>

                  <div class="info-label">Sales Today:</div>
                  <div class="info-value">0</div>

                  <div class="info-label">Products Added:</div>
                  <div class="info-value">0</div>

                  <div class="info-label">Joined:</div>
                  <div class="info-value"><?php echo date('n/j/Y'); ?></div>
                </div>
              </div>
              <div class="staff-card-actions">
                <button class="icon-btn" title="More"><i class="fas fa-ellipsis-v"></i></button>
              </div>
            </div>

            <!-- more staff-cards can be rendered here by server-side loop -->
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
