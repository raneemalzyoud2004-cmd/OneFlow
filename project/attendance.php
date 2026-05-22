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

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS work_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        work_date DATE NOT NULL,
        start_work TIME NOT NULL,
        end_work TIME NULL,
        total_hours VARCHAR(20) NULL,
        status VARCHAR(30) DEFAULT 'Working',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS login_days (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        login_date DATE NOT NULL,
        logout_date DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$presentToday = 0;
$completedToday = 0;
$workingNow = 0;
$totalWorkSessions = 0;

$result = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM work_attendance 
    WHERE work_date = '$today'
");
if ($result) $presentToday = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM work_attendance 
    WHERE work_date = '$today' 
    AND status = 'Completed'
");
if ($result) $completedToday = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM work_attendance 
    WHERE work_date = '$today' 
    AND status = 'Working'
");
if ($result) $workingNow = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM work_attendance
");
if ($result) $totalWorkSessions = mysqli_fetch_assoc($result)['total'];

$search = "";
$dateFilter = "";

$where = "WHERE users.role = 'employee'";

if (isset($_GET['search']) && trim($_GET['search']) !== "") {
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));

    $where .= "
        AND (
            users.full_name LIKE '%$search%'
            OR users.email LIKE '%$search%'
            OR users.username LIKE '%$search%'
            OR work_attendance.status LIKE '%$search%'
            OR work_attendance.total_hours LIKE '%$search%'
        )
    ";
}

if (isset($_GET['date']) && trim($_GET['date']) !== "") {
    $dateFilter = mysqli_real_escape_string($conn, trim($_GET['date']));
    $where .= " AND work_attendance.work_date = '$dateFilter'";
}

$records = mysqli_query($conn, "
    SELECT 
        work_attendance.id,
        work_attendance.work_date,
        work_attendance.start_work,
        work_attendance.end_work,
        work_attendance.total_hours,
        work_attendance.status,
        users.full_name,
        users.username,
        users.email,
        login_days.login_date,
        login_days.logout_date
    FROM work_attendance
    INNER JOIN users ON work_attendance.user_id = users.id
    LEFT JOIN login_days 
        ON login_days.user_id = users.id
        AND login_days.login_date = work_attendance.work_date
    $where
    ORDER BY work_attendance.work_date DESC, work_attendance.id DESC
");

function formatTime($time) {
    if (empty($time)) {
        return "—";
    }
    return date("h:i:s A", strtotime($time));
}

function formatDateValue($date) {
    if (empty($date)) {
        return "—";
    }
    return date("Y-m-d", strtotime($date));
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

.status-working {
    background: #fef3c7;
    color: #92400e;
}

.status-completed {
    background: #dcfce7;
    color: #166534;
}

.work-note {
    color: #64748b;
    font-size: 13px;
    font-weight: 700;
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
            <p>Work Tracking</p>
            <h4>Live</h4>
            <span>Start/End Work enabled</span>
        </div>
    </div>
</aside>

<main class="main-content">

<header class="topbar">
    <div class="topbar-left">
        <h1>Attendance</h1>
        <p>Track employee login days and actual work time from Start Work / End Work.</p>
    </div>

    <div class="topbar-right">
        <div class="search-box">
            <form method="GET" action="attendance.php">
                <i class="fas fa-search"></i>
                <input 
                    type="text" 
                    name="search"
                    placeholder="Search employee, status, time..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <?php if (!empty($dateFilter)) { ?>
                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>">
                <?php } ?>
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
        <h2>Work Attendance Overview</h2>
        <p>
            This page shows real employee work sessions, not only system login.
            <br>
            <span class="date-badge"><?php echo date("F d, Y"); ?></span>
        </p>
    </div>

    <div class="hero-actions">
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
            <p>Work Sessions Today</p>
            <span>Employees who clicked Start Work</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-circle-check"></i></div>
        <div class="card-info">
            <h3><?php echo $completedToday; ?></h3>
            <p>Completed Today</p>
            <span>Start and End Work completed</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
            <h3><?php echo $workingNow; ?></h3>
            <p>Working Now</p>
            <span>Still active sessions</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-business-time"></i></div>
        <div class="card-info">
            <h3><?php echo $totalWorkSessions; ?></h3>
            <p>Total Sessions</p>
            <span>All recorded work sessions</span>
        </div>
    </div>
</section>

<section class="attendance-filter-panel">
    <form method="GET" action="attendance.php">
        <input 
            type="text" 
            name="search" 
            placeholder="Search by name, email, username, status..."
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
        <div>
            <h2>Work Attendance Records</h2>
            <p class="work-note">Login/Logout are daily dates. Start/End Work are actual work hours.</p>
        </div>
        <a href="attendance.php">View All</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Email</th>
                    <th>Login Date</th>
                    <th>Logout Date</th>
                    <th>Work Date</th>
                    <th>Start Work</th>
                    <th>End Work</th>
                    <th>Total Work Time</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($records && mysqli_num_rows($records) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($records)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?: 'No email'); ?></td>
                            <td><?php echo formatDateValue($row['login_date']); ?></td>
                            <td><?php echo formatDateValue($row['logout_date']); ?></td>
                            <td><?php echo formatDateValue($row['work_date']); ?></td>
                            <td><?php echo formatTime($row['start_work']); ?></td>
                            <td><?php echo formatTime($row['end_work']); ?></td>
                            <td><?php echo htmlspecialchars($row['total_hours'] ?: 'Still working'); ?></td>
                            <td>
                                <?php
                                    $class = "pending";
                                    if ($row['status'] == "Completed") $class = "approved";
                                    if ($row['status'] == "Working") $class = "pending";
                                ?>

                                <span class="status <?php echo $class; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No work attendance records found.</td>
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