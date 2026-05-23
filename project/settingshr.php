<?php
session_start();
include "config.php";

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hr') {
    header("Location: login.php");
    exit();
}

$userId = intval($_SESSION['user_id']);
$successMessage = "";
$errorMessage = "";

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? AND role = 'hr'");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$userQuery = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($userQuery);

if (!$userData) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    $stmt = mysqli_prepare($conn, "UPDATE users SET full_name = ?, email = ? WHERE id = ? AND role = 'hr'");
    mysqli_stmt_bind_param($stmt, "ssi", $fullName, $email, $userId);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['full_name'] = $fullName;
        $successMessage = "Profile updated successfully.";

        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? AND role = 'hr'");
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $userQuery = mysqli_stmt_get_result($stmt);
        $userData = mysqli_fetch_assoc($userQuery);
    } else {
        $errorMessage = "Failed to update profile.";
    }
}

if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    $storedPassword = $userData['password'];

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = "Please fill in all password fields.";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = "New password and confirmation do not match.";
    } elseif (strlen($newPassword) < 4) {
        $errorMessage = "New password must be at least 4 characters.";
    } else {
        $passwordValid = false;

        if (password_verify($currentPassword, $storedPassword)) {
            $passwordValid = true;
        } elseif ($currentPassword === $storedPassword) {
            $passwordValid = true;
        }

        if (!$passwordValid) {
            $errorMessage = "Current password is incorrect.";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ? AND role = 'hr'");
            mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $userId);

            if (mysqli_stmt_execute($stmt)) {
                $successMessage = "Password updated successfully. Please use your new password next time.";

                $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? AND role = 'hr'");
                mysqli_stmt_bind_param($stmt, "i", $userId);
                mysqli_stmt_execute($stmt);
                $userQuery = mysqli_stmt_get_result($stmt);
                $userData = mysqli_fetch_assoc($userQuery);
            } else {
                $errorMessage = "Failed to update password.";
            }
        }
    }
}

$full_name = $_SESSION['full_name'];
$email = $userData['email'] ?? "";
$username = $userData['username'] ?? "";
$status = $userData['account_status'] ?? "active";
$lastLogin = $userData['last_login'] ?? "No login recorded";
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
    .settings-form {
      display: grid;
      gap: 16px;
      padding-top: 10px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 700;
      color: #0D1E4C;
    }

    .form-group input {
      width: 100%;
      padding: 14px;
      border-radius: 14px;
      border: 1px solid #d9e1ea;
      outline: none;
    }

    .save-btn {
      background: #0D1E4C;
      color: white;
      border: none;
      padding: 12px 18px;
      border-radius: 14px;
      font-weight: 700;
      cursor: pointer;
      width: fit-content;
    }

    .success-message {
      background: #e7f8ee;
      color: #166534;
      padding: 14px 18px;
      border-radius: 14px;
      margin-bottom: 18px;
      font-weight: 700;
    }

    .error-message {
      background: #fee2e2;
      color: #991b1b;
      padding: 14px 18px;
      border-radius: 14px;
      margin-bottom: 18px;
      font-weight: 700;
    }

    .preference-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #f7fafd;
      padding: 16px;
      border-radius: 14px;
      font-weight: 600;
      color: #0D1E4C;
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
      <p class="admin-role">HR Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="hrdashboard.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
      <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
      <li><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
      <li><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>
      <li><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
      <li class="active"><a href="settingshr.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>System Health</p>
        <h4>Excellent</h4>
        <span>99.2% uptime</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Settings</h1>
        <p>Manage your HR profile, preferences, and account security.</p>
      </div>

      <div class="topbar-right">
        <div class="admin-profile">
          <div class="admin-avatar">
            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
          </div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>HR Manager</span>
          </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </header>

    <?php if (!empty($successMessage)): ?>
      <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
      <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>HR Settings</h2>
        <p>Update your account details, notification preferences, and security settings.</p>
      </div>

      <div class="hero-actions">
        <a href="settingshr.php" class="hero-btn secondary-btn">
          <i class="fas fa-rotate"></i> Refresh
        </a>
      </div>
    </section>

    <section class="dashboard-grid">
      <div class="left-column">

        <div class="panel">
          <div class="panel-header">
            <h2>Profile Settings</h2>
          </div>

          <form method="POST" action="settingshr.php" class="settings-form">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
            </div>

            <div class="form-group">
              <label>Email Address</label>
              <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <div class="form-group">
              <label>Username</label>
              <input type="text" value="<?php echo htmlspecialchars($username); ?>" readonly>
            </div>

            <button type="submit" name="update_profile" class="save-btn">
              <i class="fas fa-floppy-disk"></i> Save Profile
            </button>
          </form>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>Notification Preferences</h2>
          </div>

          <div class="settings-form">
            <label class="preference-item">
              <span>Email Notifications</span>
              <input type="checkbox" checked>
            </label>

            <label class="preference-item">
              <span>Leave Request Alerts</span>
              <input type="checkbox" checked>
            </label>

            <label class="preference-item">
              <span>Attendance Alerts</span>
              <input type="checkbox" checked>
            </label>

            <label class="preference-item">
              <span>Recruitment Updates</span>
              <input type="checkbox" checked>
            </label>
          </div>
        </div>

      </div>

      <div class="right-column">

        <div class="panel">
          <div class="panel-header">
            <h2>Security</h2>
          </div>

          <form method="POST" action="settingshr.php" class="settings-form">
            <div class="form-group">
              <label>Current Password</label>
              <input type="password" name="current_password" placeholder="Enter current password" required>
            </div>

            <div class="form-group">
              <label>New Password</label>
              <input type="password" name="new_password" placeholder="Enter new password" required>
            </div>

            <div class="form-group">
              <label>Confirm Password</label>
              <input type="password" name="confirm_password" placeholder="Confirm new password" required>
            </div>

            <button type="submit" name="change_password" class="save-btn">
              <i class="fas fa-lock"></i> Update Password
            </button>
          </form>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>Account Status</h2>
          </div>

          <div class="overview-box">
            <div class="overview-row">
              <span>Role</span>
              <strong>HR Manager</strong>
            </div>

            <div class="overview-row">
              <span>Department</span>
              <strong>Human Resources</strong>
            </div>

            <div class="overview-row">
              <span>Account Status</span>
              <strong><?php echo htmlspecialchars($status); ?></strong>
            </div>

            <div class="overview-row">
              <span>Last Login</span>
              <strong><?php echo htmlspecialchars($lastLogin); ?></strong>
            </div>
          </div>
        </div>

      </div>
    </section>

  </main>
</div>

</body>
</html>