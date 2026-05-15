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

$full_name = $_SESSION['full_name'];
$today = date("Y-m-d");

$pendingLeaves = 0;
$attendanceAlerts = 0;
$newApplicants = 0;
$pendingAccounts = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM leave_requests WHERE status='Pending'");
if ($result) $pendingLeaves = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM attendance WHERE attendance_date='$today' AND (status='Late' OR status='Absent')");
if ($result) $attendanceAlerts = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM applicants WHERE status='Pending'");
if ($result) $newApplicants = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='employee' AND account_status='pending_setup'");
if ($result) $pendingAccounts = mysqli_fetch_assoc($result)['total'];

$totalNotifications = $pendingLeaves + $attendanceAlerts + $newApplicants + $pendingAccounts;

$notifications = [];

$leaveQuery = mysqli_query($conn, "
    SELECT employee_name, leave_type, status, created_at
    FROM leave_requests
    ORDER BY created_at DESC
    LIMIT 5
");

if ($leaveQuery) {
    while ($row = mysqli_fetch_assoc($leaveQuery)) {
        $notifications[] = [
            "icon" => "fas fa-file-circle-check",
            "color" => "teal",
            "title" => $row['employee_name'] . " submitted a " . $row['leave_type'] . " request",
            "details" => "Status: " . $row['status'],
            "time" => $row['created_at']
        ];
    }
}

$attendanceQuery = mysqli_query($conn, "
    SELECT attendance.status, attendance.attendance_date, attendance.check_in, users.full_name
    FROM attendance
    INNER JOIN users ON attendance.employee_id = users.id
    WHERE attendance.attendance_date='$today'
    AND (attendance.status='Late' OR attendance.status='Absent')
    ORDER BY attendance.id DESC
    LIMIT 5
");

if ($attendanceQuery) {
    while ($row = mysqli_fetch_assoc($attendanceQuery)) {
        $message = $row['status'] == "Late"
            ? $row['full_name'] . " has a late check-in today"
            : $row['full_name'] . " is absent today";

        $notifications[] = [
            "icon" => "fas fa-clock",
            "color" => "red",
            "title" => $message,
            "details" => "Attendance status: " . $row['status'],
            "time" => $row['attendance_date']
        ];
    }
}

$applicantQuery = mysqli_query($conn, "
    SELECT full_name, position_applied, status, created_at
    FROM applicants
    ORDER BY created_at DESC
    LIMIT 5
");

if ($applicantQuery) {
    while ($row = mysqli_fetch_assoc($applicantQuery)) {
        $notifications[] = [
            "icon" => "fas fa-user-plus",
            "color" => "teal",
            "title" => "New applicant: " . $row['full_name'],
            "details" => "Position: " . $row['position_applied'] . " | Status: " . $row['status'],
            "time" => $row['created_at']
        ];
    }
}




usort($notifications, function($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});

$notifications = array_slice($notifications, 0, 12);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .notification-item {
      display: flex;
      gap: 16px;
      align-items: flex-start;
      padding: 18px;
      border-radius: 18px;
      background: #fff;
      margin-bottom: 14px;
      box-shadow: 0 8px 22px rgba(13, 30, 76, 0.07);
      border: 1px solid rgba(13, 30, 76, 0.06);
    }

    .notif-icon {
      width: 46px;
      height: 46px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      flex-shrink: 0;
    }

    .notif-icon.teal {
      background: #83A6CE;
    }

    .notif-icon.green {
      background: #22c55e;
    }

    .notif-icon.red {
      background: #ef4444;
    }

    .notification-item h4 {
      color: #0D1E4C;
      margin-bottom: 5px;
      font-size: 15px;
    }

    .notification-item p {
      color: #6b7280;
      font-size: 13px;
      margin-bottom: 4px;
    }

    .notification-time {
      color: #9ca3af;
      font-size: 12px;
      font-weight: 700;
    }

    .empty-state {
      background: #fff;
      padding: 30px;
      border-radius: 18px;
      text-align: center;
      color: #6b7280;
      font-weight: 700;
      box-shadow: 0 8px 22px rgba(13, 30, 76, 0.07);
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
      <li class="active"><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>
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
        <h1>Notifications</h1>
        <p>Stay updated with employee activities, leave requests, recruitment, and HR alerts.</p>
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
      <div class="hero-text">
        <h2>Notifications Center</h2>
        <p>Review important HR updates, employee changes, attendance alerts, and recruitment actions.</p>
      </div>

      <div class="hero-actions">
        <a href="notificationshr.php" class="hero-btn secondary-btn">
          <i class="fas fa-rotate"></i> Refresh
        </a>
      </div>
    </section>

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-bell"></i></div>
        <div class="card-info">
          <h3><?php echo $totalNotifications; ?></h3>
          <p>Total Alerts</p>
          <span>Require HR attention</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="card-info">
          <h3><?php echo $pendingLeaves; ?></h3>
          <p>Pending Leaves</p>
          <span>Waiting for review</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-user-plus"></i></div>
        <div class="card-info">
          <h3><?php echo $newApplicants; ?></h3>
          <p>New Applicants</p>
          <span>Recruitment pipeline</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-user-clock"></i></div>
        <div class="card-info">
          <h3><?php echo $attendanceAlerts; ?></h3>
          <p>Attendance Alerts</p>
          <span>Late or absent today</span>
        </div>
      </div>
    </section>

    <section class="panel">
      <div class="panel-header">
        <h2>Recent Notifications</h2>
        <a href="notificationshr.php">View All</a>
      </div>

      <div class="notification-list">
        <?php if (!empty($notifications)): ?>
          <?php foreach ($notifications as $notification): ?>
            <div class="notification-item">
              <div class="notif-icon <?php echo htmlspecialchars($notification['color']); ?>">
                <i class="<?php echo htmlspecialchars($notification['icon']); ?>"></i>
              </div>

              <div>
                <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                <p><?php echo htmlspecialchars($notification['details']); ?></p>
                <span class="notification-time">
                  <?php echo htmlspecialchars($notification['time']); ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            No notifications found.
          </div>
        <?php endif; ?>
      </div>
    </section>

  </main>
</div>

</body>
</html>