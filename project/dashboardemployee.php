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

$full_name = $_SESSION['full_name'];
$user_id = (int) $_SESSION['user_id'];
$currentYear = date("Y");

$birthdayNotification = null;

$birthdayResult = mysqli_query($conn, "
    SELECT reward_amount, birthday_message, created_at
    FROM birthday_rewards
    WHERE user_id = $user_id
    AND reward_year = $currentYear
    AND birthday_message IS NOT NULL
    AND birthday_message != ''
    ORDER BY id DESC
    LIMIT 1
");

if ($birthdayResult && mysqli_num_rows($birthdayResult) > 0) {
    $birthdayNotification = mysqli_fetch_assoc($birthdayResult);
}

$notificationCount = $birthdayNotification ? 1 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Employee Dashboard - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.birthday-employee-banner{
    background:linear-gradient(135deg,#f97316,#ec4899);
    color:white;
    padding:26px 30px;
    border-radius:28px;
    margin-bottom:26px;
    box-shadow:0 18px 40px rgba(236,72,153,0.25);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:24px;
}

.birthday-employee-banner h2{
    font-size:30px;
    margin-bottom:10px;
}

.birthday-employee-banner p{
    line-height:1.7;
    font-size:16px;
}

.birthday-gift-box{
    margin-top:14px;
    display:inline-flex;
    align-items:center;
    gap:10px;
    padding:10px 16px;
    border-radius:999px;
    background:rgba(255,255,255,0.22);
    font-weight:900;
}

.birthday-gift-icon{
    width:82px;
    height:82px;
    border-radius:26px;
    background:rgba(255,255,255,0.2);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
    flex-shrink:0;
}

.employee-dashboard-grid{
    display:grid;
    grid-template-columns:1.25fr 1fr;
    gap:24px;
    margin-top:28px;
}

.employee-column{
    display:flex;
    flex-direction:column;
    gap:24px;
}

.employee-action-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
}

.employee-action-card{
    background:linear-gradient(135deg,#f8fbff,#eef8f8);
    border:1px solid #e3eef2;
    border-radius:22px;
    padding:22px;
    text-decoration:none;
    transition:0.3s;
}

.employee-action-card:hover{
    transform:translateY(-6px);
    box-shadow:0 18px 35px rgba(20,184,166,0.16);
}

.employee-action-card i{
    width:54px;
    height:54px;
    border-radius:16px;
    background:linear-gradient(135deg,#16c7c1,#22c55e);
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:20px;
    margin-bottom:16px;
}

.employee-action-card.purple i{
    background:linear-gradient(135deg,#9333ea,#c084fc);
}

.employee-action-card.blue i{
    background:linear-gradient(135deg,#2563eb,#60a5fa);
}

.employee-action-card.orange i{
    background:linear-gradient(135deg,#f97316,#ec4899);
}

.employee-action-card h4{
    color:#0D1E4C;
    font-size:19px;
    margin-bottom:8px;
}

.employee-action-card p{
    color:#64748b;
    font-size:14px;
    line-height:1.6;
}

.employee-note-card{
    background:linear-gradient(135deg,#0D1E4C,#14b8a6);
    color:white;
    border-radius:24px;
    padding:24px;
}

.employee-note-card h2{
    font-size:24px;
    margin-bottom:10px;
}

.employee-note-card p{
    line-height:1.7;
    opacity:0.92;
}

.employee-timeline{
    display:flex;
    flex-direction:column;
    gap:16px;
}

.employee-timeline-item{
    display:flex;
    gap:14px;
    align-items:flex-start;
    padding:16px;
    border-radius:20px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
}

.timeline-icon{
    width:44px;
    height:44px;
    border-radius:14px;
    background:linear-gradient(135deg,#0ea5a4,#14b8a6);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
}

.employee-timeline-item h4{
    color:#0D1E4C;
    margin-bottom:5px;
}

.employee-timeline-item p{
    color:#64748b;
    font-size:13px;
    line-height:1.5;
}

.notification-wrapper{
    position:relative;
}

.employee-notification-dropdown{
    display:none;
    position:absolute;
    top:62px;
    right:0;
    width:330px;
    background:white;
    border-radius:18px;
    padding:16px;
    box-shadow:0 20px 45px rgba(15,23,42,0.16);
    border:1px solid #e5eef5;
    z-index:9999;
}

.employee-notification-dropdown h3{
    font-size:17px;
    color:#0D1E4C;
    margin-bottom:12px;
}

.notification-item{
    padding:14px;
    border-radius:14px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
}

.notification-item h4{
    color:#0D1E4C;
    margin-bottom:6px;
}

.notification-item p{
    color:#64748b;
    font-size:13px;
    line-height:1.5;
}

@media(max-width:1100px){
    .employee-dashboard-grid{
        grid-template-columns:1fr;
    }

    .employee-action-grid{
        grid-template-columns:1fr;
    }

    .birthday-employee-banner{
        align-items:flex-start;
        flex-direction:column;
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
        <li class="active"><a href="dashboardemployee.php"><i class="fas fa-house"></i> Dashboard</a></li>
        <li><a href="mytasks.php"><i class="fas fa-list-check"></i> My Tasks</a></li>
        <li><a href="leaverequests_employee.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
        <li><a href="myattendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
        <li><a href="myschedule.php"><i class="fas fa-clock"></i> Schedule</a></li>
        <li><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
        <li><a href="settingsemployee.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
        <div class="system-card">
            <p>System Status</p>
            <h4>Online</h4>
            <span>All services running</span>
        </div>
    </div>
</aside>

<main class="main-content">

<header class="topbar">
    <div class="topbar-left">
        <h1>Employee Dashboard</h1>
        <p>Track your tasks, attendance, schedule, and personal notifications.</p>
    </div>

    <div class="topbar-right">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="employeeSearch" placeholder="Search...">
        </div>

        <div class="notification-wrapper">
            <div class="icon-btn notification-bell" onclick="toggleEmployeeNotifications()">
                <i class="fas fa-bell"></i>
                <?php if ($notificationCount > 0) { ?>
                    <span class="notif-count"><?php echo $notificationCount; ?></span>
                <?php } ?>
            </div>

            <div class="employee-notification-dropdown" id="employeeNotificationDropdown">
                <h3>Notifications</h3>

                <?php if ($birthdayNotification) { ?>
                    <div class="notification-item">
                        <h4>🎉 Birthday Message</h4>
                        <p><?php echo htmlspecialchars($birthdayNotification['birthday_message']); ?></p>
                        <p><strong>Gift:</strong> $<?php echo number_format((float)$birthdayNotification['reward_amount'], 2); ?></p>
                    </div>
                <?php } else { ?>
                    <div class="notification-item">
                        <h4>No new updates</h4>
                        <p>Your latest notifications will appear here.</p>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="admin-profile">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($full_name, 0, 1)); ?>
            </div>
            <div>
                <h4><?php echo htmlspecialchars($full_name); ?></h4>
                <span>Employee</span>
            </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<?php if ($birthdayNotification) { ?>
<section class="birthday-employee-banner searchable-item">
    <div>
        <h2>🎉 Happy Birthday, <?php echo htmlspecialchars($full_name); ?>!</h2>
        <p><?php echo htmlspecialchars($birthdayNotification['birthday_message']); ?></p>

        <div class="birthday-gift-box">
            <i class="fas fa-gift"></i>
            Birthday Gift: $<?php echo number_format((float)$birthdayNotification['reward_amount'], 2); ?>
        </div>
    </div>

    <div class="birthday-gift-icon">
        🎁
    </div>
</section>
<?php } ?>

<section class="hero-banner searchable-item">
    <div class="hero-text">
        <h2>Welcome back, <?php echo htmlspecialchars($full_name); ?> 👋</h2>
        <p>You have <strong>3 tasks</strong> to complete and <strong>1 meeting</strong> today.</p>
    </div>

    <div class="hero-actions">
        <a href="mytasks.php" class="hero-btn primary-btn">
            <i class="fas fa-list-check"></i> View Tasks
        </a>

        <a href="myschedule.php" class="hero-btn secondary-btn">
            <i class="fas fa-calendar"></i> View Schedule
        </a>
    </div>
</section>

<section class="cards">
    <div class="card searchable-item">
        <div class="card-icon"><i class="fas fa-list-check"></i></div>
        <div class="card-info">
            <h3>3</h3>
            <p>My Tasks</p>
            <span>Assigned work items</span>
        </div>
    </div>

    <div class="card searchable-item">
        <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="card-info">
            <h3>95%</h3>
            <p>Attendance</p>
            <span>This month</span>
        </div>
    </div>

    <div class="card searchable-item">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
            <h3>1</h3>
            <p>Upcoming Meetings</p>
            <span>Scheduled today</span>
        </div>
    </div>

    <div class="card searchable-item">
        <div class="card-icon"><i class="fas fa-bell"></i></div>
        <div class="card-info">
            <h3><?php echo $notificationCount; ?></h3>
            <p>Notifications</p>
            <span>Personal updates</span>
        </div>
    </div>
</section>

<section class="employee-dashboard-grid">
    <div class="employee-column">
        <div class="panel">
            <div class="panel-header">
                <h2>Quick Access</h2>
            </div>

            <div class="employee-action-grid">
                <a href="mytasks.php" class="employee-action-card searchable-item">
                    <i class="fas fa-list-check"></i>
                    <h4>My Tasks</h4>
                    <p>Review assigned tasks and track your work progress.</p>
                </a>

                <a href="leaverequests_employee.php" class="employee-action-card purple searchable-item">
                    <i class="fas fa-file-circle-check"></i>
                    <h4>Leave Requests</h4>
                    <p>Submit leave requests and follow request status.</p>
                </a>

                <a href="myattendance.php" class="employee-action-card blue searchable-item">
                    <i class="fas fa-calendar-check"></i>
                    <h4>Attendance</h4>
                    <p>View your check-in, check-out, and attendance history.</p>
                </a>

                <a href="report_issue.php" class="employee-action-card orange searchable-item">
                    <i class="fas fa-headset"></i>
                    <h4>Report Issue</h4>
                    <p>Send technical issues directly to IT Support.</p>
                </a>
            </div>
        </div>
    </div>

    <div class="employee-column">
        <div class="employee-note-card searchable-item">
            <h2>Today’s Focus ✨</h2>
            <p>
                Keep your task updates clear, check your attendance regularly,
                and report any technical issue as soon as it appears.
            </p>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>My Activity</h2>
            </div>

            <div class="employee-timeline">
                <div class="employee-timeline-item searchable-item">
                    <div class="timeline-icon"><i class="fas fa-user-check"></i></div>
                    <div>
                        <h4>Dashboard opened</h4>
                        <p>Your employee session is active and protected.</p>
                    </div>
                </div>

                <div class="employee-timeline-item searchable-item">
                    <div class="timeline-icon"><i class="fas fa-list-check"></i></div>
                    <div>
                        <h4>Tasks summary ready</h4>
                        <p>You can review your assigned tasks from the quick access panel.</p>
                    </div>
                </div>

                <?php if ($birthdayNotification) { ?>
                <div class="employee-timeline-item searchable-item">
                    <div class="timeline-icon"><i class="fas fa-cake-candles"></i></div>
                    <div>
                        <h4>Birthday gift received</h4>
                        <p>
                            HR sent you a birthday message and a gift of
                            $<?php echo number_format((float)$birthdayNotification['reward_amount'], 2); ?>.
                        </p>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

</main>
</div>

<script>
function toggleEmployeeNotifications() {
    const dropdown = document.getElementById("employeeNotificationDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function(e) {
    const dropdown = document.getElementById("employeeNotificationDropdown");
    const bell = document.querySelector(".notification-bell");

    if (dropdown && bell && !dropdown.contains(e.target) && !bell.contains(e.target)) {
        dropdown.style.display = "none";
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("employeeSearch");
    const items = document.querySelectorAll(".searchable-item");

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            const value = this.value.toLowerCase().trim();

            items.forEach(function(item) {
                const text = item.innerText.toLowerCase();
                item.style.display = text.includes(value) ? "" : "none";
            });
        });
    }
});
</script>

</body>
</html>