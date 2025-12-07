<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'sales';
$activePage = 'pos';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Point of Sale • PharmaSync</title>
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
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
    <main class="dash-wrap">
      <div class="dash-header">
        <div class="dash-title">Point of Sale (POS)</div>
      </div>

      <div style="text-align:center; margin:18px 0 28px;">
        <div style="font-size:18px; color:#666;">Choose an option to get started</div>
      </div>

      <div class="pos-cards" style="display:grid; grid-template-columns:repeat(3,1fr); gap:22px; align-items:start;">
        <div class="pos-card" style="background:#fff; border:1px solid #eef2f3; border-radius:10px; padding:28px; text-align:center; box-shadow:0 6px 18px rgba(11,18,20,0.04);">
          <div style="width:80px;height:80px;margin:0 auto;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#e6f4ea;">
            <i class="fas fa-file-invoice" style="font-size:34px; color:#11823b"></i>
          </div>
          <h3 style="margin-top:18px; margin-bottom:8px;">Create New Invoice</h3>
          <p style="color:#666; margin-bottom:18px;">Add items to cart and generate a new sales invoice</p>
          <a href="pos_create.php" class="btn" style="background:#117a2b; color:#fff; padding:10px 18px; border-radius:6px; text-decoration:none;">Get Started</a>
        </div>

        <div class="pos-card" style="background:#fff; border:1px solid #eef2f3; border-radius:10px; padding:28px; text-align:center; box-shadow:0 6px 18px rgba(11,18,20,0.04);">
          <div style="width:80px;height:80px;margin:0 auto;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#faf0ff;">
            <i class="fas fa-pills" style="font-size:34px; color:#8a2be2"></i>
          </div>
          <h3 style="margin-top:18px; margin-bottom:8px;">Upload Prescription</h3>
          <p style="color:#666; margin-bottom:18px;">Upload prescription image and generate invoice for approval</p>
          <a href="pos_upload.php" class="btn" style="background:#117a2b; color:#fff; padding:10px 18px; border-radius:6px; text-decoration:none;">Get Started</a>
        </div>

        <div class="pos-card" style="background:#fff; border:1px solid #eef2f3; border-radius:10px; padding:28px; text-align:center; box-shadow:0 6px 18px rgba(11,18,20,0.04);">
          <div style="width:80px;height:80px;margin:0 auto;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#e9fbe9;">
            <i class="fas fa-dollar-sign" style="font-size:34px; color:#139d3a"></i>
          </div>
          <h3 style="margin-top:18px; margin-bottom:8px;">Pay Pending Invoice</h3>
          <p style="color:#666; margin-bottom:18px;">Select and pay for an existing pending invoice</p>
          <a href="pos_pending.php" class="btn" style="background:#117a2b; color:#fff; padding:10px 18px; border-radius:6px; text-decoration:none;">View Invoices</a>
        </div>
      </div>

    </main>
  </div>
  <footer>
    <div class="footer">
      <p>&copy; 2025 PharmaSync Ltd. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
