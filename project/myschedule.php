<?php
session_start();
include "config.php";

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$errorMessage = "";

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS schedule (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        type VARCHAR(50) NOT NULL,
        start_time DATETIME NOT NULL,
        end_time DATETIME NOT NULL,
        status VARCHAR(30) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$today = date("Y-m-d");
$weekStart = date("Y-m-d 00:00:00", strtotime("monday this week"));
$weekEnd = date("Y-m-d 23:59:59", strtotime("sunday this week"));

if (isset($_POST['add_schedule'])) {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $type = mysqli_real_escape_string($conn, trim($_POST['type']));
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);

    if (strtotime($end_time) <= strtotime($start_time)) {
        $errorMessage = "End time must be after start time.";
    } else {
        mysqli_query($conn, "
            INSERT INTO schedule (user_id, title, type, start_time, end_time, status)
            VALUES ('$user_id', '$title', '$type', '$start_time', '$end_time', 'Pending')
        ");

        header("Location: myschedule.php");
        exit();
    }
}

if (isset($_POST['update_schedule'])) {
    $id = intval($_POST['schedule_id']);
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $type = mysqli_real_escape_string($conn, trim($_POST['type']));
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);

    if (strtotime($end_time) <= strtotime($start_time)) {
        $errorMessage = "End time must be after start time.";
    } else {
        mysqli_query($conn, "
            UPDATE schedule
            SET title='$title',
                type='$type',
                start_time='$start_time',
                end_time='$end_time'
            WHERE id='$id'
            AND user_id='$user_id'
        ");

        header("Location: myschedule.php");
        exit();
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    mysqli_query($conn, "
        DELETE FROM schedule
        WHERE id='$id'
        AND user_id='$user_id'
    ");

    header("Location: myschedule.php");
    exit();
}

if (isset($_GET['done'])) {
    $id = intval($_GET['done']);

    mysqli_query($conn, "
        UPDATE schedule
        SET status='Done'
        WHERE id='$id'
        AND user_id='$user_id'
    ");

    header("Location: myschedule.php");
    exit();
}

$meetingsToday = 0;
$workSessions = 0;
$completedToday = 0;
$upcomingToday = 0;
$nextMeeting = "No Meeting";

$result = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM schedule
    WHERE user_id='$user_id'
    AND type='Meeting'
    AND status!='Done'
    AND DATE(start_time)='$today'
");
if ($result) $meetingsToday = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM schedule
    WHERE user_id='$user_id'
    AND TRIM(LOWER(type)) IN ('sessions', 'session')
    AND start_time BETWEEN '$weekStart' AND '$weekEnd'
");
if ($result) $workSessions = mysqli_fetch_assoc($result)['total'];
$result = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM schedule
    WHERE user_id='$user_id'
    AND status='Done'
    AND DATE(start_time)='$today'
");
if ($result) $completedToday = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM schedule
    WHERE user_id='$user_id'
    AND status!='Done'
    AND start_time >= NOW()
    AND DATE(start_time)='$today'
");
if ($result) $upcomingToday = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "
    SELECT start_time
    FROM schedule
    WHERE user_id='$user_id'
    AND type='Meeting'
    AND status!='Done'
    AND start_time >= NOW()
    ORDER BY start_time ASC
    LIMIT 1
");
if ($result && mysqli_num_rows($result) > 0) {
    $nextMeetingRow = mysqli_fetch_assoc($result);
    $nextMeeting = date("h:i A", strtotime($nextMeetingRow['start_time']));
}

$scheduleQuery = mysqli_query($conn, "
    SELECT *
    FROM schedule
    WHERE user_id='$user_id'
    ORDER BY start_time ASC
");

$todayScheduleQuery = mysqli_query($conn, "
    SELECT *
    FROM schedule
    WHERE user_id='$user_id'
    AND DATE(start_time)='$today'
    ORDER BY start_time ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Schedule - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.schedule-message {
    padding: 14px 18px;
    border-radius: 16px;
    margin-bottom: 18px;
    font-weight: 800;
    background: #fee2e2;
    color: #991b1b;
}

.schedule-hero {
    background: linear-gradient(135deg, #0D1E4C, #14b8a6);
    color: white;
    border-radius: 28px;
    padding: 30px;
    margin-bottom: 26px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.schedule-hero h2 {
    font-size: 32px;
    margin-bottom: 10px;
}

.schedule-hero p {
    opacity: 0.92;
    line-height: 1.7;
}

.schedule-hero-icon {
    width: 88px;
    height: 88px;
    border-radius: 28px;
    background: rgba(255,255,255,0.16);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    flex-shrink: 0;
}

.schedule-grid {
    display: grid;
    grid-template-columns: 1.25fr 1fr;
    gap: 24px;
    margin-top: 28px;
}

.schedule-column {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.today-timeline {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.timeline-item {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    padding: 16px;
    border-radius: 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.timeline-icon {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    background: linear-gradient(135deg, #0ea5a4, #14b8a6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.timeline-item h4 {
    color: #0D1E4C;
    margin-bottom: 5px;
}

.timeline-item p {
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
}

.type-pill {
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    display: inline-block;
}

.type-meeting { background: #dbeafe; color: #1d4ed8; }
.type-work { background: #dcfce7; color: #166534; }
.type-sessions { background: #cffafe; color: #0f766e; }
.type-training { background: #f3e8ff; color: #7e22ce; }
.type-break { background: #ffedd5; color: #c2410c; }

.action-btns {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.small-btn {
    border: none;
    padding: 8px 12px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
    color: white;
    text-decoration: none;
    display: inline-block;
}

.edit-btn { background: #0D1E4C; }
.delete-btn { background: #dc2626; }
.done-btn { background: #14b8a6; }

.add-btn {
    background: #14b8a6;
    color: white;
    border: none;
    padding: 12px 18px;
    border-radius: 14px;
    cursor: pointer;
    font-weight: 800;
}

.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-content {
    width: 500px;
    max-width: 100%;
    background: white;
    padding: 30px;
    border-radius: 25px;
    box-shadow: 0 25px 70px rgba(0,0,0,0.25);
}

.modal-content h2 {
    color: #0D1E4C;
    margin-bottom: 18px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    font-weight: 700;
    color: #0D1E4C;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #dbe7f0;
    outline: none;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.save-btn { background: #14b8a6; }
.close-btn { background: #6b7280; }

.schedule-empty {
    text-align: center;
    color: #64748b;
    font-weight: 700;
    padding: 22px;
}

.schedule-filter-row {
    display: flex;
    gap: 12px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}

.filter-chip {
    border: none;
    padding: 10px 14px;
    border-radius: 999px;
    font-weight: 800;
    cursor: pointer;
    background: #e2e8f0;
    color: #0D1E4C;
}

.filter-chip.active {
    background: linear-gradient(90deg,#0ea5a4,#14b8a6);
    color: white;
}

@media(max-width: 1100px) {
    .schedule-grid {
        grid-template-columns: 1fr;
    }

    .schedule-hero {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media(max-width: 800px) {
    .action-btns {
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
        <li><a href="myattendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
        <li class="active"><a href="myschedule.php"><i class="fas fa-clock"></i> Schedule</a></li>
        <li><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
        <li><a href="settingsemployee.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
        <div class="system-card">
            <p>Schedule Status</p>
            <h4>Organized</h4>
            <span>Plan your week clearly</span>
        </div>
    </div>

</aside>

<main class="main-content">

<header class="topbar">
    <div class="topbar-left">
        <h1>My Schedule</h1>
        <p>Stay updated with your meetings, sessions, work plan, and important times.</p>
    </div>

    <div class="topbar-right">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="scheduleSearch" placeholder="Search schedule...">
        </div>

        <div class="icon-btn notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notif-count"><?php echo $upcomingToday; ?></span>
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

<?php if (!empty($errorMessage)) { ?>
    <div class="schedule-message"><?php echo htmlspecialchars($errorMessage); ?></div>
<?php } ?>

<section class="schedule-hero searchable-item">
    <div>
        <h2>Today’s Schedule Planner ⏰</h2>
        <p>
            You have <strong><?php echo $meetingsToday; ?></strong> meeting(s) today,
            <strong><?php echo $workSessions; ?></strong> session(s) this week,
            and your next meeting is at <strong><?php echo $nextMeeting; ?></strong>.
        </p>
    </div>

    <div class="schedule-hero-icon">
        <i class="fas fa-calendar-days"></i>
    </div>
</section>

<section class="cards">
    <div class="card searchable-item">
        <div class="card-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="card-info">
            <h3><?php echo $meetingsToday; ?></h3>
            <p>Meetings Today</p>
            <span>Planned for today</span>
        </div>
    </div>

   

    <div class="card searchable-item">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
            <h3><?php echo $nextMeeting; ?></h3>
            <p>Next Meeting</p>
            <span>Upcoming meeting</span>
        </div>
    </div>

    <div class="card searchable-item">
        <div class="card-icon"><i class="fas fa-circle-check"></i></div>
        <div class="card-info">
            <h3><?php echo $completedToday; ?></h3>
            <p>Completed Today</p>
            <span>Marked as done</span>
        </div>
    </div>
</section>

<section class="schedule-grid">

    <div class="schedule-column">

        <section class="panel">
            <div class="panel-header">
                <h2>My Schedule List</h2>

                <button class="add-btn" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Schedule
                </button>
            </div>

            <div class="schedule-filter-row">
                <button class="filter-chip active" onclick="filterSchedule('all', this)">All</button>
                <button class="filter-chip" onclick="filterSchedule('Meeting', this)">Meetings</button>
                <button class="filter-chip" onclick="filterSchedule('Work', this)">Work</button>
             <button class="filter-chip" onclick="filterSchedule('Sessions', this)">Sessions</button>
<button class="filter-chip" onclick="filterSchedule('Session', this)" style="display:none;">Session</button>
                <button class="filter-chip" onclick="filterSchedule('Training', this)">Training</button>
                <button class="filter-chip" onclick="filterSchedule('Break', this)">Break</button>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Activity</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($scheduleQuery && mysqli_num_rows($scheduleQuery) > 0) { ?>
                        <?php while ($row = mysqli_fetch_assoc($scheduleQuery)) { ?>
                            <?php
                            $now = time();
                            $start = strtotime($row['start_time']);
                            $end = strtotime($row['end_time']);

                            if ($row['status'] == 'Done') {
                                $statusText = "Done";
                                $statusClass = "approved";
                            } elseif ($now >= $start && $now <= $end) {
                                $statusText = "Ongoing";
                                $statusClass = "pending";
                            } elseif ($now > $end) {
                                $statusText = "Missed";
                                $statusClass = "rejected";
                            } else {
                                $statusText = "Upcoming";
                                $statusClass = "pending";
                            }

                          $typeClass = "type-" . strtolower(trim($row['type']));
if ($typeClass === "type-session") {
    $typeClass = "type-sessions";
}
                            $editTitle = htmlspecialchars($row['title'], ENT_QUOTES);
                            $editType = htmlspecialchars($row['type'], ENT_QUOTES);
                            $editStart = date('Y-m-d\TH:i', strtotime($row['start_time']));
                            $editEnd = date('Y-m-d\TH:i', strtotime($row['end_time']));
                            ?>

                            <tr class="schedule-row searchable-item" data-type="<?php echo htmlspecialchars($row['type']); ?>">
                                <td><?php echo date("M d, h:i A", strtotime($row['start_time'])); ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td>
                                    <span class="type-pill <?php echo $typeClass; ?>">
                                        <?php echo htmlspecialchars($row['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button type="button" class="small-btn edit-btn"
                                            onclick="openEditModal(
                                                '<?php echo $row['id']; ?>',
                                                '<?php echo $editTitle; ?>',
                                                '<?php echo $editType; ?>',
                                                '<?php echo $editStart; ?>',
                                                '<?php echo $editEnd; ?>'
                                            )">
                                            Edit
                                        </button>

                                        <?php if ($row['status'] != 'Done') { ?>
                                            <a class="small-btn done-btn" href="myschedule.php?done=<?php echo $row['id']; ?>">
                                                Done
                                            </a>
                                        <?php } ?>

                                        <a class="small-btn delete-btn"
                                           href="myschedule.php?delete=<?php echo $row['id']; ?>"
                                           onclick="return confirm('Delete this schedule item?');">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="5" class="schedule-empty">
                                No schedule items yet. Add your first schedule.
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <div class="schedule-column">

        <section class="panel">
            <div class="panel-header">
                <h2>Today Timeline</h2>
            </div>

            <div class="today-timeline">
                <?php if ($todayScheduleQuery && mysqli_num_rows($todayScheduleQuery) > 0) { ?>
                    <?php while ($todayRow = mysqli_fetch_assoc($todayScheduleQuery)) { ?>
                        <div class="timeline-item searchable-item">
                            <div class="timeline-icon">
                                <i class="fas fa-clock"></i>
                            </div>

                            <div>
                                <h4><?php echo htmlspecialchars($todayRow['title']); ?></h4>
                                <p>
                                    <?php echo date("h:i A", strtotime($todayRow['start_time'])); ?>
                                    -
                                    <?php echo date("h:i A", strtotime($todayRow['end_time'])); ?>
                                    · <?php echo htmlspecialchars($todayRow['type']); ?>
                                </p>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h4>No schedule for today</h4>
                            <p>Your day is currently clear.</p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <h2>Schedule Tips</h2>
            </div>

            <div class="timeline-item searchable-item">
                <div class="timeline-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div>
                    <h4>Keep your schedule updated</h4>
                    <p>Mark completed items as Done so your weekly planning stays accurate.</p>
                </div>
            </div>
        </section>

    </div>

</section>

</main>
</div>

<div class="modal" id="addModal">
    <div class="modal-content">
        <h2>Add Schedule</h2>

        <form method="POST">
            <div class="form-group">
                <label>Activity Title</label>
                <input type="text" name="title" required>
            </div>

            <div class="form-group">
                <label>Type</label>
                <select name="type" required>
                    <option value="Meeting">Meeting</option>
                    <option value="Work">Work</option>
                    <option value="Sessions">Sessions</option>
                    <option value="Training">Training</option>
                    <option value="Break">Break</option>
                </select>
            </div>

            <div class="form-group">
                <label>Start Time</label>
                <input type="datetime-local" name="start_time" required>
            </div>

            <div class="form-group">
                <label>End Time</label>
                <input type="datetime-local" name="end_time" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="small-btn close-btn" onclick="closeAddModal()">Cancel</button>
                <button type="submit" name="add_schedule" class="small-btn save-btn">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="editModal">
    <div class="modal-content">
        <h2>Edit Schedule</h2>

        <form method="POST">
            <input type="hidden" name="schedule_id" id="edit_id">

            <div class="form-group">
                <label>Activity Title</label>
                <input type="text" name="title" id="edit_title" required>
            </div>

            <div class="form-group">
                <label>Type</label>
                <select name="type" id="edit_type" required>
                    <option value="Meeting">Meeting</option>
                    <option value="Work">Work</option>
                    <option value="Sessions">Sessions</option>
                    <option value="Training">Training</option>
                    <option value="Break">Break</option>
                </select>
            </div>

            <div class="form-group">
                <label>Start Time</label>
                <input type="datetime-local" name="start_time" id="edit_start" required>
            </div>

            <div class="form-group">
                <label>End Time</label>
                <input type="datetime-local" name="end_time" id="edit_end" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="small-btn close-btn" onclick="closeEditModal()">Cancel</button>
                <button type="submit" name="update_schedule" class="small-btn save-btn">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById("addModal").style.display = "flex";
}

function closeAddModal() {
    document.getElementById("addModal").style.display = "none";
}

function openEditModal(id, title, type, start, end) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_title").value = title;
    document.getElementById("edit_type").value = type;
    document.getElementById("edit_start").value = start;
    document.getElementById("edit_end").value = end;

    document.getElementById("editModal").style.display = "flex";
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}

window.onclick = function(event) {
    const addModal = document.getElementById("addModal");
    const editModal = document.getElementById("editModal");

    if (event.target === addModal) {
        closeAddModal();
    }

    if (event.target === editModal) {
        closeEditModal();
    }
}

function filterSchedule(type, button) {
    const rows = document.querySelectorAll(".schedule-row");
    const buttons = document.querySelectorAll(".filter-chip");

    buttons.forEach(btn => btn.classList.remove("active"));
    button.classList.add("active");

    rows.forEach(row => {
        if (type === "all" || row.dataset.type === type) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("scheduleSearch");
    const searchableItems = document.querySelectorAll(".searchable-item");

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            const value = this.value.toLowerCase().trim();

            searchableItems.forEach(function(item) {
                const text = item.innerText.toLowerCase();
                item.style.display = text.includes(value) ? "" : "none";
            });
        });
    }
});
</script>

</body>
</html>