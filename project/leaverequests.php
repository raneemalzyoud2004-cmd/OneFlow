<?php
session_start();
include "config.php";

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hr') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];
$successMessage = "";

if (isset($_POST['update_status'])) {
    $requestId = intval($_POST['request_id']);
    $newStatus = mysqli_real_escape_string($conn, $_POST['status']);

    mysqli_query($conn, "
        UPDATE leave_requests 
        SET status='$newStatus' 
        WHERE id=$requestId
    ");

    $successMessage = "Leave request updated successfully.";
}

$pendingCount = 0;
$approvedCount = 0;
$rejectedCount = 0;
$onLeaveToday = 0;
$today = date("Y-m-d");

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM leave_requests WHERE status='Pending'");
if ($result) $pendingCount = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM leave_requests WHERE status='Approved'");
if ($result) $approvedCount = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM leave_requests WHERE status='Rejected'");
if ($result) $rejectedCount = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM leave_requests 
    WHERE status='Approved' 
    AND '$today' BETWEEN start_date AND end_date
");
if ($result) $onLeaveToday = mysqli_fetch_assoc($result)['total'];

$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $requests = mysqli_query($conn, "
        SELECT * FROM leave_requests
        WHERE employee_name LIKE '%$search%'
        OR leave_type LIKE '%$search%'
        OR status LIKE '%$search%'
        ORDER BY created_at DESC
    ");
} else {
    $requests = mysqli_query($conn, "
        SELECT * FROM leave_requests
        ORDER BY created_at DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leave Requests - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .search-box form {
      display: flex;
      align-items: center;
      width: 100%;
    }

    .search-box button {
      border: none;
      background: transparent;
      cursor: pointer;
      color: #0D1E4C;
      font-size: 15px;
    }

    .success-message {
      background: #e7f8ee;
      color: #166534;
      padding: 14px 18px;
      border-radius: 14px;
      margin-bottom: 18px;
      font-weight: 700;
    }

    .action-form {
      display: inline-block;
      margin-right: 6px;
    }

    .action-btn {
      padding: 8px 12px;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      font-weight: 700;
      font-size: 13px;
    }

    .approve {
      background: #dcfce7;
      color: #166534;
    }

    .reject {
      background: #fee2e2;
      color: #991b1b;
    }

    .view {
      background: #E5C9D7;
      color: #0D1E4C;
    }

    .date-text {
      color: #6b7280;
      font-size: 13px;
      font-weight: 600;
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
      <p class="admin-role">HR Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="hrdashboard.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
      <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
      <li class="active"><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
      <li><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>
      <li><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingshr.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>System Health</p>
        <h4>Excellent</h4>
        <span>99.2% uptime</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Leave Requests</h1>
        <p>Review and manage employee leave submissions.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <form method="GET" action="leaverequests.php">
            <i class="fas fa-search"></i>
            <input 
              type="text" 
              name="search" 
              placeholder="Search leave requests..."
              value="<?php echo htmlspecialchars($search); ?>"
            >
            <button type="submit"><i class="fas fa-arrow-right"></i></button>
          </form>
        </div>

        <div class="admin-profile">
          <div class="admin-avatar">
            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
          </div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>HR Manager</span>
          </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </header>

    <?php if (!empty($successMessage)): ?>
      <div class="success-message"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>Manage Leave Requests</h2>
        <p>Approve, reject, and review employee leave requests from one place.</p>
      </div>

      <div class="hero-actions">
        <a href="leaverequests.php" class="hero-btn secondary-btn">
          <i class="fas fa-rotate"></i> Refresh
        </a>
      </div>
    </section>

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="card-info">
          <h3><?php echo $pendingCount; ?></h3>
          <p>Pending Requests</p>
          <span>Waiting for review</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-circle-check"></i></div>
        <div class="card-info">
          <h3><?php echo $approvedCount; ?></h3>
          <p>Approved</p>
          <span>Processed successfully</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-circle-xmark"></i></div>
        <div class="card-info">
          <h3><?php echo $rejectedCount; ?></h3>
          <p>Rejected</p>
          <span>Declined requests</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="card-info">
          <h3><?php echo $onLeaveToday; ?></h3>
          <p>On Leave Today</p>
          <span>Approved absences</span>
        </div>
      </div>
    </section>

    <section class="panel">
      <div class="panel-header">
        <h2>Recent Leave Requests</h2>
        <a href="leaverequests.php">View All</a>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Employee</th>
              <th>Leave Type</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Status</th>
              <th>Submitted At</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <?php if ($requests && mysqli_num_rows($requests) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($requests)): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                  <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                  <td><?php echo htmlspecialchars($row['end_date']); ?></td>

                  <td>
                    <?php
                      $statusClass = "pending";
                      if ($row['status'] == "Approved") $statusClass = "approved";
                      if ($row['status'] == "Rejected") $statusClass = "rejected";
                    ?>
                    <span class="status <?php echo $statusClass; ?>">
                      <?php echo htmlspecialchars($row['status']); ?>
                    </span>
                  </td>

                  <td>
                    <span class="date-text">
                      <?php echo htmlspecialchars($row['created_at']); ?>
                    </span>
                  </td>

                  <td>
                    <?php if ($row['status'] == "Pending"): ?>
                      <form method="POST" action="leaverequests.php" class="action-form">
                        <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="status" value="Approved">
                        <button 
                          type="submit" 
                          name="update_status" 
                          class="action-btn approve"
                          onclick="return confirm('Approve this leave request?');"
                        >
                          Approve
                        </button>
                      </form>

                      <form method="POST" action="leaverequests.php" class="action-form">
                        <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="status" value="Rejected">
                        <button 
                          type="submit" 
                          name="update_status" 
                          class="action-btn reject"
                          onclick="return confirm('Reject this leave request?');"
                        >
                          Reject
                        </button>
                      </form>
                    <?php else: ?>
                      <button class="action-btn view" disabled>Reviewed</button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7">No leave requests found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

</body>
</html>