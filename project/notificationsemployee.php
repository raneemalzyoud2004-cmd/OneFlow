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

$user_id = (int) $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$currentYear = date("Y");

$notificationsList = [];

$notificationsResult = mysqli_query($conn, "
    SELECT title, message, type, created_at
    FROM notifications
    WHERE user_id = $user_id
    ORDER BY id DESC
");

if ($notificationsResult && mysqli_num_rows($notificationsResult) > 0) {
    while ($row = mysqli_fetch_assoc($notificationsResult)) {
        $notificationsList[] = $row;
    }
}

$birthdayResult = mysqli_query($conn, "
    SELECT birthday_message AS message, reward_amount, created_at
    FROM birthday_rewards
    WHERE user_id = $user_id
    AND reward_year = $currentYear
    AND birthday_message IS NOT NULL
    AND birthday_message != ''
    ORDER BY id DESC
    LIMIT 1
");

if ($birthdayResult && mysqli_num_rows($birthdayResult) > 0) {
    $birthday = mysqli_fetch_assoc($birthdayResult);
    $notificationsList[] = [
        'title' => 'Birthday Message',
        'message' => $birthday['message'] . ' Gift: $' . number_format((float)$birthday['reward_amount'], 2),
        'type' => 'success',
        'created_at' => $birthday['created_at']
    ];
}

usort($notificationsList, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$notificationCount = count($notificationsList);
$profileInitial = strtoupper(substr($full_name, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Employee Notifications - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.notification-list{
    display:flex;
    flex-direction:column;
    gap:16px;
}

.notification-item{
    display:flex;
    align-items:flex-start;
    gap:16px;
    padding:18px;
    border-radius:20px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    transition:0.3s ease;
}

.notification-item:hover{
    transform:translateY(-3px);
    box-shadow:0 14px 28px rgba(15,23,42,0.08);
}

.notif-icon{
    width:46px;
    height:46px;
    border-radius:16px;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
    background:linear-gradient(135deg,#0ea5a4,#14b8a6);
}

.notif-icon.success{
    background:linear-gradient(135deg,#16a34a,#22c55e);
}

.notif-icon.danger{
    background:linear-gradient(135deg,#dc2626,#fb7185);
}

.notif-icon.warning{
    background:linear-gradient(135deg,#f97316,#facc15);
}

.notif-icon.info{
    background:linear-gradient(135deg,#2563eb,#60a5fa);
}

.notification-content{
    flex:1;
}

.notification-content h4{
    color:#0D1E4C;
    font-size:17px;
    margin-bottom:6px;
}

.notification-content p{
    color:#64748b;
    font-size:14px;
    line-height:1.6;
}

.notification-time{
    display:block;
    margin-top:8px;
    color:#94a3b8;
    font-size:12px;
    font-weight:700;
}

.empty-state{
    text-align:center;
    background:white;
    border-radius:24px;
    padding:45px 25px;
    border:1px solid #e2e8f0;
}

.empty-state i{
    font-size:48px;
    color:#cbd5e1;
    margin-bottom:14px;
}

.empty-state h3{
    color:#0D1E4C;
    margin-bottom:8px;
}

.empty-state p{
    color:#64748b;
}

.notification-bell{
    text-decoration:none;
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
        <li><a href="myschedule.php"><i class="fas fa-clock"></i> Schedule</a></li>
        <li class="active"><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
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
        <h1>Notifications</h1>
        <p>See your latest updates, reminders, and employee alerts.</p>
    </div>

    <div class="topbar-right">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="notificationSearch" placeholder="Search notifications...">
        </div>

        <a href="notificationsemployee.php" class="icon-btn notification-bell">
            <i class="fas fa-bell"></i>
            <?php if ($notificationCount > 0) { ?>
                <span class="notif-count"><?php echo $notificationCount; ?></span>
            <?php } ?>
        </a>

        <div class="admin-profile">
            <div class="admin-avatar"><?php echo $profileInitial; ?></div>
            <div>
                <h4><?php echo htmlspecialchars($full_name); ?></h4>
                <span>Team Member</span>
            </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<section class="panel">
    <div class="panel-header">
        <h2>Recent Notifications</h2>
    </div>

    <?php if (!empty($notificationsList)) { ?>
        <div class="notification-list" id="notificationList">
            <?php foreach ($notificationsList as $notification) { ?>
                <?php
                    $typeClass = "info";
                    $iconClass = "fa-bell";

                    if ($notification['type'] == "success") {
                        $typeClass = "success";
                        $iconClass = "fa-check-circle";
                    } elseif ($notification['type'] == "danger") {
                        $typeClass = "danger";
                        $iconClass = "fa-circle-xmark";
                    } elseif ($notification['type'] == "warning") {
                        $typeClass = "warning";
                        $iconClass = "fa-triangle-exclamation";
                    }
                ?>

                <div class="notification-item">
                    <div class="notif-icon <?php echo $typeClass; ?>">
                        <i class="fas <?php echo $iconClass; ?>"></i>
                    </div>

                    <div class="notification-content">
                        <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <span class="notification-time">
                            <?php echo htmlspecialchars($notification['created_at']); ?>
                        </span>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h3>No Notifications Yet</h3>
            <p>Your latest updates and HR responses will appear here.</p>
        </div>
    <?php } ?>
</section>

</main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("notificationSearch");
    const items = document.querySelectorAll(".notification-item");

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            const value = this.value.toLowerCase().trim();

            items.forEach(function(item) {
                const text = item.innerText.toLowerCase();
                item.style.display = text.includes(value) ? "flex" : "none";
            });
        });
    }
});
</script>

</body>
</html>