<?php
session_start();
include "config.php";

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'itsupport') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

$totalAssignments = 0;
$totalEmployeesHolding = 0;
$totalLaptops = 0;
$totalAccessories = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventory WHERE assigned_to IS NOT NULL");
if ($result) {
    $totalAssignments = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(DISTINCT assigned_to) AS total FROM inventory WHERE assigned_to IS NOT NULL");
if ($result) {
    $totalEmployeesHolding = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventory WHERE assigned_to IS NOT NULL AND item_type='Laptop'");
if ($result) {
    $totalLaptops = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventory WHERE assigned_to IS NOT NULL AND item_type='Accessory'");
if ($result) {
    $totalAccessories = mysqli_fetch_assoc($result)['total'];
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';

$whereClauses = ["i.assigned_to IS NOT NULL"];

if (!empty($search)) {
    $whereClauses[] = "(
        u.full_name LIKE '%$search%' OR
        i.item_name LIKE '%$search%' OR
        i.item_type LIKE '%$search%'
    )";
}

if (!empty($roleFilter)) {
    $whereClauses[] = "u.role='$roleFilter'";
}

$whereSQL = implode(" AND ", $whereClauses);

$assignments = mysqli_query($conn, "
    SELECT 
        i.id,
        i.item_name,
        i.item_type,
        i.status,
        i.notes,
        u.full_name,
        u.username,
        u.role,
        (
            SELECT COUNT(*)
            FROM inventory inv
            WHERE inv.assigned_to = u.id
        ) AS device_count
    FROM inventory i
    INNER JOIN users u ON i.assigned_to = u.id
    WHERE $whereSQL
    ORDER BY device_count DESC, u.full_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Who Holds What - OneFlow</title>

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

.holder-box{
    display:flex;
    align-items:center;
    gap:12px;
}

.holder-avatar{
    width:46px;
    height:46px;
    border-radius:16px;
    background:linear-gradient(135deg,#0ea5a4,#14b8a6);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
}

.holder-name strong{
    color:#0f172a;
}

.holder-name span{
    display:block;
    color:#64748b;
    font-size:12px;
    margin-top:3px;
}

.device-pill{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 13px;
    border-radius:999px;
    background:#eef8f8;
    color:#0D1E4C;
    font-weight:800;
}

.assignment-status{
    padding:7px 13px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
    display:inline-block;
}

.inuse{
    background:#dbeafe;
    color:#1d4ed8;
}

.available{
    background:#dcfce7;
    color:#166534;
}

.maintenance{
    background:#fef3c7;
    color:#92400e;
}

.assignment-note{
    color:#64748b;
    font-size:13px;
    line-height:1.5;
    max-width:260px;
}

.device-count{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 11px;
    border-radius:999px;
    background:#ede9fe;
    color:#5b21b6;
    font-size:12px;
    font-weight:800;
}

.heavy-holder{
    background:#fee2e2;
    color:#991b1b;
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
<li>
<a href="itsupport_tickets.php">
<i class="fas fa-user-check"></i>
All Tickets
</a>
</li>
<li><a href="it_inventory.php"><i class="fas fa-laptop"></i> Device Inventory</a></li>
<li class="active"><a href="it_whoholdswhat.php"><i class="fas fa-user-check"></i> Who Holds What</a></li>
<li><a href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
</ul>

<div class="sidebar-bottom">
<div class="system-card">
<p>Assignment Tracker</p>
<h4>Connected</h4>
<span>Live employee-device mapping</span>
</div>
</div>

</aside>

<main class="main-content">

<header class="topbar">

<div class="topbar-left">
<h1>Who Holds What</h1>
<p>Track exactly which employee currently holds each assigned device or asset.</p>
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
<h2>Device Assignment Tracker 💻</h2>
<p>
This page focuses only on assigned devices so IT Support can quickly identify
which employee holds each asset inside the company.
</p>
</div>
</section>

<section class="cards">

<div class="card">
<div class="card-icon"><i class="fas fa-laptop-user"></i></div>
<div class="card-info">
<h3><?php echo $totalAssignments; ?></h3>
<p>Total Assignments</p>
<span>Devices currently held</span>
</div>
</div>

<div class="card">
<div class="card-icon"><i class="fas fa-users"></i></div>
<div class="card-info">
<h3><?php echo $totalEmployeesHolding; ?></h3>
<p>Device Holders</p>
<span>Employees with assets</span>
</div>
</div>

<div class="card">
<div class="card-icon"><i class="fas fa-laptop"></i></div>
<div class="card-info">
<h3><?php echo $totalLaptops; ?></h3>
<p>Laptops Held</p>
<span>Assigned laptops</span>
</div>
</div>

<div class="card">
<div class="card-icon"><i class="fas fa-keyboard"></i></div>
<div class="card-info">
<h3><?php echo $totalAccessories; ?></h3>
<p>Accessories Held</p>
<span>Assigned accessories</span>
</div>
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
placeholder="Employee or device"
value="<?php echo htmlspecialchars($search); ?>"
>
</div>

<div class="filter-field">
<label>Role</label>

<select name="role">

<option value="">All Roles</option>

<option value="employee"
<?php echo $roleFilter === 'employee' ? 'selected' : ''; ?>>
Employee
</option>

<option value="hr"
<?php echo $roleFilter === 'hr' ? 'selected' : ''; ?>>
HR
</option>

<option value="teamleader"
<?php echo $roleFilter === 'teamleader' ? 'selected' : ''; ?>>
Team Leader
</option>

<option value="admin"
<?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>
Admin
</option>

</select>
</div>

<div class="filter-actions">

<button type="submit" class="apply-btn">
Apply
</button>

<a href="it_whoholdswhat.php" class="reset-btn">
Reset
</a>

</div>

</div>

</form>

</section>

<section class="panel">

<div class="panel-header">
<h2>Employee Device Assignments</h2>
</div>

<div class="table-wrapper">

<table>

<thead>
<tr>
<th>Employee</th>
<th>Device</th>
<th>Type</th>
<th>Status</th>
<th>Devices Held</th>
<th>Notes</th>
</tr>
</thead>

<tbody>

<?php if ($assignments && mysqli_num_rows($assignments) > 0) { ?>

<?php while ($row = mysqli_fetch_assoc($assignments)) { ?>

<?php
$statusText = $row['status'];
$statusClass = "available";

if ($statusText === "In Use") {
    $statusClass = "inuse";
}

if ($statusText === "Maintenance") {
    $statusClass = "maintenance";
}

$countClass = "device-count";

if ($row['device_count'] >= 3) {
    $countClass .= " heavy-holder";
}
?>

<tr>

<td>

<div class="holder-box">

<div class="holder-avatar">
<?php echo strtoupper(substr($row['full_name'],0,1)); ?>
</div>

<div class="holder-name">

<strong>
<?php echo htmlspecialchars($row['full_name']); ?>
</strong>

<span>
<?php echo htmlspecialchars($row['username']); ?>
·
<?php echo htmlspecialchars(ucfirst($row['role'])); ?>
</span>

</div>

</div>

</td>

<td>

<span class="device-pill">
<i class="fas fa-laptop"></i>
<?php echo htmlspecialchars($row['item_name']); ?>
</span>

</td>

<td>
<?php echo htmlspecialchars($row['item_type']); ?>
</td>

<td>

<span class="assignment-status <?php echo $statusClass; ?>">
<?php echo htmlspecialchars($row['status']); ?>
</span>

</td>

<td>

<span class="<?php echo $countClass; ?>">

<i class="fas fa-boxes"></i>

<?php echo $row['device_count']; ?> devices

</span>

</td>

<td>

<div class="assignment-note">

<?php
echo !empty($row['notes'])
? htmlspecialchars($row['notes'])
: 'No notes';
?>

</div>

</td>

</tr>

<?php } ?>

<?php } else { ?>

<tr>
<td colspan="6">No assigned devices found.</td>
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