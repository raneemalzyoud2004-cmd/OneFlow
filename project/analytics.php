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

// Load KPI data
$kpis = [];
$kpiQuery = "SELECT * FROM analytics_kpis ORDER BY id ASC";
$kpiResult = mysqli_query($conn, $kpiQuery);

if ($kpiResult) {
    while ($row = mysqli_fetch_assoc($kpiResult)) {
        $kpis[] = $row;
    }
}

// Split data for layout
$topKpis = array_slice($kpis, 0, 4);
$moreKpis = array_slice($kpis, 4);

// Small live system helpers for blended insights
$totalUsers = 0;
$totalUsersQuery = "SELECT COUNT(*) AS total FROM users";
$totalUsersResult = mysqli_query($conn, $totalUsersQuery);
if ($totalUsersResult && $row = mysqli_fetch_assoc($totalUsersResult)) {
    $totalUsers = $row['total'];
}

$totalPendingSetup = 0;
$totalPendingSetupQuery = "SELECT COUNT(*) AS total FROM users WHERE account_status = 'pending_setup'";
$totalPendingSetupResult = mysqli_query($conn, $totalPendingSetupQuery);
if ($totalPendingSetupResult && $row = mysqli_fetch_assoc($totalPendingSetupResult)) {
    $totalPendingSetup = $row['total'];
}

$totalPendingRequests = 0;
$totalPendingRequestsQuery = "SELECT COUNT(*) AS total FROM requests WHERE status = 'pending'";
$totalPendingRequestsResult = mysqli_query($conn, $totalPendingRequestsQuery);
if ($totalPendingRequestsResult && $row = mysqli_fetch_assoc($totalPendingRequestsResult)) {
    $totalPendingRequests = $row['total'];
}

$totalHR = 0;
$totalHRQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'hr'";
$totalHRResult = mysqli_query($conn, $totalHRQuery);
if ($totalHRResult && $row = mysqli_fetch_assoc($totalHRResult)) {
    $totalHR = $row['total'];
}

$totalEmployees = 0;
$totalEmployeesQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'employee'";
$totalEmployeesResult = mysqli_query($conn, $totalEmployeesQuery);
if ($totalEmployeesResult && $row = mysqli_fetch_assoc($totalEmployeesResult)) {
    $totalEmployees = $row['total'];
}

$totalTeamLeaders = 0;
$totalTeamLeadersQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'teamleader'";
$totalTeamLeadersResult = mysqli_query($conn, $totalTeamLeadersQuery);
if ($totalTeamLeadersResult && $row = mysqli_fetch_assoc($totalTeamLeadersResult)) {
    $totalTeamLeaders = $row['total'];
}

$latestKpiUpdate = "Not available";
$latestUpdateQuery = "SELECT MAX(updated_at) AS latest_update FROM analytics_kpis";
$latestUpdateResult = mysqli_query($conn, $latestUpdateQuery);
if ($latestUpdateResult && $row = mysqli_fetch_assoc($latestUpdateResult) && !empty($row['latest_update'])) {
    $latestKpiUpdate = $row['latest_update'];
}
$totalAdmins = 0;
$totalAdminsQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'";
$totalAdminsResult = mysqli_query($conn, $totalAdminsQuery);
if ($totalAdminsResult && $row = mysqli_fetch_assoc($totalAdminsResult)) {
    $totalAdmins = $row['total'];
}

function kpiStatusClass($status) {
    $status = strtolower(trim($status));
    if ($status === 'good') return 'good';
    if ($status === 'warning') return 'warning';
    if ($status === 'critical') return 'critical';
    return 'normal';
}

function kpiStatusLabel($status) {
    $status = strtolower(trim($status));
    if ($status === 'good') return 'Good';
    if ($status === 'warning') return 'Needs Attention';
    if ($status === 'critical') return 'Critical';
    return 'Stable';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .analytics-hero {
      background: linear-gradient(135deg, #0f172a, #12396b, #14b8a6);
      border-radius: 26px;
      padding: 28px 30px;
      color: white;
      margin-bottom: 28px;
      box-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
    }

    .analytics-hero-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 18px;
    }

    .analytics-hero h2 {
      font-size: 30px;
      margin-bottom: 8px;
    }

    .analytics-hero p {
      color: rgba(255,255,255,0.88);
      font-size: 15px;
      line-height: 1.7;
      max-width: 760px;
    }

    .analytics-live-tag {
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.18);
      padding: 10px 14px;
      border-radius: 14px;
      font-size: 13px;
      font-weight: 700;
      white-space: nowrap;
    }

    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-bottom: 28px;
    }

    .kpi-card {
      background: rgba(255,255,255,0.82);
      backdrop-filter: blur(12px);
      border-radius: 24px;
      padding: 22px;
      border: 1px solid rgba(255,255,255,0.55);
      box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
      position: relative;
      overflow: hidden;
    }

    .kpi-card.good { border-top: 5px solid #22c55e; }
    .kpi-card.warning { border-top: 5px solid #f59e0b; }
    .kpi-card.critical { border-top: 5px solid #ef4444; }
    .kpi-card.normal { border-top: 5px solid #14b8a6; }

    .kpi-label {
      display: inline-block;
      padding: 7px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 16px;
    }

    .kpi-label.good {
      background: #dcfce7;
      color: #166534;
    }

    .kpi-label.warning {
      background: #fef3c7;
      color: #92400e;
    }

    .kpi-label.critical {
      background: #fee2e2;
      color: #991b1b;
    }

    .kpi-label.normal {
      background: #cffafe;
      color: #155e75;
    }

    .kpi-card h3 {
      font-size: 16px;
      color: #0f172a;
      margin-bottom: 12px;
      line-height: 1.5;
    }

    .kpi-value {
      font-size: 38px;
      font-weight: 800;
      color: #0f172a;
      line-height: 1;
      margin-bottom: 10px;
    }

    .kpi-desc {
      color: #64748b;
      font-size: 13px;
      line-height: 1.6;
    }

    .analytics-layout {
      display: grid;
      grid-template-columns: 1.4fr 1fr;
      gap: 22px;
    }

    .analytics-column {
      display: flex;
      flex-direction: column;
      gap: 22px;
    }

    .insight-board {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
    }

    .insight-card {
      background: linear-gradient(135deg, #f8fbff, #eef8f8);
      border: 1px solid #e3eef2;
      border-radius: 20px;
      padding: 20px;
      min-height: 150px;
    }

    .insight-card i {
      width: 48px;
      height: 48px;
      border-radius: 14px;
      background: linear-gradient(135deg, #16c7c1, #22c55e);
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 18px;
      margin-bottom: 14px;
    }

    .insight-card h4 {
      color: #0f172a;
      font-size: 18px;
      margin-bottom: 8px;
    }

    .insight-card p {
      color: #64748b;
      font-size: 14px;
      line-height: 1.6;
    }

    .kpi-table {
      width: 100%;
      border-collapse: collapse;
    }

    .kpi-table th,
    .kpi-table td {
      padding: 16px 14px;
      border-bottom: 1px solid #e8eef3;
      text-align: left;
      vertical-align: middle;
    }

    .kpi-table th {
      background: #eef4f8;
      color: #0f172a;
      font-size: 15px;
    }

    .mini-status {
      display: inline-block;
      padding: 7px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
    }

    .mini-status.good {
      background: #dcfce7;
      color: #166534;
    }

    .mini-status.warning {
      background: #fef3c7;
      color: #92400e;
    }

    .mini-status.critical {
      background: #fee2e2;
      color: #991b1b;
    }

    .mini-status.normal {
      background: #cffafe;
      color: #155e75;
    }

    .distribution-box {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .distribution-row {
      background: #f8fbff;
      border: 1px solid #e5eef5;
      border-radius: 18px;
      padding: 14px 16px;
    }

    .distribution-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .distribution-head span {
      color: #0f172a;
      font-weight: 700;
      font-size: 14px;
    }

    .distribution-track {
      width: 100%;
      height: 10px;
      background: #e2e8f0;
      border-radius: 999px;
      overflow: hidden;
    }

    .distribution-fill {
      height: 100%;
      border-radius: 999px;
      background: linear-gradient(90deg, #0ea5a4, #14b8a6);
    }

    @media (max-width: 1200px) {
      .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .analytics-layout {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 800px) {
      .kpi-grid {
        grid-template-columns: 1fr;
      }

      .insight-board {
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
        <li><a href="systemlogs.php"><i class="fas fa-clipboard-list"></i> System Logs</a></li>
        <li class="active"><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
        <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
      </ul>

      <div class="sidebar-bottom">
        <div class="system-card">
          <p>Analytics Status</p>
          <h4>Creative View</h4>
          <span>KPI center loaded</span>
        </div>
      </div>
    </aside>

    <main class="main-content">

      <header class="topbar">
        <div class="topbar-left">
          <h1>Analytics</h1>
          <p>Monitor administrative KPIs, focus areas, and operational insights across the system.</p>
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

      <section class="analytics-hero">
        <div class="analytics-hero-top">
          <div>
            <h2>Administrative KPI Center ✨</h2>
            <p>This page focuses on performance indicators and management insights instead of repeating dashboard summaries. It helps the admin understand where attention is needed and what is improving.</p>
          </div>
          <div class="analytics-live-tag">
            Last KPI Update: <?php echo htmlspecialchars($latestKpiUpdate); ?>
          </div>
        </div>
      </section>

      <section class="kpi-grid">
        <?php if (!empty($topKpis)) { ?>
          <?php foreach ($topKpis as $kpi) { ?>
            <?php $statusClass = kpiStatusClass($kpi['status']); ?>
            <div class="kpi-card <?php echo $statusClass; ?>">
              <span class="kpi-label <?php echo $statusClass; ?>">
                <?php echo kpiStatusLabel($kpi['status']); ?>
              </span>
              <h3><?php echo htmlspecialchars($kpi['metric_title']); ?></h3>
              <div class="kpi-value"><?php echo htmlspecialchars($kpi['metric_value']); ?></div>
              <div class="kpi-desc"><?php echo htmlspecialchars($kpi['metric_description']); ?></div>
            </div>
          <?php } ?>
        <?php } else { ?>
          <div class="panel" style="grid-column: 1 / -1;">
            <div class="panel-header">
              <h2>No KPI data found</h2>
            </div>
            <p>Add rows to <strong>analytics_kpis</strong> to display your analytics cards.</p>
          </div>
        <?php } ?>
      </section>

      <section class="analytics-layout">
        <div class="analytics-column">

          <div class="panel">
            <div class="panel-header">
              <h2>Attention Board</h2>
            </div>

            <div class="insight-board">
              <div class="insight-card">
                <i class="fas fa-user-clock"></i>
                <h4><?php echo $totalPendingSetup; ?> Pending Setup</h4>
                <p>These accounts still need to complete their initial setup before becoming fully active in the system.</p>
              </div>

              <div class="insight-card">
                <i class="fas fa-file-circle-question"></i>
                <h4><?php echo $totalPendingRequests; ?> Pending Requests</h4>
                <p>These requests are still waiting for review and may need HR or admin attention soon.</p>
              </div>

              <div class="insight-card">
                <i class="fas fa-user-tie"></i>
                <h4><?php echo $totalHR; ?> HR Accounts</h4>
                <p>The system currently has this number of HR members available for employee-related operations.</p>
              </div>

              <div class="insight-card">
                <i class="fas fa-users"></i>
                <h4><?php echo $totalUsers; ?> Total Accounts</h4>
                <p>This is the current size of the platform across admin, HR, employee, and team leader roles.</p>
              </div>
            </div>
          </div>

          <div class="panel">
            <div class="panel-header">
              <h2>KPI Performance Table</h2>
            </div>

            <div class="table-wrapper">
              <table class="kpi-table">
                <thead>
                  <tr>
                    <th>Metric</th>
                    <th>Value</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Updated</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($kpis)) { ?>
                    <?php foreach ($kpis as $kpi) { ?>
                      <?php $statusClass = kpiStatusClass($kpi['status']); ?>
                      <tr>
                        <td><?php echo htmlspecialchars($kpi['metric_title']); ?></td>
                        <td><strong><?php echo htmlspecialchars($kpi['metric_value']); ?></strong></td>
                        <td><?php echo htmlspecialchars($kpi['metric_description']); ?></td>
                        <td>
                          <span class="mini-status <?php echo $statusClass; ?>">
                            <?php echo kpiStatusLabel($kpi['status']); ?>
                          </span>
                        </td>
                        <td><?php echo htmlspecialchars($kpi['updated_at']); ?></td>
                      </tr>
                    <?php } ?>
                  <?php } else { ?>
                    <tr>
                      <td colspan="5">No KPI data available.</td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>

        <div class="analytics-column">

         <div class="panel">
  <div class="panel-header">
    <h2>Role Distribution</h2>
  </div>

  <div class="distribution-box">
    <?php
      $maxRoleValue = max(1, $totalAdmins, $totalHR, $totalEmployees, $totalTeamLeaders);
      $adminWidth = ($totalAdmins / $maxRoleValue) * 100;
      $hrWidth = ($totalHR / $maxRoleValue) * 100;
      $employeeWidth = ($totalEmployees / $maxRoleValue) * 100;
      $teamLeaderWidth = ($totalTeamLeaders / $maxRoleValue) * 100;
    ?>

    <div class="distribution-row">
      <div class="distribution-head">
        <span>Admins</span>
        <span><?php echo $totalAdmins; ?></span>
      </div>
      <div class="distribution-track">
        <div class="distribution-fill" style="width: <?php echo $adminWidth; ?>%;"></div>
      </div>
    </div>

    <div class="distribution-row">
      <div class="distribution-head">
        <span>HR Members</span>
        <span><?php echo $totalHR; ?></span>
      </div>
      <div class="distribution-track">
        <div class="distribution-fill" style="width: <?php echo $hrWidth; ?>%;"></div>
      </div>
    </div>

    <div class="distribution-row">
      <div class="distribution-head">
        <span>Employees</span>
        <span><?php echo $totalEmployees; ?></span>
      </div>
      <div class="distribution-track">
        <div class="distribution-fill" style="width: <?php echo $employeeWidth; ?>%;"></div>
      </div>
    </div>

    <div class="distribution-row">
      <div class="distribution-head">
        <span>Team Leaders</span>
        <span><?php echo $totalTeamLeaders; ?></span>
      </div>
      <div class="distribution-track">
        <div class="distribution-fill" style="width: <?php echo $teamLeaderWidth; ?>%;"></div>
      </div>
    </div>
  </div>
</div>
            
          <div class="panel">
            <div class="panel-header">
              <h2>Admin Insights</h2>
            </div>

            <div class="activity-list">
              <div class="activity-item">
                <span class="dot teal-dot"></span>
                <div>
                  <h4>KPI tracking is now independent</h4>
                  <p>This analytics page reads from a dedicated KPI table, so it no longer feels like a copy of the dashboard.</p>
                </div>
              </div>

              <div class="activity-item">
                <span class="dot green-dot"></span>
                <div>
                  <h4>Account setup still matters</h4>
                  <p><?php echo $totalPendingSetup; ?> accounts are still waiting for setup completion, which may affect onboarding flow.</p>
                </div>
              </div>

              <div class="activity-item">
                <span class="dot orange-dot"></span>
                <div>
                  <h4>Role balance is visible</h4>
                  <p>You can quickly compare whether your system is concentrated around employees, HR, or administrative users.</p>
                </div>
              </div>

              <div class="activity-item">
                <span class="dot red-dot"></span>
                <div>
                  <h4>Analytics can evolve further</h4>
                  <p>Later, you can add more KPIs like monthly hiring target, approval speed, training completion, or department readiness.</p>
                </div>
              </div>
            </div>
          </div>

         

        
      </section>

    </main>
  </div>

</body>
</html>