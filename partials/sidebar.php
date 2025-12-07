<?php
$menuKey = $activeMenu ?? '';
$pageKey = $activePage ?? '';
$salesOpen = $menuKey === 'sales';
$inventoryOpen = $menuKey === 'inventory';
$purchasesOpen = $menuKey === 'purchases';
$customersOpen = $menuKey === 'customers';
$staffOpen = $menuKey === 'staff';
?>
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
    <a class="menu-item<?= $menuKey === 'dashboard' ? ' active' : '' ?>" href="dashboard.php"><span><i class="fas fa-home"></i></span><span>Dashboard</span></a>

    <button class="menu-item has-sub<?= $salesOpen ? ' active' : '' ?>" data-menu="sales" data-target="sales-sub" aria-expanded="<?= $salesOpen ? 'true' : 'false' ?>"><span><i class="fas fa-dollar-sign"></i></span><span>Sales</span><i class="fas fa-chevron-down chevron"></i></button>
    <div id="sales-sub" class="submenu<?= $salesOpen ? ' show' : '' ?>">
      <a href="pos.php" class="submenu-item<?= $pageKey === 'pos' ? ' active' : '' ?>"><span><i class="fas fa-cash-register"></i></span><span>Point of Sale</span></a>
      <a href="sales_overview.php" class="submenu-item<?= $pageKey === 'sales_overview' ? ' active' : '' ?>"><span><i class="fas fa-chart-bar"></i></span><span>Sales Overview</span></a>
      <a href="sales_history.php" class="submenu-item<?= $pageKey === 'sales_history' ? ' active' : '' ?>"><span><i class="fas fa-history"></i></span><span>Sales History</span></a>
    </div>

    <button class="menu-item has-sub<?= $inventoryOpen ? ' active' : '' ?>" data-menu="inventory" data-target="inventory-sub" aria-expanded="<?= $inventoryOpen ? 'true' : 'false' ?>"><span><i class="fas fa-boxes"></i></span><span>Inventory</span><i class="fas fa-chevron-down chevron"></i></button>
    <div id="inventory-sub" class="submenu<?= $inventoryOpen ? ' show' : '' ?>">
      <a href="all_products.php" class="submenu-item<?= $pageKey === 'all_products' ? ' active' : '' ?>"><span><i class="fas fa-pills"></i></span><span>All Products</span></a>
      <a href="expiry_management.php" class="submenu-item<?= $pageKey === 'expiry_management' ? ' active' : '' ?>"><span><i class="fas fa-hourglass-end"></i></span><span>Expiry Management</span></a>
      <a href="low_stock_alerts.php" class="submenu-item<?= $pageKey === 'low_stock_alerts' ? ' active' : '' ?>"><span><i class="fas fa-exclamation-triangle"></i></span><span>Low Stock Alerts</span></a>
    </div>

    <button class="menu-item has-sub<?= $purchasesOpen ? ' active' : '' ?>" data-menu="purchases" data-target="purchases-sub" aria-expanded="<?= $purchasesOpen ? 'true' : 'false' ?>"><span><i class="fas fa-shopping-cart"></i></span><span>Purchases</span><i class="fas fa-chevron-down chevron"></i></button>
    <div id="purchases-sub" class="submenu<?= $purchasesOpen ? ' show' : '' ?>">
      <a href="purchase_orders.php" class="submenu-item<?= $pageKey === 'purchase_orders' ? ' active' : '' ?>"><span><i class="fas fa-file-invoice-dollar"></i></span><span>Purchase Orders</span></a>
      <a href="suppliers.php" class="submenu-item<?= $pageKey === 'suppliers' ? ' active' : '' ?>"><span><i class="fas fa-industry"></i></span><span>Suppliers</span></a>
      <a href="#" class="submenu-item"><span><i class="fas fa-file-invoice-dollar"></i></span><span>Purchase Orders</span></a>
      <a href="#" class="submenu-item"><span><i class="fas fa-industry"></i></span><span>Suppliers</span></a>
    </div>

    <button class="menu-item has-sub<?= $customersOpen ? ' active' : '' ?>" data-menu="customers" data-target="customers-sub" aria-expanded="<?= $customersOpen ? 'true' : 'false' ?>"><span><i class="fas fa-user-friends"></i></span><span>Customers</span><i class="fas fa-chevron-down chevron"></i></button>
    <div id="customers-sub" class="submenu<?= $customersOpen ? ' show' : '' ?>">
      <a href="customers.php" class="submenu-item<?= $pageKey === 'customers' ? ' active' : '' ?>"><span><i class="fas fa-address-book"></i></span><span>Customers List</span></a>
    </div>

    <button class="menu-item has-sub<?= $staffOpen ? ' active' : '' ?>" data-menu="staff" data-target="staff-sub" aria-expanded="<?= $staffOpen ? 'true' : 'false' ?>"><span><i class="fas fa-users-cog"></i></span><span>Staff</span><i class="fas fa-chevron-down chevron"></i></button>
    <div id="staff-sub" class="submenu<?= $staffOpen ? ' show' : '' ?>">
      <a href="staff_directory.php" data-partial="true" class="submenu-item<?= $pageKey === 'staff_directory' ? ' active' : '' ?>"><span><i class="fas fa-id-badge"></i></span><span>Staff Directory</span></a>
      <a href="activity_logs.php" data-partial="true" class="submenu-item<?= $pageKey === 'activity_logs' ? ' active' : '' ?>"><span><i class="fas fa-wave-square"></i></span><span>Activity Logs</span></a>
    </div>

    <a class="menu-item<?= $menuKey === 'settings' ? ' active' : '' ?>" href="#"><span><i class="fas fa-cog"></i></span><span>Settings</span></a>
    <a class="menu-item<?= $menuKey === 'billing' ? ' active' : '' ?>" href="#"><span><i class="fas fa-credit-card"></i></span><span>Billing</span><span class="upgrade">Upgrade</span></a>
  </nav>
</aside>
