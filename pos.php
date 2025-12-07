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
        <div class="dash-title">Point of Sale (POS)</div>
      </div>

      <div class="pos-intro">Choose an option to get started</div>

      <section class="pos-cards">
        <article class="pos-card">
          <div class="pos-icon">
            <i class="fas fa-file-invoice"></i>
          </div>
          <h3>Create New Invoice</h3>
          <p>Add items to cart and generate a new sales invoice.</p>
          <a href="pos_create.php" class="pos-btn">Get Started</a>
        </article>

        <article class="pos-card">
          <div class="pos-icon">
            <i class="fas fa-pills"></i>
          </div>
          <h3>Upload Prescription</h3>
          <p>Upload prescription image and generate an invoice for approval.</p>
          <a href="pos_upload.php" class="pos-btn">Get Started</a>
        </article>

        <article class="pos-card">
          <div class="pos-icon">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <h3>Pay Pending Invoice</h3>
          <p>Select and pay for an existing pending invoice.</p>
          <a href="pos_pending.php" class="pos-btn">View Invoices</a>
        </article>
      </section>

    </main>
  </div>
  <footer>
    <div class="footer">
      <p>&copy; 2025 PharmaSync Ltd. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
