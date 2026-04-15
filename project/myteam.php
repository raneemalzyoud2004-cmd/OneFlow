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
  <title>My Team - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .team-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
      margin-top: 25px;
    }

    .team-card {
      background: #ffffff;
      border-radius: 24px;
      padding: 24px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
      transition: 0.3s ease;
    }

    .team-card:hover {
      transform: translateY(-5px);
    }

    .team-top {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 18px;
    }

    .team-avatar {
      width: 58px;
      height: 58px;
      border-radius: 16px;
      background: linear-gradient(135deg, #19c2c9, #22c55e);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      font-weight: bold;
    }

    .team-name h3 {
      margin: 0;
      font-size: 22px;
      color: #0f172a;
    }

    .team-name span {
      color: #64748b;
      font-size: 14px;
    }

    .member-info {
      margin-top: 14px;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
      font-size: 15px;
      color: #334155;
    }

    .status-badge {
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
    }

    .active-status {
      background: #dcfce7;
      color: #166534;
    }

    .busy-status {
      background: #fef3c7;
      color: #92400e;
    }

    .offline-status {
      background: #fee2e2;
      color: #991b1b;
    }

    .member-actions {
      display: flex;
      gap: 10px;
      margin-top: 18px;
    }

    .member-btn {
      border: none;
      border-radius: 12px;
      padding: 10px 14px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    .view-btn {
      background: #e0f2fe;
      color: #0369a1;
    }

    .task-btn {
      background: #dcfce7;
      color: #166534;
    }

    .member-btn:hover {
      opacity: 0.9;
      transform: translateY(-2px);
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
      <li class="active"><a href="myteam.php"><i class="fas fa-users"></i> My Team</a></li>
      <li><a href="assigntasks.php"><i class="fas fa-list-check"></i> Assign Tasks</a></li>
      <li><a href="tasksprogress.php"><i class="fas fa-chart-line"></i> Tasks Progress</a></li>
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
        <h1>My Team</h1>
        <p>View your team members, roles, status, and workload in one place.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search team member...">
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
        <h2>Your Team Overview 👥</h2>
        <p>You currently manage <strong>4 team members</strong> with different roles and task statuses.</p>
      </div>

      <div class="hero-actions">
        <button class="hero-btn primary-btn"><i class="fas fa-user-plus"></i> Add Member</button>
        <button class="hero-btn secondary-btn"><i class="fas fa-list-check"></i> Assign New Task</button>
      </div>
    </section>

    <section class="team-grid">

      <div class="team-card">
        <div class="team-top">
          <div class="team-avatar">A</div>
          <div class="team-name">
            <h3>Ahmad Ali</h3>
            <span>Frontend Developer</span>
          </div>
        </div>

        <div class="member-info">
          <div class="info-row">
            <span>Status</span>
            <span class="status-badge active-status">Active</span>
          </div>
          <div class="info-row">
            <span>Assigned Tasks</span>
            <strong>5</strong>
          </div>
          <div class="info-row">
            <span>Completed</span>
            <strong>3</strong>
          </div>
        </div>

        <div class="member-actions">
          <button class="member-btn view-btn">View Profile</button>
          <button class="member-btn task-btn">Assign Task</button>
        </div>
      </div>

      <div class="team-card">
        <div class="team-top">
          <div class="team-avatar">S</div>
          <div class="team-name">
            <h3>Sara Khaled</h3>
            <span>Backend Developer</span>
          </div>
        </div>

        <div class="member-info">
          <div class="info-row">
            <span>Status</span>
            <span class="status-badge busy-status">Busy</span>
          </div>
          <div class="info-row">
            <span>Assigned Tasks</span>
            <strong>4</strong>
          </div>
          <div class="info-row">
            <span>Completed</span>
            <strong>2</strong>
          </div>
        </div>

        <div class="member-actions">
          <button class="member-btn view-btn">View Profile</button>
          <button class="member-btn task-btn">Assign Task</button>
        </div>
      </div>

      <div class="team-card">
        <div class="team-top">
          <div class="team-avatar">L</div>
          <div class="team-name">
            <h3>Lina Noor</h3>
            <span>UI/UX Designer</span>
          </div>
        </div>

        <div class="member-info">
          <div class="info-row">
            <span>Status</span>
            <span class="status-badge active-status">Active</span>
          </div>
          <div class="info-row">
            <span>Assigned Tasks</span>
            <strong>3</strong>
          </div>
          <div class="info-row">
            <span>Completed</span>
            <strong>3</strong>
          </div>
        </div>

        <div class="member-actions">
          <button class="member-btn view-btn">View Profile</button>
          <button class="member-btn task-btn">Assign Task</button>
        </div>
      </div>

      <div class="team-card">
        <div class="team-top">
          <div class="team-avatar">O</div>
          <div class="team-name">
            <h3>Omar Sami</h3>
            <span>QA Tester</span>
          </div>
        </div>

        <div class="member-info">
          <div class="info-row">
            <span>Status</span>
            <span class="status-badge offline-status">Offline</span>
          </div>
          <div class="info-row">
            <span>Assigned Tasks</span>
            <strong>2</strong>
          </div>
          <div class="info-row">
            <span>Completed</span>
            <strong>1</strong>
          </div>
        </div>

        <div class="member-actions">
          <button class="member-btn view-btn">View Profile</button>
          <button class="member-btn task-btn">Assign Task</button>
        </div>
      </div>

    </section>

  </main>
</div>

</body>
</html>