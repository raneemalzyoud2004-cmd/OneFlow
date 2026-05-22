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

$full_name = $_SESSION['full_name'] ?? 'HR';
$today = date("Y-m-d");

function getCount($conn, $today, $status) {
    $status = mysqli_real_escape_string($conn, $status);
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM attendance WHERE attendance_date='$today' AND status='$status'");
    if ($result) return mysqli_fetch_assoc($result)['total'];
    return 0;
}

$presentToday = getCount($conn, $today, "Present");
$absentToday = getCount($conn, $today, "Absent");
$lateToday = getCount($conn, $today, "Late");
$onLeaveToday = getCount($conn, $today, "On Leave");

$search = "";
$dateFilter = "";

$where = "WHERE users.role='employee'";

if (isset($_GET['search']) && trim($_GET['search']) !== "") {
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
    $where .= "
        AND (
            users.full_name LIKE '%$search%'
            OR users.email LIKE '%$search%'
            OR attendance.status LIKE '%$search%'
            OR attendance.notes LIKE '%$search%'
            OR attendance.attendance_date LIKE '%$search%'
        )
    ";
}

if (isset($_GET['date']) && trim($_GET['date']) !== "") {
    $dateFilter = mysqli_real_escape_string($conn, trim($_GET['date']));
    $where .= " AND attendance.attendance_date='$dateFilter'";
}

$records = mysqli_query($conn, "
    SELECT attendance.*, users.full_name, users.email
    FROM attendance
    INNER JOIN users ON attendance.employee_id = users.id
    $where
    ORDER BY attendance.attendance_date DESC, attendance.id DESC
");

function formatTime($time) {
    if (empty($time)) return "—";
    return date("h:i A", strtotime($time));
}

function calculateHours($checkIn, $checkOut) {
    if (empty($checkIn) || empty($checkOut)) return "—";
    $start = strtotime($checkIn);
    $end = strtotime($checkOut);
    if ($end <= $start) return "—";

    $diff = $end - $start;
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);

    return $hours . "h " . $minutes . "m";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.search-box form {
    display: flex;
    align-items: center;
    width: 100%;
}

.search-box button {
    border: none;
    background: transparent;
    cursor: pointer;
    color: #0D1E4C;
    font-size: 15px;
}

.clear-link {
    font-size: 14px;
    text-decoration: none;
    font-weight: 700;
    color: #0D1E4C;
    margin-left: 10px;
}

.date-badge {
    background: #eef5ff;
    color: #0D1E4C;
    padding: 8px 12px;
    border-radius: 12px;
    font-weight: 700;
    display: inline-block;
}

.attendance-filter-panel {
    background: white;
    padding: 20px;
    border-radius: 22px;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px rgba(15,23,42,0.06);
    border: 1px solid #edf2f7;
}

.attendance-filter-panel form {
    display: flex;
    gap: 14px;
    align-items: center;
    flex-wrap: wrap;
}

.attendance-filter-panel input {
    height: 48px;
    border-radius: 14px;
    border: 1px solid #dbe7f0;
    padding: 0 14px;
    outline: none;
    min-width: 220px;
}

.filter-btn {
    height: 48px;
    border: none;
    border-radius: 14px;
    padding: 0 18px;
    background: #0D1E4C;
    color: white;
    font-weight: 800;
    cursor: pointer;
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
        <li class="active"><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
        <li><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
        <li><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>
        <li><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
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
        <h1>Attendance</h1>
        <p>Track employee attendance, check-ins, check-outs, and late arrivals.</p>
    </div>

    <div class="topbar-right">
        <div class="search-box">
            <form method="GET" action="attendance.php">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    name="search"
                    placeholder="Search attendance records..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <?php if (!empty($dateFilter)): ?>
                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>">
                <?php endif; ?>
                <button type="submit"><i class="fas fa-arrow-right"></i></button>
            </form>
        </div>

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
        <h2>Attendance Overview</h2>
        <p>
            Monitor employee attendance records from employee Check In / Check Out.
            <br>
            <span class="date-badge"><?php echo date("F d, Y"); ?></span>
        </p>
    </div>

    <div class="hero-actions">
        <a href="addattendance.php" class="hero-btn primary-btn">
            <i class="fas fa-plus"></i> Add Attendance
        </a>

        <a href="attendance.php" class="hero-btn secondary-btn">
            <i class="fas fa-rotate"></i> Refresh
        </a>
    </div>
</section>

<section class="cards">
    <div class="card">
        <div class="card-icon"><i class="fas fa-user-check"></i></div>
        <div class="card-info">
            <h3><?php echo $presentToday; ?></h3>
            <p>Present Today</p>
            <span>Checked in on time</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-user-xmark"></i></div>
        <div class="card-info">
            <h3><?php echo $absentToday; ?></h3>
            <p>Absent</p>
            <span>Marked absent</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
            <h3><?php echo $lateToday; ?></h3>
            <p>Late Check-ins</p>
            <span>Require review</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-calendar-minus"></i></div>
        <div class="card-info">
            <h3><?php echo $onLeaveToday; ?></h3>
            <p>On Leave</p>
            <span>Approved leave today</span>
        </div>
    </div>
</section>

<section class="attendance-filter-panel">
    <form method="GET" action="attendance.php">
        <input 
            type="text" 
            name="search" 
            placeholder="Search by name, email, status, notes..."
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <input 
            type="date" 
            name="date"
            value="<?php echo htmlspecialchars($dateFilter); ?>"
        >

        <button type="submit" class="filter-btn">
            <i class="fas fa-filter"></i> Filter
        </button>

        <a href="attendance.php" class="clear-link">Clear</a>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Attendance Records</h2>
        <a href="attendance.php">View All</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Total Hours</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($records && mysqli_num_rows($records) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($records)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?: 'No email'); ?></td>
                            <td><?php echo htmlspecialchars($row['attendance_date']); ?></td>
                            <td><?php echo formatTime($row['check_in']); ?></td>
                            <td><?php echo formatTime($row['check_out']); ?></td>
                            <td><?php echo calculateHours($row['check_in'], $row['check_out']); ?></td>
                            <td>
                                <?php
                                    $class = "pending";
                                    if ($row['status'] == "Present") $class = "approved";
                                    if ($row['status'] == "Absent") $class = "rejected";
                                    if ($row['status'] == "Late") $class = "pending";
                                    if ($row['status'] == "On Leave") $class = "approved";
                                ?>
                                <span class="status <?php echo $class; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['notes'] ?: 'No notes'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No attendance records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

</main>
</div>

</body>
</html>