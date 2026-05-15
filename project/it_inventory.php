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

$inventory = mysqli_query($conn, "
    SELECT i.id, i.item_name, i.item_type, i.status, i.notes, u.full_name AS assigned_name
    FROM inventory i
    LEFT JOIN users u ON i.assigned_to = u.id
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
        <p>System Status</p>
        <h4>Running</h4>
        <span>All systems operational</span>
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

<section class="panel">
    <div class="panel-header">
        <h2>All Inventory Items</h2>
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
                            <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
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