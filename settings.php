<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/partials/auth.php';
$activeMenu = 'settings';
$activePage = 'settings';

$userProfile = [
  'first_name' => '',
  'last_name' => '',
  'email' => '',
  'created_at' => '',
];

if (isset($_SESSION['user_id']) && isset($conn) && $conn instanceof mysqli) {
  $uid = (int) $_SESSION['user_id'];
  $stmt = $conn->prepare('SELECT first_name, last_name, email, created_at FROM users WHERE id = ? LIMIT 1');
  if ($stmt) {
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $stmt->bind_result($fn, $ln, $email, $created);
    if ($stmt->fetch()) {
      $userProfile['first_name'] = $fn ?? '';
      $userProfile['last_name'] = $ln ?? '';
      $userProfile['email'] = $email ?? '';
      $userProfile['created_at'] = $created ?? '';
    }
    $stmt->close();
  }
}

$businessProfile = [
  'pharmacy_name' => 'Porosh Pharmacy',
  'email' => $userProfile['email'] ?: 'support@pharmasync.io',
  'phone' => '+880 1716-008-149',
  'county' => 'Dhaka',
  'region' => 'Uttara Sector 7'
];

$notificationPrefs = [
  'email' => true,
  'sms' => false,
  'push' => false,
  'low_stock' => true,
  'expired' => true,
  'new_orders' => true,
  'prescription' => false,
  'payment_due' => true,
  'insurance' => false,
  'system' => true,
  'backup' => true,
  'subscription' => true,
];

function settings_initials(array $profile): string
{
  $initials = strtoupper(substr($profile['first_name'] ?? '', 0, 1) . substr($profile['last_name'] ?? '', 0, 1));
  return $initials !== '' ? $initials : 'PS';
}

function settings_format_date(?string $dateValue): string
{
  if (!$dateValue) {
    return '—';
  }
  $ts = strtotime($dateValue);
  return $ts ? date('d M, Y', $ts) : '—';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings • PharmaSync</title>
  <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
  <link rel="stylesheet" href="style.css?v=20251205">
  <link rel="stylesheet" href="responsive.css?v=20251205">
  <link rel="stylesheet" href="dashboard.css?v=20251207">
  <link rel="stylesheet" href="settings.css?v=20251208">
  <script src="https://kit.fontawesome.com/d3e9fb9ce3.js" crossorigin="anonymous"></script>
  <script src="script.js?v=20251207" defer></script>
</head>

<body>
  <?php require __DIR__ . '/partials/nav.php'; ?>
  <div class="layout settings-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <main class="settings-wrap">
      <div class="settings-header">
        <div>
          <h1>Settings</h1>
        </div>
        <div class="profile-card" style="margin:0;">
          <div class="avatar"><?= settings_initials($userProfile); ?></div>
          <div>
            <h2><?= htmlspecialchars(trim(($userProfile['first_name'] ?? '') . ' ' . ($userProfile['last_name'] ?? ''))) ?: 'Team Member'; ?></h2>
            <div class="profile-meta">
              <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($userProfile['email'] ?: 'you@example.com'); ?></p>
              <p><i class="fas fa-calendar"></i> Member since <?= settings_format_date($userProfile['created_at']); ?></p>
            </div>
          </div>
        </div>
      </div>

      <?php
      $tabs = [
        ['id' => 'account', 'label' => 'Account Profile'],
        ['id' => 'business', 'label' => 'Business Profile'],
        ['id' => 'notifications', 'label' => 'Notifications'],
      ];
      ?>
      <div class="settings-tabs" role="tablist">
        <?php foreach ($tabs as $index => $tab): ?>
          <button class="settings-tab<?= $index === 0 ? ' active' : '' ?>" role="tab" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="<?= $tab['id']; ?>" data-target="<?= $tab['id']; ?>"><?= $tab['label']; ?></button>
        <?php endforeach; ?>
      </div>

      <section id="account" class="settings-panel active" role="tabpanel">
        <h3 class="section-title">Account Profile</h3>
        <form class="settings-form" action="#" method="post">
          <label>
            First Name *
            <input type="text" name="first_name" value="<?= htmlspecialchars($userProfile['first_name']); ?>" placeholder="First name">
          </label>
          <label>
            Last Name *
            <input type="text" name="last_name" value="<?= htmlspecialchars($userProfile['last_name']); ?>" placeholder="Last name">
          </label>
          <label>
            Phone Number
            <input type="tel" name="phone" value="+254 7XX XXX XXX" placeholder="Add phone">
          </label>
          <label>
            Profile Image
            <label class="upload-tile" for="profileImage">
              <i class="fas fa-cloud-upload-alt"></i>
              Upload profile image
              <span>JPG, PNG, GIF, WebP up to 5MB</span>
            </label>
            <input id="profileImage" type="file" name="profile_photo" accept="image/*" hidden>
          </label>
          <label>
            Email Address
            <input type="email" name="email" value="<?= htmlspecialchars($userProfile['email']); ?>" disabled>
          </label>
          <label>
            Account Created
            <input type="text" value="<?= settings_format_date($userProfile['created_at']); ?>" disabled>
          </label>
          <div class="settings-actions full-span">
            <button type="button" class="btn-ghost">Change Password</button>
            <button type="submit" class="btn-blue">Save Changes</button>
          </div>
        </form>
      </section>

      <section id="business" class="settings-panel" role="tabpanel">
        <h3 class="section-title">Business Profile</h3>
        <form class="settings-form" action="#" method="post">
          <label>
            Pharmacy Name
            <input type="text" name="pharmacy_name" value="<?= htmlspecialchars($businessProfile['pharmacy_name']); ?>">
          </label>
          <label>
            Email Address
            <input type="email" name="business_email" value="<?= htmlspecialchars($businessProfile['email']); ?>">
          </label>
          <label>
            Business Phone
            <input type="tel" name="business_phone" value="<?= htmlspecialchars($businessProfile['phone']); ?>">
          </label>
          <label>
            County
            <input type="text" name="county" value="<?= htmlspecialchars($businessProfile['county']); ?>">
          </label>
          <label>
            Region
            <select name="region">
              <option selected><?= htmlspecialchars($businessProfile['region']); ?></option>
              <option>Banani</option>
              <option>Gulshan</option>
              <option>Mirpur</option>
            </select>
          </label>
          <label>
            Business Logo
            <label class="upload-tile" for="businessLogo">
              <i class="fas fa-upload"></i>
              Upload business logo
              <span>JPG, PNG, GIF, WebP, SVG up to 5MB</span>
            </label>
            <input id="businessLogo" type="file" name="business_logo" accept="image/*" hidden>
          </label>
          <div class="settings-actions full-span">
            <button type="reset" class="btn-ghost">Reset</button>
            <button type="submit" class="btn-blue">Save Changes</button>
          </div>
        </form>
      </section>

      <section id="notifications" class="settings-panel" role="tabpanel">
        <h3 class="section-title">Notifications & Alerts</h3>
        <div class="notification-groups">
          <div class="toggle-card">
            <h4>Notification Channels</h4>
            <div class="toggle-row">
              <span>Email Notifications</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['email'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
            <div class="toggle-row">
              <span>SMS Notifications</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['sms'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
            <div class="toggle-row">
              <span>Push Notifications</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['push'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
          </div>

          <div class="toggle-card">
            <h4>Inventory Alerts</h4>
            <div class="toggle-row">
              <span>Low Stock Alerts</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['low_stock'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
            <div class="toggle-row">
              <span>Expired Products</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['expired'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
            <div class="toggle-row">
              <span>New Orders</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['new_orders'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
          </div>

          <div class="toggle-card">
            <h4>Sales & Financial</h4>
            <div class="toggle-row">
              <span>Prescription Ready</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['prescription'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
            <div class="toggle-row">
              <span>Payment Due</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['payment_due'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
            <div class="toggle-row">
              <span>Insurance Claims</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['insurance'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
          </div>

          <div class="toggle-card">
            <h4>System Alerts</h4>
            <div class="toggle-row">
              <span>System Alerts</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['system'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
            <div class="toggle-row">
              <span>Backup Completion</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['backup'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
            <div class="toggle-row">
              <span>Subscription Expiry</span>
              <label class="switch">
                <input type="checkbox" <?= $notificationPrefs['subscription'] ? 'checked' : ''; ?>>
                <span></span>
              </label>
            </div>
          </div>
        </div>
        <div class="settings-actions" style="margin-top:24px;">
          <button type="button" class="btn-blue">Save Preferences</button>
        </div>
      </section>
    </main>
  </div>

  <script>
    const tabs = document.querySelectorAll('.settings-tab');
    const panels = document.querySelectorAll('.settings-panel');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(btn => {
          btn.classList.toggle('active', btn === tab);
          btn.setAttribute('aria-selected', btn === tab ? 'true' : 'false');
        });
        panels.forEach(panel => {
          panel.classList.toggle('active', panel.id === tab.dataset.target);
        });
        const targetPanel = document.getElementById(tab.dataset.target);
        if (targetPanel) {
          targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  </script>
</body>

</html>
