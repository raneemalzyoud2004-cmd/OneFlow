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
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Employee';
$message = "";
$messageType = "";

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS work_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        work_date DATE NOT NULL,
        start_work TIME NOT NULL,
        end_work TIME NULL,
        total_hours VARCHAR(20) NULL,
        total_seconds INT DEFAULT 0,
        status VARCHAR(30) DEFAULT 'Working',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

mysqli_query($conn, "ALTER TABLE work_attendance ADD COLUMN IF NOT EXISTS total_seconds INT DEFAULT 0");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $today = date("Y-m-d");
    $now = date("H:i:s");

    if ($_POST['action'] === "start_work") {
        $activeCheck = mysqli_query($conn, "
            SELECT id
            FROM work_attendance
            WHERE user_id = $user_id
            AND status = 'Working'
            ORDER BY id DESC
            LIMIT 1
        ");

        if ($activeCheck && mysqli_num_rows($activeCheck) > 0) {
            $_SESSION['attendance_message'] = "You already have an active work session.";
            $_SESSION['attendance_message_type'] = "error";
        } else {
            $insert = mysqli_query($conn, "
                INSERT INTO work_attendance (user_id, work_date, start_work, status)
                VALUES ($user_id, '$today', '$now', 'Working')
            ");

            if ($insert) {
                $_SESSION['attendance_message'] = "Work started successfully.";
                $_SESSION['attendance_message_type'] = "success";
            } else {
                $_SESSION['attendance_message'] = "Failed to start work: " . mysqli_error($conn);
                $_SESSION['attendance_message_type'] = "error";
            }
        }

        header("Location: myattendance.php");
        exit();
    }

    if ($_POST['action'] === "end_work") {
        $active = mysqli_query($conn, "
            SELECT id, work_date, start_work
            FROM work_attendance
            WHERE user_id = $user_id
            AND status = 'Working'
            ORDER BY id DESC
            LIMIT 1
        ");

        if ($active && mysqli_num_rows($active) > 0) {
            $row = mysqli_fetch_assoc($active);

            $workDate = $row['work_date'];
            $startTimestamp = strtotime($workDate . " " . $row['start_work']);
            $endTimestamp = strtotime(date("Y-m-d") . " " . $now);

            if ($endTimestamp < $startTimestamp) {
                $endTimestamp = $startTimestamp;
            }

            $diff = $endTimestamp - $startTimestamp;

            $hours = floor($diff / 3600);
            $minutes = floor(($diff % 3600) / 60);
            $seconds = $diff % 60;

            $totalHours = sprintf("%02dh %02dm %02ds", $hours, $minutes, $seconds);
            $attendanceId = (int) $row['id'];

            $update = mysqli_query($conn, "
                UPDATE work_attendance
                SET end_work = '$now',
                    total_hours = '$totalHours',
                    total_seconds = $diff,
                    status = 'Completed'
                WHERE id = $attendanceId
            ");

            if ($update) {
                $_SESSION['attendance_message'] = "Work ended successfully. Total time: $totalHours";
                $_SESSION['attendance_message_type'] = "success";
            } else {
                $_SESSION['attendance_message'] = "Failed to end work: " . mysqli_error($conn);
                $_SESSION['attendance_message_type'] = "error";
            }
        } else {
            $_SESSION['attendance_message'] = "No active work session found.";
            $_SESSION['attendance_message_type'] = "error";
        }

        header("Location: myattendance.php");
        exit();
    }
}

if (isset($_SESSION['attendance_message'])) {
    $message = $_SESSION['attendance_message'];
    $messageType = $_SESSION['attendance_message_type'];
    unset($_SESSION['attendance_message']);
    unset($_SESSION['attendance_message_type']);
}

$activeWork = null;

$activeQuery = mysqli_query($conn, "
    SELECT *
    FROM work_attendance
    WHERE user_id = $user_id
    AND status = 'Working'
    ORDER BY id DESC
    LIMIT 1
");

if ($activeQuery && mysqli_num_rows($activeQuery) > 0) {
    $activeWork = mysqli_fetch_assoc($activeQuery);
}

$attendanceRecords = mysqli_query($conn, "
    SELECT *
    FROM work_attendance
    WHERE user_id = $user_id
    ORDER BY work_date DESC, id DESC
");

$totalTodaySeconds = 0;
$today = date("Y-m-d");

$totalTodayQuery = mysqli_query($conn, "
    SELECT SUM(total_seconds) AS total
    FROM work_attendance
    WHERE user_id = $user_id
    AND work_date = '$today'
    AND status = 'Completed'
");

if ($totalTodayQuery) {
    $totalTodayRow = mysqli_fetch_assoc($totalTodayQuery);
    $totalTodaySeconds = (int)($totalTodayRow['total'] ?? 0);
}

$todayHours = floor($totalTodaySeconds / 3600);
$todayMinutes = floor(($totalTodaySeconds % 3600) / 60);
$todaySeconds = $totalTodaySeconds % 60;
$totalTodayFormatted = sprintf("%02dh %02dm %02ds", $todayHours, $todayMinutes, $todaySeconds);
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
.search-box{
    position:relative;
    width:340px;
}

.search-box i{
    position:absolute;
    left:16px;
    top:50%;
    transform:translateY(-50%);
    color:#14b8a6;
    font-size:15px;
}

.search-box input{
    width:100%;
    height:52px;
    border-radius:18px;
    border:1px solid #dbe7f0;
    background:white;
    padding:0 18px 0 48px;
    font-size:14px;
    outline:none;
    box-shadow:0 8px 20px rgba(15,23,42,0.05);
}

.attendance-message{
    padding:14px 18px;
    border-radius:16px;
    font-weight:800;
    margin-bottom:18px;
}

.attendance-message.success{
    background:#dcfce7;
    color:#166534;
}

.attendance-message.error{
    background:#fee2e2;
    color:#991b1b;
}

.attendance-buttons{
    margin:20px 0;
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.attendance-buttons button{
    padding:12px 22px;
    font-size:16px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    background:#0D1E4C;
    color:#fff;
    transition:0.2s;
    font-weight:800;
}

.attendance-buttons button:hover:not(:disabled){
    background:#26415E;
}

.attendance-buttons button:disabled{
    background:#ccc;
    cursor:not-allowed;
}

#work-hours{
    font-weight:800;
    color:#0D1E4C;
    margin-left:10px;
}

.attendance-filter-box{
    background:white;
    border-radius:24px;
    padding:22px;
    margin:25px 0;
    box-shadow:0 10px 30px rgba(15,23,42,0.06);
    border:1px solid #edf2f7;
}

.attendance-filter-box h2{
    color:#0D1E4C;
    margin-bottom:14px;
}

.filter-row{
    display:flex;
    gap:14px;
    align-items:center;
    flex-wrap:wrap;
}

.filter-input{
    height:50px;
    border-radius:16px;
    border:1px solid #dbe7f0;
    padding:0 15px;
    outline:none;
    font-size:14px;
    min-width:220px;
}

.clear-filter-btn{
    height:50px;
    border:none;
    border-radius:16px;
    background:#e2e8f0;
    color:#0D1E4C;
    padding:0 20px;
    font-weight:800;
    cursor:pointer;
}

.no-attendance-results{
    display:none;
    background:#fff7ed;
    color:#9a3412;
    border:1px solid #fed7aa;
    padding:16px 18px;
    border-radius:18px;
    font-weight:800;
    margin-top:18px;
}

.attendance-timeline{
    display:flex;
    flex-direction:column;
    gap:20px;
    margin-top:25px;
}

.attendance-card{
    background:white;
    border-radius:22px;
    padding:24px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    box-shadow:0 8px 25px rgba(0,0,0,0.05);
    transition:0.3s;
    border-left:8px solid #14b8a6;
}

.attendance-card:hover{
    transform:translateY(-4px);
}

.attendance-date h3{
    font-size:28px;
    color:#0D1E4C;
    margin-bottom:5px;
}

.attendance-date span{
    color:#6b7280;
    font-size:14px;
}

.attendance-info{
    width:75%;
}

.attendance-times{
    display:flex;
    justify-content:space-between;
    margin-bottom:18px;
    gap:18px;
}

.attendance-times p{
    font-size:13px;
    color:#6b7280;
    margin-bottom:5px;
}

.attendance-times h4{
    font-size:20px;
    color:#0D1E4C;
}

.progress-bar{
    width:100%;
    height:10px;
    background:#e5e7eb;
    border-radius:20px;
    overflow:hidden;
    margin-bottom:10px;
}

.progress-fill{
    height:100%;
    border-radius:20px;
}

.present-fill{
    width:95%;
    background:#22c55e;
}

.working-fill{
    width:55%;
    background:#f59e0b;
}

.attendance-progress{
    display:flex;
    align-items:center;
    gap:15px;
}

.empty-attendance{
    background:white;
    border-radius:22px;
    padding:24px;
    color:#64748b;
    font-weight:800;
    border:1px solid #e2e8f0;
}

@media(max-width:900px){
    .attendance-card{
        flex-direction:column;
        align-items:flex-start;
        gap:18px;
    }

    .attendance-info{
        width:100%;
    }

    .attendance-times{
        flex-direction:column;
    }

    .search-box{
        width:100%;
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
            <p>Work Tracking</p>
            <h4>Active</h4>
            <span>Start and end work saved</span>
        </div>
    </div>
</aside>

<main class="main-content">

<header class="topbar">
    <div class="topbar-left">
        <h1>My Attendance</h1>
        <p>Track your actual work time using Start Work and End Work.</p>
    </div>

    <div class="topbar-right">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input
                type="text"
                id="attendanceTextSearch"
                placeholder="Search status, day, time..."
                oninput="filterAttendance()"
            >
        </div>

        <div class="icon-btn notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notif-count">1</span>
        </div>

        <div class="admin-profile">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($full_name, 0, 1)); ?>
            </div>
            <div>
                <h4><?php echo htmlspecialchars($full_name); ?></h4>
                <span>Team Member</span>
            </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<?php if (!empty($message)) { ?>
    <div class="attendance-message <?php echo htmlspecialchars($messageType); ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php } ?>

<section class="attendance-buttons">
    <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="start_work">
        <button type="submit" <?php echo $activeWork ? 'disabled' : ''; ?>>
            Start Work
        </button>
    </form>

    <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="end_work">
        <button type="submit" <?php echo $activeWork ? '' : 'disabled'; ?>>
            End Work
        </button>
    </form>

    <span id="work-hours"
        data-active="<?php echo $activeWork ? '1' : '0'; ?>"
        data-start="<?php echo $activeWork ? htmlspecialchars($activeWork['work_date'] . ' ' . $activeWork['start_work']) : ''; ?>">
        <?php if ($activeWork) { ?>
            Working: calculating...
        <?php } else { ?>
            No active work session
        <?php } ?>
    </span>
</section>

<section class="cards">
    <div class="card">
        <div class="card-icon"><i class="fas fa-play"></i></div>
        <div class="card-info">
            <h3><?php echo $activeWork ? 'Active' : 'Idle'; ?></h3>
            <p>Work Status</p>
            <span>Today</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-business-time"></i></div>
        <div class="card-info">
            <h3><?php echo htmlspecialchars($totalTodayFormatted); ?></h3>
            <p>Total Today</p>
            <span>Completed sessions</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
            <h3><?php echo $activeWork ? date("h:i A", strtotime($activeWork['start_work'])) : '--'; ?></h3>
            <p>Start Time</p>
            <span>Current session</span>
        </div>
    </div>
</section>

<section class="attendance-filter-box">
    <h2>Filter Attendance</h2>

    <div class="filter-row">
        <input
            type="date"
            id="attendanceDatePicker"
            class="filter-input"
            onchange="filterAttendance()"
        >

        <button type="button" class="clear-filter-btn" onclick="clearAttendanceFilter()">
            Clear Filter
        </button>
    </div>

    <div id="noAttendanceResults" class="no-attendance-results">
        No attendance record found for this date or search.
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Work Attendance Activity</h2>
        <a href="#" onclick="clearAttendanceFilter(); return false;">View All</a>
    </div>

    <div class="attendance-timeline">

        <?php if ($attendanceRecords && mysqli_num_rows($attendanceRecords) > 0) { ?>
            <?php while ($record = mysqli_fetch_assoc($attendanceRecords)) { ?>
                <?php
                    $workDate = $record['work_date'];
                    $dateTitle = date("M d", strtotime($workDate));
                    $dayName = date("l", strtotime($workDate));
                    $startWork = !empty($record['start_work']) ? date("h:i:s A", strtotime($record['start_work'])) : "-";
                    $endWork = !empty($record['end_work']) ? date("h:i:s A", strtotime($record['end_work'])) : "-";
                    $totalHours = !empty($record['total_hours']) ? $record['total_hours'] : "Still working";
                    $status = $record['status'];
                    $statusClass = ($status === "Completed") ? "approved" : "pending";
                    $fillClass = ($status === "Completed") ? "present-fill" : "working-fill";
                ?>

                <div class="attendance-card attendance-record" data-date="<?php echo htmlspecialchars($workDate); ?>">
                    <div class="attendance-date">
                        <h3><?php echo htmlspecialchars($dateTitle); ?></h3>
                        <span><?php echo htmlspecialchars($dayName); ?></span>
                    </div>

                    <div class="attendance-info">
                        <div class="attendance-times">
                            <div>
                                <p>Start Work</p>
                                <h4><?php echo htmlspecialchars($startWork); ?></h4>
                            </div>

                            <div>
                                <p>End Work</p>
                                <h4><?php echo htmlspecialchars($endWork); ?></h4>
                            </div>

                            <div>
                                <p>Total Work Time</p>
                                <h4><?php echo htmlspecialchars($totalHours); ?></h4>
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
            <?php } ?>
        <?php } else { ?>
            <div class="empty-attendance">
                No work attendance records yet. Click Start Work to create your first record.
            </div>
        <?php } ?>

    </div>
</section>

</main>
</div>

<script>
function formatSeconds(totalSeconds) {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return `${String(hours).padStart(2, "0")}h ${String(minutes).padStart(2, "0")}m ${String(seconds).padStart(2, "0")}s`;
}

function startLiveTimer() {
    const timer = document.getElementById("work-hours");

    if (!timer || timer.dataset.active !== "1") {
        return;
    }

    const startString = timer.dataset.start.replace(" ", "T");
    const startTime = new Date(startString).getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const diff = Math.max(0, Math.floor((now - startTime) / 1000));
        timer.innerText = "Working: " + formatSeconds(diff);
    }

    updateTimer();
    setInterval(updateTimer, 1000);
}

function filterAttendance() {
    const selectedDate = document.getElementById("attendanceDatePicker").value;
    const searchValue = document.getElementById("attendanceTextSearch").value.toLowerCase().trim();
    const records = document.querySelectorAll(".attendance-record");
    const noResults = document.getElementById("noAttendanceResults");

    let found = false;

    records.forEach(function(record) {
        const recordDate = record.getAttribute("data-date");
        const recordText = record.innerText.toLowerCase();

        const dateMatch = selectedDate === "" || recordDate === selectedDate;
        const textMatch = searchValue === "" || recordText.includes(searchValue);

        if (dateMatch && textMatch) {
            record.style.display = "flex";
            found = true;
        } else {
            record.style.display = "none";
        }
    });

    if (noResults) {
        noResults.style.display = found ? "none" : "block";
    }
}

function clearAttendanceFilter() {
    document.getElementById("attendanceDatePicker").value = "";
    document.getElementById("attendanceTextSearch").value = "";
    filterAttendance();
}

document.addEventListener("DOMContentLoaded", startLiveTimer);
</script>

</body>
</html>