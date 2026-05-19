<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'itsupport') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];
$user_id = $_SESSION['user_id'];

if (isset($_POST['resolve_ticket'])) {

    $ticket_id = intval($_POST['ticket_id']);

    mysqli_query($conn, "
        UPDATE support_tickets
        SET status='Resolved'
        WHERE id='$ticket_id'
    ");
}

if (isset($_POST['assign_ticket'])) {

    $ticket_id = intval($_POST['ticket_id']);

    mysqli_query($conn, "
        UPDATE support_tickets
        SET status='In Progress',
            assigned_to='$user_id'
        WHERE id='$ticket_id'
    ");
}

$totalTickets = 0;
$openTickets = 0;
$progressTickets = 0;
$resolvedTickets = 0;
$newTickets = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets");
if ($result) {
    $totalTickets = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets WHERE status='Pending'");
if ($result) {
    $openTickets = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets WHERE status='In Progress'");
if ($result) {
    $progressTickets = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets WHERE status='Resolved'");
if ($result) {
    $resolvedTickets = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM support_tickets
    WHERE status='Pending'
    AND assigned_to IS NULL
");

if ($result) {
    $newTickets = mysqli_fetch_assoc($result)['total'];
}

$tickets = mysqli_query($conn, "
    SELECT support_tickets.*, users.full_name AS assigned_name
    FROM support_tickets
    LEFT JOIN users ON support_tickets.assigned_to = users.id
    ORDER BY support_tickets.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>IT Support Dashboard - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

.ticket-status{
    padding:7px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:700;
    display:inline-block;
}

.pending{
    background:#fff7d6;
    color:#9a6700;
}

.progress{
    background:#dbeafe;
    color:#1d4ed8;
}

.resolved{
    background:#dcfce7;
    color:#166534;
}

.action-btn{
    border:none;
    padding:9px 14px;
    border-radius:12px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    color:white;
}

.assign-btn{
    background:#3b82f6;
}

.resolve-btn{
    background:#22c55e;
}

.hero-support{
    background:linear-gradient(135deg,#0D1E4C,#14b8a6);
    border-radius:26px;
    padding:28px;
    color:white;
    margin-bottom:25px;
}

.hero-support h2{
    font-size:32px;
    margin-bottom:10px;
}

.hero-support p{
    opacity:0.9;
    line-height:1.7;
}

.quick-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-bottom:25px;
}

.quick-card{
    background:white;
    border-radius:24px;
    padding:22px;
    box-shadow:0 10px 30px rgba(0,0,0,0.07);
}

.quick-card i{
    width:60px;
    height:60px;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    margin-bottom:16px;
    background:#dff7f5;
    color:#14b8a6;
}

.quick-card h3{
    font-size:38px;
    color:#0D1E4C;
}

.quick-card p{
    color:#64748b;
    margin-top:5px;
}

.ticket-box{
    background:white;
    border-radius:26px;
    padding:24px;
    box-shadow:0 10px 30px rgba(0,0,0,0.06);
}

.ticket-box table{
    width:100%;
    border-collapse:collapse;
}

.ticket-box th{
    background:#f1f5f9;
    padding:16px;
    text-align:left;
    color:#0D1E4C;
}

.ticket-box td{
    padding:16px;
    border-bottom:1px solid #e2e8f0;
    vertical-align:middle;
}

.ticket-title{
    font-weight:700;
    color:#0D1E4C;
}

.ticket-desc{
    font-size:13px;
    color:#64748b;
    margin-top:5px;
    max-width:260px;
}

@media(max-width:1100px){

.quick-grid{
    grid-template-columns:repeat(2,1fr);
}

}

@media(max-width:700px){

.quick-grid{
    grid-template-columns:1fr;
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

<p class="admin-role">IT Support Panel</p>

</div>

<ul class="sidebar-menu">

<li class="active">
<a href="itsupport_dashboard.php">
<i class="fas fa-house"></i>
Dashboard
</a>
</li>
<li>
<a href="itsupport_tickets.php">
<i class="fas fa-user-check"></i>
All Tickets
</a>
</li>
<li>
<a href="it_inventory.php">
<i class="fas fa-laptop"></i>
Device Inventory
</a>
</li>

<li>
<a href="it_whoholdswhat.php">
<i class="fas fa-user-check"></i>
Who Holds What
</a>
</li>

<li>
<a href="logout.php">
<i class="fas fa-right-from-bracket"></i>
Logout
</a>
</li>

</ul>

<div class="sidebar-bottom">

<div class="system-card">
<p>IT Operations</p>
<h4>Running</h4>
<span>Real-time support monitoring</span>
</div>

</div>

</aside>

<main class="main-content">

<header class="topbar">

<div class="topbar-left">
<h1>IT Support Dashboard</h1>
<p>Track technical issues, monitor support tickets, and manage company IT operations.</p>
</div>

<div class="topbar-right">

<div class="notification-wrapper" style="position:relative;">

<button
type="button"
id="itNotifBtn"
style="
width:48px;
height:48px;
border:none;
border-radius:16px;
background:#ffffff;
box-shadow:0 10px 25px rgba(15,23,42,0.08);
cursor:pointer;
position:relative;
font-size:18px;
color:#0D1E4C;
"
>

<i class="fas fa-bell"></i>

<?php if ($newTickets > 0) { ?>

<span style="
position:absolute;
top:-6px;
right:-4px;
min-width:22px;
height:22px;
padding:0 6px;
border-radius:999px;
background:#ef4444;
color:white;
font-size:11px;
font-weight:800;
display:flex;
align-items:center;
justify-content:center;
">
<?php echo $newTickets; ?>
</span>

<?php } ?>

</button>

<div
id="itNotifDropdown"
style="
display:none;
position:absolute;
top:62px;
right:0;
width:340px;
background:white;
border-radius:22px;
padding:18px;
box-shadow:0 20px 50px rgba(15,23,42,0.16);
border:1px solid #e5eef5;
z-index:9999;
"
>

<h3 style="
font-size:18px;
color:#0f172a;
margin-bottom:14px;
">
IT Support Notifications
</h3>

<?php if ($newTickets > 0) { ?>

<div style="
padding:16px;
border-radius:18px;
background:#f8fbff;
border:1px solid #e8eef5;
">

<div style="
display:flex;
align-items:center;
gap:12px;
margin-bottom:10px;
">

<div style="
width:42px;
height:42px;
border-radius:14px;
background:#fee2e2;
color:#991b1b;
display:flex;
align-items:center;
justify-content:center;
font-size:18px;
">
<i class="fas fa-ticket"></i>
</div>

<div>
<h4 style="
font-size:14px;
color:#0f172a;
margin-bottom:4px;
">
<?php echo $newTickets; ?> New Ticket(s)
</h4>

<p style="
font-size:12px;
color:#64748b;
line-height:1.5;
">
New technical issues are waiting for IT Support review.
</p>
</div>

</div>

<a
href="itsupport_tickets.php"
style="
display:flex;
align-items:center;
justify-content:center;
height:46px;
border-radius:14px;
background:linear-gradient(90deg,#0ea5a4,#14b8a6);
color:white;
font-weight:800;
text-decoration:none;
"
>
Open Tickets
</a>

</div>

<?php } else { ?>

<div style="
padding:18px;
border-radius:18px;
background:#f8fafc;
text-align:center;
">

<div style="
width:58px;
height:58px;
margin:auto;
margin-bottom:12px;
border-radius:18px;
background:#dcfce7;
color:#166534;
display:flex;
align-items:center;
justify-content:center;
font-size:24px;
">
<i class="fas fa-circle-check"></i>
</div>

<h4 style="
font-size:15px;
color:#0f172a;
margin-bottom:6px;
">
No New Tickets
</h4>

<p style="
font-size:12px;
color:#64748b;
line-height:1.5;
">
All technical issues are currently handled.
</p>

</div>

<?php } ?>

</div>

</div>

<div class="admin-profile">

<div class="admin-avatar">
<?php echo strtoupper(substr($full_name,0,1)); ?>
</div>

<div>
<h4><?php echo htmlspecialchars($full_name); ?></h4>
<span>IT Support</span>
</div>

</div>

<a href="logout.php" class="logout-btn">Logout</a>

</div>

</header>

<div class="hero-support">

<h2>Technical Operations Center 🛠️</h2>

<p>
Monitor support requests, resolve technical issues,
track assigned tickets, and maintain operational system stability.
</p>

</div>

<div class="quick-grid">

<div class="quick-card">
<i class="fas fa-ticket"></i>
<h3><?php echo $totalTickets; ?></h3>
<p>Total Tickets</p>
</div>

<div class="quick-card">
<i class="fas fa-circle-exclamation"></i>
<h3><?php echo $openTickets; ?></h3>
<p>Pending Tickets</p>
</div>

<div class="quick-card">
<i class="fas fa-spinner"></i>
<h3><?php echo $progressTickets; ?></h3>
<p>In Progress</p>
</div>

<div class="quick-card">
<i class="fas fa-circle-check"></i>
<h3><?php echo $resolvedTickets; ?></h3>
<p>Resolved Tickets</p>
</div>

</div>

<div class="ticket-box">

<div class="panel-header">
<h2>Latest Support Tickets</h2>
</div>

<table>

<thead>

<tr>
<th>ID</th>
<th>Issue</th>
<th>Employee</th>
<th>Status</th>
<th>Assigned To</th>
<th>Created</th>
<th>Actions</th>
</tr>

</thead>

<tbody>

<?php if ($tickets && mysqli_num_rows($tickets) > 0): ?>

<?php while($row = mysqli_fetch_assoc($tickets)): ?>

<tr>

<td>#<?php echo $row['id']; ?></td>

<td>

<div class="ticket-title">
<?php echo htmlspecialchars($row['subject']); ?>
</div>

<div class="ticket-desc">
<?php echo htmlspecialchars($row['description']); ?>
</div>

</td>

<td>
<?php echo htmlspecialchars($row['employee_name']); ?>
</td>

<td>

<?php
$statusClass = "pending";

if($row['status'] == "In Progress"){
$statusClass = "progress";
}

if($row['status'] == "Resolved"){
$statusClass = "resolved";
}
?>

<span class="ticket-status <?php echo $statusClass; ?>">
<?php echo htmlspecialchars($row['status']); ?>
</span>

</td>

<td>

<?php
if(!empty($row['assigned_name'])){
echo htmlspecialchars($row['assigned_name']);
}else{
echo "Unassigned";
}
?>

</td>

<td>
<?php echo $row['created_at']; ?>
</td>

<td style="display:flex; gap:8px;">

<?php if($row['status'] == "Pending"): ?>

<form method="POST">

<input
type="hidden"
name="ticket_id"
value="<?php echo $row['id']; ?>"
>

<button
type="submit"
name="assign_ticket"
class="action-btn assign-btn"
>
Assign
</button>

</form>

<?php endif; ?>

<?php if($row['status'] != "Resolved"): ?>

<form method="POST">

<input
type="hidden"
name="ticket_id"
value="<?php echo $row['id']; ?>"
>

<button
type="submit"
name="resolve_ticket"
class="action-btn resolve-btn"
>
Resolve
</button>

</form>

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="7">No support tickets found.</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</main>
</div>

<script>

document.addEventListener("DOMContentLoaded", function () {

const notifBtn = document.getElementById("itNotifBtn");
const notifDropdown = document.getElementById("itNotifDropdown");

if(notifBtn && notifDropdown){

notifBtn.addEventListener("click", function(e){

e.preventDefault();
e.stopPropagation();

if(notifDropdown.style.display === "block"){
notifDropdown.style.display = "none";
}else{
notifDropdown.style.display = "block";
}

});

notifDropdown.addEventListener("click", function(e){
e.stopPropagation();
});

document.addEventListener("click", function(){
notifDropdown.style.display = "none";
});

}

});

</script>

</body>
</html>