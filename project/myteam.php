<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Team Leader';
$teamQuery = "SELECT id, full_name, username, email, role, account_status
              FROM users
              WHERE role = 'employee'
              AND id IN (3, 4, 5, 8, 9)
              ORDER BY id ASC";

$teamResult = mysqli_query($conn, $teamQuery);
$totalMembers = $teamResult ? mysqli_num_rows($teamResult) : 0;
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
       <p>You currently manage <strong><?php echo $totalMembers; ?> team members</strong> from the database.</p>
      </div>

      <div class="hero-actions">
       
        <button class="hero-btn secondary-btn"><i class="fas fa-list-check"></i> Assign New Task</button>
      </div>
    </section>

    <section class="team-grid">

    <?php if ($teamResult && mysqli_num_rows($teamResult) > 0) { ?>
    
<?php while ($member = mysqli_fetch_assoc($teamResult)) { 
    
$initial = strtoupper(substr($member['full_name'], 0, 1));

$statusClass = ($member['account_status'] === 'active') 
? "active-status" 
: "offline-status";

$statusText = ucfirst($member['account_status']);
?>

<div class="team-card">

  <div class="team-top">
    <div class="team-avatar">
      <?php echo $initial; ?>
    </div>

    <div class="team-name">
      <h3><?php echo htmlspecialchars($member['full_name']); ?></h3>
      <span><?php echo ucfirst($member['role']); ?></span>
    </div>
  </div>

  <div class="member-info">

    <div class="info-row">
      <span>Status</span>

      <span class="status-badge <?php echo $statusClass; ?>">
        <?php echo $statusText; ?>
      </span>
    </div>

    <div class="info-row">
      <span>Username</span>
      <strong><?php echo htmlspecialchars($member['username']); ?></strong>
    </div>

    <div class="info-row">
      <span>Email</span>

      <strong>
        <?php 
        echo !empty($member['email']) 
        ? htmlspecialchars($member['email']) 
        : 'No email'; 
        ?>
      </strong>
    </div>

  </div>

  <div class="member-actions">

    <a href="employeeprofile_tl.php?id=<?php echo $member['id']; ?>" 
   class="member-btn view-btn" 
   style="text-decoration:none;">
   View Profile
</a>
    

  
  </div>

</div>

<?php } ?>

<?php } else { ?>

<div class="team-card">
  <h3>No team members found</h3>
</div>

<?php } ?>
</body>
</html>