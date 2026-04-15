<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

if (!isset($_GET['id'])) {
    header("Location: dashboardadmin.php");
    exit();
}

$user_id = intval($_GET['id']);

$query = "SELECT id, full_name, username, role FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "User not found.";
    exit();
}

$user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Information - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
      <p class="admin-role">Admin Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="dashboardadmin.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="manageusers.php"><i class="fas fa-users"></i> Manage Users</a></li>
      <li><a href="hrteam.php"><i class="fas fa-user-tie"></i> HR Team</a></li>
      <li><a href="requestsadmin.php"><i class="fas fa-file-circle-check"></i> Requests</a></li>
      <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
      <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Employee Information</h1>
        <p>View employee details from the system.</p>
      </div>

      <div class="topbar-right">
        <div class="admin-profile">
          <div class="admin-avatar">A</div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>Super Admin</span>
          </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </header>

    <section class="hero-banner">
      <div class="hero-text">
        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
        <p>This page shows the selected employee details.</p>
      </div>
      <div class="hero-actions">
        <a href="dashboardadmin.php" class="hero-btn primary-btn">
          <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
      </div>
    </section>

    <div class="panel">
      <div class="panel-header">
        <h2>User Details</h2>
      </div>

      <div class="overview-box">
        <div class="overview-row">
          <span>User ID</span>
          <strong><?php echo htmlspecialchars($user['id']); ?></strong>
        </div>

        <div class="overview-row">
          <span>Full Name</span>
          <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
        </div>

        <div class="overview-row">
          <span>Username</span>
          <strong><?php echo htmlspecialchars($user['username']); ?></strong>
        </div>

        <div class="overview-row">
          <span>Role</span>
          <strong><?php echo ucfirst(htmlspecialchars($user['role'])); ?></strong>
        </div>
      </div>
    </div>

  </main>
</div>

</body>
</html>