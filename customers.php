<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'customers';
$activePage = 'customers';

// Handle Add Customer POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_customer') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $status = trim($_POST['status'] ?? 'ACTIVE');

    $errors = [];
    if ($name === '') $errors[] = 'Customer name is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

    if (empty($errors)) {
        $ensure = "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(64),
            email VARCHAR(255),
            address TEXT,
            notes TEXT,
            status VARCHAR(32) DEFAULT 'ACTIVE',
            purchases_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($ensure);

        $stmt = $conn->prepare('INSERT INTO customers (name,phone,email,address,notes,status) VALUES (?,?,?,?,?,?)');
        if ($stmt) {
            $stmt->bind_param('ssssss', $name, $phone, $email, $address, $notes, $status);
            $ok = $stmt->execute();
            $stmt->close();
            if ($ok) {
                header('Location: customers.php?added=1');
                exit;
            } else {
                $errors[] = 'Failed to add customer.';
            }
        } else {
            $errors[] = 'Failed to prepare database statement.';
        }
    }
}

// Ensure table exists and fetch metrics + list
$ensure = "CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(64),
    email VARCHAR(255),
    address TEXT,
    notes TEXT,
    status VARCHAR(32) DEFAULT 'ACTIVE',
    purchases_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($ensure);

$totalCustomers = 0; $activeCustomers = 0; $inactiveCustomers = 0; $totalPurchases = 0;
$res = $conn->query('SELECT id,name,phone,email,address,status,purchases_count,created_at FROM customers ORDER BY created_at DESC');
$customers = [];
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $customers[] = $r;
        $totalCustomers++;
        $st = strtoupper($r['status'] ?? 'INACTIVE');
        if ($st === 'ACTIVE') $activeCustomers++; else $inactiveCustomers++;
        $totalPurchases += intval($r['purchases_count'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Customers • PharmaSync</title>
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
    <main class="dash-wrap fullwidth">
      <div class="dash-header">
        <div>
          <div class="dash-title">Customer Management</div>
          <div class="purchase-sub">Manage your customers and track purchase history</div>
        </div>
        <div class="po-actions">
          <button class="btn-add-supplier green" id="openAddCustomer"><i class="fas fa-plus"></i> Add Customer</button>
        </div>
      </div>

      <div class="metric-grid" style="margin-bottom:18px;">
        <div class="metric">
          <div class="metric-info"><h5>Total Customers <span class="note" title="Total customers">ⓘ</span></h5><div class="value"><?php echo $totalCustomers; ?></div></div>
          <div class="metric-icon mi-green"><i class="fas fa-user"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info"><h5>Active Customers <span class="note">ⓘ</span></h5><div class="value"><?php echo $activeCustomers; ?></div></div>
          <div class="metric-icon mi-green"><i class="fas fa-user-check"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info"><h5>Inactive Customers <span class="note">ⓘ</span></h5><div class="value"><?php echo $inactiveCustomers; ?></div></div>
          <div class="metric-icon mi-pink"><i class="fas fa-user-times"></i></div>
        </div>
        <div class="metric">
          <div class="metric-info"><h5>Total Purchases <span class="note">ⓘ</span></h5><div class="value"><?php echo $totalPurchases; ?></div></div>
          <div class="metric-icon mi-blue"><i class="fas fa-shopping-cart"></i></div>
        </div>
      </div>

      <div class="panel">
        <?php if (!empty($_GET['added'])): ?>
          <div class="note" style="color:#198754; font-weight:700; margin-bottom:12px;">Customer added successfully.</div>
        <?php endif; ?>

        <div class="suppliers-panel">
          <div class="suppliers-toolbar">
            <div class="suppliers-left">
              <input class="search-input" type="search" placeholder="Search customers..." aria-label="Search customers">
              <div class="table-meta">Showing <?php echo $totalCustomers; ?> of <?php echo $totalCustomers; ?> customers</div>
            </div>
            <div class="suppliers-actions">
              <div class="filters">
                <button class="action-btn" title="Filters"><i class="fas fa-filter"></i></button>
                <select class="page-size" aria-label="Page size">
                  <option>10 per page</option>
                  <option>25 per page</option>
                </select>
                <button class="action-btn" title="More"><i class="fas fa-ellipsis-v"></i></button>
              </div>
            </div>
          </div>

          <?php if (empty($customers)): ?>
            <div style="width:100%; text-align:center;">
              <div class="supplier-card" style="margin:auto;">
                <div class="supplier-icon"><i class="fas fa-user-circle"></i></div>
                <h3 style="color:#072A36; margin:6px 0 4px;">Customers</h3>
                <p class="muted">No customers available. Add your first customer to get started.</p>
              </div>
            </div>
          <?php else: ?>
            <table class="suppliers-table">
              <thead>
                <tr>
                  <th class="checkbox-col"><input type="checkbox" aria-label="select all"></th>
                  <th>Customer Name</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Address</th>
                  <th>Purchases</th>
                  <th>Created At</th>
                  <th class="actions-col">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($customers as $c): ?>
                  <tr>
                    <td class="checkbox-col"><input type="checkbox"></td>
                    <td><?php echo htmlspecialchars($c['name']); ?></td>
                    <td><?php echo htmlspecialchars($c['phone']); ?></td>
                    <td><?php echo htmlspecialchars($c['email']); ?></td>
                    <td><?php echo htmlspecialchars($c['address']); ?></td>
                    <td><?php echo intval($c['purchases_count']); ?></td>
                    <td><?php echo date('n/j/Y', strtotime($c['created_at'])); ?></td>
                    <td class="actions-col">
                      <button class="action-btn" title="View"><i class="fas fa-eye"></i></button>
                      <button class="action-btn" title="Edit"><i class="fas fa-edit"></i></button>
                      <button class="action-btn" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:12px;">
              <div class="table-meta">Page 1 of 1</div>
              <div class="pagination">
                <button class="page-btn">|&lt;</button>
                <button class="page-btn">&lt;</button>
                <div>1 of 1</div>
                <button class="page-btn">&gt;</button>
                <button class="page-btn">&gt;|</button>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </main>
  </div>

  <!-- Add Customer Modal -->
  <div class="modal" id="addCustomerModal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-dialog">
      <header>
        <strong>Add New Customer</strong>
        <button type="button" class="icon-btn" id="closeAddCustomer" aria-label="Close"><i class="fas fa-times"></i></button>
      </header>
      <div class="modal-body">
        <form method="post" id="addCustomerForm">
          <input type="hidden" name="action" value="add_customer">
          <div class="form-grid">
            <div class="form-group">
              <label>Customer Name <span class="required">*</span></label>
              <input type="text" name="name" placeholder="Enter customer name" required>
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="phone" placeholder="+254 741 376 766">
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <input type="email" name="email" placeholder="customer@example.com">
            </div>
            <div class="form-group">
              <label>Address</label>
              <input type="text" name="address" placeholder="Customer address">
            </div>
            <div class="form-group full-width">
              <label>Notes</label>
              <textarea name="notes" placeholder="Additional notes about the customer..."></textarea>
            </div>
            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <option>ACTIVE</option>
                <option>INACTIVE</option>
              </select>
            </div>
          </div>

          <div style="display:flex; gap:8px; margin-top:14px; justify-content:flex-end;">
            <button type="button" class="btn-secondary" id="cancelAddCustomer">Cancel</button>
            <button type="submit" class="btn-add-supplier green"> <i class="fas fa-save"></i> Add Customer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  (function(){
    var openBtn = document.getElementById('openAddCustomer');
    var modal = document.getElementById('addCustomerModal');
    var closeBtn = document.getElementById('closeAddCustomer');
    var cancelBtn = document.getElementById('cancelAddCustomer');
    function show(){ modal.setAttribute('aria-hidden','false'); modal.style.display='flex'; }
    function hide(){ modal.setAttribute('aria-hidden','true'); modal.style.display='none'; }
    if (openBtn) openBtn.addEventListener('click', function(e){ e.preventDefault(); show(); });
    if (closeBtn) closeBtn.addEventListener('click', function(e){ e.preventDefault(); hide(); });
    if (cancelBtn) cancelBtn.addEventListener('click', function(e){ e.preventDefault(); hide(); });
    modal.addEventListener('click', function(e){ if (e.target===modal) hide(); });
    <?php if (!empty($errors)): ?> show(); <?php endif; ?>
  })();
  </script>

</body>
</html>

<?php $conn->close(); ?>
