<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_item'])) {
    $item_name = trim($_POST['item_name']);
    $item_type = trim($_POST['item_type']);
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : NULL;
    $status = trim($_POST['status']);
    $notes = trim($_POST['notes']);

    $stmt = $conn->prepare("INSERT INTO inventory (item_name, item_type, assigned_to, status, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $item_name, $item_type, $assigned_to, $status, $notes);
    $stmt->execute();
    $stmt->close();

    header("Location: inventory.php");
    exit();
}

$inventoryQuery = $conn->query("
    SELECT i.id, i.item_name, i.item_type, u.full_name AS assigned_to, i.status, i.notes 
    FROM inventory i
    LEFT JOIN users u ON i.assigned_to = u.id
    ORDER BY i.id DESC
");

$employeesQuery = $conn->query("
    SELECT id, full_name 
    FROM users 
    WHERE role IN ('employee','teamleader','hr','itsupport') 
    ORDER BY full_name
");

$searchSuggestions = [];

$suggestionQuery = $conn->query("
    SELECT DISTINCT i.item_name, i.item_type, i.status, u.full_name AS assigned_to
    FROM inventory i
    LEFT JOIN users u ON i.assigned_to = u.id
");

if ($suggestionQuery) {
    while ($row = $suggestionQuery->fetch_assoc()) {
        if (!empty($row['item_name'])) $searchSuggestions[] = $row['item_name'];
        if (!empty($row['item_type'])) $searchSuggestions[] = $row['item_type'];
        if (!empty($row['status'])) $searchSuggestions[] = $row['status'];
        if (!empty($row['assigned_to'])) $searchSuggestions[] = $row['assigned_to'];
    }
}

$searchSuggestions = array_unique($searchSuggestions);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Inventory Management - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    padding: 20px;
}

.popup-content {
    background: white;
    padding: 28px;
    border-radius: 24px;
    width: 460px;
    max-width: 95%;
    box-shadow: 0 25px 70px rgba(0,0,0,0.25);
}

.popup-content h3 {
    margin-bottom: 18px;
    font-size: 24px;
    color: #0D1E4C;
}

.popup-content label {
    display: block;
    font-weight: 800;
    font-size: 13px;
    color: #0D1E4C;
    margin-bottom: 6px;
    margin-top: 12px;
}

.popup-content select,
.popup-content textarea {
    width: 100%;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #dbe7f0;
    background: #f8fafc;
    font-size: 14px;
    outline: none;
}

.popup-content textarea {
    resize: none;
    height: 85px;
}

.popup-content button {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 14px;
    background: linear-gradient(90deg, #0ea5a4, #14b8a6);
    color: white;
    font-weight: 800;
    cursor: pointer;
    font-size: 14px;
    margin-top: 14px;
}

.cancel-btn {
    background: #94a3b8 !important;
}

.inventory-status {
    padding: 7px 13px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    display: inline-block;
}

.status-available {
    background: #dcfce7;
    color: #166534;
}

.status-in-use {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-maintenance {
    background: #ffedd5;
    color: #c2410c;
}

.no-results-row {
    display: none;
    text-align: center;
    color: #991b1b;
    font-weight: 800;
    padding: 18px;
}

.inventory-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 26px;
}

.summary-card {
    background: white;
    border-radius: 22px;
    padding: 20px;
    box-shadow: 0 10px 28px rgba(15,23,42,0.06);
    border: 1px solid #edf2f7;
}

.summary-card i {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: #dff7f5;
    color: #14b8a6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 12px;
}

.summary-card h3 {
    color: #0D1E4C;
    font-size: 28px;
}

.summary-card p {
    color: #64748b;
    font-weight: 700;
}

@media(max-width: 900px) {
    .inventory-summary {
        grid-template-columns: 1fr;
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
        <p class="admin-role">Admin Panel</p>
    </div>

    <ul class="sidebar-menu">
        <li><a href="dashboardadmin.php"><i class="fas fa-house"></i> Dashboard</a></li>
        <li><a href="manageusers.php"><i class="fas fa-users"></i> Manage Users</a></li>
        <li><a href="hrteam.php"><i class="fas fa-user-tie"></i> HR Team</a></li>
        <li><a href="systemlogs.php"><i class="fas fa-file-circle-check"></i> System Logs</a></li>
        <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
        <li><a href="securitycenter.php"><i class="fas fa-shield-halved"></i> Security Center</a></li>
        <li class="active"><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory Management</a></li>
        <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
        <li><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
        <div class="system-card">
            <p>Inventory Status</p>
            <h4>Active</h4>
            <span>Assets tracking enabled</span>
        </div>
    </div>
</aside>

<main class="main-content">

<header class="topbar">
    <div class="topbar-left">
        <h1>Inventory Management</h1>
        <p>Track, assign, and manage all devices and assets.</p>
    </div>

    <div class="topbar-right">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input 
                type="text" 
                id="inventorySearch"
                list="inventoryList"
                placeholder="Search item, type, status, or assigned user..."
                onkeyup="searchInventory()"
            >

            <datalist id="inventoryList">
                <?php foreach ($searchSuggestions as $suggestion) { ?>
                    <option value="<?php echo htmlspecialchars($suggestion); ?>"></option>
                <?php } ?>
            </datalist>
        </div>

        <div class="admin-profile">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($full_name, 0, 1)); ?>
            </div>
            <div>
                <h4><?php echo htmlspecialchars($full_name); ?></h4>
                <span>Super Admin</span>
            </div>
        </div>

        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>

<section class="hero-banner">
    <div class="hero-text">
        <h2>Inventory Overview 📦</h2>
        <p>View, search, assign, and manage all inventory items from this page.</p>
    </div>

    <div class="hero-actions">
        <button onclick="openPopup()" class="hero-btn primary-btn">
            <i class="fas fa-plus"></i> Add Item
        </button>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Inventory Items</h2>
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

            <tbody id="inventoryTableBody">
                <?php if ($inventoryQuery && $inventoryQuery->num_rows > 0) { ?>
                    <?php while($item = $inventoryQuery->fetch_assoc()) { ?>
                        <?php
                            $statusClass = "status-available";
                            if ($item['status'] === "In Use") {
                                $statusClass = "status-in-use";
                            } elseif ($item['status'] === "Maintenance") {
                                $statusClass = "status-maintenance";
                            }
                        ?>

                        <tr class="inventory-row">
                            <td><?php echo $item['id']; ?></td>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['item_type']); ?></td>
                            <td><?php echo !empty($item['assigned_to']) ? htmlspecialchars($item['assigned_to']) : 'Unassigned'; ?></td>
                            <td>
                                <span class="inventory-status <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($item['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($item['notes']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6" style="text-align:center; font-weight:700; color:#64748b;">
                            No inventory items found.
                        </td>
                    </tr>
                <?php } ?>

                <tr id="noInventoryResults" class="no-results-row">
                    <td colspan="6">No matching inventory item found.</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

</main>
</div>

<div class="popup-overlay" id="popupForm">
    <div class="popup-content">
        <h3>Add Inventory Item</h3>

        <form method="POST">
            <label for="item_name">Item Name</label>
            <select name="item_name" id="item_name" required>
                <option value="" disabled selected>Select Item Name</option>
                <option value="Laptop">Laptop</option>
                <option value="Monitor">Monitor</option>
                <option value="Keyboard">Keyboard</option>
                <option value="Mouse">Mouse</option>
                <option value="Headset">Headset</option>
                <option value="Printer">Printer</option>
                <option value="Tablet">Tablet</option>
            </select>

            <label for="item_type">Item Type</label>
            <select name="item_type" id="item_type" required>
                <option value="" disabled selected>Select Type</option>
                <option value="Electronics">Electronics</option>
                <option value="Furniture">Furniture</option>
                <option value="Accessory">Accessory</option>
                <option value="Network Device">Network Device</option>
            </select>

            <label for="assigned_to">Assign To</label>
            <select name="assigned_to" id="assigned_to">
                <option value="">Unassigned</option>
                <?php if ($employeesQuery) { ?>
                    <?php while($emp = $employeesQuery->fetch_assoc()) { ?>
                        <option value="<?php echo $emp['id']; ?>">
                            <?php echo htmlspecialchars($emp['full_name']); ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>

            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="" disabled selected>Select Status</option>
                <option value="Available">Available</option>
                <option value="In Use">In Use</option>
                <option value="Maintenance">Maintenance</option>
            </select>

            <label for="notes">Notes</label>
            <textarea name="notes" id="notes" placeholder="Optional notes"></textarea>

            <button type="submit" name="add_item">Save Item</button>
            <button type="button" onclick="closePopup()" class="cancel-btn">Cancel</button>
        </form>
    </div>
</div>

<script>
function openPopup() {
    document.getElementById("popupForm").style.display = "flex";
}

function closePopup() {
    document.getElementById("popupForm").style.display = "none";
}

function searchInventory() {
    const input = document.getElementById("inventorySearch");
    const searchValue = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll(".inventory-row");
    const noResults = document.getElementById("noInventoryResults");

    let found = false;

    rows.forEach(function(row) {
        const text = row.innerText.toLowerCase();

        if (searchValue === "" || text.includes(searchValue)) {
            row.style.display = "";
            found = true;
        } else {
            row.style.display = "none";
        }
    });

    if (noResults) {
        noResults.style.display = found ? "none" : "table-row";
    }
}

document.addEventListener("click", function(e) {
    const popup = document.getElementById("popupForm");
    const content = document.querySelector(".popup-content");

    if (popup && popup.style.display === "flex" && e.target === popup && !content.contains(e.target)) {
        closePopup();
    }
});
</script>

</body>
</html>