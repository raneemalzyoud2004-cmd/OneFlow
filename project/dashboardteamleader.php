<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teamleader') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'] ?? 'Team Leader';
$first_letter = strtoupper(substr(trim($full_name), 0, 1));

$teamMembersQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'employee' AND id IN (3, 4, 5, 8, 9)");
$teamMembers = ($teamMembersQuery && $row = mysqli_fetch_assoc($teamMembersQuery)) ? $row['total'] : 0;

$assignedTasksQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks");
$assignedTasks = ($assignedTasksQuery && $row = mysqli_fetch_assoc($assignedTasksQuery)) ? $row['total'] : 0;

$inProgressTasksQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE status IN ('In Progress', 'submitted', 'pending')");
$inProgressTasks = ($inProgressTasksQuery && $row = mysqli_fetch_assoc($inProgressTasksQuery)) ? $row['total'] : 0;

$completedTasksQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE status IN ('Completed', 'completed')");
$completedTasks = ($completedTasksQuery && $row = mysqli_fetch_assoc($completedTasksQuery)) ? $row['total'] : 0;

$delayedTasksQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE due_date < CURDATE() AND status NOT IN ('Completed', 'completed')");
$delayedTasks = ($delayedTasksQuery && $row = mysqli_fetch_assoc($delayedTasksQuery)) ? $row['total'] : 0;

$todayTasksQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE due_date = CURDATE()");
$todayTasks = ($todayTasksQuery && $row = mysqli_fetch_assoc($todayTasksQuery)) ? $row['total'] : 0;

$completionRate = ($assignedTasks > 0) ? round(($completedTasks / $assignedTasks) * 100) : 0;

$recentTasksQuery = mysqli_query($conn, "
    SELECT task_title, priority, status, due_date
    FROM tasks
    ORDER BY id DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team Leader Dashboard - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .dashboard-grid {
      display: grid;
      grid-template-columns: 1.4fr 0.9fr;
      gap: 25px;
      margin-top: 30px;
    }

    .quick-actions {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-top: 30px;
    }

    .quick-action-card {
      background: #fff;
      padding: 22px;
      border-radius: 22px;
      text-decoration: none;
      color: #0D1E4C;
      box-shadow: 0 12px 35px rgba(13, 30, 76, 0.08);
      transition: 0.3s;
      border: 1px solid rgba(13, 30, 76, 0.06);
    }

    .quick-action-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 18px 45px rgba(13, 30, 76, 0.14);
    }

    .quick-action-card i {
      width: 52px;
      height: 52px;
      background: #dff8f2;
      color: #0aa99d;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      margin-bottom: 14px;
    }

    .quick-action-card h3 {
      font-size: 18px;
      margin-bottom: 6px;
    }

    .quick-action-card p {
      color: #607086;
      font-size: 14px;
      line-height: 1.5;
    }

    .panel {
      background: #fff;
      border-radius: 24px;
      padding: 25px;
      box-shadow: 0 12px 35px rgba(13, 30, 76, 0.08);
      border: 1px solid rgba(13, 30, 76, 0.06);
    }

    .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 22px;
    }

    .panel-header h2 {
      color: #0D1E4C;
      font-size: 22px;
    }

    .panel-header span {
      color: #0aa99d;
      font-weight: 700;
      font-size: 14px;
    }

    .focus-list {
      display: grid;
      gap: 15px;
    }

    .focus-item {
      display: flex;
      align-items: center;
      gap: 15px;
      background: #f7fbff;
      padding: 16px;
      border-radius: 18px;
    }

    .focus-icon {
      width: 45px;
      height: 45px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #e4f7f3;
      color: #0aa99d;
      font-size: 18px;
      flex-shrink: 0;
    }

    .focus-item h4 {
      color: #0D1E4C;
      margin-bottom: 4px;
      font-size: 16px;
    }

    .focus-item p {
      color: #607086;
      font-size: 14px;
    }

    .progress-box {
      margin-top: 15px;
    }

    .progress-info {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      color: #0D1E4C;
      font-weight: 700;
    }

    .progress-line {
      width: 100%;
      height: 13px;
      background: #edf2f7;
      border-radius: 20px;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      width: <?php echo $completionRate; ?>%;
      background: linear-gradient(90deg, #0D1E4C, #13c7b7);
      border-radius: 20px;
    }

    .task-table {
      width: 100%;
      border-collapse: collapse;
    }

    .task-table th {
      text-align: left;
      color: #607086;
      font-size: 14px;
      padding-bottom: 14px;
    }

    .task-table td {
      padding: 14px 0;
      border-top: 1px solid #edf2f7;
      color: #0D1E4C;
      font-weight: 600;
    }

    .status-badge {
      padding: 7px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 800;
      display: inline-block;
    }

    .status-completed {
      background: #e6f8ef;
      color: #16834f;
    }

    .status-progress {
      background: #fff5db;
      color: #b7791f;
    }

    .status-pending {
      background: #e8f0ff;
      color: #2457c5;
    }

    .status-delayed {
      background: #ffe8e8;
      color: #c53030;
    }

    .alert-box {
      display: grid;
      gap: 14px;
    }

    .alert-item {
      padding: 17px;
      border-radius: 18px;
      background: #fff7ed;
      border-left: 5px solid #f59e0b;
    }

    .alert-item.danger {
      background: #fff1f2;
      border-left-color: #e11d48;
    }

    .alert-item.success {
      background: #ecfdf5;
      border-left-color: #10b981;
    }

    .alert-item h4 {
      color: #0D1E4C;
      margin-bottom: 5px;
    }

    .alert-item p {
      color: #607086;
      font-size: 14px;
      line-height: 1.5;
    }

    @media (max-width: 1100px) {
      .quick-actions,
      .dashboard-grid {
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
      <li class="active"><a href="dashboardteamleader.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="myteam.php"><i class="fas fa-users"></i> My Team</a></li>
      <li><a href="assigntasks.php"><i class="fas fa-list-check"></i> Assign Tasks</a></li>
      <li><a href="tasksprogress.php"><i class="fas fa-chart-line"></i> Tasks Progress</a></li>
      <li><a href="reportsteamleader.php"><i class="fas fa-file-lines"></i> Reports</a></li>
      <li><a href="notificationsteamleader.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
      <li><a href="settingsteamleader.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>Team Completion</p>
        <h4><?php echo $completionRate; ?>%</h4>
        <span><?php echo $completedTasks; ?> of <?php echo $assignedTasks; ?> tasks completed</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Team Leader Dashboard</h1>
        <p>Monitor your team workload, assign tasks, follow deadlines, and keep daily work organized.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search team members, tasks, reports...">
        </div>

        <a href="notificationsteamleader.php" class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count"><?php echo $delayedTasks; ?></span>
        </a>

        <div class="admin-profile">
          <div class="admin-avatar"><?php echo htmlspecialchars($first_letter); ?></div>
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
        <h2>Welcome back, <?php echo htmlspecialchars($full_name); ?> 👋</h2>
        <p>
          Today you have <strong><?php echo $todayTasks; ?> tasks due today</strong>,
          <strong><?php echo $inProgressTasks; ?> tasks in progress</strong>,
          and <strong><?php echo $delayedTasks; ?> delayed tasks</strong>.
        </p>
      </div>

      <div class="hero-actions">
        <a href="assigntasks.php" class="hero-btn primary-btn" style="text-decoration:none;">
          <i class="fas fa-plus"></i> Assign New Task
        </a>
      </div>
    </section>

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-users"></i></div>
        <div class="card-info">
          <h3><?php echo $teamMembers; ?></h3>
          <p>Team Members</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-list-check"></i></div>
        <div class="card-info">
          <h3><?php echo $assignedTasks; ?></h3>
          <p>Total Tasks</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-person-running"></i></div>
        <div class="card-info">
          <h3><?php echo $inProgressTasks; ?></h3>
          <p>Tasks In Progress</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="card-info">
          <h3><?php echo $delayedTasks; ?></h3>
          <p>Delayed Tasks</p>
        </div>
      </div>
    </section>

    <section class="quick-actions">
      <a href="assigntasks.php" class="quick-action-card">
        <i class="fas fa-plus"></i>
        <h3>Create Task</h3>
        <p>Assign a new task to a team member with deadline and status.</p>
      </a>

      <a href="myteam.php" class="quick-action-card">
        <i class="fas fa-user-group"></i>
        <h3>View Team</h3>
        <p>Check employee information and follow team responsibilities.</p>
      </a>

      <a href="tasksprogress.php" class="quick-action-card">
        <i class="fas fa-chart-simple"></i>
        <h3>Track Progress</h3>
        <p>Monitor completed, delayed, and active work in one page.</p>
      </a>

      <a href="reportsteamleader.php" class="quick-action-card">
        <i class="fas fa-file-circle-check"></i>
        <h3>Open Reports</h3>
        <p>Review team performance reports and task summaries.</p>
      </a>
    </section>

    <section class="dashboard-grid">

      <div class="panel">
        <div class="panel-header">
          <h2>Recent Tasks</h2>
          <span>Latest Updates</span>
        </div>

        <table class="task-table">
          <thead>
            <tr>
              <th>Task</th>
              <th>Status</th>
              <th>Due Date</th>
            </tr>
          </thead>
 <tbody>
  <?php if ($recentTasksQuery && mysqli_num_rows($recentTasksQuery) > 0): ?>
    <?php while ($task = mysqli_fetch_assoc($recentTasksQuery)): ?>
      <?php
        $status = $task['status'];
        $badgeClass = 'status-pending';

        if (strtolower($status) == 'completed') {
            $badgeClass = 'status-completed';
        } elseif (strtolower($status) == 'submitted' || strtolower($status) == 'pending' || strtolower($status) == 'in progress') {
            $badgeClass = 'status-progress';
        }

        if (!empty($task['due_date']) && $task['due_date'] < date('Y-m-d') && strtolower($status) != 'completed') {
            $badgeClass = 'status-delayed';
            $status = 'Delayed';
        }
      ?>
      <tr>
      <?php echo htmlspecialchars($task['task_title']); ?></td>
        <td><span class="status-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span></td>
        <td><?php echo htmlspecialchars($task['due_date']); ?></td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr>
      <td colspan="3">No tasks found yet.</td>
    </tr>
  <?php endif; ?>
</tbody>
        </table>
      </div>

      <div class="panel">
        <div class="panel-header">
          <h2>Team Workload</h2>
          <span><?php echo $completionRate; ?>%</span>
        </div>

        <div class="progress-box">
          <div class="progress-info">
            <span>Completion Rate</span>
            <span><?php echo $completionRate; ?>%</span>
          </div>
          <div class="progress-line">
            <div class="progress-fill"></div>
          </div>
        </div>

        <div class="focus-list" style="margin-top:25px;">
          <div class="focus-item">
            <div class="focus-icon"><i class="fas fa-calendar-day"></i></div>
            <div>
              <h4>Due Today</h4>
              <p><?php echo $todayTasks; ?> tasks need attention today.</p>
            </div>
          </div>

          <div class="focus-item">
            <div class="focus-icon"><i class="fas fa-check-circle"></i></div>
            <div>
              <h4>Completed Work</h4>
              <p><?php echo $completedTasks; ?> tasks completed by the team.</p>
            </div>
          </div>

          <div class="focus-item">
            <div class="focus-icon"><i class="fas fa-clock"></i></div>
            <div>
              <h4>Delayed Work</h4>
              <p><?php echo $delayedTasks; ?> tasks passed the deadline.</p>
            </div>
          </div>
        </div>
      </div>

    </section>

    <section class="dashboard-grid">

      <div class="panel">
        <div class="panel-header">
          <h2>Today Focus</h2>
          <span>Action Plan</span>
        </div>

        <div class="focus-list">
          <div class="focus-item">
            <div class="focus-icon"><i class="fas fa-bullseye"></i></div>
            <div>
              <h4>Check urgent deadlines</h4>
              <p>Start with delayed tasks and tasks that are due today.</p>
            </div>
          </div>

          <div class="focus-item">
            <div class="focus-icon"><i class="fas fa-user-check"></i></div>
            <div>
              <h4>Balance team workload</h4>
              <p>Make sure tasks are distributed fairly between team members.</p>
            </div>
          </div>

          <div class="focus-item">
            <div class="focus-icon"><i class="fas fa-chart-line"></i></div>
            <div>
              <h4>Follow progress updates</h4>
              <p>Review tasks in progress before assigning new work.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-header">
          <h2>Priority Alerts</h2>
          <span>Live Summary</span>
        </div>

        <div class="alert-box">
          <?php if ($delayedTasks > 0): ?>
            <div class="alert-item danger">
              <h4>Delayed Tasks Found</h4>
              <p>There are <?php echo $delayedTasks; ?> delayed tasks. Review them from Tasks Progress.</p>
            </div>
          <?php else: ?>
            <div class="alert-item success">
              <h4>No Delays</h4>
              <p>Your team has no delayed tasks right now. Great work.</p>
            </div>
          <?php endif; ?>

          <div class="alert-item">
            <h4>Daily Workload</h4>
            <p><?php echo $todayTasks; ?> tasks are due today. Keep an eye on deadlines.</p>
          </div>

          <div class="alert-item success">
            <h4>Team Completion</h4>
            <p>The team completion rate is currently <?php echo $completionRate; ?>%.</p>
          </div>
        </div>
      </div>

    </section>

  </main>
</div>
<script>
function searchTeamLeaderDashboard() {
    const input = document.getElementById("teamSearch");
    const searchValue = input.value.toLowerCase().trim();
    const items = document.querySelectorAll(".searchable-item");

    items.forEach(function(item) {
        const text = item.innerText.toLowerCase();

        if (text.includes(searchValue)) {
            item.style.display = "";
        } else {
            item.style.display = "none";
        }
    });
}
</script>
</body>
</html>