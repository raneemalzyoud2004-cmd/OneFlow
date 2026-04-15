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
  <title>Tasks Progress - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .progress-stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-top: 25px;
    }

    .mini-stat {
      background: #ffffff;
      border-radius: 22px;
      padding: 24px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
      display: flex;
      align-items: center;
      gap: 18px;
    }

    .mini-stat-icon {
      width: 62px;
      height: 62px;
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: #fff;
      flex-shrink: 0;
    }

    .pending-icon {
      background: linear-gradient(135deg, #f59e0b, #fbbf24);
    }

    .progress-icon {
      background: linear-gradient(135deg, #0ea5e9, #38bdf8);
    }

    .done-icon {
      background: linear-gradient(135deg, #22c55e, #4ade80);
    }

    .mini-stat-text h3 {
      margin: 0;
      font-size: 34px;
      color: #0f172a;
    }

    .mini-stat-text p {
      margin: 5px 0 0;
      color: #64748b;
      font-size: 15px;
    }

    .board-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 22px;
      margin-top: 28px;
      align-items: start;
    }

    .board-column {
      background: #ffffff;
      border-radius: 24px;
      padding: 22px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
    }

    .column-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
    }

    .column-header h3 {
      margin: 0;
      font-size: 22px;
      color: #0f172a;
    }

    .column-count {
      min-width: 34px;
      height: 34px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      font-weight: 700;
      color: #fff;
    }

    .pending-count {
      background: #f59e0b;
    }

    .progress-count {
      background: #0ea5e9;
    }

    .done-count {
      background: #22c55e;
    }

    .task-card-progress {
      background: #f8fbfd;
      border: 1px solid #e8eef5;
      border-radius: 18px;
      padding: 16px;
      margin-bottom: 16px;
      transition: 0.3s ease;
    }

    .task-card-progress:hover {
      transform: translateY(-3px);
    }

    .task-card-progress h4 {
      margin: 0 0 8px;
      color: #0f172a;
      font-size: 18px;
    }

    .task-card-progress p {
      color: #64748b;
      font-size: 14px;
      line-height: 1.6;
      margin-bottom: 14px;
    }

    .task-meta {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 14px;
      font-size: 13px;
      color: #334155;
    }

    .task-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .member-chip {
      background: #e0f2fe;
      color: #0369a1;
      padding: 7px 12px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
    }

    .deadline-chip {
      background: #f1f5f9;
      color: #475569;
      padding: 7px 12px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
    }

    @media (max-width: 1150px) {
      .progress-stats,
      .board-grid {
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
      <li class="active"><a href="tasksprogress.php"><i class="fas fa-chart-line"></i> Tasks Progress</a></li>
      <li><a href="reportsteamleader.php"><i class="fas fa-file-lines"></i> Reports</a></li>
      <li><a href="notificationsteamleader.php"><i class="fas fa-bell"></i> Notifications</a></li>
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
        <h1>Tasks Progress</h1>
        <p>Track all team tasks by status and monitor the workflow clearly.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search task status, member, deadline...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">4</span>
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
        <h2>Task Workflow Overview 📊</h2>
        <p>Monitor pending, in progress, and completed tasks to keep your team aligned and productive.</p>
      </div>

      <div class="hero-actions">
        <button class="hero-btn primary-btn"><i class="fas fa-list-check"></i> Assign New Task</button>
        <button class="hero-btn secondary-btn"><i class="fas fa-file-export"></i> Export Status</button>
      </div>
    </section>

    <section class="progress-stats">
      <div class="mini-stat">
        <div class="mini-stat-icon pending-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="mini-stat-text">
          <h3>4</h3>
          <p>Pending Tasks</p>
        </div>
      </div>

      <div class="mini-stat">
        <div class="mini-stat-icon progress-icon"><i class="fas fa-spinner"></i></div>
        <div class="mini-stat-text">
          <h3>5</h3>
          <p>In Progress</p>
        </div>
      </div>

      <div class="mini-stat">
        <div class="mini-stat-icon done-icon"><i class="fas fa-circle-check"></i></div>
        <div class="mini-stat-text">
          <h3>7</h3>
          <p>Completed Tasks</p>
        </div>
      </div>
    </section>

    <section class="board-grid">

      <div class="board-column">
        <div class="column-header">
          <h3>Pending</h3>
          <span class="column-count pending-count">4</span>
        </div>

        <div class="task-card-progress">
          <h4>Prepare Team Weekly Plan</h4>
          <p>Create the weekly task plan and define priorities for each team member.</p>
          <div class="task-meta">
            <span>Priority: High</span>
            <span>Status: Pending</span>
          </div>
          <div class="task-footer">
            <span class="member-chip">Sara Khaled</span>
            <span class="deadline-chip">Due: 18 Apr</span>
          </div>
        </div>

        <div class="task-card-progress">
          <h4>UI Colors Review</h4>
          <p>Review the current dashboard color consistency and suggest improvements.</p>
          <div class="task-meta">
            <span>Priority: Medium</span>
            <span>Status: Pending</span>
          </div>
          <div class="task-footer">
            <span class="member-chip">Lina Noor</span>
            <span class="deadline-chip">Due: 19 Apr</span>
          </div>
        </div>
      </div>

      <div class="board-column">
        <div class="column-header">
          <h3>In Progress</h3>
          <span class="column-count progress-count">5</span>
        </div>

        <div class="task-card-progress">
          <h4>Employee Dashboard Frontend</h4>
          <p>Complete the employee dashboard design and improve responsive layout sections.</p>
          <div class="task-meta">
            <span>Priority: High</span>
            <span>Status: In Progress</span>
          </div>
          <div class="task-footer">
            <span class="member-chip">Ahmad Ali</span>
            <span class="deadline-chip">Due: 20 Apr</span>
          </div>
        </div>

        <div class="task-card-progress">
          <h4>Leave Request Page Testing</h4>
          <p>Test page validation, button actions, and error handling in the request flow.</p>
          <div class="task-meta">
            <span>Priority: Medium</span>
            <span>Status: In Progress</span>
          </div>
          <div class="task-footer">
            <span class="member-chip">Omar Sami</span>
            <span class="deadline-chip">Due: 21 Apr</span>
          </div>
        </div>
      </div>

      <div class="board-column">
        <div class="column-header">
          <h3>Completed</h3>
          <span class="column-count done-count">7</span>
        </div>

        <div class="task-card-progress">
          <h4>Login Page Redesign</h4>
          <p>Redesign the login interface based on OneFlow branding and improve visual hierarchy.</p>
          <div class="task-meta">
            <span>Priority: High</span>
            <span>Status: Completed</span>
          </div>
          <div class="task-footer">
            <span class="member-chip">Lina Noor</span>
            <span class="deadline-chip">Done</span>
          </div>
        </div>

        <div class="task-card-progress">
          <h4>Sidebar Navigation Update</h4>
          <p>Improve navigation links and active state styling across dashboard pages.</p>
          <div class="task-meta">
            <span>Priority: Low</span>
            <span>Status: Completed</span>
          </div>
          <div class="task-footer">
            <span class="member-chip">Ahmad Ali</span>
            <span class="deadline-chip">Done</span>
          </div>
        </div>
      </div>

    </section>

  </main>
</div>

</body>
</html>