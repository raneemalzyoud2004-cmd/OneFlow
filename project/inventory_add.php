<?php
session_start();
include 'config.php';

// Session check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

// Fetch inventory items
$inventoryQuery = $conn->query("
    SELECT i.id, i.item_name, i.item_type, i.status, i.notes, u.full_name AS assigned_name
    FROM inventory_items i
    LEFT JOIN users u ON i.assigned_to = u.id
    ORDER BY i.id DESC
");

// Fetch employees for dropdown
$employeesQuery = $conn->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Popup styles */
    .popup-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1000; justify-content: center; align-items: center; }
    .popup { background: #fff; border-radius: 14px; padding: 30px; max-width: 450px; width: 90%; }
    .popup h2 { margin-bottom: 20px; }
    .popup label { display:block; margin:10px 0 5px; font-weight:600; }
    .popup input, .popup select, .popup textarea { width:100%; padding:10px; border-radius:8px; border:1px solid #d9e1ea; margin-bottom: 12px; }
    .popup button { padding: 10px 20px; border:none; border-radius:8px; cursor:pointer; margin-top:10px; }
    .popup .cancel-btn { background:#f3c6c6; color:#333; margin-right:8px; }
    .popup .add-btn { background:#0D1E4C; color:#fff; }

    /* Inventory table scroll */
    .table-wrapper { overflow-x:auto; }
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
        <li class="active"><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
        <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
        <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
      </ul>
    </aside>

    <!-- Main content -->
    <main class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <h1>Inventory Management</h1>
          <p>Track devices, assets, and assignments in real-time.</p>
        </div>

        <div class="topbar-right">
          <div class="admin-profile">
            <div class="admin-avatar"><?= substr($full_name,0,1) ?></div>
            <div>
              <h4><?= $full_name ?></h4>
              <span>Admin</span>
            </div>
          </div>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <!-- Inventory overview -->
      <section class="hero-banner">
        <div class="hero-text">
          <h2>Inventory Overview 📦</h2>
          <p>Monitor all devices and assets, their assignments, and current status.</p>
        </div>
        <div class="hero-actions">
          <button class="hero-btn primary-btn" id="open-popup"><i class="fas fa-plus"></i> Add Item</button>
        </div>
      </section>

      <!-- Inventory Table -->
      <section class="panel">
        <div class="panel-header">
          <h2>Inventory Items</h2>
          <a href="#">View All</a>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Item Name</th>
                <th>Type</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              <?php if($inventoryQuery->num_rows > 0): ?>
                <?php while($row = $inventoryQuery->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td><?= htmlspecialchars($row['item_type']) ?></td>
                    <td><?= htmlspecialchars($row['assigned_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['notes']) ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">No inventory items found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <!-- Add Item Popup -->
  <div class="popup-overlay" id="popup">
    <div class="popup">
      <h2>Add Inventory Item</h2>
      <form action="inventory_add.php" method="POST">
        <label>Item Name</label>
        <input type="text" name="item_name" required>

        <label>Item Type</label>
        <select name="item_type" required>
          <option value="PC">PC</option>
          <option value="Laptop">Laptop</option>
          <option value="Tablet">Tablet</option>
          <option value="Other">Other</option>
        </select>

        <label>Assign To</label>
        <select name="assigned_to" required>
          <option value="">Select Employee</option>
          <?php while($emp = $employeesQuery->fetch_assoc()): ?>
            <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['full_name']) ?></option>
          <?php endwhile; ?>
        </select>

        <label>Status</label>
        <select name="status" required>
          <option value="Available">Available</option>
          <option value="Assigned">Assigned</option>
          <option value="In Repair">In Repair</option>
        </select>

        <label>Notes</label>
        <textarea name="notes"></textarea>

        <button type="button" class="cancel-btn" id="close-popup">Cancel</button>
        <button type="submit" class="add-btn">Add Item</button>
      </form>
    </div>
  </div>

  <script>
    const popup = document.getElementById('popup');
    const openBtn = document.getElementById('open-popup');
    const closeBtn = document.getElementById('close-popup');

    openBtn.addEventListener('click', () => popup.style.display = 'flex');
    closeBtn.addEventListener('click', () => popup.style.display = 'none');
    window.addEventListener('click', e => { if(e.target === popup) popup.style.display = 'none'; });
  </script>
</body>
</html>