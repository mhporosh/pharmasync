<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'purchases';
$activePage = 'suppliers';

// Process add supplier POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_supplier') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = trim($_POST['status'] ?? 'ACTIVE');

    // Basic validation
    $errors = [];
    if ($name === '') $errors[] = 'Name is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email is invalid.';

    if (empty($errors)) {
        // Ensure suppliers table exists
        $createSql = "CREATE TABLE IF NOT EXISTS suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            phone VARCHAR(60),
            address TEXT,
            status VARCHAR(32) DEFAULT 'ACTIVE',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($createSql);

        $stmt = $conn->prepare('INSERT INTO suppliers (name,email,phone,address,status) VALUES (?,?,?,?,?)');
        if ($stmt) {
            $stmt->bind_param('sssss', $name, $email, $phone, $address, $status);
            $ok = $stmt->execute();
            $stmt->close();
            if ($ok) {
                header('Location: suppliers.php?added=1');
                exit;
            } else {
                $errors[] = 'Failed to add supplier.';
            }
        } else {
            $errors[] = 'Failed to prepare database statement.';
        }
    }
}

// Fetch suppliers — ensure table exists first
$suppliers = [];
$ensureSql = "CREATE TABLE IF NOT EXISTS suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  phone VARCHAR(60),
  address TEXT,
  status VARCHAR(32) DEFAULT 'ACTIVE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($ensureSql);

$res = $conn->query('SELECT id,name,email,phone,address,status,created_at FROM suppliers ORDER BY created_at DESC');
if ($res && $res->num_rows > 0) {
  while ($r = $res->fetch_assoc()) $suppliers[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Suppliers • PharmaSync</title>
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
          <div class="dash-title">Suppliers</div>
        </div>
          <div class="suppliers-actions">
              <button class="btn-add-supplier green" id="openAddSupplier"><i class="fas fa-plus"></i> Add Supplier</button>
            </div>
      </div>

          <div class="panel">
            <?php if (!empty($_GET['added'])): ?>
              <div class="note" style="color: #198754; font-weight:700; margin-bottom:12px;">Supplier added successfully.</div>
            <?php endif; ?>

            <div class="suppliers-panel">
              <div class="suppliers-toolbar">
                <div class="suppliers-left">
                  <input class="search-input" type="search" placeholder="Search supplier" aria-label="Search supplier">
                  <div class="table-meta">Showing <?php echo count($suppliers) > 0 ? '1 of '.count($suppliers).' products' : '0 of 0 products'; ?></div>
                </div>
                <div class="suppliers-actions">
                  <div class="filters">
                    <button class="action-btn" title="Filters"><i class="fas fa-filter"></i></button>
                    <select class="page-size" aria-label="Page size">
                      <option>10 per page</option>
                      <option>25 per page</option>
                      <option>50 per page</option>
                    </select>
                    <button class="action-btn" title="More"><i class="fas fa-ellipsis-v"></i></button>
                  </div>
                </div>
              </div>

              <?php if (empty($suppliers)): ?>
                <div style="width:100%;">
                  <div class="supplier-card" style="margin:auto;">
                    <div class="supplier-icon"><i class="fas fa-user-circle"></i></div>
                    <h3 style="color:#072A36; margin:6px 0 4px;">Suppliers</h3>
                    <p class="muted">No suppliers available</p>
                  </div>
                </div>
              <?php else: ?>
                <table class="suppliers-table">
                  <thead>
                    <tr>
                      <th class="checkbox-col"><input type="checkbox" aria-label="select all"></th>
                      <th>Supplier Name</th>
                      <th>Phone</th>
                      <th>Email</th>
                      <th>Orders</th>
                      <th>Status</th>
                      <th class="actions-col">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($suppliers as $s): ?>
                      <tr>
                        <td class="checkbox-col"><input type="checkbox"></td>
                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                        <td><?php echo htmlspecialchars($s['phone']); ?></td>
                        <td><?php echo htmlspecialchars($s['email']); ?></td>
                        <td>0</td>
                        <td><span class="badge status <?php echo (strtoupper($s['status'])==='ACTIVE') ? 'active' : 'inactive'; ?>"><?php echo htmlspecialchars(strtoupper($s['status'])); ?></span></td>
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

  <!-- Modal (reuses .modal styles in dashboard.css) -->
  <div class="modal" id="addSupplierModal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-dialog">
      <header>
        <strong>Add New Supplier</strong>
        <button type="button" class="icon-btn" id="closeAddSupplier" aria-label="Close"><i class="fas fa-times"></i></button>
      </header>
      <div class="modal-body">
        <form method="post" id="addSupplierForm">
          <input type="hidden" name="action" value="add_supplier">
          <label>Search Verified Suppliers</label>
          <input type="text" name="search_verified" placeholder="Search by name, license number, or location..." />
          <p class="note">Can't find your supplier? Add manually</p>

          <label>Name</label>
          <input type="text" name="name" required>

          <label>Email Address</label>
          <input type="email" name="email">

          <label>Phone</label>
          <input type="text" name="phone">

          <label>Address</label>
          <input type="text" name="address">

          <label>Status</label>
          <select name="status" style="border:1px solid var(--accent); padding:8px; border-radius:6px;">
            <option>ACTIVE</option>
            <option>INACTIVE</option>
          </select>

          <div style="display:flex; gap:8px; margin-top:14px; justify-content:flex-end;">
            <button type="button" class="btn-secondary" id="cancelAddSupplier">Cancel</button>
            <button type="submit" class="btn-add-supplier"> <i class="fas fa-plus"></i> Add Supplier</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Modal open/close logic
    (function(){
      var openBtn = document.getElementById('openAddSupplier');
      var modal = document.getElementById('addSupplierModal');
      var closeBtn = document.getElementById('closeAddSupplier');
      var cancelBtn = document.getElementById('cancelAddSupplier');
      function show() { modal.setAttribute('aria-hidden','false'); modal.style.display = 'flex'; }
      function hide() { modal.setAttribute('aria-hidden','true'); modal.style.display = 'none'; }
      if (openBtn) openBtn.addEventListener('click', function(e){ e.preventDefault(); show(); });
      if (closeBtn) closeBtn.addEventListener('click', function(e){ e.preventDefault(); hide(); });
      if (cancelBtn) cancelBtn.addEventListener('click', function(e){ e.preventDefault(); hide(); });
      // close when clicking overlay
      modal.addEventListener('click', function(e){ if (e.target === modal) hide(); });

      // show modal if server returned validation errors
      <?php if (!empty($errors)): ?>
        show();
      <?php endif; ?>
    })();
  </script>

</body>
</html>

<?php $conn->close(); ?>
