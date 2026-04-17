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

/* ===================== SMART HELPERS ===================== */

function notificationPriority($type, $failedAttempts = 0) {
    if ($type === 'blocked') return 'high';
    if ($type === 'warning' && $failedAttempts >= 3) return 'high';
    if ($type === 'warning') return 'medium';
    if ($type === 'request') return 'medium';
    return 'low';
}

function notificationIcon($type) {
    if ($type === 'blocked') return 'fa-user-lock';
    if ($type === 'warning') return 'fa-triangle-exclamation';
    if ($type === 'request') return 'fa-file-circle-plus';
    if ($type === 'setup') return 'fa-user-clock';
    return 'fa-bell';
}

function priorityLabel($priority) {
    if ($priority === 'high') return 'High Priority';
    if ($priority === 'medium') return 'Needs Review';
    return 'Routine';
}

/* ===================== BUILD NOTIFICATIONS ===================== */

$notifications = [];

// Blocked users
$blockedQuery = mysqli_query($conn, "SELECT full_name, role, failed_attempts FROM users WHERE is_blocked = 1 ORDER BY id DESC");
if ($blockedQuery) {
    while ($row = mysqli_fetch_assoc($blockedQuery)) {
        $notifications[] = [
            "title" => "Blocked Account",
            "message" => $row['full_name'] . " was blocked after repeated failed login attempts.",
            "type" => "blocked",
            "priority" => notificationPriority('blocked', (int)$row['failed_attempts']),
            "meta" => ucfirst($row['role']) . " account"
        ];
    }
}

// Warning users
$warningQuery = mysqli_query($conn, "SELECT full_name, role, failed_attempts FROM users WHERE failed_attempts >= 2 AND is_blocked = 0 ORDER BY failed_attempts DESC, id DESC");
if ($warningQuery) {
    while ($row = mysqli_fetch_assoc($warningQuery)) {
        $notifications[] = [
            "title" => "Login Warning",
            "message" => $row['full_name'] . " has " . $row['failed_attempts'] . " failed login attempts.",
            "type" => "warning",
            "priority" => notificationPriority('warning', (int)$row['failed_attempts']),
            "meta" => ucfirst($row['role']) . " account"
        ];
    }
}

// Pending requests
$requestQuery = mysqli_query($conn, "SELECT full_name, email, created_at FROM requests WHERE status = 'pending' ORDER BY id DESC");
if ($requestQuery) {
    while ($row = mysqli_fetch_assoc($requestQuery)) {
        $notifications[] = [
            "title" => "New Request",
            "message" => $row['full_name'] . " submitted a new access request.",
            "type" => "request",
            "priority" => notificationPriority('request'),
            "meta" => !empty($row['created_at']) ? $row['created_at'] : 'Pending review'
        ];
    }
}

// Pending setup
$setupQuery = mysqli_query($conn, "SELECT full_name, role FROM users WHERE account_status = 'pending_setup' ORDER BY id DESC");
if ($setupQuery) {
    while ($row = mysqli_fetch_assoc($setupQuery)) {
        $notifications[] = [
            "title" => "Setup Pending",
            "message" => $row['full_name'] . " still needs to complete account setup.",
            "type" => "setup",
            "priority" => notificationPriority('setup'),
            "meta" => ucfirst($row['role']) . " onboarding"
        ];
    }
}

/* ===================== COUNTS ===================== */

$totalNotifications = count($notifications);
$highCount = 0;
$mediumCount = 0;
$lowCount = 0;

foreach ($notifications as $n) {
    if ($n['priority'] === 'high') $highCount++;
    elseif ($n['priority'] === 'medium') $mediumCount++;
    else $lowCount++;
}

$highNotifications = array_values(array_filter($notifications, fn($n) => $n['priority'] === 'high'));
$mediumNotifications = array_values(array_filter($notifications, fn($n) => $n['priority'] === 'medium'));
$lowNotifications = array_values(array_filter($notifications, fn($n) => $n['priority'] === 'low'));

// latest 5 for side panel
$recentNotifications = array_slice($notifications, 0, 5);

/* ===================== AI INSIGHT ===================== */

$aiHeadline = "System is stable";
$aiText = "No major issues detected. Continue normal monitoring.";
$aiClass = "ai-good";

if ($highCount >= 2) {
    $aiHeadline = "Immediate admin attention needed";
    $aiText = "There are multiple high-priority alerts. Review blocked accounts and security-related events first.";
    $aiClass = "ai-critical";
} elseif ($highCount === 1 || $mediumCount >= 3) {
    $aiHeadline = "Moderate activity detected";
    $aiText = "The system is active and requires review soon, especially for pending requests or repeated login attempts.";
    $aiClass = "ai-warning";
} elseif ($lowCount >= 2) {
    $aiHeadline = "Routine operational updates";
    $aiText = "Most alerts are normal onboarding or setup reminders. No urgent security action is required now.";
    $aiClass = "ai-good";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .notifications-hero {
      background: linear-gradient(135deg, #0f172a, #12396b, #14b8a6);
      color: white;
      border-radius: 26px;
      padding: 28px 30px;
      margin-bottom: 28px;
      box-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
    }

    .notifications-hero h2 {
      font-size: 30px;
      margin-bottom: 10px;
    }

    .notifications-hero p {
      color: rgba(255,255,255,0.88);
      font-size: 15px;
      line-height: 1.7;
      max-width: 760px;
    }

    .live-badge {
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.18);
      border-radius: 16px;
      padding: 12px 16px;
      font-weight: 700;
      font-size: 14px;
      white-space: nowrap;
    }

    .notification-summary {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-bottom: 28px;
    }

    .summary-card {
      background: rgba(255,255,255,0.82);
      backdrop-filter: blur(12px);
      border-radius: 24px;
      padding: 22px;
      border: 1px solid rgba(255,255,255,0.55);
      box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .summary-icon {
      width: 58px;
      height: 58px;
      border-radius: 18px;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      font-size: 22px;
      flex-shrink: 0;
    }

    .summary-icon.total { background: linear-gradient(135deg, #0ea5a4, #14b8a6); }
    .summary-icon.high { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .summary-icon.medium { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .summary-icon.low { background: linear-gradient(135deg, #22c55e, #16a34a); }

    .summary-info h3 {
      font-size: 34px;
      line-height: 1;
      margin-bottom: 6px;
      color: #0f172a;
    }

    .summary-info p {
      font-size: 15px;
      color: #334155;
      margin-bottom: 4px;
      font-weight: 700;
    }

    .summary-info span {
      font-size: 12px;
      color: #64748b;
    }

    .notifications-layout {
      display: grid;
      grid-template-columns: 1.6fr 1fr;
      gap: 22px;
    }

    .notifications-column {
      display: flex;
      flex-direction: column;
      gap: 22px;
    }

    .priority-section {
      background: rgba(255,255,255,0.82);
      backdrop-filter: blur(12px);
      border-radius: 26px;
      padding: 24px;
      border: 1px solid rgba(255,255,255,0.55);
      box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
    }

    .priority-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 18px;
      gap: 12px;
      flex-wrap: wrap;
    }

    .priority-header h3 {
      font-size: 24px;
      color: #0f172a;
      margin: 0;
    }

    .priority-count {
      border-radius: 999px;
      padding: 8px 12px;
      font-size: 12px;
      font-weight: 700;
    }

    .priority-count.high {
      background: #fee2e2;
      color: #991b1b;
    }

    .priority-count.medium {
      background: #fef3c7;
      color: #92400e;
    }

    .priority-count.low {
      background: #dcfce7;
      color: #166534;
    }

    .notifications-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
      gap: 16px;
    }

    .notification-card {
      background: linear-gradient(135deg, #f8fbff, #eef8f8);
      border: 1px solid #e3eef2;
      border-left-width: 6px;
      border-radius: 20px;
      padding: 18px;
      display: flex;
      gap: 14px;
      transition: 0.3s ease;
      min-height: 128px;
    }

    .notification-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
    }

    .notification-card.high { border-left-color: #ef4444; }
    .notification-card.medium { border-left-color: #f59e0b; }
    .notification-card.low { border-left-color: #22c55e; }

    .notification-icon {
      width: 52px;
      height: 52px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 18px;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .notification-icon.high { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .notification-icon.medium { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .notification-icon.low { background: linear-gradient(135deg, #22c55e, #16a34a); }

    .notification-content {
      width: 100%;
    }

    .notification-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 12px;
      margin-bottom: 8px;
    }

    .notification-top h4 {
      font-size: 20px;
      color: #0f172a;
      margin: 0;
    }

    .notification-badge {
      border-radius: 999px;
      padding: 6px 10px;
      font-size: 11px;
      font-weight: 700;
      white-space: nowrap;
    }

    .notification-badge.high {
      background: #fee2e2;
      color: #991b1b;
    }

    .notification-badge.medium {
      background: #fef3c7;
      color: #92400e;
    }

    .notification-badge.low {
      background: #dcfce7;
      color: #166534;
    }

    .notification-content p {
      font-size: 14px;
      color: #475569;
      line-height: 1.6;
      margin: 0 0 10px 0;
    }

    .notification-meta {
      font-size: 12px;
      color: #64748b;
      font-weight: 700;
    }

    .ai-panel {
      border-radius: 26px;
      padding: 24px;
      color: white;
      box-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
    }

    .ai-panel.ai-good {
      background: linear-gradient(135deg, #0ea5a4, #14b8a6);
    }

    .ai-panel.ai-warning {
      background: linear-gradient(135deg, #f59e0b, #f97316);
    }

    .ai-panel.ai-critical {
      background: linear-gradient(135deg, #dc2626, #ef4444);
    }

    .ai-panel h3 {
      font-size: 24px;
      margin-bottom: 10px;
    }

    .ai-panel p {
      font-size: 15px;
      line-height: 1.7;
      color: rgba(255,255,255,0.92);
      margin: 0;
    }

    .side-panel {
      background: rgba(255,255,255,0.82);
      backdrop-filter: blur(12px);
      border-radius: 26px;
      padding: 24px;
      border: 1px solid rgba(255,255,255,0.55);
      box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
    }

    .side-panel h3 {
      font-size: 22px;
      color: #0f172a;
      margin-bottom: 18px;
    }

    .mini-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .mini-item {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      background: #f8fbff;
      border: 1px solid #e5eef5;
      border-radius: 18px;
      padding: 14px;
    }

    .mini-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-top: 6px;
      flex-shrink: 0;
    }

    .mini-dot.high { background: #ef4444; }
    .mini-dot.medium { background: #f59e0b; }
    .mini-dot.low { background: #22c55e; }

    .mini-item h4 {
      font-size: 14px;
      color: #0f172a;
      margin-bottom: 4px;
    }

    .mini-item p {
      font-size: 12px;
      color: #64748b;
      line-height: 1.5;
      margin: 0;
    }

    .empty-state {
      text-align: center;
      padding: 28px 10px;
      color: #64748b;
      font-weight: 700;
    }

    @media (max-width: 1250px) {
      .notification-summary {
        grid-template-columns: repeat(2, 1fr);
      }

      .notifications-layout {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 780px) {
      .notification-summary {
        grid-template-columns: 1fr;
      }

      .notifications-grid {
        grid-template-columns: 1fr;
      }

      .notification-top {
        flex-direction: column;
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
        <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
        <li class="active"><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
      </ul>

      <div class="sidebar-bottom">
        <div class="system-card">
          <p>Notification Status</p>
          <h4>Smart Center</h4>
          <span><?php echo $totalNotifications; ?> live alerts</span>
        </div>
      </div>
    </aside>

    <main class="main-content">

      <header class="topbar">
        <div class="topbar-left">
          <h1>Notifications Center</h1>
          <p>Monitor smart alerts, onboarding updates, security warnings, and request-related events in one place.</p>
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

      <section class="notifications-hero">
        <div>
          <h2>Smart Notification Dashboard 🔔</h2>
          <p>This page prioritizes what matters most. High-risk events appear first, while routine onboarding or operational updates stay visible without cluttering the admin workflow.</p>
        </div>
        <div class="live-badge"><?php echo $totalNotifications; ?> Active Alerts</div>
      </section>

      <section class="notification-summary">
        <div class="summary-card">
          <div class="summary-icon total"><i class="fas fa-bell"></i></div>
          <div class="summary-info">
            <h3><?php echo $totalNotifications; ?></h3>
            <p>Total Alerts</p>
            <span>All current system notifications</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="summary-icon high"><i class="fas fa-fire"></i></div>
          <div class="summary-info">
            <h3><?php echo $highCount; ?></h3>
            <p>Critical Alerts</p>
            <span>Need immediate admin attention</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="summary-icon medium"><i class="fas fa-eye"></i></div>
          <div class="summary-info">
            <h3><?php echo $mediumCount; ?></h3>
            <p>Review Soon</p>
            <span>Warnings and request activity</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="summary-icon low"><i class="fas fa-circle-check"></i></div>
          <div class="summary-info">
            <h3><?php echo $lowCount; ?></h3>
            <p>Routine Updates</p>
            <span>Onboarding and informational items</span>
          </div>
        </div>
      </section>

      <section class="notifications-layout">
        <div class="notifications-column">

          <div class="priority-section">
            <div class="priority-header">
              <h3>🔥 Critical</h3>
              <span class="priority-count high"><?php echo $highCount; ?> alert(s)</span>
            </div>

            <?php if (!empty($highNotifications)) { ?>
              <div class="notifications-grid">
                <?php foreach ($highNotifications as $n) { ?>
                  <div class="notification-card high">
                    <div class="notification-icon high">
                      <i class="fas <?php echo notificationIcon($n['type']); ?>"></i>
                    </div>
                    <div class="notification-content">
                      <div class="notification-top">
                        <h4><?php echo htmlspecialchars($n['title']); ?></h4>
                        <span class="notification-badge high"><?php echo priorityLabel($n['priority']); ?></span>
                      </div>
                      <p><?php echo htmlspecialchars($n['message']); ?></p>
                      <div class="notification-meta"><?php echo htmlspecialchars($n['meta']); ?></div>
                    </div>
                  </div>
                <?php } ?>
              </div>
            <?php } else { ?>
              <div class="empty-state">No critical alerts right now.</div>
            <?php } ?>
          </div>

          <div class="priority-section">
            <div class="priority-header">
              <h3>⚠️ Review Soon</h3>
              <span class="priority-count medium"><?php echo $mediumCount; ?> alert(s)</span>
            </div>

            <?php if (!empty($mediumNotifications)) { ?>
              <div class="notifications-grid">
                <?php foreach ($mediumNotifications as $n) { ?>
                  <div class="notification-card medium">
                    <div class="notification-icon medium">
                      <i class="fas <?php echo notificationIcon($n['type']); ?>"></i>
                    </div>
                    <div class="notification-content">
                      <div class="notification-top">
                        <h4><?php echo htmlspecialchars($n['title']); ?></h4>
                        <span class="notification-badge medium"><?php echo priorityLabel($n['priority']); ?></span>
                      </div>
                      <p><?php echo htmlspecialchars($n['message']); ?></p>
                      <div class="notification-meta"><?php echo htmlspecialchars($n['meta']); ?></div>
                    </div>
                  </div>
                <?php } ?>
              </div>
            <?php } else { ?>
              <div class="empty-state">No medium-priority notifications.</div>
            <?php } ?>
          </div>

          <div class="priority-section">
            <div class="priority-header">
              <h3>✅ Routine Updates</h3>
              <span class="priority-count low"><?php echo $lowCount; ?> alert(s)</span>
            </div>

            <?php if (!empty($lowNotifications)) { ?>
              <div class="notifications-grid">
                <?php foreach ($lowNotifications as $n) { ?>
                  <div class="notification-card low">
                    <div class="notification-icon low">
                      <i class="fas <?php echo notificationIcon($n['type']); ?>"></i>
                    </div>
                    <div class="notification-content">
                      <div class="notification-top">
                        <h4><?php echo htmlspecialchars($n['title']); ?></h4>
                        <span class="notification-badge low"><?php echo priorityLabel($n['priority']); ?></span>
                      </div>
                      <p><?php echo htmlspecialchars($n['message']); ?></p>
                      <div class="notification-meta"><?php echo htmlspecialchars($n['meta']); ?></div>
                    </div>
                  </div>
                <?php } ?>
              </div>
            <?php } else { ?>
              <div class="empty-state">No routine updates.</div>
            <?php } ?>
          </div>

        </div>

        <div class="notifications-column">

          <div class="ai-panel <?php echo $aiClass; ?>">
            <h3>🤖 AI Insight</h3>
            <p><strong><?php echo $aiHeadline; ?></strong><br><?php echo $aiText; ?></p>
          </div>

          <div class="side-panel">
            <h3>Recent Highlights</h3>
            <?php if (!empty($recentNotifications)) { ?>
              <div class="mini-list">
                <?php foreach ($recentNotifications as $n) { ?>
                  <div class="mini-item">
                    <span class="mini-dot <?php echo $n['priority']; ?>"></span>
                    <div>
                      <h4><?php echo htmlspecialchars($n['title']); ?></h4>
                      <p><?php echo htmlspecialchars($n['message']); ?></p>
                    </div>
                  </div>
                <?php } ?>
              </div>
            <?php } else { ?>
              <div class="empty-state">No highlights yet.</div>
            <?php } ?>
          </div>

          <div class="side-panel">
            <h3>Admin Guidance</h3>
            <div class="mini-list">
              <div class="mini-item">
                <span class="mini-dot high"></span>
                <div>
                  <h4>Handle critical items first</h4>
                  <p>Blocked accounts and severe security-related events should always be reviewed before routine setup tasks.</p>
                </div>
              </div>

              <div class="mini-item">
                <span class="mini-dot medium"></span>
                <div>
                  <h4>Review pending requests regularly</h4>
                  <p>Keeping request review timely improves onboarding speed and avoids unnecessary delays for users.</p>
                </div>
              </div>

              <div class="mini-item">
                <span class="mini-dot low"></span>
                <div>
                  <h4>Monitor setup completion</h4>
                  <p>Low-priority onboarding reminders are not urgent, but too many can signal friction in the setup flow.</p>
                </div>
              </div>
            </div>
          </div>

        </div>
      </section>

    </main>
  </div>

</body>
</html>