<?php
session_start();
include "config.php";

date_default_timezone_set("Asia/Amman");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Employee';
$today = date("Y-m-d");
$message = "";
$error = "";

$todayRecord = null;

$checkToday = mysqli_query($conn, "
    SELECT * FROM attendance
    WHERE employee_id='$user_id'
    AND attendance_date='$today'
    LIMIT 1
");

if ($checkToday && mysqli_num_rows($checkToday) > 0) {
    $todayRecord = mysqli_fetch_assoc($checkToday);
}

if (isset($_POST['check_in'])) {
    if ($todayRecord) {
        $error = "You already checked in today.";
    } else {
        $now = date("H:i:s");
        $status = ($now > "09:00:00") ? "Late" : "Present";
        $notes = ($status == "Late") ? "Employee checked in late after 09:00 AM." : "Employee checked in successfully.";

        $insert = mysqli_query($conn, "
            INSERT INTO attendance (employee_id, attendance_date, check_in, check_out, status, notes)
            VALUES ('$user_id', '$today', '$now', NULL, '$status', '$notes')
        ");

        if ($insert) {
            header("Location: myattendance.php?success=checkedin");
            exit();
        } else {
            $error = "Check-in failed: " . mysqli_error($conn);
        }
    }
}

if (isset($_POST['check_out'])) {
    if (!$todayRecord) {
        $error = "You need to check in first.";
    } elseif (!empty($todayRecord['check_out'])) {
        $error = "You already checked out today.";
    } else {
        $now = date("H:i:s");

        $update = mysqli_query($conn, "
            UPDATE attendance
            SET check_out='$now',
                notes=CONCAT(IFNULL(notes,''), ' Check-out completed.')
            WHERE employee_id='$user_id'
            AND attendance_date='$today'
        ");

        if ($update) {
            header("Location: myattendance.php?success=checkedout");
            exit();
        } else {
            $error = "Check-out failed: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == 'checkedin') {
    $message = "Check-in saved successfully.";
}

if (isset($_GET['success']) && $_GET['success'] == 'checkedout') {
    $message = "Check-out saved successfully.";
}

$checkToday = mysqli_query($conn, "
    SELECT * FROM attendance
    WHERE employee_id='$user_id'
    AND attendance_date='$today'
    LIMIT 1
");

if ($checkToday && mysqli_num_rows($checkToday) > 0) {
    $todayRecord = mysqli_fetch_assoc($checkToday);
}

$records = mysqli_query($conn, "
    SELECT * FROM attendance
    WHERE employee_id='$user_id'
    ORDER BY attendance_date DESC, id DESC
    LIMIT 20
");

$month = date("m");
$year = date("Y");

$presentDays = 0;
$lateDays = 0;
$totalDays = 0;

$countResult = mysqli_query($conn, "
    SELECT 
        COUNT(*) AS total_days,
        SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) AS present_days,
        SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) AS late_days
    FROM attendance
    WHERE employee_id='$user_id'
    AND MONTH(attendance_date)='$month'
    AND YEAR(attendance_date)='$year'
");

if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $totalDays = (int) $countRow['total_days'];
    $presentDays = (int) $countRow['present_days'];
    $lateDays = (int) $countRow['late_days'];
}

$attendanceRate = $totalDays > 0 ? round((($presentDays + $lateDays) / $totalDays) * 100) : 0;

function formatTime($time) {
    if (empty($time)) {
        return "—";
    }
    return date("h:i A", strtotime($time));
}

function calculateHours($checkIn, $checkOut) {
    if (empty($checkIn) || empty($checkOut)) {
        return "—";
    }

    $start = strtotime($checkIn);
    $end = strtotime($checkOut);

    if ($end <= $start) {
        return "—";
    }

    $diff = $end - $start;
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);

    return $hours . "h " . $minutes . "m";
}

$canCheckIn = !$todayRecord;
$canCheckOut = $todayRecord && empty($todayRecord['check_out']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Attendance - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .attendance-buttons {
      margin: 20px 0;
      background: white;
      padding: 22px;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.05);
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .attendance-buttons button {
      padding: 12px 22px;
      font-size: 16px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      background: #0D1E4C;
      color: #fff;
      font-weight: 800;
      transition: background 0.2s;
    }

    .attendance-buttons button:hover:not(:disabled) {
      background: #26415E;
    }

    .attendance-buttons button:disabled {
      background: #ccc;
      cursor: not-allowed;
    }

    .today-box {
      color: #0D1E4C;
      font-weight: 700;
      margin-left: 10px;
    }

    .success-message {
      background: #dcfce7;
      color: #166534;
      padding: 14px;
      border-radius: 12px;
      margin: 15px 0;
      font-weight: 700;
    }

    .error-message {
      background: #fee2e2;
      color: #991b1b;
      padding: 14px;
      border-radius: 12px;
      margin: 15px 0;
      font-weight: 700;
    }

    .attendance-timeline {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin-top: 25px;
    }

    .attendance-card {
      background: white;
      border-radius: 22px;
      padding: 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 8px 25px rgba(0,0,0,0.05);
      transition: 0.3s;
      border-left: 8px solid #14b8a6;
    }

    .attendance-card:hover {
      transform: translateY(-4px);
    }

    .attendance-date h3 {
      font-size: 28px;
      color: #0D1E4C;
      margin-bottom: 5px;
    }

    .attendance-date span {
      color: #6b7280;
      font-size: 14px;
    }

    .attendance-info {
      width: 75%;
    }

    .attendance-times {
      display: flex;
      justify-content: space-between;
      margin-bottom: 18px;
      gap: 15px;
    }

    .attendance-times p {
      font-size: 13px;
      color: #6b7280;
      margin-bottom: 5px;
    }

    .attendance-times h4 {
      font-size: 20px;
      color: #0D1E4C;
    }

    .progress-bar {
      width: 100%;
      height: 10px;
      background: #e5e7eb;
      border-radius: 20px;
      overflow: hidden;
      margin-bottom: 10px;
    }

    .progress-fill {
      height: 100%;
      border-radius: 20px;
    }

    .present-fill {
      width: 95%;
      background: #22c55e;
    }

    .late-fill {
      width: 70%;
      background: #f59e0b;
    }

    .absent-fill {
      width: 20%;
      background: #ef4444;
    }

    .attendance-progress {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    @media(max-width:900px) {
      .attendance-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
      }

      .attendance-info {
        width: 100%;
      }

      .attendance-times {
        flex-direction: column;
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
      <p class="admin-role">Employee Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="dashboardemployee.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="mytasks.php"><i class="fas fa-list-check"></i> My Tasks</a></li>
      <li><a href="leaverequests_employee.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
      <li class="active"><a href="myattendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
      <li><a href="myschedule.php"><i class="fas fa-clock"></i> Schedule</a></li>
      <li><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
      <li><a href="settingsemployee.php"><i class="fas fa-gear"></i> Settings</a></li>
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
        <h1>My Attendance</h1>
        <p>Monitor your attendance records, check-ins, and work days.</p>
      </div>

      <div class="topbar-right">
        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">1</span>
        </div>

        <div class="admin-avatar">
          <?php echo strtoupper(substr($full_name, 0, 1)); ?>
        </div>

        <div>
          <h4><?php echo htmlspecialchars($full_name); ?></h4>
          <span>Team Member</span>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </header>

    <?php if (!empty($message)): ?>
      <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <section class="attendance-buttons">
      <form method="POST" style="display:inline;">
        <button type="submit" name="check_in" <?php echo !$canCheckIn ? 'disabled' : ''; ?>>
          <i class="fas fa-play"></i> Check In
        </button>
      </form>

      <form method="POST" style="display:inline;">
        <button type="submit" name="check_out" <?php echo !$canCheckOut ? 'disabled' : ''; ?>>
          <i class="fas fa-stop"></i> Check Out
        </button>
      </form>

      <span class="today-box">
        Today:
        <?php
          if (!$todayRecord) {
              echo "Not checked in yet";
          } else {
              echo "Check In: " . formatTime($todayRecord['check_in']) . " | Check Out: " . formatTime($todayRecord['check_out']);
          }
        ?>
      </span>
    </section>

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="card-info">
          <h3><?php echo $attendanceRate; ?>%</h3>
          <p>Attendance Rate</p>
          <span>This month</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
          <h3><?php echo $lateDays; ?></h3>
          <p>Late Check-ins</p>
          <span>This month</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-check"></i></div>
        <div class="card-info">
          <h3><?php echo $presentDays; ?></h3>
          <p>Present Days</p>
          <span>Current month</span>
        </div>
      </div>
    </section>

    <section class="panel">

      <div class="panel-header">
        <h2>Attendance Activity</h2>
        <a href="myattendance.php">View All</a>
      </div>

      <div class="attendance-timeline">

        <?php if ($records && mysqli_num_rows($records) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($records)): ?>
            <?php
              $dateText = date("M d", strtotime($row['attendance_date']));
              $dayText = date("l", strtotime($row['attendance_date']));
              $status = $row['status'];

              $fillClass = "present-fill";
              $statusClass = "approved";
              $borderColor = "#14b8a6";

              if ($status == "Late") {
                  $fillClass = "late-fill";
                  $statusClass = "pending";
                  $borderColor = "#f59e0b";
              }

              if ($status == "Absent") {
                  $fillClass = "absent-fill";
                  $statusClass = "rejected";
                  $borderColor = "#ef4444";
              }
            ?>

            <div class="attendance-card" style="border-left-color: <?php echo $borderColor; ?>;">
              <div class="attendance-date">
                <h3><?php echo htmlspecialchars($dateText); ?></h3>
                <span><?php echo htmlspecialchars($dayText); ?></span>
              </div>

              <div class="attendance-info">
                <div class="attendance-times">
                  <div>
                    <p>Check In</p>
                    <h4><?php echo formatTime($row['check_in']); ?></h4>
                  </div>

                  <div>
                    <p>Check Out</p>
                    <h4><?php echo formatTime($row['check_out']); ?></h4>
                  </div>

                  <div>
                    <p>Total Hours</p>
                    <h4><?php echo calculateHours($row['check_in'], $row['check_out']); ?></h4>
                  </div>
                </div>

                <div class="attendance-progress">
                  <div class="progress-bar">
                    <div class="progress-fill <?php echo $fillClass; ?>"></div>
                  </div>

                  <span class="status <?php echo $statusClass; ?>">
                    <?php echo htmlspecialchars($status); ?>
                  </span>
                </div>
              </div>
            </div>

          <?php endwhile; ?>
        <?php else: ?>
          <p>No attendance records found.</p>
        <?php endif; ?>

      </div>

    </section>

  </main>
</div>

</body>
</html>