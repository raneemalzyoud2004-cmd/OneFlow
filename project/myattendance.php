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

if (isset($_POST['start_work'])) {
    $activeCheck = mysqli_query($conn, "
        SELECT id
        FROM work_attendance
        WHERE user_id = $user_id
        AND status = 'Working'
        ORDER BY id DESC
        LIMIT 1
    ");

    if ($activeCheck && mysqli_num_rows($activeCheck) > 0) {
        $error = "You already have an active work session.";
    } else {
        $now = date("H:i:s");

        $insert = mysqli_query($conn, "
            INSERT INTO work_attendance (user_id, work_date, start_work, status)
            VALUES ($user_id, '$today', '$now', 'Working')
        ");

        if ($insert) {
            header("Location: myattendance.php?success=started");
            exit();
        } else {
            $error = "Start work failed: " . mysqli_error($conn);
        }
    }
}

if (isset($_POST['end_work'])) {
    $active = mysqli_query($conn, "
        SELECT id, work_date, start_work
        FROM work_attendance
        WHERE user_id = $user_id
        AND status = 'Working'
        ORDER BY id DESC
        LIMIT 1
    ");

    if (!$active || mysqli_num_rows($active) == 0) {
        $error = "No active work session found.";
    } else {
        $row = mysqli_fetch_assoc($active);

        $now = date("H:i:s");
        $workDate = $row['work_date'];

        $startTimestamp = strtotime($workDate . " " . $row['start_work']);
        $endTimestamp = strtotime(date("Y-m-d") . " " . $now);

        $diff = max(0, $endTimestamp - $startTimestamp);

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
            header("Location: myattendance.php?success=ended");
            exit();
        } else {
            $error = "End work failed: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == 'started') {
    $message = "Work started successfully.";
}

if (isset($_GET['success']) && $_GET['success'] == 'ended') {
    $message = "Work ended successfully.";
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

$records = mysqli_query($conn, "
    SELECT *
    FROM work_attendance
    WHERE user_id = $user_id
    ORDER BY work_date DESC, id DESC
    LIMIT 20
");

$month = date("m");
$year = date("Y");

$totalSessions = 0;
$completedSessions = 0;
$totalTodaySeconds = 0;

$countResult = mysqli_query($conn, "
    SELECT 
        COUNT(*) AS total_sessions,
        SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) AS completed_sessions
    FROM work_attendance
    WHERE user_id = $user_id
    AND MONTH(work_date) = '$month'
    AND YEAR(work_date) = '$year'
");

if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $totalSessions = (int) $countRow['total_sessions'];
    $completedSessions = (int) $countRow['completed_sessions'];
}

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

$totalTodayFormatted = sprintf(
    "%02dh %02dm %02ds",
    floor($totalTodaySeconds / 3600),
    floor(($totalTodaySeconds % 3600) / 60),
    $totalTodaySeconds % 60
);

function formatTime($time) {
    if (empty($time)) {
        return "—";
    }
    return date("h:i:s A", strtotime($time));
}

function formatTotalHours($value) {
    if (empty($value)) {
        return "Still working";
    }
    return $value;
}

$canStartWork = !$activeWork;
$canEndWork = $activeWork;
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
    font-weight: 800;
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

.search-filter-box {
    background: white;
    padding: 20px;
    border-radius: 20px;
    margin: 20px 0;
    box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.search-filter-box input {
    padding: 12px 16px;
    border-radius: 12px;
    border: 1px solid #dbe7f0;
    outline: none;
}

.search-filter-box button {
    border: none;
    border-radius: 12px;
    background: #e2e8f0;
    color: #0D1E4C;
    padding: 12px 18px;
    font-weight: 800;
    cursor: pointer;
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

.completed-fill {
    width: 95%;
    background: #22c55e;
}

.working-fill {
    width: 55%;
    background: #f59e0b;
}

.attendance-progress {
    display: flex;
    align-items: center;
    gap: 15px;
}

.no-results {
    display: none;
    padding: 18px;
    background: #fff7ed;
    color: #9a3412;
    border-radius: 16px;
    font-weight: 800;
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
            <p>Work Tracking</p>
            <h4>Active</h4>
            <span>Start and End Work</span>
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

<?php if (!empty($message)): ?>
    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<section class="attendance-buttons">
    <form method="POST" style="display:inline;">
        <button type="submit" name="start_work" <?php echo !$canStartWork ? 'disabled' : ''; ?>>
            <i class="fas fa-play"></i> Start Work
        </button>
    </form>

    <form method="POST" style="display:inline;">
        <button type="submit" name="end_work" <?php echo !$canEndWork ? 'disabled' : ''; ?>>
            <i class="fas fa-stop"></i> End Work
        </button>
    </form>

    <span class="today-box" id="workTimer"
        data-active="<?php echo $activeWork ? '1' : '0'; ?>"
        data-start="<?php echo $activeWork ? htmlspecialchars($activeWork['work_date'] . ' ' . $activeWork['start_work']) : ''; ?>">
        <?php if ($activeWork): ?>
            Working: calculating...
        <?php else: ?>
            No active work session
        <?php endif; ?>
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
            <span>Completed work time</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-list-check"></i></div>
        <div class="card-info">
            <h3><?php echo $completedSessions; ?></h3>
            <p>Completed Sessions</p>
            <span>This month</span>
        </div>
    </div>
</section>

<section class="search-filter-box">
    <input type="text" id="attendanceSearch" placeholder="Search date, status, time..." oninput="filterAttendance()">
    <input type="date" id="attendanceDate" onchange="filterAttendance()">
    <button type="button" onclick="clearFilters()">Clear</button>
</section>

<div id="noResults" class="no-results">
    No attendance records found.
</div>

<section class="panel">
    <div class="panel-header">
        <h2>Work Attendance Activity</h2>
        <a href="myattendance.php">View All</a>
    </div>

    <div class="attendance-timeline">

        <?php if ($records && mysqli_num_rows($records) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($records)): ?>
                <?php
                    $dateText = date("M d", strtotime($row['work_date']));
                    $dayText = date("l", strtotime($row['work_date']));
                    $status = $row['status'];

                    $fillClass = ($status === "Completed") ? "completed-fill" : "working-fill";
                    $statusClass = ($status === "Completed") ? "approved" : "pending";
                    $borderColor = ($status === "Completed") ? "#14b8a6" : "#f59e0b";
                ?>

                <div class="attendance-card attendance-record"
                     data-date="<?php echo htmlspecialchars($row['work_date']); ?>"
                     style="border-left-color: <?php echo $borderColor; ?>;">

                    <div class="attendance-date">
                        <h3><?php echo htmlspecialchars($dateText); ?></h3>
                        <span><?php echo htmlspecialchars($dayText); ?></span>
                    </div>

                    <div class="attendance-info">
                        <div class="attendance-times">
                            <div>
                                <p>Start Work</p>
                                <h4><?php echo formatTime($row['start_work']); ?></h4>
                            </div>

                            <div>
                                <p>End Work</p>
                                <h4><?php echo formatTime($row['end_work']); ?></h4>
                            </div>

                            <div>
                                <p>Total Work Time</p>
                                <h4><?php echo formatTotalHours($row['total_hours']); ?></h4>
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
            <p>No work attendance records found.</p>
        <?php endif; ?>

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
    const timer = document.getElementById("workTimer");

    if (!timer || timer.dataset.active !== "1") {
        return;
    }

    const startTime = new Date(timer.dataset.start.replace(" ", "T")).getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const diff = Math.max(0, Math.floor((now - startTime) / 1000));
        timer.innerText = "Working: " + formatSeconds(diff);
    }

    updateTimer();
    setInterval(updateTimer, 1000);
}

function filterAttendance() {
    const searchValue = document.getElementById("attendanceSearch").value.toLowerCase().trim();
    const selectedDate = document.getElementById("attendanceDate").value;
    const records = document.querySelectorAll(".attendance-record");
    const noResults = document.getElementById("noResults");

    let found = false;

    records.forEach(function(record) {
        const text = record.innerText.toLowerCase();
        const date = record.getAttribute("data-date");

        const textMatch = searchValue === "" || text.includes(searchValue);
        const dateMatch = selectedDate === "" || date === selectedDate;

        if (textMatch && dateMatch) {
            record.style.display = "flex";
            found = true;
        } else {
            record.style.display = "none";
        }
    });

    noResults.style.display = found ? "none" : "block";
}

function clearFilters() {
    document.getElementById("attendanceSearch").value = "";
    document.getElementById("attendanceDate").value = "";
    filterAttendance();
}

document.addEventListener("DOMContentLoaded", startLiveTimer);
</script>

</body>
</html>