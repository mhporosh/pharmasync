<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$activeMenu = 'sales';
$activePage = 'sales_history';

// Ensure invoices table exists
$conn->query("CREATE TABLE IF NOT EXISTS invoices (id INT AUTO_INCREMENT PRIMARY KEY, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, total DECIMAL(12,2), items TEXT, status VARCHAR(32) DEFAULT 'pending') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  
  if ($action === 'delete') {
    $delete_id = intval($_POST['delete_id'] ?? 0);
    if ($delete_id > 0) {
      $stmt = $conn->prepare('DELETE FROM invoices WHERE id = ?');
      $stmt->bind_param('i', $delete_id);
      $stmt->execute();
      $stmt->close();
    }
    // Redirect to same page to prevent form resubmission
    header('Location: sales_history.php');
    exit;
  }
}

// Handle GET pay action
if (isset($_GET['pay'])) {
  $pay_id = intval($_GET['pay']);
  if ($pay_id > 0) {
    $stmt = $conn->prepare('UPDATE invoices SET status = ? WHERE id = ?');
    $status = 'paid';
    $stmt->bind_param('si', $status, $pay_id);
    $stmt->execute();
    $stmt->close();
  }
  // Redirect to same page to prevent duplicate execution
  header('Location: sales_history.php?page=' . intval($_GET['page'] ?? 1) . '&limit=' . intval($_GET['limit'] ?? 10));
  exit;
}

// Pagination
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Build query with filters
$where = '1=1';
$params = [];
$types = '';

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
  $where .= " AND (id LIKE ? OR customer_name LIKE ?)";
  $searchTerm = "%{$search}%";
  $params = array_merge($params, [$searchTerm, $searchTerm]);
  $types .= 'ss';
}

// Status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
if ($status_filter !== '' && $status_filter !== 'all') {
  $where .= " AND status = ?";
  $params[] = $status_filter;
  $types .= 's';
}

// Date range filter
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
if ($date_from !== '') {
  $where .= " AND DATE(created_at) >= ?";
  $params[] = $date_from;
  $types .= 's';
}
if ($date_to !== '') {
  $where .= " AND DATE(created_at) <= ?";
  $params[] = $date_to;
  $types .= 's';
}

// Get total count
$countSql = "SELECT COUNT(*) as cnt FROM invoices WHERE {$where}";
$countStmt = $conn->prepare($countSql);
if ($params && $types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$total = intval($countRow['cnt']);
$countStmt->close();

$totalPages = ceil($total / $limit);
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
$offset = ($page - 1) * $limit;

// Get sales records
$sql = "SELECT id, created_at, total, items, status FROM invoices WHERE {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$stmt = $conn->prepare($sql);
if ($stmt) {
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $result = $stmt->get_result();
  $stmt->close();
} else {
  $result = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales History • PharmaSync</title>
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
    <main class="dash-wrap sales-history-page">
      <div class="dash-header">
        <div>
          <div class="dash-title">Sales History</div>
          <p class="dash-subtitle">View your pharmacy sales records and manage transactions</p>
        </div>
      </div>

      <div class="panel sales-history-panel">
        <div class="sales-toolbar">
          <form method="get" class="sales-search-form">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search sales..." class="sales-input">
            <button class="btn btn-primary" type="submit">
              <i class="fas fa-search"></i>
              Search
            </button>
          </form>

          <div class="sales-toolbar-actions">
            <button class="btn ghost-btn" id="filterBtn" type="button">
              <i class="fas fa-filter"></i>
              Filters
            </button>
            <select id="limitSelect" class="page-size" aria-label="Items per page">
              <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 per page</option>
              <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20 per page</option>
              <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 per page</option>
              <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 per page</option>
            </select>
            <button class="btn ghost-btn" type="button" title="More options">
              <i class="fas fa-ellipsis-v"></i>
            </button>
          </div>
        </div>

        <div id="filterPanel" class="sales-filter-panel" aria-hidden="true">
          <form method="get" class="filter-form">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            
            <div class="filter-field">
              <label>Status</label>
              <select name="status">
                <option value="all">All Status</option>
                <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="paid" <?= $status_filter == 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
            </div>

            <div class="filter-field">
              <label>From Date</label>
              <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
            </div>

            <div class="filter-field">
              <label>To Date</label>
              <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
            </div>

            <button class="btn btn-primary" type="submit">Apply Filters</button>
            <a href="sales_history.php" class="btn ghost-btn">Clear</a>
          </form>
        </div>

        <div class="sales-summary">
          Showing <?= $total == 0 ? 0 : ($offset + 1) ?> to <?= min($offset + $limit, $total) ?> of <?= $total ?> sales
        </div>

        <div class="sales-table-wrapper">
          <?php if ($result && $result->num_rows > 0): ?>
            <table class="sales-history-table">
              <thead>
                <tr>
                  <th class="checkbox-col"><input type="checkbox" aria-label="Select all"></th>
                  <th>Sale #</th>
                  <th>
                    <a href="?page=1&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" class="sortable">
                      Date <i class="fas fa-sort-down"></i>
                    </a>
                  </th>
                  <th>Customer</th>
                  <th>Seller</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Payment</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $result->fetch_assoc()): 
                  $items = json_decode($row['items'], true) ?: [];
                  $itemsText = '';
                  if ($items) {
                    foreach ($items as $item) {
                      $itemsText .= htmlspecialchars($item['name'] ?? 'Unknown') . ' × ' . intval($item['qty'] ?? 1) . '<br>';
                    }
                  }
                  $statusSlug = strtolower($row['status']);
                ?>
                <tr>
                  <td class="checkbox-col"><input type="checkbox" aria-label="Select sale #<?= intval($row['id']) ?>"></td>
                  <td class="sale-id">#<?= intval($row['id']) ?></td>
                  <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                  <td class="muted">Customer</td>
                  <td class="muted">Admin</td>
                  <td class="sale-amount">BDT <?= number_format(floatval($row['total']), 2) ?></td>
                  <td>
                    <span class="status-pill status-pill--<?= htmlspecialchars($statusSlug); ?>">
                      <?= ucfirst(htmlspecialchars($row['status'])) ?>
                    </span>
                  </td>
                  <td>
                    <div class="payment-status <?= $row['status'] === 'paid' ? 'is-paid' : 'is-pending'; ?>">
                      <?php if ($row['status'] === 'paid'): ?>
                        <i class="fas fa-check-circle"></i>
                        <span>Paid</span>
                      <?php else: ?>
                        <i class="fas fa-clock"></i>
                        <span>Pending</span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td>
                    <div class="sales-actions">
                      <button class="sales-action-btn view-btn" data-id="<?= $row['id'] ?>" data-items="<?= htmlspecialchars(json_encode($items)) ?>" data-total="<?= $row['total'] ?>">
                        <i class="fas fa-eye"></i>
                        View
                      </button>
                      <?php if ($row['status'] !== 'paid'): ?>
                        <a href="?pay=<?= $row['id'] ?>&page=<?= $page ?>&limit=<?= $limit ?>" class="sales-action-btn pay-btn">
                          <i class="fas fa-check"></i>
                          Pay
                        </a>
                      <?php endif; ?>
                      <button class="sales-action-btn delete-btn" data-id="<?= $row['id'] ?>">
                        <i class="fas fa-trash"></i>
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="sales-empty">
              <i class="fas fa-calculator"></i>
              <h3>No Sales Found</h3>
              <p>There are no sales matching your criteria.</p>
            </div>
          <?php endif; ?>
        </div>

        <div class="sales-pagination">
          <div class="table-meta">Page <?= $page ?> of <?= max(1, $totalPages) ?></div>
          <div class="pagination">
            <a href="?page=1&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" class="page-btn<?= $page == 1 ? ' disabled' : '' ?>" aria-label="First page">
              <i class="fas fa-step-backward"></i>
            </a>
            <a href="?page=<?= max(1, $page - 1) ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" class="page-btn<?= $page == 1 ? ' disabled' : '' ?>" aria-label="Previous page">
              <i class="fas fa-chevron-left"></i>
            </a>
            <div class="page-indicator"><?= $page ?> of <?= max(1, $totalPages) ?></div>
            <a href="?page=<?= min($totalPages, $page + 1) ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" class="page-btn<?= $page == $totalPages ? ' disabled' : '' ?>" aria-label="Next page">
              <i class="fas fa-chevron-right"></i>
            </a>
            <a href="?page=<?= $totalPages ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" class="page-btn<?= $page == $totalPages ? ' disabled' : '' ?>" aria-label="Last page">
              <i class="fas fa-step-forward"></i>
            </a>
          </div>
        </div>
      </div>
    </main>
  </div>

  <div id="viewModal" class="sales-modal" aria-hidden="true">
    <div class="sales-modal-card" role="dialog" aria-modal="true">
      <header>
        <h3>Sale Details</h3>
        <button id="closeModal" class="modal-close" aria-label="Close">&times;</button>
      </header>
      <div id="modalContent"></div>
    </div>
  </div>

  <div id="deleteModal" class="sales-modal" aria-hidden="true">
    <div class="sales-modal-card" role="dialog" aria-modal="true">
      <header>
        <h3>Delete Sale</h3>
      </header>
      <p>Are you sure you want to delete this sale? This action cannot be undone.</p>
      <div class="modal-actions">
        <button id="cancelDelete" class="btn ghost-btn">Cancel</button>
        <form method="post" class="delete-form">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="delete_id" id="deleteId">
          <button type="submit" class="btn danger-btn">Delete</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    const filterBtn = document.getElementById('filterBtn');
    const filterPanel = document.getElementById('filterPanel');
    const viewModal = document.getElementById('viewModal');
    const deleteModal = document.getElementById('deleteModal');
    const closeModalBtn = document.getElementById('closeModal');
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    const limitSelect = document.getElementById('limitSelect');

    if (filterBtn && filterPanel) {
      filterBtn.addEventListener('click', function () {
        const open = filterPanel.classList.toggle('open');
        filterPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
      });
    }

    if (limitSelect) {
      limitSelect.addEventListener('change', function(){
        const url = new URL(window.location.href);
        url.searchParams.set('limit', this.value);
        window.location.href = url.toString();
      });
    }

    document.querySelectorAll('.view-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const items = JSON.parse(this.dataset.items);
        const total = this.dataset.total;
        
        let itemsHtml = '<ul style="margin:0; padding-left:20px;">';
        items.forEach(item => {
          itemsHtml += '<li>' + item.name + ' × ' + item.qty + '</li>';
        });
        itemsHtml += '</ul>';
        
        document.getElementById('modalContent').innerHTML = `
          <div class="modal-row">
            <label>Sale ID</label>
            <div>#${id}</div>
          </div>
          <div class="modal-row">
            <label>Items</label>
            ${itemsHtml}
          </div>
          <div class="modal-row">
            <label>Total</label>
            <div>BDT ${parseFloat(total).toFixed(2)}</div>
          </div>
        `;
        viewModal.classList.add('open');
        viewModal.setAttribute('aria-hidden','false');
      });
    });

    if (closeModalBtn) {
      closeModalBtn.addEventListener('click', function(){
        viewModal.classList.remove('open');
        viewModal.setAttribute('aria-hidden','true');
      });
    }

    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('deleteId').value = this.dataset.id;
        deleteModal.classList.add('open');
        deleteModal.setAttribute('aria-hidden','false');
      });
    });

    if (cancelDeleteBtn) {
      cancelDeleteBtn.addEventListener('click', function(){
        deleteModal.classList.remove('open');
        deleteModal.setAttribute('aria-hidden','true');
      });
    }

    window.addEventListener('click', function(e) {
      if (e.target === viewModal) {
        viewModal.classList.remove('open');
        viewModal.setAttribute('aria-hidden','true');
      }
      if (e.target === deleteModal) {
        deleteModal.classList.remove('open');
        deleteModal.setAttribute('aria-hidden','true');
      }
    });
  </script>
</body>
</html>
