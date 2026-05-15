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

// Fetch inventory items
$inventoryQuery = $conn->query("SELECT i.id, i.item_name, i.item_type, u.full_name AS assigned_to, i.status, i.notes 
                                FROM inventory i
                                LEFT JOIN users u ON i.assigned_to = u.id
                                ORDER BY i.id DESC");

// Fetch employees for Assign To dropdown
$employeesQuery = $conn->query("SELECT id, full_name FROM users WHERE role IN ('employee','teamleader') ORDER BY full_name");

// Handle Add Item form
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
/* Pop-up styling */
.popup-overlay {
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.5);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:9999;
}
.popup-content {
    background:white;
    padding:25px;
    border-radius:16px;
    width:420px;
    max-width:90%;
    box-shadow:0 10px 28px rgba(0,0,0,0.25);
    display:flex;
    flex-direction:column;
    gap:14px;
    font-family: Arial, sans-serif;
}
.popup-content h3 {
    margin:0 0 12px 0;
    font-size:20px;
    color:#0D1E4C;
}
.popup-content label {
    font-weight:700;
    font-size:13px;
    color:#0D1E4C;
    margin-bottom:4px;
}
.popup-content select, .popup-content textarea {
    width:100%;
    padding:10px 12px;
    border-radius:10px;
    border:1px solid #dbe7f0;
    background:#f8fafc;
    font-size:14px;
    outline:none;
    box-sizing:border-box;
}
.popup-content textarea {
    resize:none;
    height:80px;
}
.popup-content button {
    width:100%;
    padding:12px;
    border:none;
    border-radius:12px;
    background: linear-gradient(90deg, #0D1E4C, #83A6CE);
    color:white;
    font-weight:700;
    cursor:pointer;
    font-size:14px;
}
.popup-content button:hover {
    opacity:0.9;
}
</style>

</head>
<body>

<div class="dashboard-container">

  <!-- Sidebar -->
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
      <li class="active"><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory Management</a></li>
      <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>System Health</p>
        <h4>Excellent</h4>
        <span>99.2% uptime</span>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Inventory Management</h1>
        <p>Track, assign, and manage all devices and assets.</p>
      </div>
      <div class="topbar-right">
        <div class="admin-profile">
          <div class="admin-avatar">A</div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>Super Admin</span>
          </div>
        </div>
        <a href="dashboardadmin.php" class="logout-btn" style="text-decoration:none;">Back to Dashboard</a>
      </div>
    </header>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>Inventory Overview 📦</h2>
        <p>View and manage all inventory items from this page.</p>
      </div>
      <div class="hero-actions">
        <button onclick="openPopup()" class="hero-btn primary-btn"><i class="fas fa-plus"></i> Add Item</button>
      </div>
    </section>

    <div class="panel">
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
          <tbody>
            <?php if ($inventoryQuery && $inventoryQuery->num_rows > 0) { ?>
              <?php while($item = $inventoryQuery->fetch_assoc()) { ?>
                <tr>
                  <td><?php echo $item['id']; ?></td>
                  <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                  <td><?php echo htmlspecialchars($item['item_type']); ?></td>
                  <td><?php echo $item['assigned_to'] ?? 'Unassigned'; ?></td>
                  <td><?php echo htmlspecialchars($item['status']); ?></td>
                  <td><?php echo htmlspecialchars($item['notes']); ?></td>
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
    </div>

  </main>
</div>

<!-- Pop-up Form -->
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
      </select>

      <label for="item_type">Item Type</label>
      <select name="item_type" id="item_type" required>
        <option value="" disabled selected>Select Type</option>
        <option value="Electronics">Electronics</option>
        <option value="Furniture">Furniture</option>
        <option value="Accessory">Accessory</option>
      </select>

      <label for="assigned_to">Assign To</label>
      <select name="assigned_to" id="assigned_to">
        <option value="">Unassigned</option>
        <?php while($emp = $employeesQuery->fetch_assoc()) { ?>
          <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['full_name']); ?></option>
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
      <button type="button" onclick="closePopup()" style="margin-top:8px; background:#ccc;">Cancel</button>
    </form>
  </div>
</div>

<script>
function openPopup() {
  document.getElementById('popupForm').style.display = 'flex';
}
function closePopup() {
  document.getElementById('popupForm').style.display = 'none';
}
</script>

</body>
</html>