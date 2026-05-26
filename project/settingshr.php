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

function getHrUser($conn, $userId) {
    $stmt = mysqli_prepare($conn, "SELECT id, full_name, username, email, password, role, account_status, last_login FROM users WHERE id = ? AND role = 'hr' LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

$userData = getHrUser($conn, $userId);

if (!$userData) {
    session_destroy();
    header("Location: login.php");
    exit();
}

/* =========================
   UPDATE PROFILE INFORMATION
   ========================= */
if (isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (empty($fullName) || empty($username)) {
        $errorMessage = "Full name and username are required.";
    } elseif (!preg_match("/^[a-zA-Z0-9_.]+$/", $username)) {
        $errorMessage = "Username can only contain letters, numbers, underscore, and dot.";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid email address.";
    } else {
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1");
        mysqli_stmt_bind_param($checkStmt, "si", $username, $userId);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if (mysqli_num_rows($checkResult) > 0) {
            $errorMessage = "This username is already used by another account.";
        } else {
            $updateStmt = mysqli_prepare($conn, "UPDATE users SET full_name = ?, username = ?, email = ? WHERE id = ? AND role = 'hr'");
            mysqli_stmt_bind_param($updateStmt, "sssi", $fullName, $username, $email, $userId);

            if (mysqli_stmt_execute($updateStmt)) {
                $_SESSION['full_name'] = $fullName;
                $_SESSION['username'] = $username;

                $successMessage = "Profile updated successfully.";
                $userData = getHrUser($conn, $userId);
            } else {
                $errorMessage = "Failed to update profile.";
            }
        }
    }
}

/* =========================
   CHANGE PASSWORD
   ========================= */
if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = "Please fill in all password fields.";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = "New password and confirmation do not match.";
    } elseif (strlen($newPassword) < 4) {
        $errorMessage = "New password must be at least 4 characters.";
    } else {
        $storedPassword = $userData['password'];
        $passwordValid = false;

        if (password_verify($currentPassword, $storedPassword)) {
            $passwordValid = true;
        } elseif (hash('sha256', $currentPassword) === $storedPassword) {
            $passwordValid = true;
        } elseif ($currentPassword === $storedPassword) {
            $passwordValid = true;
        }

        if (!$passwordValid) {
            $errorMessage = "Current password is incorrect.";
        } else {
            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $updateStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ? AND role = 'hr'");
            mysqli_stmt_bind_param($updateStmt, "si", $newHashedPassword, $userId);

            if (mysqli_stmt_execute($updateStmt)) {
                $successMessage = "Password updated successfully.";
                $userData = getHrUser($conn, $userId);
            } else {
                $errorMessage = "Failed to update password.";
            }
        }
    }
}

$full_name = $userData['full_name'] ?? 'HR Manager';
$username = $userData['username'] ?? '';
$email = $userData['email'] ?? '';
$status = $userData['account_status'] ?? 'active';
$lastLogin = $userData['last_login'] ?? 'No login recorded';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>HR Settings - OneFlow</title>

  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --primary: #0D1E4C;
      --secondary: #C48CB3;
      --soft-pink: #E5C9D7;
      --soft-blue: #83A6CE;
      --dark-blue: #26415E;
      --deep-navy: #0B1B32;
      --white: #FFFFFF;
      --light-bg: #F6F8FC;
      --text-muted: #64748B;
      --danger: #991B1B;
      --success: #166534;
    }

    .settings-page {
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 24px;
      margin-top: 24px;
    }

    .settings-card {
      background: var(--white);
      border-radius: 24px;
      padding: 24px;
      box-shadow: 0 14px 35px rgba(13, 30, 76, 0.08);
      border: 1px solid rgba(131, 166, 206, 0.18);
      margin-bottom: 24px;
    }

    .settings-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
      padding-bottom: 14px;
      border-bottom: 1px solid #edf2f7;
    }

    .settings-card-header h2 {
      color: var(--primary);
      font-size: 20px;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .settings-card-header i {
      color: var(--secondary);
    }

    .settings-card-header p {
      margin: 5px 0 0;
      color: var(--text-muted);
      font-size: 13px;
    }

    .settings-form {
      display: grid;
      gap: 16px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: var(--primary);
      font-weight: 700;
      font-size: 14px;
    }

    .form-group input {
      width: 100%;
      padding: 14px 15px;
      border-radius: 14px;
      border: 1px solid #dbe4ef;
      outline: none;
      background: #fff;
      color: var(--deep-navy);
      font-size: 14px;
      transition: 0.2s ease;
      box-sizing: border-box;
    }

    .form-group input:focus {
      border-color: var(--soft-blue);
      box-shadow: 0 0 0 4px rgba(131, 166, 206, 0.18);
    }

    .small-note {
      display: block;
      color: var(--text-muted);
      font-size: 12px;
      margin-top: 6px;
      line-height: 1.5;
    }

    .save-btn {
      width: fit-content;
      border: none;
      border-radius: 14px;
      padding: 13px 18px;
      background: var(--primary);
      color: white;
      font-weight: 800;
      cursor: pointer;
      transition: 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }

    .save-btn:hover {
      background: var(--dark-blue);
      transform: translateY(-1px);
      box-shadow: 0 10px 22px rgba(13, 30, 76, 0.16);
    }

    .success-message,
    .error-message {
      padding: 14px 18px;
      border-radius: 16px;
      font-weight: 800;
      margin: 18px 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .success-message {
      background: #DCFCE7;
      color: var(--success);
      border: 1px solid #BBF7D0;
    }

    .error-message {
      background: #FEE2E2;
      color: var(--danger);
      border: 1px solid #FECACA;
    }

    .account-box {
      display: grid;
      gap: 13px;
    }

    .account-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #F8FAFC;
      padding: 14px 15px;
      border-radius: 14px;
      border: 1px solid #edf2f7;
    }

    .account-row span {
      color: var(--text-muted);
      font-weight: 700;
      font-size: 13px;
    }

    .account-row strong {
      color: var(--primary);
      font-size: 13px;
      text-align: right;
      max-width: 60%;
      overflow-wrap: anywhere;
    }

    .profile-preview {
      background: linear-gradient(135deg, var(--primary), var(--dark-blue));
      color: white;
      border-radius: 24px;
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: 0 16px 34px rgba(13, 30, 76, 0.18);
    }

    .profile-preview .big-avatar {
      width: 76px;
      height: 76px;
      border-radius: 22px;
      background: rgba(255,255,255,0.18);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 34px;
      font-weight: 900;
      margin-bottom: 16px;
      border: 1px solid rgba(255,255,255,0.25);
    }

    .profile-preview h2 {
      margin: 0 0 5px;
      font-size: 22px;
    }

    .profile-preview p {
      margin: 0;
      opacity: 0.86;
    }

    @media (max-width: 950px) {
      .settings-page {
        grid-template-columns: 1fr;
      }
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
      <li>
        <a href="hrdashboard.php">
          <i class="fas fa-house"></i> Dashboard
        </a>
      </li>

      <li>
        <a href="employees.php">
          <i class="fas fa-users"></i> Employees
        </a>
      </li>

      <li>
        <a href="attendance.php">
          <i class="fas fa-calendar-check"></i> Attendance
        </a>
      </li>

      <li>
        <a href="leaverequests.php">
          <i class="fas fa-file-circle-check"></i> Leave Requests
        </a>
      </li>

      <li>
        <a href="recruitment.php">
          <i class="fas fa-user-plus"></i> Recruitment
        </a>
      </li>

      <li>
        <a href="notificationshr.php">
          <i class="fas fa-bell"></i> Notifications
        </a>
      </li>

      <li>
        <a href="itsupport.php">
          <i class="fas fa-headset"></i> IT Support
        </a>
      </li>

      <li class="active">
        <a href="settingshr.php">
          <i class="fas fa-gear"></i> Settings
        </a>
      </li>
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
        <p>Manage your HR profile and password securely.</p>
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
      <div class="success-message">
        <i class="fas fa-circle-check"></i>
        <?php echo htmlspecialchars($successMessage); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
      <div class="error-message">
        <i class="fas fa-triangle-exclamation"></i>
        <?php echo htmlspecialchars($errorMessage); ?>
      </div>
    <?php endif; ?>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>HR Account Settings</h2>
        <p>Update your account details and change your password securely.</p>
      </div>

      <div class="hero-actions">
        <a href="settingshr.php" class="hero-btn secondary-btn">
          <i class="fas fa-rotate"></i> Refresh
        </a>
      </div>
    </section>

    <section class="settings-page">

      <div>

        <div class="settings-card">
          <div class="settings-card-header">
            <div>
              <h2><i class="fas fa-user-pen"></i> Profile Information</h2>
              <p>Edit your name, username, and email address.</p>
            </div>
          </div>

          <form method="POST" action="settingshr.php" class="settings-form">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
            </div>

            <div class="form-group">
              <label>Username</label>
              <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
              <span class="small-note">Allowed: letters, numbers, underscore, and dot.</span>
            </div>

            <div class="form-group">
              <label>Email Address</label>
              <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <button type="submit" name="update_profile" class="save-btn">
              <i class="fas fa-floppy-disk"></i> Save Profile
            </button>
          </form>
        </div>

        <div class="settings-card">
          <div class="settings-card-header">
            <div>
              <h2><i class="fas fa-lock"></i> Change Password</h2>
              <p>Enter your current password, then choose a new one.</p>
            </div>
          </div>

          <form method="POST" action="settingshr.php" class="settings-form">
            <div class="form-group">
              <label>Current Password</label>
              <input type="password" name="current_password" placeholder="Enter your current password" required>
            </div>

            <div class="form-group">
              <label>New Password</label>
              <input type="password" name="new_password" placeholder="Enter new password" required>
              <span class="small-note">Minimum 4 characters.</span>
            </div>

            <div class="form-group">
              <label>Confirm New Password</label>
              <input type="password" name="confirm_password" placeholder="Confirm new password" required>
            </div>

            <button type="submit" name="change_password" class="save-btn">
              <i class="fas fa-key"></i> Update Password
            </button>
          </form>
        </div>

      </div>

      <div>

        <div class="profile-preview">
          <div class="big-avatar">
            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
          </div>
          <h2><?php echo htmlspecialchars($full_name); ?></h2>
          <p>Human Resources Manager</p>
        </div>

        <div class="settings-card">
          <div class="settings-card-header">
            <div>
              <h2><i class="fas fa-id-card"></i> Account Details</h2>
              <p>Your current HR account information.</p>
            </div>
          </div>

          <div class="account-box">
            <div class="account-row">
              <span>Role</span>
              <strong>HR Manager</strong>
            </div>

            <div class="account-row">
              <span>Full Name</span>
              <strong><?php echo htmlspecialchars($full_name); ?></strong>
            </div>

            <div class="account-row">
              <span>Username</span>
              <strong><?php echo htmlspecialchars($username); ?></strong>
            </div>

            <div class="account-row">
              <span>Email</span>
              <strong><?php echo !empty($email) ? htmlspecialchars($email) : 'Not set'; ?></strong>
            </div>

            <div class="account-row">
              <span>Status</span>
              <strong><?php echo htmlspecialchars($status); ?></strong>
            </div>

            <div class="account-row">
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