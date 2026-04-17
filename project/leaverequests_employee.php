<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

include("config.php");

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leave_type = trim($_POST['leave_type']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);

    if (empty($leave_type) || empty($start_date) || empty($end_date)) {
        $errorMessage = "Please fill in all required fields.";
    } elseif ($end_date < $start_date) {
        $errorMessage = "End date cannot be before start date.";
    } else {
        $stmt = $conn->prepare("INSERT INTO leave_requests (employee_id, employee_name, leave_type, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, 'Pending')");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("issss", $user_id, $full_name, $leave_type, $start_date, $end_date);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Leave request submitted successfully!";
            header("Location: leaverequests_employee.php");
            exit();
        } else {
            $errorMessage = "Execute failed: " . $stmt->error;
        }

        $stmt->close();
    }
}

$myRequests = $conn->prepare("SELECT leave_type, start_date, end_date, status FROM leave_requests WHERE employee_id = ? ORDER BY id DESC");

if (!$myRequests) {
    die("Prepare failed: " . $conn->error);
}

$myRequests->bind_param("i", $user_id);
$myRequests->execute();
$result = $myRequests->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leave Requests - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">

  <aside class="sidebar">
    <div class="sidebar-top">
      <div class="logo-box">
        <i class="fa-solid fa-leaf"></i>
        <h2>OneFlow</h2>
      </div>
      <p class="admin-role">Employee Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="dashboardemployee.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="mytasks.php"><i class="fas fa-list-check"></i> My Tasks</a></li>
      <li><a href="myattendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
      <li><a href="myschedule.php"><i class="fas fa-clock"></i> Schedule</a></li>
      <li class="active"><a href="leaverequests_employee.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
      <li><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingsemployee.php"><i class="fas fa-gear"></i> Settings</a></li>
      <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>System Status</p>
        <h4>Online</h4>
        <span>All services running</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Leave Requests</h1>
        <p>Submit and track your leave requests in one place.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search your requests...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">2</span>
        </div>

        <div class="admin-profile">
          <div class="admin-avatar">
            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
          </div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>Employee</span>
          </div>
        </div>
      </div>
    </header>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>Request Your Leave Easily ✨</h2>
        <p>Fill out the form below and track the status of your leave requests anytime.</p>
      </div>

      <div class="hero-actions">
        <a href="#leaveForm" class="hero-btn primary-btn"><i class="fas fa-paper-plane"></i> New Request</a>
      </div>
    </section>

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="card-info">
          <h3>2</h3>
          <p>Pending Requests</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-circle-check"></i></div>
        <div class="card-info">
          <h3>5</h3>
          <p>Approved Requests</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-circle-xmark"></i></div>
        <div class="card-info">
          <h3>1</h3>
          <p>Rejected Requests</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="card-info">
          <h3>14</h3>
          <p>Leave Balance</p>
        </div>
      </div>
    </section>

    <section class="panel" id="leaveForm">
      <div class="panel-header">
        <h2>Submit Leave Request</h2>
      </div>

      <?php if (isset($_SESSION['success_message'])) : ?>
        <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>

      <?php if (!empty($errorMessage)) : ?>
        <div class="error-message"><?php echo $errorMessage; ?></div>
      <?php endif; ?>

      <form class="leave-form" method="POST" action="">
        <div class="form-grid">
          <div class="form-group">
            <label for="leave_type">Leave Type</label>
            <select name="leave_type" id="leave_type" required>
              <option value="">Select leave type</option>
              <option value="Annual Leave">Annual Leave</option>
              <option value="Sick Leave">Sick Leave</option>
              <option value="Emergency Leave">Emergency Leave</option>
              <option value="Maternity Leave">Maternity Leave</option>
            </select>
          </div>

          <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" id="start_date" required>
          </div>

          <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" name="end_date" id="end_date" required>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="submit-btn">Submit Request</button>
        </div>
      </form>
    </section>

    <section class="panel">
      <div class="panel-header">
        <h2>My Leave Requests</h2>
        <a href="#">View All</a>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Leave Type</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                  <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                  <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                  <td>
                    <span class="status <?php echo strtolower($row['status']); ?>">
                      <?php echo htmlspecialchars($row['status']); ?>
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" style="text-align:center;">No leave requests yet.</td>
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