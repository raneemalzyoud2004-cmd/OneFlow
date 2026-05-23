<?php
session_start();
include "config.php";

date_default_timezone_set("Asia/Amman");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hr') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'] ?? 'HR';
$today = date("Y-m-d");
$success = "";
$error = "";

/* Add notes column if not exists */
mysqli_query($conn, "ALTER TABLE work_attendance ADD COLUMN IF NOT EXISTS notes TEXT NULL");

/* HR update notes */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_note'])) {
    $record_id = (int)$_POST['record_id'];
    $note = mysqli_real_escape_string($conn, trim($_POST['notes']));

    $update = mysqli_query($conn, "
        UPDATE work_attendance
        SET notes = '$note'
        WHERE id = $record_id
    ");

    if ($update) {
        $success = "HR note updated successfully.";
    } else {
        $error = "Failed to update note: " . mysqli_error($conn);
    }
}

function getCalculatedStatus($startWork, $endWork, $totalSeconds) {
    if (empty($endWork)) {
        return "Working";
    }

    if ($totalSeconds < 18000) {
        return "Absent";
    }

    if ($startWork > "08:00:00") {
        return "Late";
    }

    if ($totalSeconds > 25200) {
        return "Overtime";
    }

    if ($totalSeconds >= 25200) {
        return "Present";
    }

    return "Late";
}

function getCount($conn, $today, $status) {
    $status = mysqli_real_escape_string($conn, $status);

    $result = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM (
            SELECT 
                CASE
                    WHEN wa.end_work IS NULL THEN 'Working'
                    WHEN wa.total_seconds < 18000 THEN 'Absent'
                    WHEN wa.start_work > '08:00:00' THEN 'Late'
                    WHEN wa.total_seconds > 25200 THEN 'Overtime'
                    WHEN wa.total_seconds >= 25200 THEN 'Present'
                    ELSE 'Late'
                END AS calculated_status
            FROM work_attendance wa
            JOIN users u ON wa.user_id = u.id
            WHERE wa.work_date = '$today'
            AND u.role = 'employee'
        ) x
        WHERE calculated_status = '$status'
    ");

    if ($result) {
        return (int)mysqli_fetch_assoc($result)['total'];
    }

    return 0;
}

$presentToday = getCount($conn, $today, "Present");
$absentToday = getCount($conn, $today, "Absent");
$lateToday = getCount($conn, $today, "Late");
$overtimeToday = getCount($conn, $today, "Overtime");

$search = "";
$dateFilter = "";

$where = "WHERE u.role = 'employee'";

if (isset($_GET['search']) && trim($_GET['search']) !== "") {
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
    $where .= "
        AND (
            u.full_name LIKE '%$search%'
            OR u.email LIKE '%$search%'
            OR wa.work_date LIKE '%$search%'
            OR wa.notes LIKE '%$search%'
        )
    ";
}

if (isset($_GET['date']) && trim($_GET['date']) !== "") {
    $dateFilter = mysqli_real_escape_string($conn, trim($_GET['date']));
    $where .= " AND wa.work_date = '$dateFilter'";
}

$records = mysqli_query($conn, "
    SELECT 
        wa.id,
        wa.user_id AS employee_id,
        wa.work_date AS attendance_date,
        wa.start_work AS check_in,
        wa.end_work AS check_out,
        wa.total_hours,
        wa.total_seconds,
        wa.notes,
        wa.created_at,
        u.full_name,
        u.email,
        CASE
            WHEN wa.end_work IS NULL THEN 'Working'
            WHEN wa.total_seconds < 18000 THEN 'Absent'
            WHEN wa.start_work > '08:00:00' THEN 'Late'
            WHEN wa.total_seconds > 25200 THEN 'Overtime'
            WHEN wa.total_seconds >= 25200 THEN 'Present'
            ELSE 'Late'
        END AS status
    FROM work_attendance wa
    JOIN users u ON wa.user_id = u.id
    $where
    ORDER BY wa.work_date DESC, wa.id DESC
");

function formatTime($time) {
    if (empty($time)) return "—";
    return date("h:i A", strtotime($time));
}

function formatHours($seconds) {
    if ($seconds <= 0) return "0h 0m";
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return $hours . "h " . $minutes . "m";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.note-form {
    display: flex;
    gap: 8px;
    align-items: center;
}
.note-form input {
    padding: 8px 10px;
    border-radius: 10px;
    border: 1px solid #dbe7f0;
    width: 220px;
}
.note-form button {
    border: none;
    background: #0D1E4C;
    color: white;
    padding: 8px 12px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
}
.alert-success {
    background: #dcfce7;
    color: #166534;
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 15px;
    font-weight: 700;
}
.alert-error {
    background: #fee2e2;
    color: #991b1b;
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 15px;
    font-weight: 700;
}
.attendance-filter-panel {
    background: white;
    padding: 20px;
    border-radius: 22px;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px rgba(15,23,42,0.06);
}
.attendance-filter-panel form {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}
.attendance-filter-panel input {
    height: 48px;
    border-radius: 14px;
    border: 1px solid #dbe7f0;
    padding: 0 14px;
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
.clear-link {
    padding: 14px 5px;
    color: #0D1E4C;
    font-weight: 800;
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
</aside>

<main class="main-content">

<header class="topbar">
    <div class="topbar-left">
        <h1>Attendance</h1>
        <p>Track employee attendance, check-ins, check-outs, late arrivals, and overtime.</p>
    </div>

    <div class="topbar-right">
        <div class="admin-profile">
            <div class="admin-avatar"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
            <div>
                <h4><?php echo htmlspecialchars($full_name); ?></h4>
                <span>HR Manager</span>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<?php if ($success): ?>
    <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<section class="hero-banner">
    <div class="hero-text">
        <h2>Attendance Overview</h2>
        <p>Monitor employee attendance records from employee Check In / Check Out.</p>
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
            <span>7 hours completed</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-user-xmark"></i></div>
        <div class="card-info">
            <h3><?php echo $absentToday; ?></h3>
            <p>Absent</p>
            <span>Less than 5 hours</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
            <h3><?php echo $lateToday; ?></h3>
            <p>Late</p>
            <span>Started after 8:00 AM</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-business-time"></i></div>
        <div class="card-info">
            <h3><?php echo $overtimeToday; ?></h3>
            <p>Overtime</p>
            <span>More than 7 hours</span>
        </div>
    </div>
</section>

<section class="attendance-filter-panel">
    <form method="GET" action="attendance.php">
        <input 
            type="text" 
            name="search" 
            placeholder="Search by name, email, notes..."
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
                    <th>HR Notes</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($records && mysqli_num_rows($records) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($records)): ?>
                        <?php
                            $class = "pending";
                            if ($row['status'] === "Present" || $row['status'] === "Overtime") $class = "approved";
                            if ($row['status'] === "Absent") $class = "rejected";
                            if ($row['status'] === "Late" || $row['status'] === "Working") $class = "pending";
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?: 'No email'); ?></td>
                            <td><?php echo htmlspecialchars($row['attendance_date']); ?></td>
                            <td><?php echo formatTime($row['check_in']); ?></td>
                            <td><?php echo formatTime($row['check_out']); ?></td>
                            <td><?php echo formatHours((int)$row['total_seconds']); ?></td>
                            <td>
                                <span class="status <?php echo $class; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="note-form">
                                    <input type="hidden" name="record_id" value="<?php echo (int)$row['id']; ?>">
                                    <input 
                                        type="text" 
                                        name="notes" 
                                        value="<?php echo htmlspecialchars($row['notes'] ?? ''); ?>"
                                        placeholder="Write HR note..."
                                    >
                                    <button type="submit" name="update_note">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">No attendance records found.</td>
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