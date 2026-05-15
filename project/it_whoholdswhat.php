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

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventory WHERE assigned_to IS NOT NULL AND item_name='Laptop'");
if ($result) {
    $totalLaptops = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventory WHERE assigned_to IS NOT NULL AND item_type='Accessory'");
if ($result) {
    $totalAccessories = mysqli_fetch_assoc($result)['total'];
}

$assignments = mysqli_query($conn, "
    SELECT i.id, i.item_name, i.item_type, i.status, i.notes, u.full_name, u.username, u.role
    FROM inventory i
    INNER JOIN users u ON i.assigned_to = u.id
    WHERE i.assigned_to IS NOT NULL
    ORDER BY u.full_name ASC, i.item_name ASC
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
.holder-box {
    display:flex;
    align-items:center;
    gap:12px;
}

.holder-avatar {
    width:42px;
    height:42px;
    border-radius:14px;
    background:linear-gradient(135deg,#0ea5a4,#14b8a6);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
}

.holder-name strong {
    color:#0f172a;
}

.holder-name span {
    display:block;
    color:#64748b;
    font-size:12px;
    margin-top:3px;
}

.device-pill {
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 12px;
    border-radius:999px;
    background:#eef8f8;
    color:#0D1E4C;
    font-weight:800;
}

.assignment-status {
    padding:7px 13px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
    display:inline-block;
}

.inuse {
    background:#dbeafe;
    color:#1d4ed8;
}

.available {
    background:#dcfce7;
    color:#166534;
}

.maintenance {
    background:#fef3c7;
    color:#92400e;
}

.assignment-note {
    color:#64748b;
    font-size:13px;
    line-height:1.5;
    max-width:280px;
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
    <li><a href="it_inventory.php"><i class="fas fa-laptop"></i> Device Inventory</a></li>
    <li class="active"><a href="it_whoholdswhat.php"><i class="fas fa-user-check"></i> Who Holds What</a></li>
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
        <h1>Who Holds What</h1>
        <p>See exactly which employee holds each assigned device or asset.</p>
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
        <h2>Device Assignment Tracker 💻</h2>
        <p>This page only shows assigned devices, so IT Support can quickly know who holds what.</p>
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
                        ?>
                        <tr>
                            <td>
                                <div class="holder-box">
                                    <div class="holder-avatar">
                                        <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                    </div>
                                    <div class="holder-name">
                                        <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                        <span><?php echo htmlspecialchars($row['username']); ?> · <?php echo htmlspecialchars(ucfirst($row['role'])); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="device-pill">
                                    <i class="fas fa-laptop"></i>
                                    <?php echo htmlspecialchars($row['item_name']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['item_type']); ?></td>
                            <td>
                                <span class="assignment-status <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="assignment-note">
                                    <?php echo !empty($row['notes']) ? htmlspecialchars($row['notes']) : 'No notes'; ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="5">No assigned devices found.</td>
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
