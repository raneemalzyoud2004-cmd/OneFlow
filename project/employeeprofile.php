<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hr') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

if (!isset($_GET['id'])) {
    header("Location: employees.php");
    exit();
}

$user_id = intval($_GET['id']);

$query = "SELECT id, full_name, username, email, role, account_status, salary, failed_attempts, is_blocked, last_login 
          FROM users 
          WHERE id = $user_id AND role = 'employee'";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Employee not found.";
    exit();
}

$user = mysqli_fetch_assoc($result);

$statusClass = ($user['account_status'] == 'active') ? 'approved' : 'pending';
$blockedText = ($user['is_blocked'] == 1) ? 'Blocked' : 'Not Blocked';
$lastLogin = !empty($user['last_login']) ? $user['last_login'] : 'No login yet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Profile - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .profile-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 18px;
      margin-top: 20px;
    }

    .profile-card {
      background: #ffffff;
      border-radius: 18px;
      padding: 22px;
      box-shadow: 0 10px 25px rgba(13, 30, 76, 0.08);
      border: 1px solid rgba(13, 30, 76, 0.08);
    }

    .profile-card h3 {
      margin-bottom: 18px;
      color: #0D1E4C;
      font-size: 18px;
    }

    .overview-row {
      display: flex;
      justify-content: space-between;
      gap: 20px;
      padding: 13px 0;
      border-bottom: 1px solid rgba(13, 30, 76, 0.08);
    }

    .overview-row:last-child {
      border-bottom: none;
    }

    .overview-row span {
      color: #6b7280;
      font-weight: 600;
    }

    .overview-row strong {
      color: #0D1E4C;
      text-align: right;
    }

    .profile-avatar-large {
      width: 88px;
      height: 88px;
      border-radius: 24px;
      background: linear-gradient(135deg, #0D1E4C, #C48CB3);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 34px;
      font-weight: 800;
      margin-bottom: 15px;
    }

    .profile-summary {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .profile-summary h2 {
      color: #0D1E4C;
      margin-bottom: 5px;
    }

    .profile-summary p {
      color: #6b7280;
      margin-bottom: 8px;
    }

    .action-btn {
      padding: 10px 14px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 700;
      display: inline-block;
      margin-right: 8px;
    }

    .edit-btn {
      background: #83A6CE;
      color: #0D1E4C;
    }

    .back-btn {
      background: #E5C9D7;
      color: #0D1E4C;
    }

    @media (max-width: 900px) {
      .profile-grid {
        grid-template-columns: 1fr;
      }

      .profile-summary {
        flex-direction: column;
        align-items: flex-start;
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
      <li><a href="hrdashboard.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li class="active"><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
      <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
      <li><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
      <li><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>
      <li><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingshr.php"><i class="fas fa-gear"></i> Settings</a></li>
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
        <h1>Employee Profile</h1>
        <p>View detailed employee information and account status.</p>
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

    <section class="hero-banner">
      <div class="profile-summary">
        <div class="profile-avatar-large">
          <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
        </div>

        <div>
          <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
          <p><?php echo htmlspecialchars($user['email'] ?: 'No email available'); ?></p>
          <span class="status <?php echo $statusClass; ?>">
            <?php echo htmlspecialchars($user['account_status']); ?>
          </span>
        </div>
      </div>

      <div class="hero-actions">
        <a href="editemployee.php?id=<?php echo $user['id']; ?>" class="hero-btn primary-btn">
          <i class="fas fa-pen"></i> Edit Employee
        </a>

        <a href="employees.php" class="hero-btn secondary-btn">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </div>
    </section>

    <div class="profile-grid">

      <div class="profile-card">
        <h3><i class="fas fa-id-card"></i> Basic Information</h3>

        <div class="overview-row">
          <span>Employee ID</span>
          <strong><?php echo htmlspecialchars($user['id']); ?></strong>
        </div>

        <div class="overview-row">
          <span>Full Name</span>
          <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
        </div>

        <div class="overview-row">
          <span>Username</span>
          <strong><?php echo htmlspecialchars($user['username']); ?></strong>
        </div>

        <div class="overview-row">
          <span>Email</span>
          <strong><?php echo htmlspecialchars($user['email'] ?: 'No email'); ?></strong>
        </div>

        <div class="overview-row">
          <span>Role</span>
          <strong><?php echo ucfirst(htmlspecialchars($user['role'])); ?></strong>
        </div>
      </div>

      <div class="profile-card">
        <h3><i class="fas fa-briefcase"></i> Work & Account Details</h3>

        <div class="overview-row">
          <span>Account Status</span>
          <strong><?php echo htmlspecialchars($user['account_status']); ?></strong>
        </div>

        <div class="overview-row">
          <span>Blocked Status</span>
          <strong><?php echo $blockedText; ?></strong>
        </div>

        <div class="overview-row">
          <span>Failed Login Attempts</span>
          <strong><?php echo htmlspecialchars($user['failed_attempts']); ?></strong>
        </div>

        <div class="overview-row">
          <span>Salary</span>
          <strong><?php echo number_format((float)$user['salary'], 2); ?></strong>
        </div>

        <div class="overview-row">
          <span>Last Login</span>
          <strong><?php echo htmlspecialchars($lastLogin); ?></strong>
        </div>
      </div>

    </div>

  </main>
</div>

</body>
</html>