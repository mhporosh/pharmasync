<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
session_start();
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
    <main class="dash-wrap">
      <div class="dash-header">
        <div>
          <div class="dash-title">Sales History</div>
          <div style="color:#666; font-size:14px; margin-top:4px;">View your pharmacy sales records and manage transactions</div>
        </div>
      </div>

      <div class="panel">
        <!-- Search and Filters -->
        <div style="display:flex; gap:12px; margin-bottom:18px; flex-wrap:wrap; align-items:flex-end;">
          <div style="flex:1; min-width:250px;">
            <form method="get" style="display:flex; gap:8px;">
              <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search sales..." style="flex:1; padding:10px 12px; border-radius:8px; border:1px solid #d0d7de; background:#fff;">
              <button class="btn btn-primary" type="submit" style="padding:10px 16px;">
                <i class="fas fa-search" style="margin-right:6px;"></i>Search
              </button>
            </form>
          </div>

          <div style="display:flex; gap:8px; align-items:center;">
            <button class="btn" id="filterBtn" style="padding:10px 16px; border:1px solid #d0d7de; background:#fff; cursor:pointer;">
              <i class="fas fa-filter" style="margin-right:6px;"></i>Filters
            </button>
            <div style="position:relative;">
              <select name="limit" id="limitSelect" onchange="this.form.submit()" style="padding:10px 12px; border-radius:8px; border:1px solid #d0d7de; background:#fff; cursor:pointer;">
                <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 per page</option>
                <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20 per page</option>
                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 per page</option>
                <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 per page</option>
              </select>
              <form method="get" id="limitForm" style="display:none;">
                <input type="hidden" name="limit" id="limitInput">
              </form>
            </div>
            <button class="btn" style="padding:10px 12px; border:1px solid #d0d7de; background:#fff; cursor:pointer;" title="More options">
              <i class="fas fa-ellipsis-v"></i>
            </button>
          </div>
        </div>

        <!-- Filter Panel (Hidden by default) -->
        <div id="filterPanel" style="display:none; background:#f9fafb; border:1px solid #e5eef2; padding:16px; border-radius:8px; margin-bottom:18px;">
          <form method="get" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            
            <div style="display:flex; flex-direction:column;">
              <label style="font-weight:600; font-size:13px; margin-bottom:6px;">Status</label>
              <select name="status" style="padding:8px 10px; border-radius:6px; border:1px solid #d0d7de; background:#fff;">
                <option value="all">All Status</option>
                <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="paid" <?= $status_filter == 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
            </div>

            <div style="display:flex; flex-direction:column;">
              <label style="font-weight:600; font-size:13px; margin-bottom:6px;">From Date</label>
              <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" style="padding:8px 10px; border-radius:6px; border:1px solid #d0d7de; background:#fff;">
            </div>

            <div style="display:flex; flex-direction:column;">
              <label style="font-weight:600; font-size:13px; margin-bottom:6px;">To Date</label>
              <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" style="padding:8px 10px; border-radius:6px; border:1px solid #d0d7de; background:#fff;">
            </div>

            <button class="btn btn-primary" type="submit" style="padding:8px 16px;">Apply Filters</button>
            <a href="sales_history.php" class="btn" style="padding:8px 16px; border:1px solid #d0d7de; background:#fff; text-decoration:none;">Clear</a>
          </form>
        </div>

        <!-- Results Summary -->
        <div style="margin-bottom:12px; color:#666; font-size:14px;">
          Showing <?= $total == 0 ? 0 : ($offset + 1) ?> to <?= min($offset + $limit, $total) ?> of <?= $total ?> sales
        </div>

        <!-- Sales Table -->
        <div style="background:#fff; border:1px solid #eef2f3; border-radius:8px; overflow:hidden;">
          <?php if ($result && $result->num_rows > 0): ?>
            <table style="width:100%; border-collapse:collapse;">
              <thead>
                <tr style="border-bottom:1px solid #f1f4f6; background:#f9fafb;">
                  <th style="padding:12px; text-align:left; font-weight:600; color:#072a36;"><input type="checkbox" style="cursor:pointer;"></th>
                  <th style="padding:12px; text-align:left; font-weight:600; color:#072a36;">Sale #</th>
                  <th style="padding:12px; text-align:left; font-weight:600; color:#072a36;">
                    <a href="?page=1&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" style="display:flex; align-items:center; gap:6px; text-decoration:none; color:#072a36;">
                      Date <i class="fas fa-sort-down" style="font-size:12px;"></i>
                    </a>
                  </th>
                  <th style="padding:12px; text-align:left; font-weight:600; color:#072a36;">Customer</th>
                  <th style="padding:12px; text-align:left; font-weight:600; color:#072a36;">Seller</th>
                  <th style="padding:12px; text-align:left; font-weight:600; color:#072a36;">Amount</th>
                  <th style="padding:12px; text-align:left; font-weight:600; color:#072a36;">Status</th>
                  <th style="padding:12px; text-align:left; font-weight:600; color:#072a36;">Payment</th>
                  <th style="padding:12px; text-align:left; font-weight:600; color:#072a36;">Actions</th>
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
                ?>
                <tr style="border-bottom:1px solid #f6f8f9;">
                  <td style="padding:12px; text-align:center;"><input type="checkbox" style="cursor:pointer;"></td>
                  <td style="padding:12px; font-weight:600; color:#0b6ef0;">#<?= intval($row['id']) ?></td>
                  <td style="padding:12px;"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                  <td style="padding:12px; color:#666;">Customer</td>
                  <td style="padding:12px; color:#666;">Admin</td>
                  <td style="padding:12px; font-weight:600;">BDT <?= number_format(floatval($row['total']), 2) ?></td>
                  <td style="padding:12px;">
                    <span style="display:inline-block; padding:6px 10px; border-radius:6px; font-size:12px; font-weight:600; 
                      <?php 
                        if ($row['status'] === 'paid') echo 'background:#d4edda; color:#155724;';
                        elseif ($row['status'] === 'pending') echo 'background:#fff3cd; color:#856404;';
                        else echo 'background:#f8d7da; color:#721c24;';
                      ?>
                    ">
                      <?= ucfirst(htmlspecialchars($row['status'])) ?>
                    </span>
                  </td>
                  <td style="padding:12px; color:#666;">
                    <?= $row['status'] === 'paid' ? '<i class="fas fa-check-circle" style="color:#28a745;"></i> Paid' : '<i class="fas fa-clock" style="color:#ffc107;"></i> Pending' ?>
                  </td>
                  <td style="padding:12px;">
                    <div style="display:flex; gap:6px;">
                      <button class="btn viewBtn" data-id="<?= $row['id'] ?>" data-items="<?= htmlspecialchars(json_encode($items)) ?>" data-total="<?= $row['total'] ?>" style="background:#0b6ef0; color:#fff; padding:6px 10px; border-radius:6px; border:none; cursor:pointer; font-size:12px;">
                        <i class="fas fa-eye"></i> View
                      </button>
                      <?php if ($row['status'] !== 'paid'): ?>
                        <a href="?pay=<?= $row['id'] ?>&page=<?= $page ?>&limit=<?= $limit ?>" class="btn" style="background:#0b6ef0; color:#fff; padding:6px 10px; border-radius:6px; text-decoration:none; font-size:12px;">
                          <i class="fas fa-check"></i> Pay
                        </a>
                      <?php endif; ?>
                      <button class="btn deleteBtn" data-id="<?= $row['id'] ?>" style="background:#0d5fd5; color:#fff; padding:6px 10px; border-radius:6px; border:none; cursor:pointer; font-size:12px;">
                        <i class="fas fa-trash"></i> Delete
                      </button>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div style="padding:60px 20px; text-align:center;">
              <i class="fas fa-calculator" style="font-size:48px; color:#ccc; margin-bottom:16px; display:block;"></i>
              <div style="font-size:18px; font-weight:600; margin-bottom:8px;">No Sales Found</div>
              <div style="color:#666;">There are no sales matching your criteria.</div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
          <div style="color:#666; font-size:14px;">
            Page <?= $page ?> of <?= max(1, $totalPages) ?>
          </div>
          <div style="display:flex; gap:8px;">
            <a href="?page=1&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" class="btn" style="padding:8px 12px; border-radius:6px; <?= $page == 1 ? 'background:#9ca3af; cursor:not-allowed;' : 'background:#0b6ef0; cursor:pointer;' ?> text-decoration:none; color:#fff;">
              <i class="fas fa-step-backward"></i>
            </a>
            <a href="?page=<?= max(1, $page - 1) ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" class="btn" style="padding:8px 12px; border-radius:6px; <?= $page == 1 ? 'background:#9ca3af; cursor:not-allowed;' : 'background:#0b6ef0; cursor:pointer;' ?> text-decoration:none; color:#fff;">
              <i class="fas fa-chevron-left"></i>
            </a>
            <div style="padding:8px 16px; border-radius:6px; background:#f0f0f0; color:#333; font-weight:600;">
              <?= $page ?> of <?= max(1, $totalPages) ?>
            </div>
            <a href="?page=<?= min($totalPages, $page + 1) ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" class="btn" style="padding:8px 12px; border-radius:6px; <?= $page == $totalPages ? 'background:#9ca3af; cursor:not-allowed;' : 'background:#0b6ef0; cursor:pointer;' ?> text-decoration:none; color:#fff;">
              <i class="fas fa-chevron-right"></i>
            </a>
            <a href="?page=<?= $totalPages ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" class="btn" style="padding:8px 12px; border-radius:6px; <?= $page == $totalPages ? 'background:#9ca3af; cursor:not-allowed;' : 'background:#0b6ef0; cursor:pointer;' ?> text-decoration:none; color:#fff;">
              <i class="fas fa-step-forward"></i>
            </a>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- View Modal -->
  <div id="viewModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:24px; max-width:500px; width:90%; max-height:80vh; overflow:auto;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h3 style="margin:0; font-size:18px;">Sale Details</h3>
        <button id="closeModal" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
      </div>
      <div id="modalContent"></div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:24px; max-width:400px; width:90%;">
      <h3 style="margin-top:0;">Delete Sale</h3>
      <p style="color:#666;">Are you sure you want to delete this sale? This action cannot be undone.</p>
      <div style="display:flex; gap:8px; justify-content:flex-end;">
        <button id="cancelDelete" class="btn" style="padding:10px 16px; border:1px solid #d0d7de; background:#fff; cursor:pointer;">Cancel</button>
        <form method="post" style="display:inline;">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="delete_id" id="deleteId">
          <button type="submit" class="btn" style="padding:10px 16px; background:#dc3545; color:#fff; cursor:pointer;">Delete</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Filter toggle
    document.getElementById('filterBtn').addEventListener('click', function() {
      const panel = document.getElementById('filterPanel');
      panel.style.display = panel.style.display === 'none' ? 'flex' : 'none';
    });

    // View details
    document.querySelectorAll('.viewBtn').forEach(btn => {
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
          <div style="margin-bottom:12px;">
            <label style="font-weight:600; color:#666;">Sale ID</label>
            <div>#${id}</div>
          </div>
          <div style="margin-bottom:12px;">
            <label style="font-weight:600; color:#666;">Items</label>
            ${itemsHtml}
          </div>
          <div style="margin-bottom:12px;">
            <label style="font-weight:600; color:#666;">Total</label>
            <div>BDT ${parseFloat(total).toFixed(2)}</div>
          </div>
        `;
        document.getElementById('viewModal').style.display = 'flex';
      });
    });

    document.getElementById('closeModal').addEventListener('click', function() {
      document.getElementById('viewModal').style.display = 'none';
    });

    // Delete confirmation
    document.querySelectorAll('.deleteBtn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('deleteId').value = this.dataset.id;
        document.getElementById('deleteModal').style.display = 'flex';
      });
    });

    document.getElementById('cancelDelete').addEventListener('click', function() {
      document.getElementById('deleteModal').style.display = 'none';
    });

    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
      const viewModal = document.getElementById('viewModal');
      const deleteModal = document.getElementById('deleteModal');
      if (e.target === viewModal) viewModal.style.display = 'none';
      if (e.target === deleteModal) deleteModal.style.display = 'none';
    });
  </script>
</body>
</html>
