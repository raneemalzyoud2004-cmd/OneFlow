<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teamleader') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$messageType = "";

$stmt = mysqli_prepare($conn, "SELECT full_name, email, role, password FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$full_name = $user['full_name'] ?? 'Team Leader';
$email = $user['email'] ?? 'teamleader@oneflow.com';
$role = $user['role'] ?? 'teamleader';
$currentHashedPassword = $user['password'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in all password fields.";
        $messageType = "error";
    } elseif ($new_password !== $confirm_password) {
        $message = "New password and confirm password do not match.";
        $messageType = "error";
    } elseif (strlen($new_password) < 4) {
        $message = "New password must be at least 4 characters.";
        $messageType = "error";
    } else {
        $passwordIsCorrect = false;

        $defaultPassword = strtolower(substr($full_name, 0, 1)) . "1234";

        if (password_verify($current_password, $currentHashedPassword)) {
            $passwordIsCorrect = true;
        } elseif ($current_password === $currentHashedPassword) {
            $passwordIsCorrect = true;
        } elseif ($current_password === $defaultPassword) {
            $passwordIsCorrect = true;
        }

        if (!$passwordIsCorrect) {
            $message = "Current password is incorrect.";
            $messageType = "error";
        } else {
            $newHashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

            $update = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "si", $newHashedPassword, $user_id);

            if (mysqli_stmt_execute($update)) {
                $message = "Password changed successfully.";
                $messageType = "success";
            } else {
                $message = "Something went wrong. Please try again.";
                $messageType = "error";
            }
        }
    }
}

$initial = strtoupper(substr($full_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team Leader Settings - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .settings-grid {
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 24px;
      margin-top: 28px;
      align-items: start;
    }

    .settings-card,
    .preferences-card {
      background: #ffffff;
      border-radius: 24px;
      padding: 26px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
    }

    .settings-card h3,
    .preferences-card h3 {
      font-size: 24px;
      color: #0f172a;
      margin-bottom: 20px;
    }

    .profile-info {
      display: flex;
      flex-direction: column;
      gap: 14px;
      margin-bottom: 26px;
    }

    .profile-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      background: #f8fbfd;
      border: 1px solid #dbe4ee;
      border-radius: 14px;
      padding: 14px 16px;
    }

    .profile-row span {
      color: #64748b;
      font-weight: 600;
    }

    .profile-row strong {
      color: #0f172a;
      font-weight: 800;
      text-align: right;
    }

    .settings-form .form-group {
      margin-bottom: 16px;
    }

    .settings-form label {
      display: block;
      margin-bottom: 8px;
      color: #0D1E4C;
      font-weight: 700;
      font-size: 15px;
    }

    .settings-form input {
      width: 100%;
      border: 1px solid #dbe4ee;
      border-radius: 14px;
      padding: 14px 16px;
      font-size: 15px;
      outline: none;
      transition: 0.3s;
      background: #f8fbfd;
      color: #0f172a;
    }

    .settings-form input:focus {
      border-color: #19c2c9;
      background: #ffffff;
      box-shadow: 0 0 0 4px rgba(25, 194, 201, 0.10);
    }

    .settings-actions {
      display: flex;
      gap: 12px;
      margin-top: 10px;
      flex-wrap: wrap;
    }

    .settings-btn {
      border: none;
      border-radius: 14px;
      padding: 13px 18px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: 0.3s;
    }

    .settings-btn.primary {
      background: linear-gradient(135deg, #12c2cc, #2dd4bf);
      color: #fff;
      box-shadow: 0 10px 24px rgba(18, 194, 204, 0.22);
    }

    .settings-btn.secondary {
      background: #eff6ff;
      color: #0369a1;
    }

    .settings-btn:hover {
      transform: translateY(-2px);
      opacity: 0.95;
    }

    .alert-msg {
      padding: 14px 18px;
      border-radius: 14px;
      font-weight: 700;
      margin-bottom: 18px;
    }

    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #86efac;
    }

    .alert-error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    .password-card {
      border: 2px solid #12c2cc;
      border-radius: 20px;
      padding: 22px;
      background: #effdfb;
    }

    .password-card i {
      width: 54px;
      height: 54px;
      border-radius: 14px;
      background: linear-gradient(135deg, #12c2cc, #2dd4bf);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      margin-bottom: 16px;
    }

    .password-card h4 {
      color: #0f172a;
      font-size: 22px;
      margin-bottom: 8px;
    }

    .password-card p {
      color: #64748b;
      line-height: 1.6;
      margin: 0;
    }

    .account-box {
      margin-top: 18px;
      border-top: 1px solid #edf2f7;
      padding-top: 18px;
    }

    .account-box h4 {
      margin-bottom: 12px;
      color: #0f172a;
      font-size: 18px;
    }

    .account-box ul {
      padding-left: 18px;
      color: #64748b;
      line-height: 1.8;
      font-size: 14px;
    }

    @media (max-width: 1100px) {
      .settings-grid {
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
      <p class="admin-role">Team Leader Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="dashboardteamleader.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="myteam.php"><i class="fas fa-users"></i> My Team</a></li>
      <li><a href="assigntasks.php"><i class="fas fa-list-check"></i> Assign Tasks</a></li>
      <li><a href="tasksprogress.php"><i class="fas fa-chart-line"></i> Tasks Progress</a></li>
      <li><a href="reportsteamleader.php"><i class="fas fa-file-lines"></i> Reports</a></li>
      <li><a href="notificationsteamleader.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
      <li class="active"><a href="settingsteamleader.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>Team Performance</p>
        <h4>Excellent</h4>
        <span>92% tasks completed</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Settings</h1>
        <p>Manage your account password securely.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search settings...">
        </div>

        <a href="notificationsteamleader.php" class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">6</span>
        </a>

        <div class="admin-profile">
          <div class="admin-avatar">
            <?php echo htmlspecialchars($initial); ?>
          </div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>Team Leader</span>
          </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </header>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>Account Security ⚙️</h2>
        <p>Change your password to keep your Team Leader account protected.</p>
      </div>

      <div class="hero-actions">
        <a href="dashboardteamleader.php" class="hero-btn secondary-btn">
          <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
      </div>
    </section>

    <section class="settings-grid">

      <div class="settings-card">
        <h3>Profile Settings</h3>

        <?php if (!empty($message)): ?>
          <div class="alert-msg <?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?>">
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php endif; ?>

        <div class="profile-info">
          <div class="profile-row">
            <span>Full Name</span>
            <strong><?php echo htmlspecialchars($full_name); ?></strong>
          </div>

          <div class="profile-row">
            <span>Email</span>
            <strong><?php echo htmlspecialchars($email); ?></strong>
          </div>

          <div class="profile-row">
            <span>Role</span>
            <strong><?php echo htmlspecialchars(ucfirst($role)); ?></strong>
          </div>
        </div>

        <h3>Change Password</h3>

        <form method="POST" class="settings-form">
          <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" placeholder="Enter current password" required>
          </div>

          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Enter new password" required>
          </div>

          <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" placeholder="Confirm new password" required>
          </div>

          <div class="settings-actions">
            <button type="submit" name="change_password" class="settings-btn primary">
              <i class="fas fa-lock"></i> Update Password
            </button>

            <button type="reset" class="settings-btn secondary">
              <i class="fas fa-rotate-left"></i> Reset
            </button>
          </div>
        </form>
      </div>

      <div class="preferences-card">
        <h3>Account Options</h3>

        <div class="password-card">
          <i class="fas fa-lock"></i>
          <h4>Change Password</h4>
          <p>Update your Team Leader account password securely. The new password will be saved as a hashed password in the database.</p>
        </div>

        <div class="account-box">
          <h4>Account Notes</h4>
          <ul>
            <li>Your role is currently set as <strong>Team Leader</strong>.</li>
            <li>Only password changing is enabled on this settings page.</li>
            <li>Your password will remain hashed in the users table.</li>
          </ul>
        </div>
      </div>

    </section>

  </main>
</div>

</body>
</html>