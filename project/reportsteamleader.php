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
  <title>Reports - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .reports-stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-top: 25px;
    }

    .report-stat-card {
      background: #ffffff;
      border-radius: 22px;
      padding: 24px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
    }

    .report-stat-card h3 {
      font-size: 34px;
      color: #0f172a;
      margin-bottom: 8px;
    }

    .report-stat-card p {
      color: #64748b;
      font-size: 15px;
      margin-bottom: 14px;
    }

    .mini-progress {
      width: 100%;
      height: 10px;
      background: #e2e8f0;
      border-radius: 999px;
      overflow: hidden;
    }

    .mini-progress span {
      display: block;
      height: 100%;
      border-radius: 999px;
    }

    .progress-green { background: linear-gradient(135deg, #22c55e, #4ade80); width: 88%; }
    .progress-blue { background: linear-gradient(135deg, #0ea5e9, #38bdf8); width: 74%; }
    .progress-yellow { background: linear-gradient(135deg, #f59e0b, #fbbf24); width: 61%; }
    .progress-red { background: linear-gradient(135deg, #ef4444, #f87171); width: 32%; }

    .reports-layout {
      display: grid;
      grid-template-columns: 1.3fr 0.9fr;
      gap: 24px;
      margin-top: 28px;
      align-items: start;
    }

    .report-box,
    .performance-box {
      background: #ffffff;
      border-radius: 24px;
      padding: 26px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
    }

    .report-box h3,
    .performance-box h3 {
      font-size: 24px;
      color: #0f172a;
      margin-bottom: 20px;
    }

    .table-wrapper {
      overflow-x: auto;
    }

    .report-table {
      width: 100%;
      border-collapse: collapse;
    }

    .report-table th,
    .report-table td {
      text-align: left;
      padding: 14px 12px;
      border-bottom: 1px solid #edf2f7;
      font-size: 14px;
    }

    .report-table th {
      color: #475569;
      font-weight: 700;
      background: #f8fbfd;
    }

    .report-table td {
      color: #0f172a;
    }

    .status-label {
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      display: inline-block;
    }

    .done-label {
      background: #dcfce7;
      color: #166534;
    }

    .progress-label {
      background: #dbeafe;
      color: #1d4ed8;
    }

    .pending-label {
      background: #fef3c7;
      color: #92400e;
    }

    .member-performance-list {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .member-performance-item {
      padding: 16px;
      border: 1px solid #edf2f7;
      border-radius: 18px;
      background: #fbfdff;
    }

    .member-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
      gap: 12px;
    }

    .member-top h4 {
      margin: 0;
      font-size: 18px;
      color: #0f172a;
    }

    .member-top span {
      color: #64748b;
      font-size: 14px;
    }

    .member-score {
      font-size: 14px;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 10px;
    }

    .member-progress {
      width: 100%;
      height: 10px;
      background: #e2e8f0;
      border-radius: 999px;
      overflow: hidden;
      margin-bottom: 10px;
    }

    .member-progress span {
      display: block;
      height: 100%;
      border-radius: 999px;
      background: linear-gradient(135deg, #12c2cc, #2dd4bf);
    }

    .member-note {
      font-size: 13px;
      color: #64748b;
      line-height: 1.5;
    }

    @media (max-width: 1200px) {
      .reports-stats {
        grid-template-columns: repeat(2, 1fr);
      }

      .reports-layout {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 700px) {
      .reports-stats {
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
      <li class="active"><a href="reportsteamleader.php"><i class="fas fa-file-lines"></i> Reports</a></li>
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
        <h1>Reports</h1>
        <p>Review team performance, task completion, and productivity insights in one place.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search report, task, member...">
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
        <h2>Team Reports & Insights 📑</h2>
        <p>Analyze team output, review task completion rates, and monitor overall weekly productivity.</p>
      </div>

      <div class="hero-actions">
        <button class="hero-btn primary-btn"><i class="fas fa-file-export"></i> Export Report</button>
        <button class="hero-btn secondary-btn"><i class="fas fa-chart-line"></i> View Analytics</button>
      </div>
    </section>

    <section class="reports-stats">
      <div class="report-stat-card">
        <h3>88%</h3>
        <p>Overall Completion Rate</p>
        <div class="mini-progress"><span class="progress-green"></span></div>
      </div>

      <div class="report-stat-card">
        <h3>74%</h3>
        <p>On-Time Delivery</p>
        <div class="mini-progress"><span class="progress-blue"></span></div>
      </div>

      <div class="report-stat-card">
        <h3>61%</h3>
        <p>Weekly Productivity</p>
        <div class="mini-progress"><span class="progress-yellow"></span></div>
      </div>

      <div class="report-stat-card">
        <h3>32%</h3>
        <p>Delayed Tasks Ratio</p>
        <div class="mini-progress"><span class="progress-red"></span></div>
      </div>
    </section>

    <section class="reports-layout">

      <div class="report-box">
        <h3>Recent Task Reports</h3>

        <div class="table-wrapper">
          <table class="report-table">
            <thead>
              <tr>
                <th>Task</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Deadline</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Employee Dashboard UI</td>
                <td>Ahmad Ali</td>
                <td><span class="status-label progress-label">In Progress</span></td>
                <td>20 Apr 2026</td>
              </tr>
              <tr>
                <td>Leave Request Testing</td>
                <td>Omar Sami</td>
                <td><span class="status-label pending-label">Pending</span></td>
                <td>22 Apr 2026</td>
              </tr>
              <tr>
                <td>Profile Page Improvement</td>
                <td>Lina Noor</td>
                <td><span class="status-label done-label">Completed</span></td>
                <td>18 Apr 2026</td>
              </tr>
              <tr>
                <td>System Settings Cleanup</td>
                <td>Sara Khaled</td>
                <td><span class="status-label progress-label">In Progress</span></td>
                <td>25 Apr 2026</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="performance-box">
        <h3>Team Member Performance</h3>

        <div class="member-performance-list">

          <div class="member-performance-item">
            <div class="member-top">
              <div>
                <h4>Ahmad Ali</h4>
                <span>Frontend Developer</span>
              </div>
            </div>
            <div class="member-score">Performance Score: 91%</div>
            <div class="member-progress"><span style="width: 91%;"></span></div>
            <div class="member-note">Strong progress in dashboard implementation and UI consistency updates.</div>
          </div>

          <div class="member-performance-item">
            <div class="member-top">
              <div>
                <h4>Sara Khaled</h4>
                <span>Backend Developer</span>
              </div>
            </div>
            <div class="member-score">Performance Score: 84%</div>
            <div class="member-progress"><span style="width: 84%;"></span></div>
            <div class="member-note">Good delivery speed with a few pending technical reviews this week.</div>
          </div>

          <div class="member-performance-item">
            <div class="member-top">
              <div>
                <h4>Lina Noor</h4>
                <span>UI/UX Designer</span>
              </div>
            </div>
            <div class="member-score">Performance Score: 95%</div>
            <div class="member-progress"><span style="width: 95%;"></span></div>
            <div class="member-note">Excellent design execution and timely completion of assigned tasks.</div>
          </div>

          <div class="member-performance-item">
            <div class="member-top">
              <div>
                <h4>Omar Sami</h4>
                <span>QA Tester</span>
              </div>
            </div>
            <div class="member-score">Performance Score: 78%</div>
            <div class="member-progress"><span style="width: 78%;"></span></div>
            <div class="member-note">Testing coverage is improving, with a few delayed checks still open.</div>
          </div>

        </div>
      </div>

    </section>

  </main>
</div>

</body>
</html>