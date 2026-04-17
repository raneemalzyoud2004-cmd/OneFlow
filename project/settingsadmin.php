<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

$popupMessage = "";
$popupType = "";

// Get current admin data
$adminQuery = "SELECT id, full_name, username, email, password, role, account_status, last_login FROM users WHERE id = $user_id LIMIT 1";
$adminResult = mysqli_query($conn, $adminQuery);
$adminData = mysqli_fetch_assoc($adminResult);

if (!$adminData) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// ===================== LOAD / CREATE ADMIN SETTINGS =====================
$settingsQuery = "SELECT * FROM admin_settings WHERE user_id = $user_id LIMIT 1";
$settingsResult = mysqli_query($conn, $settingsQuery);
$settingsData = mysqli_fetch_assoc($settingsResult);

if (!$settingsData) {
    mysqli_query($conn, "INSERT INTO admin_settings (user_id) VALUES ($user_id)");
    $settingsResult = mysqli_query($conn, $settingsQuery);
    $settingsData = mysqli_fetch_assoc($settingsResult);
}

// ===================== HANDLE PROFILE UPDATE =====================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_profile'])) {
    $new_full_name = trim($_POST['full_name']);
    $new_email = trim($_POST['email']);

    if (empty($new_full_name) || empty($new_email)) {
        $popupMessage = "Please fill in all profile fields.";
        $popupType = "error";
    } else {
        $new_full_name = mysqli_real_escape_string($conn, $new_full_name);
        $new_email = mysqli_real_escape_string($conn, $new_email);

        $checkEmailQuery = "SELECT id FROM users WHERE email = '$new_email' AND id != $user_id LIMIT 1";
        $checkEmailResult = mysqli_query($conn, $checkEmailQuery);

        if ($checkEmailResult && mysqli_num_rows($checkEmailResult) > 0) {
            $popupMessage = "This email is already used by another account.";
            $popupType = "error";
        } else {
            $updateProfileQuery = "UPDATE users SET full_name = '$new_full_name', email = '$new_email' WHERE id = $user_id";
            if (mysqli_query($conn, $updateProfileQuery)) {
                $_SESSION['full_name'] = $new_full_name;
                $full_name = $new_full_name;
                $popupMessage = "Profile settings updated successfully.";
                $popupType = "success";

                $adminResult = mysqli_query($conn, $adminQuery);
                $adminData = mysqli_fetch_assoc($adminResult);
            } else {
                $popupMessage = "Failed to update profile settings.";
                $popupType = "error";
            }
        }
    }
}

// ===================== HANDLE PASSWORD UPDATE =====================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $popupMessage = "Please fill in all password fields.";
        $popupType = "error";
    } elseif ($current_password !== $adminData['password']) {
        $popupMessage = "Current password is incorrect.";
        $popupType = "error";
    } elseif (strlen($new_password) < 3) {
        $popupMessage = "New password must be at least 3 characters.";
        $popupType = "error";
    } elseif ($new_password !== $confirm_password) {
        $popupMessage = "New password and confirm password do not match.";
        $popupType = "error";
    } else {
        $safe_password = mysqli_real_escape_string($conn, $new_password);
        $updatePasswordQuery = "UPDATE users SET password = '$safe_password' WHERE id = $user_id";

        if (mysqli_query($conn, $updatePasswordQuery)) {
            $popupMessage = "Password updated successfully.";
            $popupType = "success";

            $adminResult = mysqli_query($conn, $adminQuery);
            $adminData = mysqli_fetch_assoc($adminResult);
        } else {
            $popupMessage = "Failed to update password.";
            $popupType = "error";
        }
    }
}

// ===================== HANDLE NOTIFICATION PREFERENCES =====================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_notifications'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $registration_alerts = isset($_POST['registration_alerts']) ? 1 : 0;
    $system_notifications = isset($_POST['system_notifications']) ? 1 : 0;
    $analytics_reports = isset($_POST['analytics_reports']) ? 1 : 0;

    $updateSettingsQuery = "UPDATE admin_settings SET
        email_notifications = $email_notifications,
        registration_alerts = $registration_alerts,
        system_notifications = $system_notifications,
        analytics_reports = $analytics_reports
        WHERE user_id = $user_id";

    if (mysqli_query($conn, $updateSettingsQuery)) {
        $popupMessage = "Notification preferences saved successfully.";
        $popupType = "success";

        $settingsResult = mysqli_query($conn, $settingsQuery);
        $settingsData = mysqli_fetch_assoc($settingsResult);
    } else {
        $popupMessage = "Failed to save notification preferences.";
        $popupType = "error";
    }
}

// ===================== ACCOUNT INFO HELPERS =====================
$roleLabel = "Super Admin";
if ($adminData['role'] === 'hr') {
    $roleLabel = "HR";
} elseif ($adminData['role'] === 'employee') {
    $roleLabel = "Employee";
} elseif ($adminData['role'] === 'teamleader') {
    $roleLabel = "Team Leader";
}

$accountStatusLabel = ucfirst(str_replace('_', ' ', $adminData['account_status']));
$lastLoginLabel = !empty($adminData['last_login']) ? date("Y-m-d h:i A", strtotime($adminData['last_login'])) : "No login recorded yet";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .settings-note {
      background: linear-gradient(135deg, #f8fbff, #eef8f8);
      border: 1px solid #dbe7f0;
      border-radius: 18px;
      padding: 16px 18px;
      color: #475569;
      font-size: 14px;
      line-height: 1.7;
    }

    .settings-form-grid {
      display: grid;
      gap: 16px;
      padding-top: 10px;
    }

    .settings-form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 700;
      color: #0f172a;
      font-size: 14px;
    }

    .settings-input {
      width: 100%;
      padding: 14px 16px;
      border-radius: 14px;
      border: 1px solid #d9e1ea;
      outline: none;
      background: #ffffff;
      font-size: 14px;
      transition: 0.3s ease;
    }

    .settings-input:focus {
      border-color: #14b8a6;
      box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12);
    }

    .settings-section-actions {
      display: flex;
      justify-content: flex-end;
      margin-top: 18px;
    }

    .settings-save-btn {
      border: none;
      background: linear-gradient(90deg, #0ea5a4, #14b8a6);
      color: white;
      padding: 13px 20px;
      border-radius: 14px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 10px 18px rgba(20, 184, 166, 0.22);
      transition: 0.3s ease;
    }

    .settings-save-btn:hover {
      transform: translateY(-2px);
    }

    .toggle-card {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #f7fafd;
      padding: 16px 18px;
      border-radius: 16px;
      border: 1px solid #e6eef5;
    }

    .toggle-card span {
      color: #0f172a;
      font-weight: 600;
      font-size: 14px;
    }

    .toggle-card input[type="checkbox"] {
      width: 18px;
      height: 18px;
      accent-color: #14b8a6;
      cursor: pointer;
    }

    .hero-btn-link {
      text-decoration: none;
    }
  </style>
</head>
<body>

  <div class="dashboard-container">

    <aside class="sidebar">
      <div class="sidebar-top">
        <div class="logo-box">
          <i class="fa-solid fa-leaf"></i>
          <h2>OneFlow</h2>
        </div>
        <p class="admin-role">Admin Panel</p>
      </div>

      <ul class="sidebar-menu">
        <li><a href="dashboardadmin.php"><i class="fas fa-house"></i> Dashboard</a></li>
        <li><a href="manageusers.php"><i class="fas fa-users"></i> Manage Users</a></li>
        <li><a href="hrteam.php"><i class="fas fa-user-tie"></i> HR Team</a></li>
        <li><a href="systemlogs.php"><i class="fas fa-clipboard-list"></i> System Logs</a></li>
        <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
        <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li class="active"><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
      </ul>

      <div class="sidebar-bottom">
        <div class="system-card">
          <p>Settings Status</p>
          <h4>Ready</h4>
          <span>Profile and security controls</span>
        </div>
      </div>
    </aside>

    <main class="main-content">

      <header class="topbar">
        <div class="topbar-left">
          <h1>Settings</h1>
          <p>Manage your admin profile, preferences, and security settings from one place.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search settings..." disabled>
          </div>

          <div class="admin-profile">
            <div class="admin-avatar">A</div>
            <div>
              <h4><?php echo htmlspecialchars($full_name); ?></h4>
              <span>Super Admin</span>
            </div>
          </div>

          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <section class="hero-banner">
        <div class="hero-text">
          <h2>Admin Settings ⚙️</h2>
          <p>Update your profile information, change your password, and review your account details in a secure and clean settings area.</p>
        </div>
        <div class="hero-actions">
          <a href="dashboardadmin.php" class="hero-btn secondary-btn hero-btn-link">
            <i class="fas fa-house"></i> Back to Dashboard
          </a>
        </div>
      </section>

      <section class="dashboard-grid">
        <div class="left-column">

          <div class="panel">
            <div class="panel-header">
              <h2>Profile Settings</h2>
            </div>

            <form method="POST" class="settings-form-grid">
              <div class="settings-form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="settings-input" value="<?php echo htmlspecialchars($adminData['full_name']); ?>" required>
              </div>

              <div class="settings-form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="settings-input" value="<?php echo htmlspecialchars($adminData['email'] ?? ''); ?>" required>
              </div>

              <div class="settings-form-group">
                <label>Username</label>
                <input type="text" class="settings-input" value="<?php echo htmlspecialchars($adminData['username']); ?>" disabled>
              </div>

              <div class="settings-section-actions">
                <button type="submit" name="save_profile" class="settings-save-btn">
                  <i class="fas fa-floppy-disk"></i> Save Profile
                </button>
              </div>
            </form>
          </div>

          <div class="panel">
            <div class="panel-header">
              <h2>Notification Preferences</h2>
            </div>

            <form method="POST" class="settings-form-grid">
              <label class="toggle-card">
                <span>Email Notifications</span>
                <input type="checkbox" name="email_notifications" <?php echo ($settingsData['email_notifications'] == 1) ? 'checked' : ''; ?>>
              </label>

              <label class="toggle-card">
                <span>User Registration Alerts</span>
                <input type="checkbox" name="registration_alerts" <?php echo ($settingsData['registration_alerts'] == 1) ? 'checked' : ''; ?>>
              </label>

              <label class="toggle-card">
                <span>System Notifications</span>
                <input type="checkbox" name="system_notifications" <?php echo ($settingsData['system_notifications'] == 1) ? 'checked' : ''; ?>>
              </label>

              <label class="toggle-card">
                <span>Analytics Reports</span>
                <input type="checkbox" name="analytics_reports" <?php echo ($settingsData['analytics_reports'] == 1) ? 'checked' : ''; ?>>
              </label>

              <div class="settings-note">
                These preferences are now saved in the database for this admin account.
              </div>

              <div class="settings-section-actions">
                <button type="submit" name="save_notifications" class="settings-save-btn">
                  <i class="fas fa-bell"></i> Save Preferences
                </button>
              </div>
            </form>
          </div>

        </div>

        <div class="right-column">

          <div class="panel">
            <div class="panel-header">
              <h2>Security</h2>
            </div>

            <form method="POST" class="settings-form-grid">
              <div class="settings-form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" class="settings-input" placeholder="Enter current password" required>
              </div>

              <div class="settings-form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="settings-input" placeholder="Enter new password" required>
              </div>

              <div class="settings-form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="settings-input" placeholder="Confirm new password" required>
              </div>

              <div class="settings-section-actions">
                <button type="submit" name="save_password" class="settings-save-btn">
                  <i class="fas fa-key"></i> Update Password
                </button>
              </div>
            </form>
          </div>

          <div class="panel">
            <div class="panel-header">
              <h2>Account Status</h2>
            </div>

            <div class="overview-box">
              <div class="overview-row">
                <span>Role</span>
                <strong><?php echo htmlspecialchars($roleLabel); ?></strong>
              </div>
              <div class="overview-row">
                <span>Department</span>
                <strong>Administration</strong>
              </div>
              <div class="overview-row">
                <span>Account Status</span>
                <strong><?php echo htmlspecialchars($accountStatusLabel); ?></strong>
              </div>
              <div class="overview-row">
                <span>Last Login</span>
                <strong><?php echo htmlspecialchars($lastLoginLabel); ?></strong>
              </div>
              <div class="overview-row">
                <span>Username</span>
                <strong><?php echo htmlspecialchars($adminData['username']); ?></strong>
              </div>
            </div>
          </div>

          <div class="panel">
            <div class="panel-header">
              <h2>Admin Notes</h2>
            </div>

            <div class="settings-note">
              This page now supports real profile updates, password changes, and saved notification preferences for the current admin account.
            </div>
          </div>

        </div>
      </section>

    </main>
  </div>

  <div id="actionPopup" class="action-popup"></div>

  <script>
    function showPopup(message, type) {
      const popup = document.getElementById("actionPopup");
      if (!popup) return;

      popup.textContent = message;
      popup.className = "action-popup show " + type;

      setTimeout(function () {
        popup.className = "action-popup";
      }, 2500);
    }

    document.addEventListener("DOMContentLoaded", function () {
      const popupMessage = <?php echo json_encode($popupMessage); ?>;
      const popupType = <?php echo json_encode($popupType); ?>;

      if (popupMessage) {
        showPopup(popupMessage, popupType);
      }
    });
  </script>

</body>
</html>