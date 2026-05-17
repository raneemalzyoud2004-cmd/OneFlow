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

$totalItems = 0;
$assignedItems = 0;
$unassignedItems = 0;
$maintenanceItems = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventory");
if ($result) {
    $totalItems = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventory WHERE assigned_to IS NOT NULL");
if ($result) {
    $assignedItems = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventory WHERE assigned_to IS NULL");
if ($result) {
    $unassignedItems = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventory WHERE status='Maintenance'");
if ($result) {
    $maintenanceItems = mysqli_fetch_assoc($result)['total'];
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$whereClauses = [];

if (!empty($search)) {
    $whereClauses[] = "(
        i.item_name LIKE '%$search%' OR
        i.item_type LIKE '%$search%' OR
        u.full_name LIKE '%$search%'
    )";
}

if (!empty($statusFilter)) {
    $whereClauses[] = "i.status = '$statusFilter'";
}

$whereSQL = "";

if (!empty($whereClauses)) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}

$inventory = mysqli_query($conn, "
    SELECT i.id, i.item_name, i.item_type, i.status, i.notes, u.full_name AS assigned_name
    FROM inventory i
    LEFT JOIN users u ON i.assigned_to = u.id
    $whereSQL
    ORDER BY i.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Device Inventory - OneFlow</title>
<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.inventory-status {
    padding:7px 13px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
    display:inline-block;
}

.available {
    background:#dcfce7;
    color:#166534;
}

.inuse {
    background:#dbeafe;
    color:#1d4ed8;
}

.maintenance {
    background:#fef3c7;
    color:#92400e;
}

.inventory-note {
    color:#64748b;
    font-size:13px;
    line-height:1.5;
    max-width:280px;
}

.empty-holder {
    color:#94a3b8;
    font-weight:700;
}

.filter-panel {
    background:white;
    border-radius:24px;
    padding:22px;
    box-shadow:0 10px 30px rgba(15,23,42,0.06);
    margin-bottom:24px;
}

.filter-grid {
    display:grid;
    grid-template-columns:2fr 1fr auto;
    gap:18px;
    align-items:end;
}

.filter-field label {
    display:block;
    margin-bottom:10px;
    font-weight:800;
    color:#0D1E4C;
    font-size:14px;
}

.filter-field input,
.filter-field select {
    width:100%;
    height:48px;
    padding:0 14px;
    border-radius:14px;
    border:1px solid #dbe7f0;
    outline:none;
    font-size:14px;
    color:#0f172a;
    background:#ffffff;
    box-shadow:0 6px 18px rgba(15,23,42,0.04);
}

.filter-actions {
    display:flex;
    gap:10px;
}

.apply-btn {
    height:48px;
    padding:0 18px;
    border:none;
    border-radius:14px;
    background:linear-gradient(90deg,#0ea5a4,#14b8a6);
    color:white;
    font-weight:800;
    cursor:pointer;
    box-shadow:0 10px 18px rgba(20,184,166,0.22);
}

.reset-btn {
    height:48px;
    padding:0 18px;
    border-radius:14px;
    background:#e2e8f0;
    color:#0D1E4C;
    font-weight:800;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
}

.item-name-box {
    display:flex;
    align-items:center;
    gap:10px;
}

.item-icon {
    width:38px;
    height:38px;
    border-radius:12px;
    background:#eef8f8;
    color:#14b8a6;
    display:flex;
    align-items:center;
    justify-content:center;
}

@media(max-width:900px) {
    .filter-grid {
        grid-template-columns:1fr;
    }

    .filter-actions {
        justify-content:flex-start;
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
    <li class="active"><a href="it_inventory.php"><i class="fas fa-laptop"></i> Device Inventory</a></li>
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
        <h1>Device Inventory</h1>
        <p>Review all company devices and assets, including assigned and unassigned items.</p>
    </div>

    <div class="topbar-right">
        <div class="admin-profile">
            <div class="admin-avatar"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
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
        <h2>Inventory Management View 📦</h2>
        <p>This page shows every registered device in the system, whether it is assigned, available, or under maintenance.</p>
    </div>
</section>

<section class="cards">
    <div class="card">
        <div class="card-icon"><i class="fas fa-boxes"></i></div>
        <div class="card-info">
            <h3><?php echo $totalItems; ?></h3>
            <p>Total Items</p>
            <span>All inventory assets</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-user-check"></i></div>
        <div class="card-info">
            <h3><?php echo $assignedItems; ?></h3>
            <p>Assigned</p>
            <span>Held by employees</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-box-open"></i></div>
        <div class="card-info">
            <h3><?php echo $unassignedItems; ?></h3>
            <p>Unassigned</p>
            <span>Available for assignment</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-screwdriver-wrench"></i></div>
        <div class="card-info">
            <h3><?php echo $maintenanceItems; ?></h3>
            <p>Maintenance</p>
            <span>Needs technical review</span>
        </div>
    </div>
</section>

<section class="filter-panel">

<div class="panel-header">
    <h2>Search and Filter</h2>
</div>

<form method="GET" style="width:100%; margin-top:14px;">

<div class="filter-grid">

    <div class="filter-field">
        <label>Search</label>
        <input
            type="text"
            name="search"
            placeholder="Search by item name, item type, or employee name"
            value="<?php echo htmlspecialchars($search); ?>"
        >
    </div>

    <div class="filter-field">
        <label>Status</label>
        <select name="status">
            <option value="">All Status</option>
            <option value="Available" <?php echo $statusFilter === 'Available' ? 'selected' : ''; ?>>Available</option>
            <option value="In Use" <?php echo $statusFilter === 'In Use' ? 'selected' : ''; ?>>In Use</option>
            <option value="Maintenance" <?php echo $statusFilter === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
        </select>
    </div>

    <div class="filter-actions">
        <button type="submit" class="apply-btn">Apply</button>
        <a href="it_inventory.php" class="reset-btn">Reset</a>
    </div>

</div>

</form>

</section>

<section class="panel">
    <div class="panel-header">
        <h2>All Inventory Items</h2>
        <a href="it_whoholdswhat.php">View Assigned Devices</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Item Type</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($inventory && mysqli_num_rows($inventory) > 0) { ?>
                    <?php while ($item = mysqli_fetch_assoc($inventory)) { ?>
                        <?php
                            $statusText = $item['status'];
                            $statusClass = "available";

                            if ($statusText === "In Use") {
                                $statusClass = "inuse";
                            }

                            if ($statusText === "Maintenance") {
                                $statusClass = "maintenance";
                            }
                        ?>
                        <tr>
                            <td>#<?php echo $item['id']; ?></td>

                            <td>
                                <div class="item-name-box">
                                    <div class="item-icon">
                                        <i class="fas fa-laptop"></i>
                                    </div>
                                    <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                </div>
                            </td>

                            <td><?php echo htmlspecialchars($item['item_type']); ?></td>

                            <td>
                                <?php if (!empty($item['assigned_name'])) { ?>
                                    <?php echo htmlspecialchars($item['assigned_name']); ?>
                                <?php } else { ?>
                                    <span class="empty-holder">Unassigned</span>
                                <?php } ?>
                            </td>

                            <td>
                                <span class="inventory-status <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($item['status']); ?>
                                </span>
                            </td>

                            <td>
                                <div class="inventory-note">
                                    <?php echo !empty($item['notes']) ? htmlspecialchars($item['notes']) : 'No notes'; ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6">No inventory items found.</td>
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