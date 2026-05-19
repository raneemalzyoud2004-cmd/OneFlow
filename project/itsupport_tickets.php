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

$search = isset($_GET['search'])
? mysqli_real_escape_string($conn, $_GET['search'])
: '';

$statusFilter = isset($_GET['status'])
? mysqli_real_escape_string($conn, $_GET['status'])
: '';

$whereClauses = [];

if (!empty($search)) {

    $whereClauses[] = "(
        support_tickets.subject LIKE '%$search%' OR
        support_tickets.employee_name LIKE '%$search%' OR
        support_tickets.description LIKE '%$search%'
    )";
}

if (!empty($statusFilter)) {

    $whereClauses[] = "
        support_tickets.status = '$statusFilter'
    ";
}

$whereSQL = "";

if (!empty($whereClauses)) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}

$tickets = mysqli_query($conn, "
    SELECT support_tickets.*, users.full_name AS assigned_name
    FROM support_tickets
    LEFT JOIN users
    ON support_tickets.assigned_to = users.id
    $whereSQL
    ORDER BY support_tickets.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>All Tickets - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

.filter-panel{
    background:white;
    border-radius:24px;
    padding:22px;
    margin-bottom:24px;
    box-shadow:0 10px 30px rgba(15,23,42,0.06);
}

.filter-grid{
    display:grid;
    grid-template-columns:2fr 1fr auto;
    gap:18px;
    align-items:end;
}

.filter-field label{
    display:block;
    margin-bottom:10px;
    font-weight:800;
    color:#0D1E4C;
}

.filter-field input,
.filter-field select{
    width:100%;
    height:48px;
    border-radius:14px;
    border:1px solid #dbe7f0;
    padding:0 14px;
    outline:none;
}
.sidebar-menu{
    padding:0 12px;
}

.sidebar-menu li{
    margin-bottom:10px;
}
.filter-actions{
    display:flex;
    gap:10px;
}

.apply-btn{
    height:48px;
    padding:0 18px;
    border:none;
    border-radius:14px;
    background:#14b8a6;
    color:white;
    font-weight:800;
    cursor:pointer;
}

.reset-btn{
    height:48px;
    padding:0 18px;
    border-radius:14px;
    background:#e2e8f0;
    color:#0D1E4C;
    text-decoration:none;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
}

.ticket-status{
    padding:7px 13px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
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

.ticket-priority{
    padding:7px 13px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
    display:inline-block;
}

.high{
    background:#fee2e2;
    color:#991b1b;
}

.medium{
    background:#fef3c7;
    color:#92400e;
}

.low{
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

.ticket-description{
    color:#64748b;
    font-size:13px;
    line-height:1.5;
    max-width:280px;
}

@media(max-width:900px){

.filter-grid{
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
    <li><a href="itsupport_dashboard.php"><i class="fas fa-house"></i> Dashboard</a></li>
    <li class="active">
<a href="itsupport_tickets.php">
<i class="fas fa-user-check"></i>
All Tickets
</a>
</li>
    <li ><a href="it_inventory.php"><i class="fas fa-laptop"></i> Device Inventory</a></li>
    <li><a href="it_whoholdswhat.php"><i class="fas fa-user-check"></i> Who Holds What</a></li>
    <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
</ul>

<div class="sidebar-bottom">
    <div class="system-card">
        <p>Inventory Status</p>
        <h4>Live Data</h4>
        <span>Search and filter enabled</span>
    </div>
</div>

</aside>

<main class="main-content">

<header class="topbar">

<div class="topbar-left">
<h1>All Support Tickets</h1>
<p>Review and manage every submitted technical issue.</p>
</div>

<div class="topbar-right">

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

<section class="hero-banner">

<div class="hero-text">

<h2>Technical Tickets Center 🎫</h2>

<p>
Monitor all submitted support tickets, assign issues,
track progress, and resolve technical problems.
</p>

</div>

</section>

<section class="filter-panel">

<div class="panel-header">
<h2>Search and Filter</h2>
</div>

<form method="GET" style="margin-top:14px;">

<div class="filter-grid">

<div class="filter-field">

<label>Search</label>

<input
type="text"
name="search"
placeholder="Employee or issue"
value="<?php echo htmlspecialchars($search); ?>"
>

</div>

<div class="filter-field">

<label>Status</label>

<select name="status">

<option value="">All Status</option>

<option value="Pending"
<?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>
Pending
</option>

<option value="In Progress"
<?php echo $statusFilter === 'In Progress' ? 'selected' : ''; ?>>
In Progress
</option>

<option value="Resolved"
<?php echo $statusFilter === 'Resolved' ? 'selected' : ''; ?>>
Resolved
</option>

</select>

</div>

<div class="filter-actions">

<button type="submit" class="apply-btn">
Apply
</button>

<a href="itsupport_tickets.php" class="reset-btn">
Reset
</a>

</div>

</div>

</form>

</section>

<section class="panel">

<div class="panel-header">
<h2>Submitted Tickets</h2>
</div>

<div class="table-wrapper">

<table>

<thead>

<tr>
<th>ID</th>
<th>Employee</th>
<th>Subject</th>
<th>Priority</th>
<th>Status</th>
<th>Assigned To</th>
<th>Created</th>
<th>Actions</th>
</tr>

</thead>

<tbody>

<?php if ($tickets && mysqli_num_rows($tickets) > 0) { ?>

<?php while($row = mysqli_fetch_assoc($tickets)) { ?>

<?php

$statusClass = "pending";

if($row['status'] == "In Progress"){
$statusClass = "progress";
}

if($row['status'] == "Resolved"){
$statusClass = "resolved";
}

$priorityClass = "low";

if($row['priority'] == "Medium"){
$priorityClass = "medium";
}

if($row['priority'] == "High"){
$priorityClass = "high";
}

?>

<tr>

<td>#<?php echo $row['id']; ?></td>

<td>
<?php echo htmlspecialchars($row['employee_name']); ?>
</td>

<td>

<strong>
<?php echo htmlspecialchars($row['subject']); ?>
</strong>

<div class="ticket-description">
<?php echo htmlspecialchars($row['description']); ?>
</div>

</td>

<td>

<span class="ticket-priority <?php echo $priorityClass; ?>">
<?php echo htmlspecialchars($row['priority']); ?>
</span>

</td>

<td>

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

<?php if($row['status'] == "Pending"){ ?>

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

<?php } ?>

<?php if($row['status'] != "Resolved"){ ?>

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

<?php } ?>

</td>

</tr>

<?php } ?>

<?php } else { ?>

<tr>
<td colspan="8">No support tickets found.</td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

</section>

</main>
</div>

</body>
</html>