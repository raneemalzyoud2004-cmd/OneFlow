<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
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

$full_name = $user['full_name'] ?? 'Employee';
$email = $user['email'] ?? 'employee@oneflow.com';
$role = $user['role'] ?? 'employee';
$department = 'Not Assigned';
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
  <title>Employee Settings - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .password-form {
      margin-top: 25px;
      display: grid;
      gap: 18px;
    }

    .form-group label {
      display: block;
      font-weight: 700;
      color: #0D1E4C;
      margin-bottom: 8px;
    }

    .form-group input {
      width: 100%;
      padding: 15px 18px;
      border: 1px solid #dce7f3;
      border-radius: 16px;
      font-size: 15px;
      outline: none;
      background: #f8fbff;
      color: #0d1e4c;
    }

    .form-group input:focus {
      border-color: #15bdb4;
      box-shadow: 0 0 0 4px rgba(21, 189, 180, 0.12);
    }

    .save-password-btn {
      border: none;
      padding: 15px 22px;
      border-radius: 16px;
      background: linear-gradient(135deg, #14b8a6, #0d9488);
      color: white;
      font-weight: 800;
      font-size: 16px;
      cursor: pointer;
      width: fit-content;
      box-shadow: 0 10px 25px rgba(13, 148, 136, 0.25);
    }

    .save-password-btn:hover {
      transform: translateY(-2px);
    }

    .alert-msg {
      padding: 14px 18px;
      border-radius: 14px;
      font-weight: 700;
      margin-top: 15px;
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

    .quick-card.active-option {
      border: 2px solid #14b8a6;
      background: #eefdfb;
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
      <p class="admin-role">Employee Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="dashboardemployee.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="mytasks.php"><i class="fas fa-list-check"></i> My Tasks</a></li>
      <li><a href="leaverequests_employee.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
      <li><a href="myattendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
      <li><a href="myschedule.php"><i class="fas fa-clock"></i> Schedule</a></li>
      <li><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
      <li class="active"><a href="settingsemployee.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>Performance Status</p>
        <h4>Excellent</h4>
        <span>On track this week</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Settings</h1>
        <p>Manage your account and password.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search settings...">
        </div>

      

        <div class="admin-avatar"><?php echo htmlspecialchars($initial); ?></div>
        <div>
          <h4><?php echo htmlspecialchars($full_name); ?></h4>
          <span>Team Member</span>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </header>

    <section class="dashboard-grid">

      <div class="left-column">
        <div class="panel">
          <div class="panel-header">
            <h2>Profile Settings</h2>
          </div>

          <?php if (!empty($message)): ?>
            <div class="alert-msg <?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?>">
              <?php echo htmlspecialchars($message); ?>
            </div>
          <?php endif; ?>

          <div class="overview-box">
            <div class="overview-row">
              <span>Full Name</span>
              <strong><?php echo htmlspecialchars($full_name); ?></strong>
            </div>

            <div class="overview-row">
              <span>Email</span>
              <strong><?php echo htmlspecialchars($email); ?></strong>
            </div>

            <div class="overview-row">
              <span>Role</span>
              <strong><?php echo htmlspecialchars(ucfirst($role)); ?></strong>
            </div>

            <div class="overview-row">
              <span>Department</span>
              <strong><?php echo htmlspecialchars($department); ?></strong>
            </div>
          </div>
        </div>

        <div class="panel" style="margin-top: 25px;">
          <div class="panel-header">
            <h2>Change Password</h2>
          </div>

          <form method="POST" class="password-form">
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

            <button type="submit" name="change_password" class="save-password-btn">
              <i class="fas fa-lock"></i> Update Password
            </button>
          </form>
        </div>
      </div>

      <div class="right-column">
        <div class="panel">
          <div class="panel-header">
            <h2>Account Options</h2>
          </div>

          <div class="quick-actions">
            <div class="quick-card active-option">
              <i class="fas fa-lock"></i>
              <h4>Change Password</h4>
              <p>Update your account password securely</p>
            </div>
          </div>
        </div>
      </div>

    </section>

  </main>
</div>

</body>
</html>