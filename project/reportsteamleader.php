<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teamleader') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'] ?? 'Team Leader';
$initial = strtoupper(substr($full_name, 0, 1));
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

    .report-stat-card,
    .report-box,
    .performance-box {
      background: #ffffff;
      border-radius: 24px;
      padding: 26px;
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

    .mini-progress,
    .member-progress {
      width: 100%;
      height: 10px;
      background: #e2e8f0;
      border-radius: 999px;
      overflow: hidden;
    }

    .mini-progress span,
    .member-progress span {
      display: block;
      height: 100%;
      border-radius: 999px;
      background: linear-gradient(135deg, #12c2cc, #2dd4bf);
    }

    .progress-green { width: 88%; }
    .progress-blue { width: 74%; }
    .progress-yellow { width: 61%; }
    .progress-red { width: 32%; }

    .reports-layout {
      display: grid;
      grid-template-columns: 1.3fr 0.9fr;
      gap: 24px;
      margin-top: 28px;
      align-items: start;
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
      padding: 15px 12px;
      border-bottom: 1px solid #edf2f7;
      font-size: 14px;
    }

    .report-table th {
      color: #475569;
      font-weight: 800;
      background: #f8fbfd;
    }

    .status-label {
      padding: 7px 13px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
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
      gap: 16px;
    }

    .member-performance-item {
      padding: 18px;
      border: 1px solid #edf2f7;
      border-radius: 20px;
      background: #fbfdff;
    }

    .member-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
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
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 10px;
    }

    .member-note {
      margin-top: 10px;
      font-size: 13px;
      color: #64748b;
      line-height: 1.5;
    }


    .search-hidden {
      display: none !important;
    }

    .search-match {
      box-shadow: 0 0 0 3px rgba(18, 194, 204, 0.14);
      border-color: rgba(18, 194, 204, 0.35) !important;
    }

    .search-empty-row td,
    .search-empty-box {
      padding: 18px;
      text-align: center;
      color: #64748b;
      font-weight: 700;
      background: #f8fbfd;
      border-radius: 14px;
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
      <li><a href="meetings.php"><i class="fas fa-calendar-days"></i> Meetings</a></li>
      <li class="active"><a href="reportsteamleader.php"><i class="fas fa-file-lines"></i> Reports</a></li>
      <li><a href="notificationsteamleader.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
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

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Reports</h1>
        <p>Review team performance, task completion, and productivity insights.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="reportSearch" onkeyup="searchReportsPage()" placeholder="Search reports, tasks, members, status...">
        </div>

     

        <div class="admin-profile">
          <div class="admin-avatar"><?php echo htmlspecialchars($initial); ?></div>
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
        <p>Analyze team output, review task completion rates, and monitor weekly productivity.</p>
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
              <tr class="report-search-row" data-search="leave request review noor in progress 15 may 2026">
                <td>Leave Request Review</td>
                <td>Noor</td>
                <td><span class="status-label progress-label">In Progress</span></td>
                <td>15 May 2026</td>
              </tr>

              <tr class="report-search-row" data-search="attendance records update ammar pending 17 may 2026">
                <td>Attendance Records Update</td>
                <td>Ammar</td>
                <td><span class="status-label pending-label">Pending</span></td>
                <td>17 May 2026</td>
              </tr>

              <tr class="report-search-row" data-search="employee profile testing sara completed 10 may 2026">
                <td>Employee Profile Testing</td>
                <td>Sara</td>
                <td><span class="status-label done-label">Completed</span></td>
                <td>10 May 2026</td>
              </tr>

              <tr class="report-search-row" data-search="tasks progress monitoring khaled in progress 20 may 2026">
                <td>Tasks Progress Monitoring</td>
                <td>Khaled</td>
                <td><span class="status-label progress-label">In Progress</span></td>
                <td>20 May 2026</td>
              </tr>

              <tr class="report-search-row" data-search="notifications page review dana pending 22 may 2026">
                <td>Notifications Page Review</td>
                <td>Dana</td>
                <td><span class="status-label pending-label">Pending</span></td>
                <td>22 May 2026</td>
              </tr>
              <tr id="reportNoResultsRow" class="search-empty-row" style="display:none;">
                <td colspan="4">No report or task found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="performance-box">
        <h3>Team Member Performance</h3>

        <div class="member-performance-list">

          <div class="member-performance-item member-search-item" data-search="noor excellent follow-up on leave requests and task updates employee performance score 91">
            <div class="member-top">
              <div>
                <h4>Noor</h4>
                <span>Employee</span>
              </div>
            </div>

            <div class="member-score">Performance Score: 91%</div>
            <div class="member-progress"><span style="width: 91%;"></span></div>
            <div class="member-note">Excellent follow-up on leave requests and task updates.</div>
          </div>

          <div class="member-performance-item member-search-item" data-search="ammar good attendance tracking and profile management performance employee performance score 84">
            <div class="member-top">
              <div>
                <h4>Ammar</h4>
                <span>Employee</span>
              </div>
            </div>

            <div class="member-score">Performance Score: 84%</div>
            <div class="member-progress"><span style="width: 84%;"></span></div>
            <div class="member-note">Good attendance tracking and profile management performance.</div>
          </div>

          <div class="member-performance-item member-search-item" data-search="sara fast completion of assigned system tasks employee performance score 96">
            <div class="member-top">
              <div>
                <h4>Sara</h4>
                <span>Employee</span>
              </div>
            </div>

            <div class="member-score">Performance Score: 96%</div>
            <div class="member-progress"><span style="width: 96%;"></span></div>
            <div class="member-note">Fast completion of assigned system tasks.</div>
          </div>

          <div class="member-performance-item member-search-item" data-search="khaled needs improvement in task completion deadlines employee performance score 78">
            <div class="member-top">
              <div>
                <h4>Khaled</h4>
                <span>Employee</span>
              </div>
            </div>

            <div class="member-score">Performance Score: 78%</div>
            <div class="member-progress"><span style="width: 78%;"></span></div>
            <div class="member-note">Needs improvement in task completion deadlines.</div>
          </div>

          <div class="member-performance-item member-search-item" data-search="dana inactive recently with pending assigned reviews employee performance score 69">
            <div class="member-top">
              <div>
                <h4>Dana</h4>
                <span>Employee</span>
              </div>
            </div>

            <div class="member-score">Performance Score: 69%</div>
            <div class="member-progress"><span style="width: 69%;"></span></div>
            <div class="member-note">Inactive recently with pending assigned reviews.</div>
          </div>

          <div id="memberNoResultsBox" class="search-empty-box" style="display:none;">
            No team member found.
          </div>

        </div>
      </div>

    </section>

  </main>
</div>


<script>
function searchReportsPage() {
  const input = document.getElementById("reportSearch");
  const value = input.value.toLowerCase().trim();

  const reportRows = document.querySelectorAll(".report-search-row");
  const memberItems = document.querySelectorAll(".member-search-item");
  const reportNoResultsRow = document.getElementById("reportNoResultsRow");
  const memberNoResultsBox = document.getElementById("memberNoResultsBox");

  let visibleReports = 0;
  let visibleMembers = 0;

  reportRows.forEach(function(row) {
    const text = (row.getAttribute("data-search") || row.innerText || "").toLowerCase();

    if (value === "" || text.includes(value)) {
      row.classList.remove("search-hidden");
      row.classList.toggle("search-match", value !== "");
      visibleReports++;
    } else {
      row.classList.add("search-hidden");
      row.classList.remove("search-match");
    }
  });

  memberItems.forEach(function(item) {
    const text = (item.getAttribute("data-search") || item.innerText || "").toLowerCase();

    if (value === "" || text.includes(value)) {
      item.classList.remove("search-hidden");
      item.classList.toggle("search-match", value !== "");
      visibleMembers++;
    } else {
      item.classList.add("search-hidden");
      item.classList.remove("search-match");
    }
  });

  if (reportNoResultsRow) {
    reportNoResultsRow.style.display = visibleReports === 0 ? "" : "none";
  }

  if (memberNoResultsBox) {
    memberNoResultsBox.style.display = visibleMembers === 0 ? "block" : "none";
  }
}
</script>

</body>
</html>