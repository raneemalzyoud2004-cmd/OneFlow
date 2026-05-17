<?php
session_start();
include "config.php";

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

$today = date("Y-m-d");
$weekStart = date("Y-m-d 00:00:00", strtotime("monday this week"));
$weekEnd = date("Y-m-d 23:59:59", strtotime("sunday this week"));

if (isset($_POST['add_schedule'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);

    mysqli_query($conn, "INSERT INTO schedule (user_id, title, type, start_time, end_time, status) 
    VALUES ('$user_id', '$title', '$type', '$start_time', '$end_time', 'Pending')");

    header("Location: myschedule.php");
    exit();
}

if (isset($_POST['update_schedule'])) {
    $id = intval($_POST['schedule_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);

    mysqli_query($conn, "UPDATE schedule 
    SET title='$title', 
        type='$type', 
        start_time='$start_time', 
        end_time='$end_time' 
    WHERE id='$id' AND user_id='$user_id'");

    header("Location: myschedule.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    mysqli_query($conn, "DELETE FROM schedule 
    WHERE id='$id' AND user_id='$user_id'");

    header("Location: myschedule.php");
    exit();
}

if (isset($_GET['done'])) {
    $id = intval($_GET['done']);

    mysqli_query($conn, "UPDATE schedule 
    SET status='Done' 
    WHERE id='$id' AND user_id='$user_id'");

    header("Location: myschedule.php");
    exit();
}

$meetingsTodayQuery = mysqli_query($conn, "
SELECT COUNT(*) AS total 
FROM schedule 
WHERE user_id='$user_id' 
AND type='Meeting'
AND status!='Done'
AND DATE(start_time)='$today'
");

$meetingsToday = mysqli_fetch_assoc($meetingsTodayQuery)['total'];

$workSessionsQuery = mysqli_query($conn, "
SELECT COUNT(*) AS total 
FROM schedule 
WHERE user_id='$user_id' 
AND type='Work'
AND start_time BETWEEN '$weekStart' AND '$weekEnd'
");

$workSessions = mysqli_fetch_assoc($workSessionsQuery)['total'];

$nextMeetingQuery = mysqli_query($conn, "
SELECT start_time 
FROM schedule 
WHERE user_id='$user_id'
AND type='Meeting'
AND status!='Done'
AND start_time >= NOW()
ORDER BY start_time ASC
LIMIT 1
");

$nextMeetingRow = mysqli_fetch_assoc($nextMeetingQuery);

$nextMeeting = $nextMeetingRow 
? date("h:i A", strtotime($nextMeetingRow['start_time'])) 
: "No Meeting";

$scheduleQuery = mysqli_query($conn, "
SELECT * 
FROM schedule 
WHERE user_id='$user_id'
ORDER BY start_time ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Schedule - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

.action-btns{
display:flex;
gap:8px;
}

.small-btn{
border:none;
padding:8px 12px;
border-radius:10px;
cursor:pointer;
font-weight:700;
color:white;
text-decoration:none;
}

.edit-btn{
background:#0D1E4C;
}

.delete-btn{
background:#d9534f;
}

.done-btn{
background:#14b8a6;
}

.add-btn{
background:#14b8a6;
color:white;
border:none;
padding:12px 18px;
border-radius:14px;
cursor:pointer;
font-weight:800;
}

.modal{
display:none;
position:fixed;
inset:0;
background:rgba(0,0,0,0.4);
z-index:9999;
align-items:center;
justify-content:center;
}

.modal-content{
width:450px;
background:white;
padding:30px;
border-radius:25px;
}

.form-group{
margin-bottom:16px;
}

.form-group label{
display:block;
margin-bottom:6px;
font-weight:700;
}

.form-group input,
.form-group select{
width:100%;
padding:12px;
border-radius:12px;
border:1px solid #ddd;
}

.modal-actions{
display:flex;
justify-content:flex-end;
gap:10px;
margin-top:20px;
}

.save-btn{
background:#14b8a6;
}

.close-btn{
background:#6b7280;
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

    <ul class="sidebar-menu">
      <li><a href="dashboardemployee.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="mytasks.php"><i class="fas fa-list-check"></i> My Tasks</a></li>
      <li><a href="myattendance.php"><i class="fas fa-calendar-check"></i> My Attendance</a></li>
      <li class="active"><a href="myschedule.php"><i class="fas fa-clock"></i> My Schedule</a></li>
      <li><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
      <li><a href="settingsemployee.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>Performance Status</p>
        <h4>Excellent</h4>
        <span>On track this week</span>
      </div>
    </div>
  </aside>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1>My Schedule</h1>
        <p>Stay updated with your meetings, work plan, and important times.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search schedule...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">3</span>
        </div>

        <div class="admin-avatar">E</div>
        <div>
          <h4>Employee</h4>
          <span>Team Member</span>
        </div>

        <button class="logout-btn">Logout</button>
      </div>
    </header>

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="card-info">
          <h3>1</h3>
          <p>Meetings Today</p>
          <span>Planned for today</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-business-time"></i></div>
        <div class="card-info">
          <h3>5</h3>
          <p>Work Sessions</p>
          <span>This week</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
          <h3>2:00 PM</h3>
          <p>Next Meeting</p>
          <span>Today</span>
        </div>
      </div>
    </section>

    <section class="panel">
      <div class="panel-header">
        <h2>Today Schedule</h2>
        <a href="#">View Calendar</a>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Time</th>
              <th>Activity</th>
              <th>Type</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>08:00 AM</td>
              <td>Check In</td>
              <td>Work Start</td>
              <td><span class="status approved">Done</span></td>
            </tr>
            <tr>
              <td>10:00 AM</td>
              <td>Team Review</td>
              <td>Meeting</td>
              <td><span class="status approved">Done</span></td>
            </tr>
            <tr>
              <td>02:00 PM</td>
              <td>Project Sync</td>
              <td>Meeting</td>
              <td><span class="status pending">Upcoming</span></td>
            </tr>
            <tr>
              <td>04:00 PM</td>
              <td>Check Out</td>
              <td>Work End</td>
              <td><span class="status rejected">Pending</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
>>>>>>> 1a1950db7b23d9fe7423380d6752c068516d834e
</div>

<p class="admin-role">Employee Panel</p>
</div>

<ul class="sidebar-menu">
<li><a href="dashboardemployee.php"><i class="fas fa-house"></i> Dashboard</a></li>
<li><a href="mytasks.php"><i class="fas fa-list-check"></i> My Tasks</a></li>
<li><a href="leaverequests_employee.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
<li><a href="myattendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
<li class="active"><a href="myschedule.php"><i class="fas fa-clock"></i> Schedule</a></li>
<li><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
<li><a href="settingsemployee.php"><i class="fas fa-gear"></i> Settings</a></li>
</ul>

<div class="sidebar-bottom">
<div class="system-card">
<p>Performance Status</p>
<h4>Excellent</h4>
<span>On track this week</span>
</div>
</div>

</aside>

<main class="main-content">

<header class="topbar">

<div class="topbar-left">
<h1>My Schedule</h1>
<p>Stay updated with your meetings, work plan, and important times.</p>
</div>

<div class="topbar-right">

<div class="search-box">
<i class="fas fa-search"></i>
<input type="text" placeholder="Search schedule...">
</div>

<div class="icon-btn notification-bell">
<i class="fas fa-bell"></i>
<span class="notif-count">3</span>
</div>

<div class="admin-avatar">
<?php echo strtoupper(substr($full_name,0,1)); ?>
</div>

<div>
<h4><?php echo htmlspecialchars($full_name); ?></h4>
<span>Team Member</span>
</div>

<button class="logout-btn" onclick="window.location.href='logout.php'">
Logout
</button>

</div>

</header>

<section class="cards">

<div class="card">
<div class="card-icon">
<i class="fas fa-calendar-day"></i>
</div>

<div class="card-info">
<h3><?php echo $meetingsToday; ?></h3>
<p>Meetings Today</p>
<span>Planned for today</span>
</div>
</div>

<div class="card">
<div class="card-icon">
<i class="fas fa-business-time"></i>
</div>

<div class="card-info">
<h3><?php echo $workSessions; ?></h3>
<p>Work Sessions</p>
<span>This week</span>
</div>
</div>

<div class="card">
<div class="card-icon">
<i class="fas fa-clock"></i>
</div>

<div class="card-info">
<h3><?php echo $nextMeeting; ?></h3>
<p>Next Meeting</p>
<span>Today</span>
</div>
</div>

</section>

<section class="panel">

<div class="panel-header">
<h2>Today Schedule</h2>

<button class="add-btn" onclick="openAddModal()">
Add Schedule
</button>
</div>

<div class="table-wrapper">

<table>

<thead>
<tr>
<th>Time</th>
<th>Activity</th>
<th>Type</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($scheduleQuery)) {

$now = time();
$start = strtotime($row['start_time']);
$end = strtotime($row['end_time']);

if ($row['status'] == 'Done') {
$statusText = "Done";
$statusClass = "approved";
}

elseif ($now >= $start && $now <= $end) {
$statusText = "Ongoing";
$statusClass = "pending";
}

else {
$statusText = "Upcoming";
$statusClass = "pending";
}
?>

<tr>

<td>
<?php echo date("h:i A", strtotime($row['start_time'])); ?>
</td>

<td>
<?php echo htmlspecialchars($row['title']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['type']); ?>
</td>

<td>
<span class="status <?php echo $statusClass; ?>">
<?php echo $statusText; ?>
</span>
</td>

<td>

<div class="action-btns">

<button class="small-btn edit-btn"
onclick="openEditModal(
'<?php echo $row['id']; ?>',
'<?php echo htmlspecialchars($row['title']); ?>',
'<?php echo $row['type']; ?>',
'<?php echo date('Y-m-d\TH:i', strtotime($row['start_time'])); ?>',
'<?php echo date('Y-m-d\TH:i', strtotime($row['end_time'])); ?>'
)">
Edit
</button>

<a class="small-btn done-btn"
href="myschedule.php?done=<?php echo $row['id']; ?>">
Done
</a>

<a class="small-btn delete-btn"
href="myschedule.php?delete=<?php echo $row['id']; ?>">
Delete
</a>

</div>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</section>

</main>

</div>

<div class="modal" id="addModal">

<div class="modal-content">

<h2>Add Schedule</h2>

<form method="POST">

<div class="form-group">
<label>Activity Title</label>
<input type="text" name="title" required>
</div>

<div class="form-group">
<label>Type</label>

<select name="type" required>
<option value="Meeting">Meeting</option>
<option value="Work">Work</option>
<option value="Training">Training</option>
<option value="Break">Break</option>
</select>
</div>

<div class="form-group">
<label>Start Time</label>
<input type="datetime-local" name="start_time" required>
</div>

<div class="form-group">
<label>End Time</label>
<input type="datetime-local" name="end_time" required>
</div>

<div class="modal-actions">

<button type="button"
class="small-btn close-btn"
onclick="closeAddModal()">
Cancel
</button>

<button type="submit"
name="add_schedule"
class="small-btn save-btn">
Save
</button>

</div>

</form>

</div>

</div>

<div class="modal" id="editModal">

<div class="modal-content">

<h2>Edit Schedule</h2>

<form method="POST">

<input type="hidden" name="schedule_id" id="edit_id">

<div class="form-group">
<label>Activity Title</label>
<input type="text" name="title" id="edit_title" required>
</div>

<div class="form-group">
<label>Type</label>

<select name="type" id="edit_type" required>
<option value="Meeting">Meeting</option>
<option value="Work">Work</option>
<option value="Training">Training</option>
<option value="Break">Break</option>
</select>

</div>

<div class="form-group">
<label>Start Time</label>
<input type="datetime-local" name="start_time" id="edit_start" required>
</div>

<div class="form-group">
<label>End Time</label>
<input type="datetime-local" name="end_time" id="edit_end" required>
</div>

<div class="modal-actions">

<button type="button"
class="small-btn close-btn"
onclick="closeEditModal()">
Cancel
</button>

<button type="submit"
name="update_schedule"
class="small-btn save-btn">
Update
</button>

</div>

</form>

</div>

</div>

<script>

function openAddModal(){
document.getElementById("addModal").style.display="flex";
}

function closeAddModal(){
document.getElementById("addModal").style.display="none";
}

function openEditModal(id,title,type,start,end){

document.getElementById("edit_id").value=id;
document.getElementById("edit_title").value=title;
document.getElementById("edit_type").value=type;
document.getElementById("edit_start").value=start;
document.getElementById("edit_end").value=end;

document.getElementById("editModal").style.display="flex";
}

function closeEditModal(){
document.getElementById("editModal").style.display="none";
}

</script>

</body>
</html>