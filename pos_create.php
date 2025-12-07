<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
session_start();
$activeMenu = 'sales';
$activePage = 'pos';

// initialize cart
if (!isset($_SESSION['pos_cart'])) $_SESSION['pos_cart'] = [];

// handle add/remove/checkout actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'add') {
    $mid = intval($_POST['medicine_id'] ?? 0);
    $qty = max(1, intval($_POST['qty'] ?? 1));
    if ($mid) {
      // fetch medicine
      $stmt = $conn->prepare('SELECT id, medicine_name, price, stock FROM medicines WHERE id = ? LIMIT 1');
      $stmt->bind_param('i', $mid);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($row = $res->fetch_assoc()) {
        $found = false;
        foreach ($_SESSION['pos_cart'] as &$item) {
          if ($item['id'] == $row['id']) {
            $item['qty'] += $qty;
            $found = true;
            break;
          }
        }
        if (!$found) {
          $_SESSION['pos_cart'][] = ['id' => $row['id'], 'name' => $row['medicine_name'], 'price' => floatval($row['price']), 'qty' => $qty];
        }
      }
      $stmt->close();
    }
  } elseif ($action === 'remove') {
    $mid = intval($_POST['medicine_id'] ?? 0);
    foreach ($_SESSION['pos_cart'] as $k => $it) {
      if ($it['id'] == $mid) unset($_SESSION['pos_cart'][$k]);
    }
    $_SESSION['pos_cart'] = array_values($_SESSION['pos_cart']);
  } elseif ($action === 'checkout') {
    // create invoices table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS invoices (id INT AUTO_INCREMENT PRIMARY KEY, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, total DECIMAL(12,2), items TEXT, status VARCHAR(32) DEFAULT 'pending') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $items = $_SESSION['pos_cart'];
    $total = 0;
    foreach ($items as $it) $total += $it['price'] * $it['qty'];
    $items_json = $conn->real_escape_string(json_encode($items));
    $stmt = $conn->prepare('INSERT INTO invoices (total, items, status) VALUES (?, ?, ?)');
    $status = 'pending';
    $stmt->bind_param('dss', $total, $items_json, $status);
    $stmt->execute();
    $invoice_id = $stmt->insert_id;
    $stmt->close();
    // reduce stock (best-effort)
    foreach ($items as $it) {
      $conn->query('UPDATE medicines SET stock = GREATEST(0, stock - ' . intval($it['qty']) . ') WHERE id = ' . intval($it['id']));
    }
    $_SESSION['pos_cart'] = [];
    header('Location: pos_pending.php?created=' . $invoice_id);
    exit;
  }
  header('Location: pos_create.php');
  exit;
}

// search medicines
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
  $sql = "SELECT * FROM medicines WHERE medicine_name LIKE ? OR slug LIKE ? OR generic_name LIKE ? ORDER BY medicine_name ASC";
  $term = "%{$search}%";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sss', $term, $term, $term);
  $stmt->execute();
  $result = $stmt->get_result();
  $stmt->close();
} else {
  $result = $conn->query('SELECT * FROM medicines ORDER BY medicine_name ASC LIMIT 80');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Create Invoice • PharmaSync</title>
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
        <div class="dash-title">Create New Invoice</div>
      </div>
      <div class="panel" style="display:flex; gap:18px; align-items:flex-start;">
        <div style="flex:1;">
          <form method="get" action="" style="display:flex; gap:8px; margin-bottom:14px;">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search medicine..." style="flex:1; padding:10px; border-radius:8px; border:1px solid #d0d7de;">
            <button class="btn btn-primary">Search</button>
          </form>
          <div style="max-height:520px; overflow:auto; border:1px solid #eef2f3; padding:10px; border-radius:8px; background:#fff;">
            <table style="width:100%; border-collapse:collapse;">
              <tr style="border-bottom:1px solid #f1f4f6;">
                <th style="text-align:left; padding:8px">Product</th>
                <th style="text-align:left">Price</th>
                <th>Stock</th>
                <th></th>
              </tr>
              <?php if ($result && $result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                  <tr style="border-bottom:1px solid #f6f8f9;">
                    <td style="padding:8px"><?= htmlspecialchars($row['medicine_name']) ?></td>
                    <td style="width:120px;"><?= htmlspecialchars($row['price']) ?></td>
                    <td style="width:90px;"><?= intval($row['stock']) ?></td>
                    <td style="width:170px;">
                      <form method="post" style="display:flex; gap:6px; align-items:center; justify-content:flex-end;">
                        <input type="hidden" name="medicine_id" value="<?= $row['id'] ?>">
                        <input type="number" name="qty" value="1" min="1" max="<?= max(1, intval($row['stock'])) ?>" style="width:64px; padding:6px; border-radius:6px; border:1px solid #e5eef2;">
                        <input type="hidden" name="action" value="add">
                        <button class="btn" type="submit" style="background:#117a2b; color:#fff; padding:6px 10px; border-radius:6px;">Add</button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile;
              else: ?>
                <tr>
                  <td colspan="4" style="text-align:center; padding:18px; color:#777;">No products found.</td>
                </tr>
              <?php endif; ?>
            </table>
          </div>
        </div>

        <div style="width:360px;">
          <div style="background:#fff; border:1px solid #eef2f3; padding:14px; border-radius:8px;">
            <a href="pos.php" class="btn" style="display:inline-flex; align-items:center; gap:6px; padding:8px 12px; border-radius:6px; border:1px solid #d0d7de; background:#f9fbfb; margin-bottom:10px; text-decoration:none; color:#1b4253;">
              Back
            </a>
            <h4 style="margin-top:0;">Cart</h4>
            <div style="max-height:360px; overflow:auto;">
              <?php if (!empty($_SESSION['pos_cart'])): $subtotal = 0;
                foreach ($_SESSION['pos_cart'] as $it): $subtotal += $it['price'] * $it['qty']; ?>
                  <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #f1f4f6;">
                    <div>
                      <div style="font-weight:600"><?= htmlspecialchars($it['name']) ?></div>
                      <div style="color:#777; font-size:13px;"><?= $it['qty'] ?> × <?= number_format($it['price'], 2) ?></div>
                    </div>
                    <div style="text-align:right;">
                      <div style="font-weight:600">BDT <?= number_format($it['price'] * $it['qty'], 2) ?></div>
                      <form method="post" style="margin-top:6px;">
                        <input type="hidden" name="medicine_id" value="<?= $it['id'] ?>">
                        <input type="hidden" name="action" value="remove">
                        <button class="btn" style="background:#fff; border:1px solid #e5eef2; color:#c62828; padding:6px 8px; border-radius:6px;">Remove</button>
                      </form>
                    </div>
                  </div>
                <?php endforeach;
              else: ?>
                <div style="text-align:center; color:#777; padding:28px 0;">Cart is empty</div>
              <?php endif; ?>
            </div>

            <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
              <div style="font-weight:700">Total</div>
              <div style="font-weight:700">BDT <?= number_format($subtotal ?? 0, 2) ?></div>
            </div>

            <form method="post" style="margin-top:12px; display:flex; gap:8px;">
              <input type="hidden" name="action" value="checkout">
              <button class="btn" style="flex:1; background:#117a2b; color:#fff; padding:10px; border-radius:6px;" <?= empty($_SESSION['pos_cart']) ? 'disabled' : '' ?>>Generate Invoice</button>
              <a href="pos.php" class="btn" style="padding:10px; border-radius:6px; border:1px solid #e5eef2; text-decoration:none; display:inline-flex; align-items:center;">Cancel</a>
            </form>

          </div>
        </div>
      </div>

    </main>
  </div>
</body>

</html>