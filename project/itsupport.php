<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hr') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

// Handle Add Ticket
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_ticket'])) {
    $description = trim($_POST['description']);
    $reported_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO technical_issues (reported_by, description, status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("is", $reported_by, $description);
    $stmt->execute();
    $stmt->close();

    header("Location: itsupport.php");
    exit();
}

// Handle Update Ticket
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_ticket'])) {
    $ticket_id = (int)$_POST['ticket_id'];
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE technical_issues SET assigned_to = ?, status = ? WHERE id = ?");
    $stmt->bind_param("isi", $assigned_to, $status, $ticket_id);
    $stmt->execute();
    $stmt->close();

    header("Location: itsupport.php");
    exit();
}

// Fetch tickets
$ticketsQuery = mysqli_query($conn, "
    SELECT t.id, t.description, t.status, t.created_at, t.updated_at, 
           u.full_name AS reporter, t.assigned_to
    FROM technical_issues t
    LEFT JOIN users u ON t.reported_by = u.id
    ORDER BY t.id DESC
");
$tickets = [];
if ($ticketsQuery) {
    while($row = mysqli_fetch_assoc($ticketsQuery)) {
        $tickets[] = $row;
    }
}

// Fetch employees for Assign To dropdown
$employeesQuery = mysqli_query($conn, "SELECT id, full_name FROM users WHERE role='employee' ORDER BY full_name");
$employees = [];
if ($employeesQuery) {
    while($row = mysqli_fetch_assoc($employeesQuery)) {
        $employees[] = $row;
    }
}

// Insert example ticket if empty
if (empty($tickets)) {
    $conn->query("INSERT INTO technical_issues (reported_by, description) VALUES (".$_SESSION['user_id'].", 'Example Ticket: System login error')");
    header("Location: itsupport.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IT Support - HR | OneFlow</title>
<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.panel { background:#fff; border-radius:16px; padding:20px; box-shadow:0 8px 20px rgba(0,0,0,0.05); margin-bottom:24px; }
.table-wrapper { overflow-x:auto; }
.kpi-table { width:100%; border-collapse:collapse; }
.kpi-table th, .kpi-table td { padding:12px; border-bottom:1px solid #e5e8eb; text-align:left; }
.kpi-table th { background:#f2f6f8; font-weight:700; }
.status { padding:6px 10px; border-radius:12px; font-weight:700; font-size:12px; display:inline-block; }
.status.Pending { background:#fef3c7; color:#92400e; }
.status['In Progress'] { background:#bfdbfe; color:#1e40af; }
.status.Resolved { background:#dcfce7; color:#166534; }
.add-btn { background:#0ea5a4; color:white; border:none; padding:10px 14px; border-radius:12px; cursor:pointer; font-weight:700; margin-bottom:16px; }
.popup-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:none; justify-content:center; align-items:center; z-index:9999; }
.popup-content { background:white; padding:24px; border-radius:16px; width:400px; max-width:90%; display:flex; flex-direction:column; gap:14px; }
.popup-content textarea, select { width:100%; padding:10px; border-radius:12px; border:1px solid #dbe7f0; }
.popup-content button, .update-btn { padding:8px 12px; border:none; border-radius:10px; background:#14b8a6; color:white; font-weight:700; cursor:pointer; }
.popup-content button.cancel { background:#ccc; color:#0f172a; }
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
      <li><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
      <li><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>
      <li class="active"><a href="itsupport.php"><i class="fas fa-tools"></i> IT Support</a></li>
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
    <h1>IT Support Section</h1>
    <p>Manage technical issues submitted by employees through HR.</p>
  </div>
  <div class="topbar-right">
    <div class="admin-profile">
      <div class="admin-avatar">HR</div>
      <div><h4><?php echo htmlspecialchars($full_name); ?></h4><span>HR</span></div>
    </div>
  </div>
</header>

<section class="panel">
  <button class="add-btn" onclick="openPopup()"><i class="fas fa-plus"></i> Add Ticket</button>
  <div class="table-wrapper">
    <table class="kpi-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Description</th>
          <th>Reported By</th>
          <th>Assign To</th>
          <th>Status</th>
          <th>Created At</th>
          <th>Updated At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($tickets as $ticket) { ?>
        <tr>
          <td><?php echo $ticket['id']; ?></td>
          <td><?php echo htmlspecialchars($ticket['description']); ?></td>
          <td><?php echo htmlspecialchars($ticket['reporter']); ?></td>
          <td>
            <form method="POST" style="display:flex; gap:4px;">
              <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
              <select name="assigned_to">
                <option value="">-- Select Employee --</option>
                <?php foreach($employees as $emp) { ?>
                  <option value="<?php echo $emp['id']; ?>" <?php if($ticket['assigned_to']==$emp['id']) echo "selected"; ?>>
                    <?php echo htmlspecialchars($emp['full_name']); ?>
                  </option>
                <?php } ?>
              </select>
          </td>
          <td>
              <select name="status">
                <option value="Pending" <?php if($ticket['status']=="Pending") echo "selected"; ?>>Pending</option>
                <option value="In Progress" <?php if($ticket['status']=="In Progress") echo "selected"; ?>>In Progress</option>
                <option value="Resolved" <?php if($ticket['status']=="Resolved") echo "selected"; ?>>Resolved</option>
              </select>
          </td>
          <td><?php echo $ticket['created_at']; ?></td>
          <td><?php echo $ticket['updated_at']; ?></td>
          <td><button type="submit" name="update_ticket" class="update-btn">Update</button></td>
            </form>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Pop-up Add Ticket -->
<div class="popup-overlay" id="popupForm">
  <div class="popup-content">
    <h3>Add Technical Issue</h3>
    <form method="POST">
      <label for="description">Description</label>
      <textarea name="description" id="description" placeholder="Describe the technical issue..." required></textarea>
      <button type="submit" name="add_ticket">Add Ticket</button>
      <button type="button" class="cancel" onclick="closePopup()">Cancel</button>
    </form>
  </div>
</div>

<script>
function openPopup(){ document.getElementById('popupForm').style.display='flex'; }
function closePopup(){ document.getElementById('popupForm').style.display='none'; }
</script>

</main>
</div>
</body>
</html>