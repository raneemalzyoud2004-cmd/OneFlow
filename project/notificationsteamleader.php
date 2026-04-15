<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Team Leader';
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

  <!-- Sidebar -->
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
      <li><a href="settingsteamleader.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>Team Performance</p>
        <h4>Excellent</h4>
        <span>92% tasks completed</span>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Notifications</h1>
        <p>Stay updated with team alerts, task changes, reminders, and important notices.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search notification, alert, member...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">6</span>
        </div>

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
        <h2>Team Alerts & Updates 🔔</h2>
        <p>Track new activity, urgent reminders, and important team events from one place.</p>
      </div>

      <div class="hero-actions">
        <button class="hero-btn primary-btn"><i class="fas fa-check-double"></i> Mark All Read</button>
        <button class="hero-btn secondary-btn"><i class="fas fa-filter"></i> Filter Alerts</button>
      </div>
    </section>

    <section class="notif-stats">
      <div class="notif-stat-card">
        <h3>6</h3>
        <p>Unread Notifications</p>
      </div>
      <div class="notif-stat-card">
        <h3>3</h3>
        <p>Urgent Alerts</p>
      </div>
      <div class="notif-stat-card">
        <h3>4</h3>
        <p>Task Updates</p>
      </div>
      <div class="notif-stat-card">
        <h3>2</h3>
        <p>Review Reminders</p>
      </div>
    </section>

    <section class="notif-layout">

      <div class="notif-box">
        <h3>Recent Notifications</h3>

        <div class="notif-list">
          <div class="notif-item">
            <div class="notif-icon icon-info"><i class="fas fa-list-check"></i></div>
            <div class="notif-content">
              <h4>Task status updated</h4>
              <p>Ahmad Ali changed “Employee Dashboard UI” from Pending to In Progress.</p>
              <span class="notif-time">10 minutes ago</span>
            </div>
          </div>

          <div class="notif-item">
            <div class="notif-icon icon-warning"><i class="fas fa-clock"></i></div>
            <div class="notif-content">
              <h4>Deadline approaching</h4>
              <p>The task “Leave Request Testing” is due tomorrow and still needs final review.</p>
              <span class="notif-time">35 minutes ago</span>
            </div>
          </div>

          <div class="notif-item">
            <div class="notif-icon icon-success"><i class="fas fa-circle-check"></i></div>
            <div class="notif-content">
              <h4>Task completed</h4>
              <p>Lina Noor completed “Profile Page Improvement” successfully.</p>
              <span class="notif-time">1 hour ago</span>
            </div>
          </div>

          <div class="notif-item">
            <div class="notif-icon icon-danger"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="notif-content">
              <h4>Delayed task alert</h4>
              <p>“System Settings Cleanup” is delayed and needs follow-up with Sara Khaled.</p>
              <span class="notif-time">2 hours ago</span>
            </div>
          </div>
        </div>
      </div>

      <div class="quick-alerts-box">
        <h3>Quick Alerts</h3>

        <div class="quick-alerts-list">
          <div class="alert-card">
            <h4>Code Review Needed</h4>
            <p>Two tasks are waiting for your approval before moving to completed status.</p>
            <span class="alert-tag tag-high">High Priority</span>
          </div>

          <div class="alert-card">
            <h4>Team Meeting Reminder</h4>
            <p>Your weekly team sync is scheduled for tomorrow at 10:00 AM.</p>
            <span class="alert-tag tag-medium">Reminder</span>
          </div>

          <div class="alert-card">
            <h4>Good Progress</h4>
            <p>The design and frontend work this week is moving faster than planned.</p>
            <span class="alert-tag tag-low">Positive Update</span>
          </div>
        </div>
      </div>

    </section>

  </main>
</div>

</body>
</html>