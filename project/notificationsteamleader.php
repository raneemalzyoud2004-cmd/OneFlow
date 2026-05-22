<?php
session_start();
include "config.php";

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teamleader') {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Team Leader';

if (isset($_POST['mark_all_read'])) {
    mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
    header("Location: notificationsteamleader.php");
    exit();
}

$notificationsList = [];

$notificationsResult = mysqli_query($conn, "
    SELECT *
    FROM notifications
    WHERE user_id = $user_id
    ORDER BY id DESC
");

if ($notificationsResult && mysqli_num_rows($notificationsResult) > 0) {
    while ($row = mysqli_fetch_assoc($notificationsResult)) {
        $notificationsList[] = $row;
    }
}

$totalNotifications = count($notificationsList);

$unreadResult = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = $user_id AND is_read = 0
");

$unreadNotifications = 0;
if ($unreadResult) {
    $unreadNotifications = (int) mysqli_fetch_assoc($unreadResult)['total'];
}

$taskUpdates = 0;
$urgentAlerts = 0;
$reviewReminders = 0;

foreach ($notificationsList as $notification) {
    $title = strtolower($notification['title']);
    $type = strtolower($notification['type']);

    if (strpos($title, 'task') !== false || strpos($title, 'ticket') !== false) {
        $taskUpdates++;
    }

    if ($type === 'danger' || $type === 'warning') {
        $urgentAlerts++;
    }

    if (strpos($title, 'submitted') !== false || strpos($title, 'review') !== false) {
        $reviewReminders++;
    }
}

function notificationIconClass($type)
{
    if ($type === 'success') {
        return 'icon-success';
    }

    if ($type === 'warning') {
        return 'icon-warning';
    }

    if ($type === 'danger') {
        return 'icon-danger';
    }

    return 'icon-info';
}

function notificationIcon($type)
{
    if ($type === 'success') {
        return 'fa-circle-check';
    }

    if ($type === 'warning') {
        return 'fa-clock';
    }

    if ($type === 'danger') {
        return 'fa-triangle-exclamation';
    }

    return 'fa-bell';
}
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
    .notif-stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-top: 25px;
    }

    .notif-stat-card {
      background: #ffffff;
      border-radius: 22px;
      padding: 22px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
    }

    .notif-stat-card h3 {
      font-size: 32px;
      color: #0f172a;
      margin-bottom: 6px;
    }

    .notif-stat-card p {
      color: #64748b;
      font-size: 15px;
      margin: 0;
    }

    .notif-layout {
      display: grid;
      grid-template-columns: 1.35fr 0.85fr;
      gap: 24px;
      margin-top: 28px;
      align-items: start;
    }

    .notif-box,
    .quick-alerts-box {
      background: #ffffff;
      border-radius: 24px;
      padding: 26px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
    }

    .notif-box h3,
    .quick-alerts-box h3 {
      font-size: 24px;
      color: #0f172a;
      margin-bottom: 18px;
    }

    .notif-list {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .notif-item {
      display: flex;
      gap: 14px;
      align-items: flex-start;
      padding: 18px;
      border-radius: 18px;
      background: #f8fbfd;
      border: 1px solid #e8eef5;
    }

    .notif-item.unread {
      background: #eff6ff;
      border-color: #bfdbfe;
    }

    .notif-icon {
      width: 48px;
      height: 48px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 18px;
      flex-shrink: 0;
    }

    .icon-info { background: linear-gradient(135deg, #0ea5e9, #38bdf8); }
    .icon-warning { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .icon-success { background: linear-gradient(135deg, #22c55e, #4ade80); }
    .icon-danger { background: linear-gradient(135deg, #ef4444, #f87171); }

    .notif-content h4 {
      margin: 0 0 6px;
      font-size: 18px;
      color: #0f172a;
    }

    .notif-content p {
      margin: 0 0 8px;
      color: #64748b;
      font-size: 14px;
      line-height: 1.6;
    }

    .notif-time {
      font-size: 13px;
      color: #94a3b8;
      font-weight: 600;
    }

    .quick-alerts-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .alert-card {
      border-radius: 18px;
      padding: 16px;
      border: 1px solid #edf2f7;
      background: #fbfdff;
    }

    .alert-card h4 {
      margin: 0 0 8px;
      font-size: 17px;
      color: #0f172a;
    }

    .alert-card p {
      margin: 0 0 10px;
      color: #64748b;
      font-size: 14px;
      line-height: 1.5;
    }

    .alert-tag {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
    }

    .tag-high {
      background: #fee2e2;
      color: #b91c1c;
    }

    .tag-medium {
      background: #fef3c7;
      color: #92400e;
    }

    .tag-low {
      background: #dcfce7;
      color: #166534;
    }

    .empty-box {
      padding: 35px;
      text-align: center;
      color: #64748b;
      border-radius: 20px;
      background: #f8fafc;
      border: 1px dashed #cbd5e1;
    }

    .notification-bell {
      text-decoration: none;
    }

    .mark-read-form {
      margin: 0;
    }

    .hero-btn {
      border: none;
      cursor: pointer;
    }

    @media (max-width: 1200px) {
      .notif-stats {
        grid-template-columns: repeat(2, 1fr);
      }

      .notif-layout {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 700px) {
      .notif-stats {
        grid-template-columns: 1fr;
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
      <p class="admin-role">Team Leader Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="dashboardteamleader.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="myteam.php"><i class="fas fa-users"></i> My Team</a></li>
      <li><a href="assigntasks.php"><i class="fas fa-list-check"></i> Assign Tasks</a></li>
      <li><a href="tasksprogress.php"><i class="fas fa-chart-line"></i> Tasks Progress</a></li>
      <li><a href="reportsteamleader.php"><i class="fas fa-file-lines"></i> Reports</a></li>
      <li class="active"><a href="notificationsteamleader.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
      <li><a href="settingsteamleader.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>Team Updates</p>
        <h4><?php echo $totalNotifications; ?></h4>
        <span>Total notifications received</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Notifications</h1>
        <p>Stay updated with team alerts, task changes, reminders, and important notices.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="notificationSearch" placeholder="Search notification, alert, member...">
        </div>

        <a href="notificationsteamleader.php" class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <?php if ($unreadNotifications > 0) { ?>
            <span class="notif-count"><?php echo $unreadNotifications; ?></span>
          <?php } ?>
        </a>

        <div class="admin-profile">
          <div class="admin-avatar">
            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
          </div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>Team Leader</span>
          </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </header>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>Team Alerts & Updates</h2>
        <p>Track new activity, urgent reminders, and important team events from one place.</p>
      </div>

      <div class="hero-actions">
        <form method="POST" class="mark-read-form">
          <button type="submit" name="mark_all_read" class="hero-btn primary-btn">
            <i class="fas fa-check-double"></i> Mark All Read
          </button>
        </form>

        <a href="tasksprogress.php" class="hero-btn secondary-btn">
          <i class="fas fa-chart-line"></i> View Progress
        </a>
      </div>
    </section>

    <section class="notif-stats">
      <div class="notif-stat-card">
        <h3><?php echo $unreadNotifications; ?></h3>
        <p>Unread Notifications</p>
      </div>

      <div class="notif-stat-card">
        <h3><?php echo $urgentAlerts; ?></h3>
        <p>Urgent Alerts</p>
      </div>

      <div class="notif-stat-card">
        <h3><?php echo $taskUpdates; ?></h3>
        <p>Task Updates</p>
      </div>

      <div class="notif-stat-card">
        <h3><?php echo $reviewReminders; ?></h3>
        <p>Review Reminders</p>
      </div>
    </section>

    <section class="notif-layout">

      <div class="notif-box">
        <h3>Recent Notifications</h3>

        <?php if (!empty($notificationsList)) { ?>
          <div class="notif-list" id="notificationList">
            <?php foreach ($notificationsList as $notification) { ?>
              <?php
                $type = strtolower($notification['type']);
                $isUnread = ((int) $notification['is_read'] === 0) ? 'unread' : '';
              ?>

              <div class="notif-item <?php echo $isUnread; ?>">
                <div class="notif-icon <?php echo notificationIconClass($type); ?>">
                  <i class="fas <?php echo notificationIcon($type); ?>"></i>
                </div>

                <div class="notif-content">
                  <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                  <p><?php echo htmlspecialchars($notification['message']); ?></p>
                  <span class="notif-time"><?php echo htmlspecialchars($notification['created_at']); ?></span>
                </div>
              </div>
            <?php } ?>
          </div>
        <?php } else { ?>
          <div class="empty-box">
            <h3>No Notifications Yet</h3>
            <p>Employee task submissions and team updates will appear here.</p>
          </div>
        <?php } ?>
      </div>

      <div class="quick-alerts-box">
        <h3>Quick Alerts</h3>

        <div class="quick-alerts-list">
          <div class="alert-card">
            <h4>Submitted Tasks</h4>
            <p>Task submissions from employees will appear as notifications here.</p>
            <span class="alert-tag tag-high">Review Center</span>
          </div>

          <div class="alert-card">
            <h4>Task Status Updates</h4>
            <p>Track assigned, submitted, returned, and approved tickets.</p>
            <span class="alert-tag tag-medium">Task Updates</span>
          </div>

          <div class="alert-card">
            <h4>Team Workflow</h4>
            <p>Use Tasks Progress to approve completed submissions or return them for revision.</p>
            <span class="alert-tag tag-low">Workflow</span>
          </div>
        </div>
      </div>

    </section>

  </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("notificationSearch");
    const items = document.querySelectorAll(".notif-item");

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