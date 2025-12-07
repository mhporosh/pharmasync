<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$navAdminName = $admin_name ?? ($_SESSION['user_name'] ?? 'Admin');
$navUserRole = $user_role ?? ($_SESSION['user_role'] ?? 'Admin');
?>
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
          <span class="admin-name"><?= htmlspecialchars($navAdminName) ?></span>
          <div class="admin-role"><?= htmlspecialchars($navUserRole) ?></div>
        </div>
      </button>
      <div id="profileMenu" class="dd-menu" aria-hidden="true">
        <a href="#" class="dd-item" id="editInfoBtn"><i class="fas fa-user-edit"></i> Edit Info</a>
        <a href="handlers/logout.php" class="dd-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
  </div>
</header>
