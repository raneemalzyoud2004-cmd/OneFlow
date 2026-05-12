<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teamleader') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

if (!isset($_GET['id'])) {
    header("Location: myteam.php");
    exit();
}

$id = intval($_GET['id']);

$query = "SELECT * FROM users WHERE id = $id AND role = 'employee'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Employee not found.";
    exit();
}

$employee = mysqli_fetch_assoc($result);

$tasksQuery = "SELECT * FROM tasks WHERE employee_id = $id ORDER BY id DESC";
$tasksResult = mysqli_query($conn, $tasksQuery);

$totalTasks = $tasksResult ? mysqli_num_rows($tasksResult) : 0;

$completedQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE employee_id = $id AND status = 'completed'");
$completedTasks = ($completedQuery && $row = mysqli_fetch_assoc($completedQuery)) ? $row['total'] : 0;

$pendingQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE employee_id = $id AND status != 'completed'");
$pendingTasks = ($pendingQuery && $row = mysqli_fetch_assoc($pendingQuery)) ? $row['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Profile - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .profile-wrapper {
      display: grid;
      grid-template-columns: 1fr 1.4fr;
      gap: 24px;
      margin-top: 30px;
    }

    .profile-card, .tasks-card {
      background: white;
      border-radius: 26px;
      padding: 28px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
    }

    .profile-header {
      text-align: center;
      margin-bottom: 25px;
    }

    .profile-avatar {
      width: 90px;
      height: 90px;
      border-radius: 24px;
      background: linear-gradient(135deg, #19c2c9, #22c55e);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 38px;
      font-weight: bold;
      margin: 0 auto 14px;
    }

    .profile-header h2 {
      margin: 0;
      font-size: 30px;
      color: #0f172a;
    }

    .profile-header p {
      color: #64748b;
      margin-top: 5px;
    }

    .info-box {
      display: flex;
      justify-content: space-between;
      padding: 14px 0;
      border-bottom: 1px solid #eef2f7;
      color: #334155;
    }

    .info-box strong {
      color: #0f172a;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
      margin-top: 24px;
    }

    .stat-box {
      background: #f8fbff;
      padding: 18px;
      border-radius: 18px;
      text-align: center;
      border: 1px solid #e5eef5;
    }

    .stat-box h3 {
      font-size: 28px;
      color: #0f172a;
      margin: 0;
    }

    .stat-box p {
      color: #64748b;
      font-size: 13px;
      margin-top: 5px;
    }

    .action-row {
      display: flex;
      gap: 12px;
      margin-top: 24px;
    }

    .profile-btn {
      padding: 12px 18px;
      border-radius: 14px;
      text-decoration: none;
      font-weight: 700;
      display: inline-block;
    }

    .assign-btn {
      background: #dcfce7;
      color: #166534;
    }

    .back-btn {
      background: #e0f2fe;
      color: #0369a1;
    }

    .task-item {
      background: #f8fbff;
      border: 1px solid #e5eef5;
      border-radius: 18px;
      padding: 18px;
      margin-bottom: 14px;
    }

    .task-item h4 {
      margin: 0 0 8px;
      color: #0f172a;
      font-size: 19px;
    }

    .task-item p {
      color: #64748b;
      margin: 0 0 10px;
    }

    .badge {
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      background: #dcfce7;
      color: #166534;
    }

    @media(max-width: 900px) {
      .profile-wrapper {
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
      <li class="active"><a href="myteam.php"><i class="fas fa-users"></i> My Team</a></li>
      <li><a href="assigntasks.php"><i class="fas fa-list-check"></i> Assign Tasks</a></li>
      <li><a href="tasksprogress.php"><i class="fas fa-chart-line"></i> Tasks Progress</a></li>
      <li><a href="reportsteamleader.php"><i class="fas fa-file-lines"></i> Reports</a></li>
      <li><a href="notificationsteamleader.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingsteamleader.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Employee Profile</h1>
        <p>View employee details, login activity, and assigned tasks.</p>
      </div>

      <div class="topbar-right">
        <div class="admin-profile">
          <div class="admin-avatar"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
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
        <h2><?php echo htmlspecialchars($employee['full_name']); ?> Profile</h2>
        <p>Review this employee information and manage their assigned tasks.</p>
      </div>

      <div class="hero-actions">
        <a href="assigntasks.php?employee_id=<?php echo $employee['id']; ?>" class="hero-btn primary-btn">
          <i class="fas fa-list-check"></i> Assign Task
        </a>
      </div>
    </section>

    <section class="profile-wrapper">

      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-avatar">
            <?php echo strtoupper(substr($employee['full_name'], 0, 1)); ?>
          </div>
          <h2><?php echo htmlspecialchars($employee['full_name']); ?></h2>
          <p><?php echo ucfirst($employee['role']); ?></p>
        </div>

        <div class="info-box"><span>Username</span><strong><?php echo htmlspecialchars($employee['username']); ?></strong></div>
        <div class="info-box"><span>Email</span><strong><?php echo !empty($employee['email']) ? htmlspecialchars($employee['email']) : 'No email'; ?></strong></div>
        <div class="info-box"><span>Status</span><strong><?php echo ucfirst($employee['account_status']); ?></strong></div>
        <div class="info-box"><span>Salary</span><strong><?php echo $employee['salary']; ?></strong></div>
        <div class="info-box"><span>Failed Attempts</span><strong><?php echo $employee['failed_attempts']; ?></strong></div>
        <div class="info-box"><span>Blocked</span><strong><?php echo $employee['is_blocked'] == 1 ? 'Yes' : 'No'; ?></strong></div>
        <div class="info-box"><span>Last Login</span><strong><?php echo !empty($employee['last_login']) ? $employee['last_login'] : 'Never'; ?></strong></div>

        <div class="stats-grid">
          <div class="stat-box">
            <h3><?php echo $totalTasks; ?></h3>
            <p>Total Tasks</p>
          </div>
          <div class="stat-box">
            <h3><?php echo $completedTasks; ?></h3>
            <p>Completed</p>
          </div>
          <div class="stat-box">
            <h3><?php echo $pendingTasks; ?></h3>
            <p>Pending</p>
          </div>
        </div>

        <div class="action-row">
          <a href="assigntasks.php?employee_id=<?php echo $employee['id']; ?>" class="profile-btn assign-btn">Assign Task</a>
          <a href="myteam.php" class="profile-btn back-btn">Back</a>
        </div>
      </div>

      <div class="tasks-card">
        <h2>Assigned Tasks</h2>

        <?php if ($tasksResult && mysqli_num_rows($tasksResult) > 0) { ?>
          <?php while ($task = mysqli_fetch_assoc($tasksResult)) { ?>
            <div class="task-item">
              <h4><?php echo htmlspecialchars($task['task_title']); ?></h4>
              <p><?php echo htmlspecialchars($task['task_description']); ?></p>
              <span class="badge"><?php echo htmlspecialchars($task['status']); ?></span>
              <span class="badge"><?php echo htmlspecialchars($task['priority']); ?></span>
              <span class="badge">Due: <?php echo htmlspecialchars($task['due_date']); ?></span>
            </div>
          <?php } ?>
        <?php } else { ?>
          <p>No tasks assigned to this employee yet.</p>
        <?php } ?>

      </div>

    </section>

  </main>

</div>

</body>
</html>