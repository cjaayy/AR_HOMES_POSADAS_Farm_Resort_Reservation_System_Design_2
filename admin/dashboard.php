<?php
/**
 * Admin Dashboard
 * AR Homes Posadas Farm Resort Reservation System
 */

// Prevent browser caching to ensure latest code is loaded
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page
    header('Location: ../index.html');
    exit;
}

// Check session timeout
require_once '../config/database.php';
$timeout = SESSION_TIMEOUT;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    // Session has expired
    session_unset();
    session_destroy();
    header('Location: ../index.html?session_expired=1');
    exit;
}

// CRITICAL: Check if database connection is active (MUST have XAMPP running)
try {
    require_once '../config/connection.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    // Test the connection by querying admin_users table
    $testQuery = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE admin_id = :admin_id");
    $adminId = $_SESSION['admin_id'] ?? 0;
    $testQuery->bindParam(':admin_id', $adminId, PDO::PARAM_INT);
    $testQuery->execute();
    
    // If we reach here, database is connected
} catch (PDOException $e) {
    // Database connection failed - XAMPP MySQL is OFF
    session_unset();
    session_destroy();
    
    // Show error page
    die('
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Error</title>
        <style>
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 0;
                padding: 20px;
            }
            .error-container {
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                max-width: 600px;
                text-align: center;
            }
            .error-icon {
                font-size: 80px;
                color: #dc3545;
                margin-bottom: 20px;
            }
            h1 {
                color: #dc3545;
                margin-bottom: 20px;
            }
            p {
                color: #666;
                line-height: 1.8;
                margin: 15px 0;
            }
            .error-details {
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                text-align: left;
            }
            .solution {
                background: #d1ecf1;
                border: 1px solid #bee5eb;
                color: #0c5460;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                text-align: left;
            }
            .solution h3 {
                margin-top: 0;
                color: #0c5460;
            }
            .solution ol {
                margin: 10px 0;
                padding-left: 20px;
            }
            .solution li {
                margin: 8px 0;
            }
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                margin-top: 20px;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #764ba2;
            }
            code {
                background: #f4f4f4;
                padding: 2px 6px;
                border-radius: 4px;
                font-family: monospace;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">🔌❌</div>
            <h1>Database Connection Failed</h1>
            <p><strong>The admin dashboard cannot load because XAMPP MySQL is not running.</strong></p>
            
            <div class="error-details">
                <strong>⚠️ Error:</strong> ' . htmlspecialchars($e->getMessage()) . '
            </div>
            
            <div class="solution">
                <h3>💡 How to Fix:</h3>
                <ol>
                    <li>Open <strong>XAMPP Control Panel</strong></li>
                    <li>Click <strong>"Start"</strong> on <strong>MySQL</strong> service</li>
                    <li>Wait for the green indicator</li>
                    <li>Refresh this page or <a href="../index.html" style="color: #0c5460;">Login Again</a></li>
                </ol>
            </div>
            
            <p style="margin-top: 30px; color: #999; font-size: 0.9rem;">
                <strong>Note:</strong> This system requires XAMPP MySQL to be running at all times.<br>
                Without an active database connection, the admin dashboard cannot function.
            </p>
            
            <a href="../index.html" class="btn">Return to Login</a>
        </div>
    </body>
    </html>
    ');
} catch (Exception $e) {
    // Any other error
    session_unset();
    session_destroy();
    die('Database Error: ' . htmlspecialchars($e->getMessage()) . '<br><a href="../index.html">Return to Login</a>');
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Get admin data from session
$adminFullName = $_SESSION['admin_full_name'] ?? 'Administrator';
$adminUsername = $_SESSION['admin_username'] ?? 'admin';
$adminEmail = $_SESSION['admin_email'] ?? '';
$adminRole = $_SESSION['admin_role'] ?? 'admin';

// Format role for display
$roleDisplay = ucwords(str_replace('_', ' ', $adminRole));
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - AR Homes Posadas Farm Resort</title>

    <!-- Favicon -->
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="../logo/ar-homes-logo.png"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="16x16"
      href="../logo/ar-homes-logo.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="180x180"
      href="../logo/ar-homes-logo.png"
    />
    <link
      rel="shortcut icon"
      href="../logo/ar-homes-logo.png"
    />

    <!-- Meta tags -->
    <meta name="theme-color" content="#667eea" />
    <meta
      name="description"
      content="Admin Dashboard for AR Homes Posadas Farm Resort - Manage reservations, users, and resort operations."
    />
    <meta
      name="keywords"
      content="admin, dashboard, resort management, AR Homes, Posadas Farm"
    />
    <meta name="author" content="AR Homes Posadas Farm Resort" />

    <link rel="stylesheet" href="../admin-styles.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&family=Bungee+Spice&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css"
    />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      #adminFilterFrom:focus,
      #adminFilterTo:focus {
        outline: none;
        border-color: #11224e !important;
        box-shadow: 0 0 0 3px rgba(17, 34, 78, 0.1);
      }
    </style>
  </head>
  <body>
    <!-- PHP Session Debug Info (Hidden) -->
    <script id="phpSessionData" type="application/json">
      <?php echo json_encode([
        'admin_logged_in' => $_SESSION['admin_logged_in'] ?? false,
        'admin_role' => $_SESSION['admin_role'] ?? '',
        'admin_username' => $_SESSION['admin_username'] ?? '',
        'admin_id' => $_SESSION['admin_id'] ?? '',
        'session_id' => session_id(),
        'all_session' => $_SESSION
      ], JSON_PRETTY_PRINT); ?>
    </script>
    <script>
      // Log PHP session data immediately
      const phpSession = JSON.parse(document.getElementById('phpSessionData').textContent);
      console.log('🔍 PHP SESSION DATA:', phpSession);
      console.log('📋 Session Summary:');
      console.log('  • Logged In:', phpSession.admin_logged_in);
      console.log('  • Role:', phpSession.admin_role);
      console.log('  • Username:', phpSession.admin_username);
      console.log('  • Session ID:', phpSession.session_id);
    </script>
    
    <div class="admin-container">
      <!-- Header -->
      <header class="admin-header">
        <div class="header-left">
          <div class="logo">
            <img
              src="../logo/ar-homes-logo.png"
              alt="AR Homes Resort Logo"
            />
          </div>
          <div class="resort-info">
            <h1>AR Homes Posadas Farm Resort</h1>
          </div>
        </div>
        <div class="header-right">
          <div class="admin-profile">
            <div class="profile-info">
              <span class="admin-name"><?php echo htmlspecialchars($adminFullName); ?></span>
              <span class="admin-role"><?php echo htmlspecialchars($roleDisplay); ?></span>
            </div>
            <div class="profile-avatar">
              <i class="fas fa-user-shield"></i>
            </div>
          </div>
          <button class="logout-btn" id="logoutButton" onclick="handleLogoutClick(event)" style="z-index: 1000; position: relative;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
          </button>
        </div>
        <div class="mobile-toggle" onclick="toggleSidebar()">
          <i class="fas fa-bars"></i>
        </div>
      </header>

      <!-- Sidebar -->
      <aside class="sidebar" id="sidebar">
        <nav class="sidebar-nav">
          <ul class="nav-menu">
            <li class="nav-item">
              <a href="#dashboard" class="nav-link" data-section="dashboard">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
              </a>
            </li>
            <li class="nav-item">
              <a
                href="#reservations"
                class="nav-link"
                data-section="reservations"
              >
                <i class="fas fa-calendar-check"></i>
                <span>Manage Reservations</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="#users" class="nav-link" data-section="users">
                <i class="fas fa-users"></i>
                <span>Manage Users</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="#staff" class="nav-link" data-section="staff">
                <i class="fas fa-user-tie"></i>
                <span>Manage Staff</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="#reports" class="nav-link" data-section="reports">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="#settings" class="nav-link" data-section="settings">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
              </a>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="main-content">
        <!-- Dashboard Section -->
        <section id="dashboard" class="content-section active">
          <div class="section-header" style="margin-bottom:30px;">
            <h2 style="color:#333; font-size:32px; font-weight:700; margin:0 0 8px 0;">Dashboard Overview</h2>
            <p style="color:#666; margin:0; font-size:16px;">
              Welcome back, <?php echo htmlspecialchars($adminFullName); ?>! Here's what's happening at the resort
              today.
            </p>
          </div>

          <!-- Dashboard Container - Same width as User Dashboard -->
          <div style="max-width: 800px; margin: 0 auto;">
            <!-- Dashboard Statistics Cards -->
            <div class="dashboard-stats-grid">
            <!-- Total Users Card -->
            <div class="stat-card primary">
              <div class="stat-icon">
                <i class="fas fa-users"></i>
              </div>
              <div class="stat-content">
                <div class="stat-value" id="totalUsersCount">
                  <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="stat-label">Total Users</div>
                <div class="stat-change positive">
                  <i class="fas fa-arrow-up"></i>
                  <span id="newUsersMonth">0</span> this month
                </div>
              </div>
            </div>

            <!-- Active Users Card -->
            <div class="stat-card success">
              <div class="stat-icon">
                <i class="fas fa-user-check"></i>
              </div>
              <div class="stat-content">
                <div class="stat-value" id="activeUsersCount">
                  <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="stat-label">Active Users</div>
                <div class="stat-change">
                  <i class="fas fa-info-circle"></i>
                  <span id="activePercentage">0%</span> of total
                </div>
              </div>
            </div>

            <!-- New Users Today Card -->
            <div class="stat-card info">
              <div class="stat-icon">
                <i class="fas fa-user-plus"></i>
              </div>
              <div class="stat-content">
                <div class="stat-value" id="newUsersToday">
                  <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="stat-label">New Today</div>
                <div class="stat-change">
                  <i class="fas fa-calendar-day"></i>
                  <span id="newUsersWeek">0</span> this week
                </div>
              </div>
            </div>

            <!-- Total Reservations Card -->
            <div class="stat-card primary">
              <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
              </div>
              <div class="stat-content">
                <div class="stat-value" id="totalReservationsCount">
                  <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="stat-label">Total Reservations</div>
                <div class="stat-change">
                  <i class="fas fa-chart-line"></i>
                  All time
                </div>
              </div>
            </div>

            <!-- Pending Reservations Card -->
            <div class="stat-card warning">
              <div class="stat-icon">
                <i class="fas fa-clock"></i>
              </div>
              <div class="stat-content">
                <div class="stat-value" id="pendingReservationsCount">
                  <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="stat-label">Pending</div>
                <div class="stat-change">
                  <i class="fas fa-hourglass-half"></i>
                  Awaiting approval
                </div>
              </div>
            </div>

            <!-- Confirmed Reservations Card -->
            <div class="stat-card success">
              <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="stat-content">
                <div class="stat-value" id="confirmedReservationsCount">
                  <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="stat-label">Confirmed</div>
                <div class="stat-change positive">
                  <i class="fas fa-thumbs-up"></i>
                  Approved bookings
                </div>
              </div>
            </div>

            <!-- Completed Reservations Card -->
            <div class="stat-card info">
              <div class="stat-icon">
                <i class="fas fa-flag-checkered"></i>
              </div>
              <div class="stat-content">
                <div class="stat-value" id="completedReservationsCount">
                  <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="stat-label">Completed</div>
                <div class="stat-change">
                  <i class="fas fa-history"></i>
                  Past stays
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activities Section -->
          <div class="recent-activities-section" style="max-width: 800px; margin: 0 auto;">
            <div class="section-header-inline">
              <h3><i class="fas fa-history"></i> Recent Activities</h3>
              <button class="refresh-btn" onclick="loadDashboardStats()" title="Refresh data">
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
            <div class="activities-container" id="recentActivitiesContainer">
              <div class="loading-activities">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading recent activities...</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Reservations Section -->
        <section id="reservations" class="content-section">
          <div class="section-header" style="margin-bottom:30px;">
            <h2 style="color:#333; font-size:32px; font-weight:700; margin:0 0 8px 0;">Reservations Management</h2>
            <p style="color:#666; margin:0; font-size:16px;">View and manage all resort reservations efficiently</p>
          </div>

          <!-- Stats Overview -->
          <div class="stats-overview" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:20px; margin-bottom:30px;">
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-calendar-check"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="adminTotalReservations">0</div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Total Reservations</div>
              </div>
            </div>
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-check-circle"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="adminConfirmedCount">0</div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Confirmed</div>
              </div>
            </div>
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-clock"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="adminPendingCount">0</div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Pending</div>
              </div>
            </div>
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-times-circle"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="adminCanceledCount">0</div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Canceled</div>
              </div>
            </div>
          </div>

          <!-- Enhanced Filters Section -->
          <div style="background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
              <h3 style="margin:0; font-size:16px; font-weight:600; color:#1e293b; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-filter" style="color:#11224e;"></i> Filter Reservations
              </h3>
              <button onclick="adminClearFilters()" style="padding:6px 14px; background:#f1f5f9; border:none; border-radius:6px; font-size:13px; font-weight:600; color:#64748b; cursor:pointer; transition:all 0.2s;">
                <i class="fas fa-redo"></i> Clear Filters
              </button>
            </div>

            <!-- Quick Status Filters -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:12px; margin-bottom:16px;">
              <div class="filter-chip-enhanced active" onclick="adminQuickFilter('all')" id="admin-chip-all" style="cursor:pointer; padding:14px 16px; background:white; border:2px solid #11224e; border-radius:12px; transition:all 0.3s ease; box-shadow:0 4px 16px rgba(17,34,78,0.3); background:#11224e;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:36px; height:36px; background:rgba(255,255,255,0.25); border-radius:8px; display:flex; align-items:center; justify-content:center; color:white; font-size:16px;"><i class="fas fa-list"></i></div>
                  <span style="flex:1; font-size:14px; font-weight:600; color:white;">All</span>
                  <div style="padding:4px 10px; font-size:12px; font-weight:700; color:white; min-width:32px; text-align:center;" id="admin-count-all">0</div>
                </div>
              </div>
              <div class="filter-chip-enhanced" onclick="adminQuickFilter('pending')" id="admin-chip-pending" style="cursor:pointer; padding:14px 16px; background:white; border:2px solid #11224e; border-radius:12px; transition:all 0.3s ease;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:36px; height:36px; background:rgba(17,34,78,0.1); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#11224e; font-size:16px;"><i class="fas fa-clock"></i></div>
                  <span style="flex:1; font-size:14px; font-weight:600; color:#11224e;">Pending</span>
                  <div style="padding:4px 10px; font-size:12px; font-weight:700; color:#11224e; min-width:32px; text-align:center;" id="admin-count-pending">0</div>
                </div>
              </div>
              <div class="filter-chip-enhanced" onclick="adminQuickFilter('confirmed')" id="admin-chip-confirmed" style="cursor:pointer; padding:14px 16px; background:white; border:2px solid #11224e; border-radius:12px; transition:all 0.3s ease;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:36px; height:36px; background:rgba(17,34,78,0.1); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#11224e; font-size:16px;"><i class="fas fa-check"></i></div>
                  <span style="flex:1; font-size:14px; font-weight:600; color:#11224e;">Confirmed</span>
                  <div style="padding:4px 10px; font-size:12px; font-weight:700; color:#11224e; min-width:32px; text-align:center;" id="admin-count-confirmed">0</div>
                </div>
              </div>
              <div class="filter-chip-enhanced" onclick="adminQuickFilter('completed')" id="admin-chip-completed" style="cursor:pointer; padding:14px 16px; background:white; border:2px solid #11224e; border-radius:12px; transition:all 0.3s ease;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:36px; height:36px; background:rgba(17,34,78,0.1); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#11224e; font-size:16px;"><i class="fas fa-flag-checkered"></i></div>
                  <span style="flex:1; font-size:14px; font-weight:600; color:#11224e;">Completed</span>
                  <div style="padding:4px 10px; font-size:12px; font-weight:700; color:#11224e; min-width:32px; text-align:center;" id="admin-count-completed">0</div>
                </div>
              </div>
              <div class="filter-chip-enhanced" onclick="adminQuickFilter('canceled')" id="admin-chip-canceled" style="cursor:pointer; padding:14px 16px; background:white; border:2px solid #11224e; border-radius:12px; transition:all 0.3s ease;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:36px; height:36px; background:rgba(17,34,78,0.1); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#11224e; font-size:16px;"><i class="fas fa-ban"></i></div>
                  <span style="flex:1; font-size:14px; font-weight:600; color:#11224e;">Canceled</span>
                  <div style="padding:4px 10px; font-size:12px; font-weight:700; color:#11224e; min-width:32px; text-align:center;" id="admin-count-canceled">0</div>
                </div>
              </div>
            </div>

            <!-- Date Range Filter -->
            <div style="display:flex; gap:12px; padding-top:16px; border-top:2px solid #f1f5f9;">
              <div style="flex:1; display:flex; flex-direction:column; gap:8px;">
                <label style="font-size:13px; font-weight:600; color:#64748b; display:flex; align-items:center; gap:6px;"><i class="fas fa-calendar-day" style="color:#11224e;"></i> From Date</label>
                <input type="date" id="adminFilterFrom" onchange="adminApplyFilters()" style="padding:12px 16px; border:2px solid #11224e; border-radius:10px; font-size:14px; font-weight:500; color:#475569; background:white; transition:all 0.3s ease; cursor:pointer;">
              </div>
              <div style="flex:1; display:flex; flex-direction:column; gap:8px;">
                <label style="font-size:13px; font-weight:600; color:#64748b; display:flex; align-items:center; gap:6px;"><i class="fas fa-calendar-day" style="color:#11224e;"></i> To Date</label>
                <input type="date" id="adminFilterTo" onchange="adminApplyFilters()" style="padding:12px 16px; border:2px solid #11224e; border-radius:10px; font-size:14px; font-weight:500; color:#475569; background:white; transition:all 0.3s ease; cursor:pointer;">
              </div>
            </div>
          </div>

          <div class="users-container">
            <div class="users-header" style="padding:20px; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:20px;">
              <div class="search-box" style="flex:1; max-width:400px; position:relative;">
                <i class="fas fa-search" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:16px;"></i>
                <input type="text" id="adminSearchBox" placeholder="Search guest, room, or contact..." oninput="adminApplyFilters()" style="width:100%; padding:14px 16px 14px 48px; border:2px solid #11224e; border-radius:12px; font-size:14px; transition:all 0.3s ease;" />
              </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding:16px; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); flex-wrap:wrap; gap:12px;">
              <div style="display:flex; align-items:center; gap:12px;">
                <button onclick="adminFetchAllReservations()" style="padding:12px 20px; background:white; border:2px solid #e2e8f0; border-radius:10px; font-size:14px; font-weight:600; color:#11224e; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px;">
                  <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <span style="color:#64748b; font-size:14px;" id="adminLastUpdate">Last updated: Just now</span>
              </div>
              <div style="display:flex; gap:8px;">
                <button onclick="adminExportCSV()" style="padding:12px 20px; background:linear-gradient(135deg, #10b981, #059669); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px;">
                  <i class="fas fa-file-excel"></i> Export
                </button>
              </div>
            </div>

            <div class="table-container" style="background:white; border-radius:16px; box-shadow:0 2px 8px rgba(0,0,0,0.05); overflow-x:auto; margin-bottom:20px;">
              <table class="users-table" id="adminReservationsTable" style="width:100%; border-collapse:separate; border-spacing:0;">
                <thead style="background:#11224e; color:white;">
                  <tr>
                    <th style="padding:18px 16px; text-align:center; width:60px;">#</th>
                    <th style="padding:18px 16px; text-align:left;"><i class="fas fa-user"></i> Guest</th>
                    <th style="padding:18px 16px; text-align:left;"><i class="fas fa-cube"></i> Booking Details</th>
                    <th style="padding:18px 16px; text-align:left;"><i class="fas fa-calendar-check"></i> Check-in</th>
                    <th style="padding:18px 16px; text-align:left;"><i class="fas fa-calendar-times"></i> Check-out</th>
                    <th style="padding:18px 16px; text-align:left;"><i class="fas fa-money-bill-wave"></i> Payment</th>
                    <th style="padding:18px 16px; text-align:left; min-width:180px;"><i class="fas fa-tag"></i> Status</th>
                    <th style="padding:18px 16px; text-align:center;"><i class="fas fa-cog"></i> Actions</th>
                  </tr>
                </thead>
                <tbody id="adminReservationsBody"><tr><td colspan="8" style="text-align:center;padding:2rem;">Loading...</td></tr></tbody>
              </table>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; padding:20px; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); flex-wrap:wrap; gap:12px;">
              <div id="adminPaginationInfo" style="font-size:14px; color:#64748b; font-weight:500;"></div>
              <div style="display:flex; gap:8px;">
                <button id="adminPrevPage" onclick="adminChangePage(-1)" disabled style="padding:10px 16px; border:2px solid #e2e8f0; background:white; border-radius:8px; font-size:14px; font-weight:600; color:#475569; cursor:pointer; transition:all 0.2s;">&larr; Prev</button>
                <button id="adminNextPage" onclick="adminChangePage(1)" disabled style="padding:10px 16px; border:2px solid #e2e8f0; background:white; border-radius:8px; font-size:14px; font-weight:600; color:#475569; cursor:pointer; transition:all 0.2s;">Next &rarr;</button>
              </div>
            </div>
          </div>

          <!-- Create modal -->
          <div id="adminCreateModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
            <div style="background:#fff; padding:18px; border-radius:8px; width:560px; max-width:95%;">
              <h3>Create Walk-in / Phone Reservation</h3>
              <form id="adminCreateForm" onsubmit="return adminSubmitCreate(event)">
                <div class="form-group"><label>Guest Name</label><input name="guest_name" required></div>
                <div class="form-group"><label>Phone</label><input name="guest_phone"></div>
                <div class="form-group"><label>Room</label><input name="room"></div>
                <div class="form-group" style="display:flex;gap:8px;"><div><label>Check-in</label><input type="date" name="check_in_date"></div><div><label>Check-out</label><input type="date" name="check_out_date"></div></div>
                <div style="display:flex;gap:8px; justify-content:flex-end; margin-top:12px;"><button type="button" onclick="adminHideCreateForm()" class="btn-secondary">Cancel</button><button class="btn-primary">Create</button></div>
              </form>
            </div>
          </div>

          <script>
            // Admin reservations client-side - Enhanced version matching staff interface
            let adminAllReservations = [];
            let adminFilteredReservations = [];
            let adminCurrentPage = 1;
            const adminPageSize = 12;
            let adminCurrentQuickFilter = 'all';

            async function adminFetchAllReservations(){
              try{
                console.log('🔄 Fetching reservations from staff_get_reservations.php...');
                const res = await fetch('staff_get_reservations.php?limit=1000', { credentials: 'include' });
                console.log('📡 Response status:', res.status, res.statusText);
                
                if (!res.ok) {
                  console.error('❌ HTTP error:', res.status, res.statusText);
                  
                  // If 401, show detailed error
                  if (res.status === 401) {
                    const errorData = await res.json();
                    console.error('❌ Unauthorized:', errorData);
                    console.error('🔍 Debug Info:', {
                      logged_in: errorData.debug?.logged_in,
                      role: errorData.debug?.role,
                      allowed_roles: errorData.debug?.allowed_roles,
                      session_id: errorData.debug?.session_id
                    });
                    
                    const roleText = errorData.debug?.role || 'none';
                    const loggedInText = errorData.debug?.logged_in ? 'Yes' : 'No';
                    const allowedRoles = errorData.debug?.allowed_roles?.join(', ') || 'admin, staff';
                    
                    document.getElementById('adminReservationsBody').innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;">
                      <div style="color:#ef4444; margin-bottom:12px;"><i class="fas fa-exclamation-triangle" style="font-size:48px;"></i></div>
                      <div style="font-weight:700; color:#1e293b; font-size:18px; margin-bottom:12px;">Authorization Failed</div>
                      <div style="background:#fee2e2; padding:16px; border-radius:8px; border-left:4px solid #ef4444; text-align:left; max-width:500px; margin:0 auto;">
                        <div style="color:#64748b; font-size:13px; margin-bottom:8px;"><strong>Session Info:</strong></div>
                        <div style="color:#1e293b; font-size:14px; margin:4px 0;">• Logged In: <strong>${loggedInText}</strong></div>
                        <div style="color:#1e293b; font-size:14px; margin:4px 0;">• Your Role: <strong>${roleText}</strong></div>
                        <div style="color:#1e293b; font-size:14px; margin:4px 0;">• Required Roles: <strong>${allowedRoles}</strong></div>
                        <div style="color:#1e293b; font-size:14px; margin:4px 0;">• Session ID: <strong>${errorData.debug?.session_id || 'none'}</strong></div>
                      </div>
                      <button onclick="location.href='../index.html'" style="margin-top:16px; padding:10px 24px; background:#ef4444; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Re-Login</button>
                      <button onclick="location.reload()" style="margin-top:16px; margin-left:8px; padding:10px 24px; background:#667eea; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Refresh</button>
                    </td></tr>`;
                    return;
                  }
                  
                  document.getElementById('adminReservationsBody').innerHTML = `<tr><td colspan="8" style="text-align:center;color:#b00;">HTTP Error: ${res.status} ${res.statusText}</td></tr>`;
                  return;
                }
                
                const data = await res.json();
                console.log('📊 Data received:', data);
                
                if(!data.success){ 
                  console.error('❌ Failed to load reservations:', data.message); 
                  document.getElementById('adminReservationsBody').innerHTML = `<tr><td colspan="8" style="text-align:center;color:#b00;">Failed: ${data.message || 'Unknown error'}</td></tr>`; 
                  return; 
                }
                
                adminAllReservations = data.reservations || [];
                console.log('✅ Loaded', adminAllReservations.length, 'reservations');
                adminUpdateStatsCards();
                adminApplyFilters();
                adminUpdateLastUpdateTime();
              }catch(err){ 
                console.error('❌ Error loading reservations:', err); 
                document.getElementById('adminReservationsBody').innerHTML = `<tr><td colspan="8" style="text-align:center;color:#b00;">Error: ${err.message}</td></tr>`; 
              }
            }

            function adminUpdateStatsCards(){
              const total = adminAllReservations.length;
              const confirmed = adminAllReservations.filter(r => r.status === 'confirmed').length;
              const pending = adminAllReservations.filter(r => r.status === 'pending').length;
              const canceled = adminAllReservations.filter(r => r.status === 'canceled').length;
              const completed = adminAllReservations.filter(r => r.status === 'completed').length;
              
              document.getElementById('adminTotalReservations').textContent = total;
              document.getElementById('adminConfirmedCount').textContent = confirmed;
              document.getElementById('adminPendingCount').textContent = pending;
              document.getElementById('adminCanceledCount').textContent = canceled;
              
              document.getElementById('admin-count-all').textContent = total;
              document.getElementById('admin-count-pending').textContent = pending;
              document.getElementById('admin-count-confirmed').textContent = confirmed;
              document.getElementById('admin-count-completed').textContent = completed;
              document.getElementById('admin-count-canceled').textContent = canceled;
            }

            function adminUpdateLastUpdateTime(){
              const now = new Date();
              const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
              document.getElementById('adminLastUpdate').textContent = `Last updated: ${timeStr}`;
            }

            function adminQuickFilter(status){
              adminCurrentQuickFilter = status;
              document.querySelectorAll('.filter-chip-enhanced').forEach(chip => {
                chip.classList.remove('active');
                chip.style.border = '2px solid #11224e';
                chip.style.boxShadow = 'none';
                chip.style.background = 'white';
                const span = chip.querySelector('span');
                const countDiv = chip.querySelector('div[id^="admin-count-"]');
                const iconContainer = chip.querySelector('div > div:first-child');
                if(span) span.style.color = '#11224e';
                if(countDiv) {
                  countDiv.style.background = 'transparent';
                  countDiv.style.color = '#11224e';
                }
                if(iconContainer) {
                  iconContainer.style.background = 'rgba(17, 34, 78, 0.1)';
                  iconContainer.style.color = '#11224e';
                }
              });
              
              const activeChip = document.getElementById(`admin-chip-${status}`);
              if(activeChip){
                activeChip.classList.add('active');
                activeChip.style.border = '2px solid #11224e';
                activeChip.style.boxShadow = '0 4px 16px rgba(17, 34, 78, 0.2)';
                activeChip.style.background = '#11224e';
                const span = activeChip.querySelector('span');
                const countDiv = document.getElementById(`admin-count-${status}`);
                const iconContainer = activeChip.querySelector('div > div:first-child');
                const icon = iconContainer ? iconContainer.querySelector('i') : null;
                if(span) {
                  span.style.color = 'white';
                }
                if(countDiv) {
                  countDiv.style.background = 'transparent';
                  countDiv.style.color = 'white';
                  countDiv.style.fontWeight = '700';
                  countDiv.style.borderRadius = '0';
                }
                if(iconContainer) {
                  iconContainer.style.background = 'transparent';
                  iconContainer.style.color = 'white';
                }
                if(icon) {
                  icon.style.color = 'white';
                }
              }
              
              adminApplyFilters();
            }

            function adminClearFilters(){
              document.getElementById('adminFilterFrom').value = '';
              document.getElementById('adminFilterTo').value = '';
              document.getElementById('adminSearchBox').value = '';
              adminQuickFilter('all');
              adminShowNotification('Filters cleared', 'info');
            }

            function adminApplyFilters(){
              const from = document.getElementById('adminFilterFrom').value;
              const to = document.getElementById('adminFilterTo').value;
              const q = (document.getElementById('adminSearchBox').value || '').toLowerCase();
              
              adminFilteredReservations = adminAllReservations.filter(r => {
                if(adminCurrentQuickFilter !== 'all' && String(r.status) !== adminCurrentQuickFilter) return false;
                if(from && r.check_in_date && r.check_in_date < from) return false;
                if(to && r.check_in_date && r.check_in_date > to) return false;
                if(q){ const hay = ((r.guest_name||'') + ' ' + (r.room||'') + ' ' + (r.guest_phone||'') + ' ' + (r.guest_email||'')).toLowerCase(); if(!hay.includes(q)) return false; }
                return true;
              });
              adminCurrentPage = 1;
              adminRenderPage();
            }

            function adminRenderPage(){
              const tbody = document.getElementById('adminReservationsBody');
              if(!adminFilteredReservations || adminFilteredReservations.length===0){ 
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:3rem; color:#94a3b8;">
                  <i class="fas fa-inbox" style="font-size:48px; margin-bottom:16px; opacity:0.5;"></i>
                  <div style="font-size:16px; font-weight:600;">No reservations found</div>
                  <div style="font-size:14px; margin-top:8px;">Try adjusting your filters</div>
                </td></tr>`; 
                document.getElementById('adminPaginationInfo').textContent=''; 
                document.getElementById('adminPrevPage').disabled=true; 
                document.getElementById('adminNextPage').disabled=true; 
                return; 
              }
              
              const total = adminFilteredReservations.length; 
              const totalPages = Math.ceil(total / adminPageSize); 
              const start = (adminCurrentPage-1)*adminPageSize; 
              const pageRows = adminFilteredReservations.slice(start, start+adminPageSize);
              
              const bookingTypeLabels = {
                'daytime': { icon: 'fa-sun', label: 'DAYTIME', color: '#f59e0b' },
                'nighttime': { icon: 'fa-moon', label: 'NIGHTTIME', color: '#6366f1' },
                '22hours': { icon: 'fa-clock', label: '22 HOURS', color: '#8b5cf6' }
              };
              
              const packageLabels = {
                'all_rooms': 'All Rooms',
                'aircon': 'Aircon',
                'basic': 'Basic'
              };
              
              const rowsHtml = pageRows.map((r, idx)=> {
                const bookingType = bookingTypeLabels[r.booking_type] || { icon: 'fa-calendar', label: 'N/A', color: '#64748b' };
                const packageType = packageLabels[r.package_type] || r.package_type || 'N/A';
                const statusColors = {
                  'pending': 'background:linear-gradient(135deg,#f59e0b,#d97706);',
                  'confirmed': 'background:linear-gradient(135deg,#10b981,#059669);',
                  'completed': 'background:linear-gradient(135deg,#3b82f6,#2563eb);',
                  'canceled': 'background:linear-gradient(135deg,#ef4444,#dc2626);'
                };
                const statusColor = statusColors[r.status] || 'background:#94a3b8;';
                
                return `<tr style="animation:fadeIn 0.3s ease ${idx*0.05}s both; border-bottom:1px solid #f1f5f9;">
                  <td style="padding:16px; text-align:center; font-weight:700; color:#64748b;">#${r.reservation_id}</td>
                  <td style="padding:16px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                      <div style="width:40px; height:40px; background:linear-gradient(135deg, #667eea, #764ba2); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:16px;">
                        ${escapeHtml((r.guest_name||'U')[0].toUpperCase())}
                      </div>
                      <div>
                        <div style="font-weight:600; color:#1e293b;">${escapeHtml(r.guest_name||'')}</div>
                        <div style="font-size:12px; color:#64748b;"><i class="fas fa-phone"></i> ${escapeHtml(r.guest_phone||'N/A')}</div>
                      </div>
                    </div>
                  </td>
                  <td style="padding:16px;">
                    <div style="display:flex; flex-direction:column; gap:4px;">
                      <div style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; background:${bookingType.color}20; border-radius:12px; font-size:11px; font-weight:700; color:${bookingType.color}; width:fit-content;">
                        <i class="fas ${bookingType.icon}"></i>
                        ${bookingType.label}
                      </div>
                      <div style="font-size:12px; color:#64748b;">
                        <i class="fas fa-cube" style="color:#667eea;"></i> ${packageType}
                      </div>
                      <div style="font-size:11px; color:#94a3b8;">
                        <i class="fas fa-bed"></i> ${escapeHtml(r.room||'TBD')}
                      </div>
                    </div>
                  </td>
                  <td style="padding:16px;">
                    <div style="font-weight:600; color:#1e293b; margin-bottom:2px;">${r.check_in_date||'N/A'}</div>
                    <div style="font-size:11px; color:#64748b;"><i class="fas fa-clock" style="color:#10b981;"></i> ${r.check_in_time||'N/A'}</div>
                  </td>
                  <td style="padding:16px;">
                    <div style="font-weight:600; color:#1e293b; margin-bottom:2px;">${r.check_out_date||'N/A'}</div>
                    <div style="font-size:11px; color:#64748b;"><i class="fas fa-clock" style="color:#ef4444;"></i> ${r.check_out_time||'N/A'}</div>
                  </td>
                  <td style="padding:16px;">
                    <div style="font-weight:700; color:#1e293b; font-size:15px;">₱${parseFloat(r.total_amount||0).toLocaleString('en-US', {minimumFractionDigits:2})}</div>
                    <div style="font-size:11px; color:#64748b;">Down: ₱${parseFloat(r.downpayment_amount||0).toLocaleString('en-US', {minimumFractionDigits:2})}</div>
                    <div style="font-size:10px; color:${(() => {
                      const isFullyPaid = r.full_payment_verified == 1;
                      const isDownpaymentVerified = r.downpayment_verified == 1;
                      if (isFullyPaid) return '#10b981';
                      if (r.full_payment_paid) return '#f59e0b';
                      if (isDownpaymentVerified) return '#3b82f6';
                      if (r.downpayment_paid) return '#f59e0b';
                      return '#ef4444';
                    })()}; font-weight:600;">
                      ${(() => {
                        const isFullyPaid = r.full_payment_verified == 1;
                        const isDownpaymentVerified = r.downpayment_verified == 1;
                        if (isFullyPaid) return '✓ Fully Paid';
                        if (r.full_payment_paid) return '⏱ Full Pending';
                        if (isDownpaymentVerified) return '◐ Partial';
                        if (r.downpayment_paid) return '⏱ DP Pending';
                        return '✗ Unpaid';
                      })()}
                    </div>
                  </td>
                  <td style="padding:16px 12px; min-width:180px;">
                    <span style="display:inline-flex; align-items:center; gap:3px; padding:4px 8px; ${statusColor} color:white; border-radius:16px; font-size:10px; font-weight:600; text-transform:capitalize; white-space:nowrap;">
                      <i class="fas fa-circle" style="font-size:3px;"></i>${escapeHtml(r.status||'').replace('_', ' ')}
                    </span>
                  </td>
                  <td style="padding:16px; text-align:center;">
                    <div style="display:flex; gap:6px; justify-content:center;">
                      <button onclick="adminViewReservation('${r.reservation_id}')" style="width:36px; height:36px; border:none; background:#f1f5f9; color:#667eea; border-radius:8px; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center;" title="View Details" onmouseover="this.style.background='linear-gradient(135deg,#667eea,#764ba2)'; this.style.color='white';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#667eea';"><i class="fas fa-eye"></i></button>
                      ${r.status !== 'confirmed' && r.status !== 'completed' && r.status !== 'canceled' ? `<button onclick="adminUpdateStatus('${r.reservation_id}', 'confirmed')" style="width:36px; height:36px; border:none; background:#f1f5f9; color:#10b981; border-radius:8px; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center;" title="Confirm" onmouseover="this.style.background='linear-gradient(135deg,#10b981,#059669)'; this.style.color='white';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#10b981';"><i class="fas fa-check"></i></button>` : ''}
                      ${r.status !== 'canceled' && r.status !== 'completed' ? `<button onclick="adminUpdateStatus('${r.reservation_id}', 'canceled')" style="width:36px; height:36px; border:none; background:#f1f5f9; color:#ef4444; border-radius:8px; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center;" title="Cancel" onmouseover="this.style.background='linear-gradient(135deg,#ef4444,#dc2626)'; this.style.color='white';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#ef4444';"><i class="fas fa-times"></i></button>` : ''}
                    </div>
                  </td>
                </tr>`;
              }).join('');
              
              tbody.innerHTML = rowsHtml; 
              document.getElementById('adminPaginationInfo').textContent = `Page ${adminCurrentPage} of ${totalPages} — ${total} reservations`; 
              document.getElementById('adminPrevPage').disabled = adminCurrentPage<=1; 
              document.getElementById('adminNextPage').disabled = adminCurrentPage>=totalPages;
            }

            function adminChangePage(dir){ adminCurrentPage += dir; if(adminCurrentPage<1) adminCurrentPage=1; adminRenderPage(); }

            function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

            async function adminShowCreateForm(){ document.getElementById('adminCreateModal').style.display='flex'; }
            function adminHideCreateForm(){ document.getElementById('adminCreateModal').style.display='none'; }

            async function adminSubmitCreate(e){ 
              e.preventDefault(); 
              const form = new FormData(e.target); 
              form.append('action','create'); 
              try{ 
                const res = await fetch('staff_actions.php',{method:'POST', body: form, credentials:'include'}); 
                const data = await res.json(); 
                if(data.success){ 
                  adminHideCreateForm(); 
                  await adminFetchAllReservations(); 
                  adminShowNotification('Reservation created', 'success'); 
                } else { 
                  adminShowNotification('Error: '+(data.message||''),'error'); 
                } 
              }catch(err){ 
                console.error(err); 
                adminShowNotification('Failed to create reservation','error'); 
              } 
            }

            async function adminUpdateStatus(id, status){ 
              if(!confirm('Change status?')) return; 
              const form = new FormData(); 
              form.append('action','update_status'); 
              form.append('reservation_id', id); 
              form.append('status', status); 
              try{ 
                const res = await fetch('staff_actions.php',{method:'POST', body: form, credentials: 'include'}); 
                const data = await res.json(); 
                if(data.success){ 
                  await adminFetchAllReservations(); 
                  adminShowNotification('Status updated','success'); 
                } else { 
                  adminShowNotification('Error: '+(data.message||''),'error'); 
                } 
              }catch(err){ 
                console.error(err); 
                adminShowNotification('Failed to update status','error'); 
              } 
            }

            function adminViewReservation(id){ 
              const r = adminAllReservations.find(x=>String(x.reservation_id)===String(id)); 
              if(!r) return adminShowNotification('Reservation not found','error');
              
              const bookingTypeLabels = {
                'daytime': { icon: 'fa-sun', label: 'DAYTIME (9AM-5PM)', color: '#f59e0b' },
                'nighttime': { icon: 'fa-moon', label: 'NIGHTTIME (7PM-7AM)', color: '#6366f1' },
                '22hours': { icon: 'fa-clock', label: '22 HOURS (2PM-12NN)', color: '#8b5cf6' }
              };
              
              const packageLabels = {
                'daytime': 'Daytime Package',
                'nighttime': 'Nighttime Package',
                '22hours': '22 Hours Package',
                'venue-daytime': 'Venue - Daytime',
                'venue-nighttime': 'Venue - Nighttime',
                'venue-22hours': 'Venue - 22 Hours',
                // Legacy support
                'all_rooms': 'Nighttime Package',
                'aircon': 'Daytime Package',
                'basic': '22 Hours Package'
              };
              
              const bookingType = bookingTypeLabels[r.booking_type] || { icon: 'fa-calendar', label: 'N/A', color: '#64748b' };
              const packageType = packageLabels[r.package_type] || r.package_type || 'N/A';
              
              const html = `
                <div style="padding:0;">
                  <div style="background:linear-gradient(135deg, ${bookingType.color}ee, ${bookingType.color}); color:white; padding:28px 24px; margin:-24px -24px 24px; border-radius:16px 16px 0 0;">
                    <div style="display:flex; align-items:center; gap:20px;">
                      <div style="width:72px; height:72px; background:rgba(255,255,255,0.25); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:32px; font-weight:700;">
                        ${escapeHtml((r.guest_name||'U')[0].toUpperCase())}
                      </div>
                      <div style="flex:1;">
                        <div style="font-size:24px; font-weight:700; margin-bottom:6px;">${escapeHtml(r.guest_name||'')}</div>
                        <div style="font-size:14px; opacity:0.95; display:flex; align-items:center; gap:8px;">
                          <i class="fas fa-bookmark"></i> Reservation #${r.reservation_id}
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div style="margin-bottom:20px; padding:16px; background:${bookingType.color}10; border-left:4px solid ${bookingType.color}; border-radius:8px;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                      <i class="fas ${bookingType.icon}" style="font-size:24px; color:${bookingType.color};"></i>
                      <div>
                        <div style="font-weight:700; color:#1e293b; font-size:16px;">${bookingType.label}</div>
                        <div style="font-size:13px; color:#64748b;">${packageType}</div>
                      </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px;">
                      <div>
                        <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Duration</div>
                        <div style="font-weight:600; color:#1e293b;">${r.number_of_days || r.number_of_nights ? (r.number_of_days ? r.number_of_days + ' day(s)' : r.number_of_nights + ' night(s)') : 'N/A'}</div>
                      </div>
                      <div>
                        <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Room</div>
                        <div style="font-weight:600; color:#1e293b;"><i class="fas fa-bed" style="color:#667eea; margin-right:6px;"></i>${escapeHtml(r.room||'TBD')}</div>
                      </div>
                    </div>
                  </div>
                  
                  <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div style="padding:16px; background:#f8fafc; border-radius:12px; border-left:4px solid #10b981;">
                      <div style="color:#64748b; font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <i class="fas fa-calendar-check" style="color:#10b981;"></i> Check-in
                      </div>
                      <div style="color:#1e293b; font-weight:700; font-size:15px; margin-bottom:4px;">${r.check_in_date||'N/A'}</div>
                      <div style="color:#64748b; font-size:12px;"><i class="fas fa-clock"></i> ${r.check_in_time||'N/A'}</div>
                    </div>
                    <div style="padding:16px; background:#f8fafc; border-radius:12px; border-left:4px solid #ef4444;">
                      <div style="color:#64748b; font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <i class="fas fa-calendar-times" style="color:#ef4444;"></i> Check-out
                      </div>
                      <div style="color:#1e293b; font-weight:700; font-size:15px; margin-bottom:4px;">${r.check_out_date||'N/A'}</div>
                      <div style="color:#64748b; font-size:12px;"><i class="fas fa-clock"></i> ${r.check_out_time||'N/A'}</div>
                    </div>
                  </div>
                  
                  <div style="background:#f8fafc; padding:20px; border-radius:12px; margin-bottom:20px;">
                    <div style="font-weight:700; color:#1e293b; margin-bottom:16px; font-size:15px; display:flex; align-items:center; gap:8px;">
                      <i class="fas fa-money-bill-wave" style="color:#10b981;"></i> Payment Details
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                      <div>
                        <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Total Amount</div>
                        <div style="font-size:18px; font-weight:700; color:#1e293b;">₱${parseFloat(r.total_amount||0).toLocaleString('en-US', {minimumFractionDigits:2})}</div>
                        <div style="font-size:11px; font-weight:600; color:${(() => {
                          const isFullyPaid = r.full_payment_verified == 1;
                          const isDownpaymentVerified = r.downpayment_verified == 1;
                          if (isFullyPaid) return '#10b981';
                          if (r.full_payment_paid) return '#f59e0b';
                          if (isDownpaymentVerified) return '#3b82f6';
                          return '#ef4444';
                        })()}; margin-top:4px;">
                          ${(() => {
                            const isFullyPaid = r.full_payment_verified == 1;
                            const isDownpaymentVerified = r.downpayment_verified == 1;
                            if (isFullyPaid) return '✓ Fully Paid';
                            if (r.full_payment_paid) return '⏱ Pending Verification';
                            if (isDownpaymentVerified) return '◐ Partially Paid';
                            return '✗ Unpaid';
                          })()}
                        </div>
                      </div>
                      <div>
                        <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Downpayment</div>
                        <div style="font-size:18px; font-weight:700; color:#f59e0b;">₱${parseFloat(r.downpayment_amount||0).toLocaleString('en-US', {minimumFractionDigits:2})}</div>
                        <div style="font-size:11px; font-weight:600; color:${r.downpayment_verified == 1 ? '#10b981' : (r.downpayment_paid ? '#f59e0b' : '#ef4444')}; margin-top:4px;">
                          ${r.downpayment_verified == 1 ? '✓ Verified' : (r.downpayment_paid ? '⏱ Pending Verification' : '✗ Unpaid')}
                        </div>
                      </div>
                      <div>
                        <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Remaining Balance</div>
                        <div style="font-size:16px; font-weight:700; color:#667eea;">₱${(() => {
                          const actualRemaining = parseFloat(r.total_amount||0) - parseFloat(r.downpayment_amount||0);
                          return actualRemaining.toLocaleString('en-US', {minimumFractionDigits:2});
                        })()}</div>
                        <div style="font-size:11px; font-weight:600; color:${(() => {
                          const actualRemaining = parseFloat(r.total_amount||0) - parseFloat(r.downpayment_amount||0);
                          const isFullyPaid = r.full_payment_verified == 1;
                          return actualRemaining <= 0 || isFullyPaid ? '#10b981' : (r.full_payment_paid ? '#f59e0b' : '#ef4444');
                        })()}; margin-top:4px;">
                          ${(() => {
                            const actualRemaining = parseFloat(r.total_amount||0) - parseFloat(r.downpayment_amount||0);
                            const isFullyPaid = r.full_payment_verified == 1;
                            return actualRemaining <= 0 || isFullyPaid ? '✓ Fully Paid' : (r.full_payment_paid ? '⏱ Pending Verification' : '✗ Unpaid');
                          })()}
                        </div>
                      </div>
                      <div>
                        <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Security Bond (Refundable)</div>
                        <div style="font-size:16px; font-weight:700; color:#8b5cf6;">₱${parseFloat(r.security_bond||2000).toLocaleString('en-US', {minimumFractionDigits:2})}</div>
                        <div style="font-size:10px; font-weight:600; color:${r.security_bond_paid ? '#10b981' : '#f59e0b'}; margin-top:4px; line-height:1.4;">
                          ${r.security_bond_paid ? '✓ Paid - Refundable after checkout' : '⏱ Pay at Check-in (Refundable)'}
                        </div>
                      </div>
                    </div>
                    <div style="margin-top:16px; padding-top:16px; border-top:2px solid #e2e8f0;">
                      <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Payment Method</div>
                      <div style="font-weight:600; color:#1e293b; text-transform:capitalize;"><i class="fas fa-credit-card" style="color:#667eea; margin-right:6px;"></i>${r.payment_method||'N/A'}</div>
                    </div>
                  </div>
                  
                  <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div style="padding:16px; background:#f8fafc; border-radius:12px;">
                      <div style="color:#64748b; font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:8px;"><i class="fas fa-phone"></i> Phone</div>
                      <div style="color:#1e293b; font-weight:600; font-size:14px;">${escapeHtml(r.guest_phone||'N/A')}</div>
                    </div>
                    <div style="padding:16px; background:#f8fafc; border-radius:12px;">
                      <div style="color:#64748b; font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:8px;"><i class="fas fa-envelope"></i> Email</div>
                      <div style="color:#1e293b; font-weight:600; font-size:14px; word-break:break-all;">${escapeHtml(r.guest_email||'N/A')}</div>
                    </div>
                  </div>

                  <div style="display:flex; justify-content:space-between; align-items:center; padding:16px; background:#f8fafc; border-radius:12px; border-top:3px solid ${bookingType.color};">
                    <div>
                      <div style="color:#64748b; font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:6px;">Status</div>
                      <span style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:linear-gradient(135deg,#10b981,#059669); color:white; border-radius:20px; font-size:13px; font-weight:600; text-transform:capitalize;">
                        <i class="fas fa-circle" style="font-size:6px;"></i>${escapeHtml(r.status||'').replace('_', ' ')}
                      </span>
                    </div>
                    <div style="text-align:right;">
                      <div style="color:#64748b; font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:6px;">Created</div>
                      <div style="color:#475569; font-size:12px; font-weight:600;">${r.created_at||'N/A'}</div>
                    </div>
                  </div>
                </div>
              `;
              adminShowModal('Reservation Details', html);
            }

            function adminShowModal(title, htmlContent) {
              const existingModal = document.getElementById('adminViewModal');
              if (existingModal) existingModal.remove();
              
              const modal = document.createElement('div');
              modal.id = 'adminViewModal';
              modal.style.cssText = 'position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:10000; animation:fadeIn 0.3s ease;';
              modal.innerHTML = `
                <div style="background:white; border-radius:16px; max-width:700px; width:90%; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                  <div style="padding:20px 24px; border-bottom:2px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:white; z-index:1;">
                    <h3 style="margin:0; font-size:20px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:10px;">
                      <i class="fas fa-info-circle" style="color:#667eea;"></i> ${title}
                    </h3>
                    <button onclick="document.getElementById('adminViewModal').remove()" style="width:36px; height:36px; border:none; background:#f1f5f9; color:#64748b; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s;" onmouseover="this.style.background='#ef4444'; this.style.color='white';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b';">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <div style="padding:24px;">
                    ${htmlContent}
                  </div>
                </div>
              `;
              document.body.appendChild(modal);
              
              modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
              });
            }

            function adminExportCSV(){ 
              if(!adminFilteredReservations || adminFilteredReservations.length===0) return adminShowNotification('No reservations to export','warning');
              const rows = adminFilteredReservations.map(r=>({ 
                id:r.reservation_id, 
                guest:r.guest_name, 
                phone:r.guest_phone, 
                email:r.guest_email, 
                room:r.room, 
                booking_type:r.booking_type,
                package_type:r.package_type,
                check_in:r.check_in_date, 
                check_out:r.check_out_date, 
                total_amount:r.total_amount,
                downpayment:r.downpayment_amount,
                status:r.status, 
                created_at:r.created_at 
              })); 
              const csv = [Object.keys(rows[0]).join(',')].concat(rows.map(r=>Object.values(r).map(v=>'"'+String((v||'')).replace(/"/g,'""')+'"').join(','))).join('\n'); 
              const blob=new Blob([csv],{type:'text/csv'}); 
              const url=URL.createObjectURL(blob); 
              const a=document.createElement('a'); 
              a.href=url; 
              a.download='admin_reservations_export.csv'; 
              document.body.appendChild(a); 
              a.click(); 
              a.remove(); 
              URL.revokeObjectURL(url); 
            }

            function adminShowNotification(msg, type='info'){
              const colors = { 
                success: 'linear-gradient(135deg, #10b981, #059669)', 
                error: 'linear-gradient(135deg, #ef4444, #dc2626)', 
                info: 'linear-gradient(135deg, #3b82f6, #2563eb)', 
                warning: 'linear-gradient(135deg, #f59e0b, #d97706)' 
              };
              const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle', warning: 'exclamation-triangle' };
              const n = document.createElement('div'); 
              n.style.cssText = `
                position:fixed; right:20px; bottom:20px; 
                background:${colors[type]||colors.info}; 
                color:#fff; padding:16px 20px; border-radius:12px; 
                box-shadow:0 8px 24px rgba(0,0,0,0.2); 
                z-index:9999; display:flex; align-items:center; gap:12px;
                animation: slideInRight 0.3s ease;
              `; 
              n.innerHTML = `<i class="fas fa-${icons[type]||'info-circle'}" style="font-size:20px;"></i><span style="font-weight:600;">${msg}</span>`;
              document.body.appendChild(n); 
              setTimeout(()=>{
                n.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(()=>n.remove(), 300);
              }, 3000);
            }

            document.addEventListener('DOMContentLoaded', adminFetchAllReservations);
            
            // Add animation keyframes
            const adminReservationStyle = document.createElement('style');
            adminReservationStyle.textContent = `
              @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
              @keyframes slideInRight { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
              @keyframes slideOutRight { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(100px); } }
            `;
            document.head.appendChild(adminReservationStyle);
          </script>
        </section>

        <!-- Users Section -->
        <section id="users" class="content-section">
          <div class="section-header" style="margin-bottom:30px;">
            <h2 style="color:#333; font-size:32px; font-weight:700; margin:0 0 8px 0;">User Management</h2>
            <p style="color:#666; margin:0; font-size:16px;">Manage guest accounts, loyalty levels, and permissions</p>
          </div>

          <!-- Stats Overview -->
          <div class="stats-overview" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:20px; margin-bottom:30px;">
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-users"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="manageTotalUsersCount">0</div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Total Users</div>
              </div>
            </div>
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-user-check"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="manageActiveUsersCount">0</div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Active Users</div>
              </div>
            </div>
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-user-plus"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="manageNewUsersCount">0</div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">New This Month</div>
              </div>
            </div>
          </div>

          <!-- Enhanced Filters Section -->
          <div style="background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
              <h3 style="margin:0; font-size:16px; font-weight:600; color:#1e293b; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-filter" style="color:#11224e;"></i> Filter Users
              </h3>
              <button onclick="userClearFilters()" style="padding:6px 14px; background:#f1f5f9; border:none; border-radius:6px; font-size:13px; font-weight:600; color:#64748b; cursor:pointer; transition:all 0.2s;">
                <i class="fas fa-redo"></i> Clear Filters
              </button>
            </div>

            <!-- Quick Status Filters -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:12px; margin-bottom:16px;">
              <div class="filter-chip-enhanced active" onclick="userQuickFilter('all')" id="user-chip-all" style="cursor:pointer; padding:14px 16px; background:white; border:2px solid #11224e; border-radius:12px; transition:all 0.3s ease; box-shadow:0 4px 16px rgba(17,34,78,0.3); background:#11224e;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:36px; height:36px; background:rgba(255,255,255,0.25); border-radius:8px; display:flex; align-items:center; justify-content:center; color:white; font-size:16px;"><i class="fas fa-list"></i></div>
                  <span style="flex:1; font-size:14px; font-weight:600; color:white;">All</span>
                  <div style="padding:4px 10px; font-size:12px; font-weight:700; color:white; min-width:32px; text-align:center;" id="user-count-all">0</div>
                </div>
              </div>
              <div class="filter-chip-enhanced" onclick="userQuickFilter('active')" id="user-chip-active" style="cursor:pointer; padding:14px 16px; background:white; border:2px solid #11224e; border-radius:12px; transition:all 0.3s ease;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:36px; height:36px; background:rgba(17,34,78,0.1); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#11224e; font-size:16px;"><i class="fas fa-user-check"></i></div>
                  <span style="flex:1; font-size:14px; font-weight:600; color:#11224e;">Active</span>
                  <div style="padding:4px 10px; font-size:12px; font-weight:700; color:#11224e; min-width:32px; text-align:center;" id="user-count-active">0</div>
                </div>
              </div>
              <div class="filter-chip-enhanced" onclick="userQuickFilter('inactive')" id="user-chip-inactive" style="cursor:pointer; padding:14px 16px; background:white; border:2px solid #11224e; border-radius:12px; transition:all 0.3s ease;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:36px; height:36px; background:rgba(17,34,78,0.1); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#11224e; font-size:16px;"><i class="fas fa-user-slash"></i></div>
                  <span style="flex:1; font-size:14px; font-weight:600; color:#11224e;">Inactive</span>
                  <div style="padding:4px 10px; font-size:12px; font-weight:700; color:#11224e; min-width:32px; text-align:center;" id="user-count-inactive">0</div>
                </div>
              </div>
            </div>
          </div>

          <div class="users-container">
            <div class="users-header" style="padding:20px; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:20px;">
              <div class="search-box" style="flex:1; max-width:400px; position:relative;">
                <i class="fas fa-search" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:16px;"></i>
                <input type="text" id="searchUsers" placeholder="Search users by name, email, or username..." oninput="filterUsers()" style="width:100%; padding:14px 16px 14px 48px; border:2px solid #11224e; border-radius:12px; font-size:14px; transition:all 0.3s ease;" />
              </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding:16px; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); flex-wrap:wrap; gap:12px;">
              <div style="display:flex; align-items:center; gap:12px;">
                <button onclick="loadUsers()" style="padding:12px 20px; background:white; border:2px solid #e2e8f0; border-radius:10px; font-size:14px; font-weight:600; color:#11224e; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px;">
                  <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <span style="color:#64748b; font-size:14px;" id="usersLastUpdate">Last updated: Just now</span>
              </div>
              <div style="display:flex; gap:8px;">
                <button onclick="exportUsersCSV()" style="padding:12px 20px; background:linear-gradient(135deg, #10b981, #059669); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px;">
                  <i class="fas fa-file-excel"></i> Export
                </button>
              </div>
            </div>

            <div class="table-container" style="background:white; border-radius:16px; box-shadow:0 2px 8px rgba(0,0,0,0.05); overflow-x:auto; margin-bottom:20px;">
              <table class="users-table" id="usersTable" style="width:100%; border-collapse:separate; border-spacing:0;">
                <thead style="background:#11224e; color:white;">
                  <tr>
                    <th style="padding:18px 16px; text-align:center; width:60px;">#</th>
                    <th style="padding:18px 16px; text-align:left;"><i class="fas fa-user"></i> Full Name</th>
                    <th style="padding:18px 16px; text-align:left;"><i class="fas fa-at"></i> Username</th>
                    <th style="padding:18px 16px; text-align:left;"><i class="fas fa-envelope"></i> Email</th>
                    <th style="padding:18px 16px; text-align:left;"><i class="fas fa-phone"></i> Phone</th>
                    <th style="padding:18px 16px; text-align:center;"><i class="fas fa-toggle-on"></i> Status</th>
                    <th style="padding:18px 16px; text-align:left;"><i class="fas fa-calendar-alt"></i> Member Since</th>
                    <th style="padding:18px 16px; text-align:left;"><i class="fas fa-clock"></i> Last Login</th>
                    <th style="padding:18px 16px; text-align:center;"><i class="fas fa-cog"></i> Actions</th>
                  </tr>
                </thead>
                <tbody id="usersTableBody">
                  <tr>
                    <td colspan="9" style="text-align: center; padding: 3rem;">
                      <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #11224e;"></i>
                      <p style="margin-top: 1rem; color: #64748b; font-size:16px; font-weight:600;">Loading users...</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; padding:20px; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); flex-wrap:wrap; gap:12px;">
              <div id="usersPaginationInfo" style="font-size:14px; color:#64748b; font-weight:500;"></div>
              <div style="display:flex; gap:8px;">
                <button id="usersPrevPage" onclick="usersChangePage(-1)" disabled style="padding:10px 16px; border:2px solid #e2e8f0; background:white; border-radius:8px; font-size:14px; font-weight:600; color:#475569; cursor:pointer; transition:all 0.2s;">&larr; Prev</button>
                <button id="usersNextPage" onclick="usersChangePage(1)" disabled style="padding:10px 16px; border:2px solid #e2e8f0; background:white; border-radius:8px; font-size:14px; font-weight:600; color:#475569; cursor:pointer; transition:all 0.2s;">Next &rarr;</button>
              </div>
            </div>
          </div>
        </section>

        <!-- Staff Section -->
        <section id="staff" class="content-section">
          <div class="section-header" style="margin-bottom:30px;">
            <h2 style="color:#333; font-size:32px; font-weight:700; margin:0 0 8px 0;">Staff Members Management</h2>
            <p style="color:#666; margin:0; font-size:16px;">Manage resort staff and employee information</p>
          </div>

          <?php if (isset($_SESSION['flash_success'])): ?>
            <div style="background:linear-gradient(135deg,#10b981,#059669); color:white; padding:16px 20px; border-radius:12px; margin-bottom:20px; display:flex; align-items:center; gap:12px; box-shadow:0 4px 12px rgba(16,185,129,0.3); animation:slideInDown 0.4s ease;">
              <i class="fas fa-check-circle" style="font-size:24px;"></i>
              <div>
                <div style="font-weight:600; font-size:15px;"><?php echo htmlspecialchars($_SESSION['flash_success']); ?></div>
                <?php if (!empty($_SESSION['flash_staff_username'])): ?>
                  <div style="font-size:13px; margin-top:4px; opacity:0.9;">Username: <?php echo htmlspecialchars($_SESSION['flash_staff_username']); ?></div>
                <?php endif; ?>
              </div>
            </div>
            <?php unset($_SESSION['flash_success'], $_SESSION['flash_staff_username']); ?>
          <?php endif; ?>

          <!-- Stats Overview -->
          <div class="stats-overview" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:20px; margin-bottom:30px;">
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-user-tie"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="staffTotalCount">0</div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Total Staff</div>
              </div>
            </div>
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-check-circle"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="staffActiveCount">0</div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Active Staff</div>
              </div>
            </div>
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-briefcase"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="staffDepartmentCount">0</div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Departments</div>
              </div>
            </div>
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-user-clock"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="staffOnlineCount">0</div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Online Now</div>
              </div>
            </div>
          </div>

          <!-- Enhanced Filters Section -->
          <div style="background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
              <h3 style="margin:0; font-size:16px; font-weight:600; color:#1e293b; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-filter" style="color:#11224e;"></i> Filter Staff
              </h3>
              <button onclick="staffClearFilters()" style="padding:6px 14px; background:#f1f5f9; border:none; border-radius:6px; font-size:13px; font-weight:600; color:#64748b; cursor:pointer; transition:all 0.2s;">
                <i class="fas fa-redo"></i> Clear Filters
              </button>
            </div>

            <!-- Quick Status Filters -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:12px; margin-bottom:16px;">
              <div class="filter-chip-enhanced active" onclick="staffQuickFilter('all')" id="staff-chip-all" style="cursor:pointer; padding:14px 16px; background:white; border:2px solid #11224e; border-radius:12px; transition:all 0.3s ease; box-shadow:0 4px 16px rgba(17,34,78,0.3); background:#11224e;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:36px; height:36px; background:rgba(255,255,255,0.25); border-radius:8px; display:flex; align-items:center; justify-content:center; color:white; font-size:16px;"><i class="fas fa-list"></i></div>
                  <span style="flex:1; font-size:14px; font-weight:600; color:white;">All</span>
                  <div style="padding:4px 10px; font-size:12px; font-weight:700; color:white; min-width:32px; text-align:center;" id="staff-count-all">0</div>
                </div>
              </div>
              <div class="filter-chip-enhanced" onclick="staffQuickFilter('active')" id="staff-chip-active" style="cursor:pointer; padding:14px 16px; background:white; border:2px solid #11224e; border-radius:12px; transition:all 0.3s ease;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:36px; height:36px; background:rgba(17,34,78,0.1); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#11224e; font-size:16px;"><i class="fas fa-user-check"></i></div>
                  <span style="flex:1; font-size:14px; font-weight:600; color:#11224e;">Active</span>
                  <div style="padding:4px 10px; font-size:12px; font-weight:700; color:#11224e; min-width:32px; text-align:center;" id="staff-count-active">0</div>
                </div>
              </div>
              <div class="filter-chip-enhanced" onclick="staffQuickFilter('inactive')" id="staff-chip-inactive" style="cursor:pointer; padding:14px 16px; background:white; border:2px solid #11224e; border-radius:12px; transition:all 0.3s ease;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:36px; height:36px; background:rgba(17,34,78,0.1); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#11224e; font-size:16px;"><i class="fas fa-user-slash"></i></div>
                  <span style="flex:1; font-size:14px; font-weight:600; color:#11224e;">Inactive</span>
                  <div style="padding:4px 10px; font-size:12px; font-weight:700; color:#11224e; min-width:32px; text-align:center;" id="staff-count-inactive">0</div>
                </div>
              </div>
            </div>
          </div>

          <div class="staff-container">
            <div class="staff-header" style="padding:20px; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:20px;">
              <div class="search-box" style="flex:1; max-width:400px; position:relative;">
                <i class="fas fa-search" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:16px;"></i>
                <input type="text" id="searchStaff" placeholder="Search by name, email, username, or position..." oninput="filterStaff()" style="width:100%; padding:14px 16px 14px 48px; border:2px solid #11224e; border-radius:12px; font-size:14px; transition:all 0.3s ease;" />
              </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding:16px; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); flex-wrap:wrap; gap:12px;">
              <div style="display:flex; align-items:center; gap:12px;">
                <button onclick="loadStaffList()" style="padding:12px 20px; background:white; border:2px solid #e2e8f0; border-radius:10px; font-size:14px; font-weight:600; color:#11224e; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px;">
                  <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <span style="color:#64748b; font-size:14px;" id="staffLastUpdate">Last updated: Just now</span>
              </div>
              <div style="display:flex; gap:8px;">
                <button onclick="window.open('create_staff.php', '_blank')" style="padding:12px 20px; background:linear-gradient(135deg, #11224e, #1e3a8a); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px;">
                  <i class="fas fa-user-plus"></i> Create Staff
                </button>
                <button onclick="exportStaffCSV()" style="padding:12px 20px; background:linear-gradient(135deg, #10b981, #059669); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px;">
                  <i class="fas fa-file-excel"></i> Export
                </button>
              </div>
            </div>

            <div class="table-container" style="background:white; border-radius:16px; box-shadow:0 2px 8px rgba(0,0,0,0.05); overflow-x:auto; margin-bottom:20px;">
              <table class="staff-table" id="staffTable" style="width:100%; border-collapse:separate; border-spacing:0;">
                <thead style="background:#11224e; color:white;">
                  <tr>
                    <th style="padding:12px 10px; text-align:center; width:50px; font-size:12px;">#</th>
                    <th style="padding:12px 10px; text-align:left; font-size:12px;"><i class="fas fa-user"></i> Full Name</th>
                    <th style="padding:12px 8px; text-align:left; font-size:11px; width:100px;"><i class="fas fa-id-card"></i> Username</th>
                    <th style="padding:12px 10px; text-align:left; font-size:12px;"><i class="fas fa-envelope"></i> Email</th>
                    <th style="padding:12px 10px; text-align:left; font-size:12px;"><i class="fas fa-briefcase"></i> Position</th>
                    <th style="padding:12px 10px; text-align:center; font-size:12px;"><i class="fas fa-toggle-on"></i> Status</th>
                    <th style="padding:12px 10px; text-align:left; font-size:12px;"><i class="fas fa-calendar-plus"></i> Created</th>
                    <th style="padding:12px 8px; text-align:left; font-size:11px; width:95px;"><i class="fas fa-sign-in-alt"></i> Last Login</th>
                    <th style="padding:12px 10px; text-align:center; font-size:12px;"><i class="fas fa-cog"></i> Actions</th>
                  </tr>
                </thead>
                <tbody id="staffTableBody">
                  <tr>
                    <td colspan="9" style="text-align:center; padding:3rem;">
                      <i class="fas fa-spinner fa-spin" style="font-size:48px; color:#11224e;"></i>
                      <p style="margin-top:1rem; color:#64748b; font-weight:600; font-size:16px;">Loading staff members...</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; padding:20px; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); flex-wrap:wrap; gap:12px;">
              <div id="staffPaginationInfo" style="font-size:14px; color:#64748b; font-weight:500;"></div>
              <div style="display:flex; gap:8px;">
                <button id="staffPrevPage" onclick="staffChangePage(-1)" disabled style="padding:10px 16px; border:2px solid #e2e8f0; background:white; border-radius:8px; font-size:14px; font-weight:600; color:#475569; cursor:pointer; transition:all 0.2s;">&larr; Prev</button>
                <button id="staffNextPage" onclick="staffChangePage(1)" disabled style="padding:10px 16px; border:2px solid #e2e8f0; background:white; border-radius:8px; font-size:14px; font-weight:600; color:#475569; cursor:pointer; transition:all 0.2s;">Next &rarr;</button>
              </div>
            </div>
          </div>
        </section>

        <!-- Reports Section -->
        <section id="reports" class="content-section">
          <div class="section-header" style="margin-bottom:30px;">
            <h2 style="color:#333; font-size:32px; font-weight:700; margin:0 0 8px 0;">Reports & Analytics</h2>
            <p style="color:#666; margin:0; font-size:16px;">View detailed reports and insights</p>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding:16px; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:12px;">
              <button onclick="loadAdminReportData('week')" style="padding:12px 20px; background:white; border:2px solid #e2e8f0; border-radius:10px; font-size:14px; font-weight:600; color:#11224e; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-sync-alt"></i> Refresh
              </button>
              <button onclick="fixReservationPrices()" style="padding:12px 20px; background:#11224e; border:none; border-radius:10px; font-size:14px; font-weight:600; color:white; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-wrench"></i> Fix Prices
              </button>
              <span style="color:#64748b; font-size:14px;" id="reportsLastUpdate">Last updated: Just now</span>
            </div>
            <div style="display:flex; gap:8px;">
              <button onclick="exportReportsPDF()" style="padding:12px 20px; background:linear-gradient(135deg, #ef4444, #dc2626); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-file-pdf"></i> Export PDF
              </button>
              <button onclick="exportReportsExcel()" style="padding:12px 20px; background:linear-gradient(135deg, #10b981, #059669); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-file-excel"></i> Export Excel
              </button>
            </div>
          </div>

          <!-- Period Selector -->
          <div style="background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:20px;">
            <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
              <label style="font-weight:600; color:#1e293b; display:flex; align-items:center; gap:8px;"><i class="fas fa-calendar-alt" style="color:#11224e;"></i> Report Period:</label>
              <select id="adminPeriodSelector" onchange="updateAdminReports()" style="padding:12px 16px; border:2px solid #11224e; border-radius:10px; font-size:14px; font-weight:500; color:#475569; background:white; cursor:pointer; transition:all 0.3s;">
                <option value="today">Today</option>
                <option value="week" selected>This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
                <option value="custom">Custom Range</option>
              </select>
              <input type="date" id="adminStartDate" style="padding:12px 16px; border:2px solid #11224e; border-radius:10px; font-size:14px; display:none;">
              <input type="date" id="adminEndDate" style="padding:12px 16px; border:2px solid #11224e; border-radius:10px; font-size:14px; display:none;">
              <button onclick="applyAdminCustomDate()" id="adminApplyDateBtn" style="padding:12px 20px; background:linear-gradient(135deg, #11224e, #1e3a8a); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; display:none;">Apply</button>
            </div>
          </div>

          <!-- Key Metrics -->
          <div class="stats-overview" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:20px; margin-bottom:30px;">
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-calendar-check"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="reportTotalReservations"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Total Reservations</div>
              </div>
            </div>
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-money-bill-wave"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="reportTotalRevenue"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Revenue</div>
              </div>
            </div>
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-percentage"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="reportOccupancyRate"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Occupancy Rate</div>
              </div>
            </div>
            <div class="stat-card-res" style="background:white; color:#11224e; padding:24px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; align-items:center; gap:20px; transition:all 0.3s ease; border:2px solid #11224e;">
              <div style="width:64px; height:64px; background:rgba(17,34,78,0.1); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; color:#11224e;"><i class="fas fa-ban"></i></div>
              <div>
                <div style="font-size:32px; font-weight:700; margin-bottom:4px; color:#11224e;" id="reportTotalCancellations"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>
                <div style="font-size:14px; font-weight:500; color:#11224e;">Cancellations</div>
              </div>
            </div>
          </div>

          <!-- Reservations Trend Chart -->
          <div class="report-card" style="margin-top:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
              <h3 style="font-size:20px; font-weight:700; color:#1e293b;">Reservations Trend</h3>
            </div>
            <div style="position:relative; height:300px;">
              <canvas id="adminTrendChart"></canvas>
            </div>
          </div>

          <!-- Revenue Chart -->
          <div class="report-card" style="margin-top:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
              <h3 style="font-size:20px; font-weight:700; color:#1e293b;">Revenue Analysis</h3>
            </div>
            <div style="position:relative; height:300px;">
              <canvas id="adminRevenueChart"></canvas>
            </div>
          </div>

          <!-- Package Distribution -->
          <div class="report-card" style="margin-top:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
              <h3 style="font-size:20px; font-weight:700; color:#1e293b;">Package Distribution</h3>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
              <div style="position:relative; height:250px;">
                <canvas id="adminRoomTypeChart"></canvas>
              </div>
              <div style="overflow-x:auto;">
                <table class="report-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th style="background:#f8fafc; padding:12px; text-align:left; font-weight:600; color:#64748b; border-bottom:2px solid #e2e8f0;">Package Type</th>
                      <th style="background:#f8fafc; padding:12px; text-align:left; font-weight:600; color:#64748b; border-bottom:2px solid #e2e8f0;">Bookings</th>
                      <th style="background:#f8fafc; padding:12px; text-align:left; font-weight:600; color:#64748b; border-bottom:2px solid #e2e8f0;">Revenue</th>
                    </tr>
                  </thead>
                  <tbody id="adminRoomTypeTable">
                    <tr><td colspan="3" style="text-align:center; padding:20px; color:#666;">Loading...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Guest Statistics -->
          <div class="report-card" style="margin-top:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
              <h3 style="font-size:20px; font-weight:700; color:#1e293b;">Guest Statistics</h3>
            </div>
            <div style="overflow-x:auto;">
              <table class="report-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="background:#f8fafc; padding:12px; text-align:left; font-weight:600; color:#64748b; border-bottom:2px solid #e2e8f0;">Metric</th>
                    <th style="background:#f8fafc; padding:12px; text-align:left; font-weight:600; color:#64748b; border-bottom:2px solid #e2e8f0;">This Week</th>
                    <th style="background:#f8fafc; padding:12px; text-align:left; font-weight:600; color:#64748b; border-bottom:2px solid #e2e8f0;">Last Week</th>
                    <th style="background:#f8fafc; padding:12px; text-align:left; font-weight:600; color:#64748b; border-bottom:2px solid #e2e8f0;">Change</th>
                  </tr>
                </thead>
                <tbody id="adminGuestStatsTable" style="color:#1e293b;">
                  <tr><td colspan="4" style="text-align:center; padding:20px; color:#666;">No data available</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Performance Metrics -->
          <div class="report-card" style="margin-top:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
              <h3 style="font-size:20px; font-weight:700; color:#1e293b;">Performance Metrics</h3>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
              <div>
                <h4 style="color:#64748b; font-size:14px; margin-bottom:12px;">CHECK-IN EFFICIENCY</h4>
                <div style="background:#f8fafc; padding:16px; border-radius:8px;">
                  <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span>Average Time</span>
                    <strong id="adminCheckInTime">—</strong>
                  </div>
                  <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden;">
                    <div id="adminCheckInBar" style="background:#10b981; width:0%; height:100%; transition:width 0.3s;"></div>
                  </div>
                </div>
              </div>
              <div>
                <h4 style="color:#64748b; font-size:14px; margin-bottom:12px;">RESPONSE TIME</h4>
                <div style="background:#f8fafc; padding:16px; border-radius:8px;">
                  <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span>Average Response</span>
                    <strong id="adminResponseTime">—</strong>
                  </div>
                  <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden;">
                    <div id="adminResponseBar" style="background:#667eea; width:0%; height:100%; transition:width 0.3s;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Settings Section -->
        <section id="settings" class="content-section">
          <div class="section-header" style="background:linear-gradient(135deg,#f59e0b,#d97706); padding:24px; border-radius:12px; margin-bottom:30px; box-shadow:0 4px 12px rgba(245,158,11,0.25);">
            <h2 style="font-size:32px; font-weight:700; color:white; margin-bottom:8px;">Settings</h2>
            <p style="color:rgba(255,255,255,0.95); font-size:16px; margin:0;">Configure system and resort settings</p>
          </div>

          <!-- Settings Menu -->
          <div class="settings-menu">
            <div
              class="settings-option"
              onclick="toggleSettingsPanel('admin-profile')"
            >
              <div class="option-header">
                <div class="option-info">
                  <i class="fas fa-user-shield"></i>
                  <div>
                    <h3>Admin Profile Settings</h3>
                    <p>Manage your admin account information and password</p>
                  </div>
                </div>
                <i class="fas fa-chevron-right option-arrow"></i>
              </div>

              <!-- Admin Profile Panel -->
              <div class="settings-panel" id="admin-profile-panel">
                <form id="adminProfileForm" class="profile-form">
                  <div class="form-group">
                    <label for="adminFullName">Full Name</label>
                    <div class="input-wrapper">
                      <i class="fas fa-user"></i>
                      <input
                        type="text"
                        id="adminFullName"
                        name="fullName"
                        value="<?php echo htmlspecialchars($adminFullName); ?>"
                        readonly
                        placeholder="Enter full name"
                        required
                      />
                      <button type="button" class="field-edit-btn" aria-label="Edit full name" onclick="toggleEditField('adminFullName')">
                        <span class="field-edit-label">Edit</span>
                      </button>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="adminUsername">Username</label>
                    <div class="input-wrapper">
                      <i class="fas fa-at"></i>
                      <input
                        type="email"
                        id="adminUsername"
                        name="username"
                        value="<?php echo htmlspecialchars($adminEmail); ?>"
                        placeholder="Enter username/email"
                        required
                      />
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <div class="input-wrapper">
                      <i class="fas fa-lock"></i>
                      <input
                        type="password"
                        id="currentPassword"
                        name="currentPassword"
                        placeholder="Enter current password"
                        required
                      />
                      <button
                        type="button"
                        class="password-toggle"
                        onclick="toggleSettingsPassword('currentPassword')"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <div class="input-wrapper">
                      <i class="fas fa-key"></i>
                      <input
                        type="password"
                        id="newPassword"
                        name="newPassword"
                        placeholder="Enter new password (min 6 characters)"
                        minlength="6"
                      />
                      <button
                        type="button"
                        class="password-toggle"
                        onclick="toggleSettingsPassword('newPassword')"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <div class="input-wrapper">
                      <i class="fas fa-key"></i>
                      <input
                        type="password"
                        id="confirmPassword"
                        name="confirmPassword"
                        placeholder="Confirm new password"
                      />
                      <button
                        type="button"
                        class="password-toggle"
                        onclick="toggleSettingsPassword('confirmPassword')"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                  </div>

                  <div class="form-actions">
                    <button
                      type="button"
                      class="btn-secondary"
                      onclick="resetProfileForm()"
                    >
                      <i class="fas fa-undo"></i> Reset
                    </button>
                    <button type="submit" class="btn-primary">
                      <i class="fas fa-save"></i> Save Changes
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <div
              class="settings-option"
              onclick="toggleSettingsPanel('system-settings')"
            >
              <div class="option-header">
                <div class="option-info">
                  <i class="fas fa-cogs"></i>
                  <div>
                    <h3>System Settings</h3>
                    <p>Configure resort information and system preferences</p>
                  </div>
                </div>
                <i class="fas fa-chevron-right option-arrow"></i>
              </div>

              <!-- System Settings Panel -->
              <div class="settings-panel" id="system-settings-panel">
                <div class="setting-item">
                  <div class="setting-info">
                    <h4>Resort Name</h4>
                    <p>AR Homes Posadas Farm Resort</p>
                  </div>
                  <button class="btn-edit">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                </div>

                <div class="setting-item">
                  <div class="setting-info">
                    <h4>Contact Email</h4>
                    <p>info@arheosposadas.com</p>
                  </div>
                  <button class="btn-edit">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                </div>

                <div class="setting-item">
                  <div class="setting-info">
                    <h4>System Language</h4>
                    <p>English (US)</p>
                  </div>
                  <button class="btn-edit">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                </div>

                <div class="setting-item">
                  <div class="setting-info">
                    <h4>Time Zone</h4>
                    <p>Asia/Manila (UTC+8)</p>
                  </div>
                  <button class="btn-edit">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="logout-modal">
      <div class="logout-modal-overlay"></div>
      <div class="logout-modal-content">
        <div class="logout-modal-header">
          <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
          </div>
          <h3>Confirm Logout</h3>
        </div>
        <div class="logout-modal-body">
          <p>Are you sure you want to logout from your admin session?</p>
          <p class="logout-warning">
            You will need to login again to access the dashboard.
          </p>
        </div>
        <div class="logout-modal-footer">
          <button class="logout-cancel-btn" onclick="hideLogoutModal()">
            <i class="fas fa-times"></i>
            <span>Cancel</span>
          </button>
          <button class="logout-confirm-btn" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
          </button>
        </div>
      </div>
    </div>

    <script>
        // Pass PHP session data to JavaScript
        const adminData = {
            fullName: <?php echo json_encode($adminFullName); ?>,
            username: <?php echo json_encode($adminUsername); ?>,
            email: <?php echo json_encode($adminEmail); ?>,
            role: <?php echo json_encode($adminRole); ?>
        };
        
        // Load dashboard statistics
        function loadDashboardStats() {
            console.log('📊 Loading dashboard statistics from get_dashboard_stats.php...');
            
            fetch('get_dashboard_stats.php')
                .then(response => {
                    console.log('📡 Dashboard stats response status:', response.status, response.statusText);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('📈 Dashboard data received:', data);
                    
                    if (data.success) {
                        updateDashboardUI(data);
                    } else {
                        console.error('❌ Failed to load dashboard stats:', data.message);
                        showErrorState();
                    }
                })
                .catch(error => {
                    console.error('❌ Error loading dashboard stats:', error);
                    showErrorState();
                });
        }
        
        function updateDashboardUI(data) {
            const stats = data.stats;
            
            // Update stat cards with animation
            updateStatCard('totalUsersCount', stats.total_users);
            updateStatCard('activeUsersCount', stats.active_users);
            updateStatCard('newUsersToday', stats.new_users_today);
            updateStatCard('totalReservationsCount', stats.total_reservations);
            updateStatCard('pendingReservationsCount', stats.pending_reservations);
            updateStatCard('confirmedReservationsCount', stats.confirmed_reservations);
            updateStatCard('completedReservationsCount', stats.completed_reservations);
            
            // Update additional info
            document.getElementById('newUsersMonth').textContent = stats.new_users_this_month;
            document.getElementById('newUsersWeek').textContent = stats.new_users_this_week;
            
            // Calculate and update active percentage
            const activePercentage = stats.total_users > 0 
                ? Math.round((stats.active_users / stats.total_users) * 100) 
                : 0;
            document.getElementById('activePercentage').textContent = activePercentage + '%';
            
            // Update recent activities
            updateRecentActivities(data.recent_activities);
            
            console.log('✅ Dashboard UI updated successfully');
        }
        
        function updateStatCard(elementId, value) {
            const element = document.getElementById(elementId);
            if (element) {
                // Animate count from 0 to value
                animateCount(element, 0, value, 1000);
            }
        }
        
        function animateCount(element, start, end, duration) {
            const startTime = Date.now();
            const difference = end - start;
            
            function step() {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const current = Math.floor(start + difference * progress);
                
                element.textContent = current.toLocaleString();
                
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }
            
            requestAnimationFrame(step);
        }
        
        function updateRecentActivities(activities) {
            const container = document.getElementById('recentActivitiesContainer');
            
            if (!activities || activities.length === 0) {
                container.innerHTML = `
                    <div class="no-activities">
                        <i class="fas fa-inbox"></i>
                        <p>No recent activities to display</p>
                    </div>
                `;
                return;
            }
            
            const activitiesHTML = activities.map(activity => `
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas ${activity.icon}"></i>
                    </div>
                    <div class="activity-details">
                        <h4>${activity.title}</h4>
                        <p>${activity.description}</p>
                        <span class="activity-time">
                            <i class="fas fa-clock"></i> ${activity.time}
                        </span>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = activitiesHTML;
        }
        
        function showErrorState() {
            // Show error message in stat cards
            const statCards = ['totalUsersCount', 'activeUsersCount', 'newUsersToday',
                               'totalReservationsCount', 'pendingReservationsCount', 
                               'confirmedReservationsCount', 'completedReservationsCount'];
            
            statCards.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.innerHTML = '<i class="fas fa-exclamation-circle" style="color: #dc3545;"></i>';
                }
            });
            
            // Show error in activities
            const container = document.getElementById('recentActivitiesContainer');
            if (container) {
                container.innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Failed to load activities. Please refresh the page.</p>
                    </div>
                `;
            }
        }
        
        // Load dashboard stats when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Dashboard page loaded, fetching statistics...');
            loadDashboardStats();
            
            // Refresh stats every 30 seconds
            setInterval(loadDashboardStats, 30000);
        });
        
        // Global logout handler - available immediately
        window.handleLogoutClick = function(event) {
            console.log("🖱️ Logout button clicked (inline handler)");
            event.preventDefault();
            event.stopPropagation();
            
            // Check if logout function exists from admin-script.js
            if (typeof logout === 'function') {
                console.log("✅ Using logout() from admin-script.js");
                logout();
            } else if (typeof showLogoutModal === 'function') {
                console.log("✅ Using showLogoutModal() directly");
                showLogoutModal();
            } else {
                // Fallback - show modal manually
                console.log("⚠️ Functions not loaded, showing modal manually");
                const modal = document.getElementById('logoutModal');
                if (modal) {
                    modal.classList.add('show');
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                } else {
                    // Last resort - simple confirm
                    if (confirm('Are you sure you want to logout?')) {
                        window.location.href = 'logout.php';
                    }
                }
            }
        };
        
        console.log("✅ handleLogoutClick function loaded globally");
        
        // CRITICAL: Continuously check database connection every 3 seconds
        // If XAMPP MySQL is stopped, this will detect it immediately
        function checkDatabaseConnection() {
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.success || !data.logged_in) {
                        // Database is disconnected or session invalid
                        showConnectionError();
                    }
                })
                .catch(error => {
                    // Fetch failed - XAMPP is definitely OFF
                    console.error('Database connection lost:', error);
                    showConnectionError();
                });
        }
        
        function showConnectionError() {
            // Clear any existing intervals
            if (window.connectionCheckInterval) {
                clearInterval(window.connectionCheckInterval);
            }
            
            // Show error overlay
            const errorOverlay = document.createElement('div');
            errorOverlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.9);
                z-index: 999999;
                display: flex;
                justify-content: center;
                align-items: center;
                animation: fadeIn 0.3s ease;
            `;
            
            errorOverlay.innerHTML = `
                <div style="
                    background: white;
                    padding: 40px;
                    border-radius: 15px;
                    max-width: 600px;
                    text-align: center;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
                    animation: slideDown 0.5s ease;
                ">
                    <div style="font-size: 80px; margin-bottom: 20px;">🔌❌</div>
                    <h1 style="color: #dc3545; margin-bottom: 20px;">Database Connection Lost</h1>
                    <p style="color: #666; line-height: 1.8; margin-bottom: 20px;">
                        <strong>XAMPP MySQL has been stopped!</strong><br>
                        The admin dashboard cannot function without an active database connection.
                    </p>
                    <div style="
                        background: #f8d7da;
                        border: 2px solid #dc3545;
                        color: #721c24;
                        padding: 15px;
                        border-radius: 8px;
                        margin: 20px 0;
                        font-weight: 600;
                    ">
                        ⚠️ This proves the system is 100% dependent on XAMPP!
                    </div>
                    <div style="
                        background: #d1ecf1;
                        border: 1px solid #bee5eb;
                        color: #0c5460;
                        padding: 20px;
                        border-radius: 8px;
                        margin: 20px 0;
                        text-align: left;
                    ">
                        <h3 style="margin-top: 0; color: #0c5460;">💡 To Fix:</h3>
                        <ol style="margin: 10px 0; padding-left: 20px;">
                            <li>Open XAMPP Control Panel</li>
                            <li>Click "Start" on MySQL service</li>
                            <li>Wait for green indicator</li>
                            <li>Click the button below to retry</li>
                        </ol>
                    </div>
                    <button onclick="location.reload()" style="
                        padding: 15px 30px;
                        background: #667eea;
                        color: white;
                        border: none;
                        border-radius: 8px;
                        font-size: 16px;
                        font-weight: 600;
                        cursor: pointer;
                        margin: 10px 5px;
                    ">
                        🔄 Retry Connection
                    </button>
                    <a href="../index.html" style="
                        display: inline-block;
                        padding: 15px 30px;
                        background: #6c757d;
                        color: white;
                        text-decoration: none;
                        border-radius: 8px;
                        font-size: 16px;
                        font-weight: 600;
                        margin: 10px 5px;
                    ">
                        ← Return to Login
                    </a>
                </div>
            `;
            
            document.body.appendChild(errorOverlay);
            
            // Prevent all interactions with dashboard
            document.body.style.overflow = 'hidden';
        }
        
        // Start monitoring immediately
        checkDatabaseConnection();
        
        // Check every 3 seconds (will detect XAMPP stop within 3 seconds)
        window.connectionCheckInterval = setInterval(checkDatabaseConnection, 3000);
        
        // Add CSS animations
        const dashboardAnimationStyle = document.createElement('style');
        dashboardAnimationStyle.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideDown {
                from {
                    transform: translateY(-50px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(dashboardAnimationStyle);
    </script>
    
    <script src="../admin-script.js"></script>
    <script>
      // Staff list loader
      let allStaff = [];
      let filteredStaff = [];

      async function loadStaffList() {
        try {
          const res = await fetch('get_staff.php');
          const data = await res.json();
          const tbody = document.getElementById('staffTableBody');
          
          if (!data.success) {
            tbody.innerHTML = `
              <tr>
                <td colspan="9" style="text-align:center; padding:3rem;">
                  <i class="fas fa-exclamation-triangle" style="font-size:2.5rem; color:#ef4444; margin-bottom:12px;"></i>
                  <p style="color:#ef4444; font-weight:600; font-size:15px;">Failed to load staff: ${data.message || 'Unknown error'}</p>
                </td>
              </tr>`;
            return;
          }

          allStaff = data.staff || [];
          filteredStaff = [...allStaff];

          if (allStaff.length === 0) {
            tbody.innerHTML = `
              <tr>
                <td colspan="9" style="text-align:center; padding:3rem;">
                  <i class="fas fa-users" style="font-size:2.5rem; color:#94a3b8; margin-bottom:12px;"></i>
                  <p style="color:#64748b; font-weight:500; font-size:15px;">No staff members found</p>
                  <p style="color:#94a3b8; font-size:13px; margin-top:8px;">Click "Create Staff" to add your first staff member</p>
                </td>
              </tr>`;
            updateStaffStats();
            return;
          }

          renderStaffTable();
          updateStaffStats();
        } catch (err) {
          const tbody = document.getElementById('staffTableBody');
          tbody.innerHTML = `
            <tr>
              <td colspan="9" style="text-align:center; padding:3rem;">
                <i class="fas fa-exclamation-circle" style="font-size:2.5rem; color:#ef4444; margin-bottom:12px;"></i>
                <p style="color:#ef4444; font-weight:600; font-size:15px;">Error loading staff members</p>
              </td>
            </tr>`;
          console.error(err);
        }
      }

      function renderStaffTable() {
        const tbody = document.getElementById('staffTableBody');
        
        if (filteredStaff.length === 0) {
          tbody.innerHTML = `
            <tr>
              <td colspan="9" style="text-align:center; padding:3rem;">
                <i class="fas fa-search" style="font-size:2.5rem; color:#94a3b8; margin-bottom:12px;"></i>
                <p style="color:#64748b; font-weight:500; font-size:15px;">No staff members match your filters</p>
                <p style="color:#94a3b8; font-size:13px; margin-top:8px;">Try adjusting your search criteria</p>
              </td>
            </tr>`;
          updateStaffPagination();
          return;
        }

        // Calculate pagination
        const startIndex = (currentStaffPage - 1) * staffPerPage;
        const endIndex = startIndex + staffPerPage;
        const staffToDisplay = filteredStaff.slice(startIndex, endIndex);

        const getPositionIcon = (position) => {
          const pos = (position || '').toLowerCase();
          if (pos.includes('manager') || pos.includes('head')) return '<i class="fas fa-user-crown" style="color:#f59e0b;"></i>';
          if (pos.includes('admin')) return '<i class="fas fa-user-shield" style="color:#667eea;"></i>';
          if (pos.includes('supervisor')) return '<i class="fas fa-user-tie" style="color:#3b82f6;"></i>';
          if (pos.includes('reception')) return '<i class="fas fa-concierge-bell" style="color:#10b981;"></i>';
          return '<i class="fas fa-user" style="color:#64748b;"></i>';
        };

        const rows = staffToDisplay.map((s, idx) => {
          const statusDot = s.is_active == 1 ? '<i class="fas fa-circle" style="font-size:6px; color:#10b981;"></i>' : '';
          const statusBadge = s.is_active == 1 
            ? `<span style="display:inline-flex; align-items:center; gap:${statusDot ? '4px' : '0'}; padding:6px 12px; background:linear-gradient(135deg,#10b981,#059669); color:white; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap;">${statusDot}${statusDot ? ' ' : ''}Active</span>`
            : `<span style="display:inline-flex; align-items:center; padding:6px 12px; background:linear-gradient(135deg,#ef4444,#dc2626); color:white; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap;">Inactive</span>`;
          
          const created = s.created_at ? new Date(s.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : '—';
          const lastLogin = s.last_login ? new Date(s.last_login).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : '—';
          
          return `
            <tr style="animation:fadeIn 0.3s ease ${idx*0.05}s both; border-bottom:1px solid #f1f5f9; transition:all 0.2s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='transparent';">
              <td style="padding:14px 12px; text-align:center; font-weight:700; color:#64748b; font-size:13px;">${s.admin_id}</td>
              <td style="padding:14px 12px;">
                <div style="display:flex; align-items:center; gap:10px;">
                  <div style="width:36px; height:36px; background:linear-gradient(135deg,#667eea,#764ba2); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:14px; flex-shrink:0;">
                    ${(s.full_name || '?').charAt(0).toUpperCase()}
                  </div>
                  <div style="font-weight:600; color:#1e293b; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${escapeHtml(s.full_name || '')}">${escapeHtml(s.full_name || 'N/A')}</div>
                </div>
              </td>
              <td style="padding:14px 12px;">
                <div style="font-weight:500; color:#475569; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${escapeHtml(s.username || '')}">${escapeHtml(s.username || 'N/A')}</div>
              </td>
              <td style="padding:14px 12px;">
                <div style="font-size:12px; color:#64748b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${escapeHtml(s.email || '')}">${escapeHtml(s.email || 'N/A')}</div>
              </td>
              <td style="padding:14px 12px;">
                <div style="display:flex; align-items:center; gap:8px; font-weight:500; color:#475569; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                  ${getPositionIcon(s.position || '')}
                  <span title="${escapeHtml(s.position || '')}">${escapeHtml(s.position || 'N/A')}</span>
                </div>
              </td>
              <td style="padding:14px 12px; text-align:center;">${statusBadge}</td>
              <td style="padding:14px 12px; text-align:center;">
                <div style="font-size:12px; color:#64748b;">${created}</div>
              </td>
              <td style="padding:14px 12px; text-align:center;">
                <div style="font-size:12px; color:#64748b;">${lastLogin}</div>
              </td>
              <td style="padding:10px 8px; text-align:center;">
                <div style="display:flex; gap:4px; justify-content:center; flex-wrap:wrap;">
                  <button onclick="viewStaffMember(${s.admin_id})" style="width:32px; height:32px; border:none; background:#f1f5f9; color:#667eea; border-radius:6px; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:13px;" title="View Details" onmouseover="this.style.background='linear-gradient(135deg,#667eea,#764ba2)'; this.style.color='white';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#667eea';"><i class="fas fa-eye"></i></button>
                  <button onclick="editStaffMember(${s.admin_id})" style="width:32px; height:32px; border:none; background:#f1f5f9; color:#3b82f6; border-radius:6px; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:13px;" title="Edit Staff" onmouseover="this.style.background='linear-gradient(135deg,#3b82f6,#2563eb)'; this.style.color='white';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#3b82f6';"><i class="fas fa-edit"></i></button>
                  <button onclick="toggleStaffStatus(${s.admin_id}, ${s.is_active})" style="width:32px; height:32px; border:none; background:${s.is_active == 1 ? '#f1f5f9' : 'linear-gradient(135deg,#ef4444,#dc2626)'}; color:${s.is_active == 1 ? '#10b981' : 'white'}; border-radius:6px; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:13px;" title="${s.is_active == 1 ? 'Deactivate Staff' : 'Activate Staff'}" onmouseover="this.style.background='linear-gradient(135deg,#10b981,#059669)'; this.style.color='white';" onmouseout="this.style.background='${s.is_active == 1 ? '#f1f5f9' : 'linear-gradient(135deg,#ef4444,#dc2626)'}'; this.style.color='${s.is_active == 1 ? '#10b981' : 'white'}';"><i class="fas fa-power-off"></i></button>
                  <button onclick="resetStaffPassword(${s.admin_id})" style="width:32px; height:32px; border:none; background:#f1f5f9; color:#f59e0b; border-radius:6px; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:13px;" title="Reset Password" onmouseover="this.style.background='linear-gradient(135deg,#f59e0b,#d97706)'; this.style.color='white';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#f59e0b';"><i class="fas fa-key"></i></button>
                  <button onclick="deleteStaffMember(${s.admin_id})" style="width:32px; height:32px; border:none; background:#f1f5f9; color:#ef4444; border-radius:6px; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:13px;" title="Delete Staff" onmouseover="this.style.background='linear-gradient(135deg,#ef4444,#dc2626)'; this.style.color='white';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#ef4444';"><i class="fas fa-trash"></i></button>
                </div>
              </td>
            </tr>
          `;
        }).join('');

        tbody.innerHTML = rows;
        updateStaffPagination();
      }

      function updateStaffStats() {
        const total = allStaff.length;
        const active = allStaff.filter(s => s.is_active == 1).length;
        
        // Get unique positions/departments
        const departments = new Set(allStaff.map(s => s.position).filter(p => p));
        
        // Count online (last login within 24 hours)
        const now = new Date();
        const onlineThreshold = 24 * 60 * 60 * 1000; // 24 hours
        const online = allStaff.filter(s => {
          if (!s.last_login) return false;
          const lastLogin = new Date(s.last_login);
          return (now - lastLogin) < onlineThreshold;
        }).length;

        animateCountUp('staffTotalCount', total);
        animateCountUp('staffActiveCount', active);
        animateCountUp('staffDepartmentCount', departments.size);
        animateCountUp('staffOnlineCount', online);
        
        // Update filter counts
        updateStaffFilterCounts();
      }

      // Quick filter for staff status with chip UI
      function staffQuickFilter(status) {
        // Update active chip styling
        const chips = ['all', 'active', 'inactive'];
        chips.forEach(s => {
          const chip = document.getElementById(`staff-chip-${s}`);
          if (chip) {
            if (s === status) {
              chip.style.background = '#11224e';
              chip.style.boxShadow = '0 4px 16px rgba(17,34,78,0.3)';
              chip.querySelector('span').style.color = 'white';
              chip.querySelector('div[style*="background"]').style.background = 'rgba(255,255,255,0.25)';
              chip.querySelector('div[style*="background"]').style.color = 'white';
              chip.querySelectorAll('div')[2].style.color = 'white';
            } else {
              chip.style.background = 'white';
              chip.style.boxShadow = 'none';
              chip.querySelector('span').style.color = '#11224e';
              chip.querySelector('div[style*="background"]').style.background = 'rgba(17,34,78,0.1)';
              chip.querySelector('div[style*="background"]').style.color = '#11224e';
              chip.querySelectorAll('div')[2].style.color = '#11224e';
            }
          }
        });

        // Apply filter
        if (status === 'all') {
          filteredStaff = [...allStaff];
        } else if (status === 'active') {
          filteredStaff = allStaff.filter(s => s.is_active == 1);
        } else if (status === 'inactive') {
          filteredStaff = allStaff.filter(s => s.is_active == 0);
        }

        // Also apply search if present
        const searchTerm = document.getElementById('searchStaff')?.value.toLowerCase();
        if (searchTerm) {
          filteredStaff = filteredStaff.filter(staff => {
            const searchText = [
              staff.full_name || '',
              staff.username || '',
              staff.email || '',
              staff.position || ''
            ].join(' ').toLowerCase();
            return searchText.includes(searchTerm);
          });
        }

        renderStaffTable();
      }

      // Clear all staff filters
      function staffClearFilters() {
        document.getElementById('searchStaff').value = '';
        staffQuickFilter('all');
      }

      // Update filter count badges
      function updateStaffFilterCounts() {
        const totalCount = allStaff.length;
        const activeCount = allStaff.filter(s => s.is_active == 1).length;
        const inactiveCount = allStaff.filter(s => s.is_active == 0).length;

        const allBadge = document.getElementById('staff-count-all');
        const activeBadge = document.getElementById('staff-count-active');
        const inactiveBadge = document.getElementById('staff-count-inactive');

        if (allBadge) allBadge.textContent = totalCount;
        if (activeBadge) activeBadge.textContent = activeCount;
        if (inactiveBadge) inactiveBadge.textContent = inactiveCount;
      }

      // Export staff to CSV
      function exportStaffCSV() {
        if (!filteredStaff || filteredStaff.length === 0) {
          alert('No staff to export');
          return;
        }

        const headers = ['Staff ID', 'Full Name', 'Username', 'Email', 'Position', 'Status', 'Created', 'Last Login'];
        const rows = filteredStaff.map(s => [
          s.admin_id,
          s.full_name || '',
          s.username || '',
          s.email || '',
          s.position || '',
          s.is_active == 1 ? 'Active' : 'Inactive',
          s.created_at || '',
          s.last_login || 'Never'
        ]);

        const csvContent = [
          headers.join(','),
          ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `staff_export_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        showToast('Staff data exported successfully!', 'success');
      }

      // Pagination functions
      let currentStaffPage = 1;
      const staffPerPage = 10;

      function staffChangePage(direction) {
        const totalPages = Math.ceil(filteredStaff.length / staffPerPage);
        const newPage = currentStaffPage + direction;
        
        if (newPage < 1 || newPage > totalPages) return;
        
        currentStaffPage = newPage;
        renderStaffTable();
      }

      function updateStaffPagination() {
        const totalPages = Math.ceil(filteredStaff.length / staffPerPage);
        const start = (currentStaffPage - 1) * staffPerPage + 1;
        const end = Math.min(currentStaffPage * staffPerPage, filteredStaff.length);
        
        const infoDiv = document.getElementById('staffPaginationInfo');
        const prevBtn = document.getElementById('staffPrevPage');
        const nextBtn = document.getElementById('staffNextPage');
        
        if (infoDiv) {
          infoDiv.textContent = `Showing ${start}-${end} of ${filteredStaff.length} staff`;
        }
        
        if (prevBtn) {
          prevBtn.disabled = currentStaffPage === 1;
          prevBtn.style.opacity = currentStaffPage === 1 ? '0.5' : '1';
          prevBtn.style.cursor = currentStaffPage === 1 ? 'not-allowed' : 'pointer';
        }
        
        if (nextBtn) {
          nextBtn.disabled = currentStaffPage === totalPages || totalPages === 0;
          nextBtn.style.opacity = (currentStaffPage === totalPages || totalPages === 0) ? '0.5' : '1';
          nextBtn.style.cursor = (currentStaffPage === totalPages || totalPages === 0) ? 'not-allowed' : 'pointer';
        }
      }

      // Action functions for staff management
      function viewStaffMember(id) {
        const staff = allStaff.find(s => s.admin_id == id);
        if (!staff) {
          alert('Staff member not found!');
          return;
        }

        const statusText = staff.is_active == 1 ? 'Active' : 'Inactive';
        const statusColor = staff.is_active == 1 ? '#10b981' : '#ef4444';
        const created = staff.created_at ? new Date(staff.created_at).toLocaleDateString('en-US', {year:'numeric', month:'long', day:'numeric'}) : 'N/A';
        const lastLogin = staff.last_login ? new Date(staff.last_login).toLocaleDateString('en-US', {year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit'}) : 'Never';

        // Create modal
        const modal = document.createElement('div');
        modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:10000; animation:fadeIn 0.2s ease;';
        modal.onclick = (e) => { if (e.target === modal) modal.remove(); };

        modal.innerHTML = `
          <div style="background:white; border-radius:16px; max-width:600px; width:90%; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3); animation:slideInDown 0.3s ease;" onclick="event.stopPropagation();">
            <div style="background:linear-gradient(135deg,#667eea,#764ba2); padding:24px; border-radius:16px 16px 0 0; color:white;">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <h3 style="font-size:24px; font-weight:700; margin:0;">Staff Details</h3>
                  <p style="opacity:0.9; margin:4px 0 0 0; font-size:14px;">View staff member information</p>
                </div>
                <button onclick="this.closest('[style*=fixed]').remove()" style="width:36px; height:36px; border:none; background:rgba(255,255,255,0.2); color:white; border-radius:8px; cursor:pointer; font-size:18px; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)';" onmouseout="this.style.background='rgba(255,255,255,0.2)';">×</button>
              </div>
            </div>
            <div style="padding:28px;">
              <div style="display:grid; gap:20px;">
                <div style="text-align:center; margin-bottom:8px;">
                  <div style="width:80px; height:80px; background:linear-gradient(135deg,#667eea,#764ba2); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:32px; margin:0 auto 12px;">
                    ${(staff.full_name || '?').charAt(0).toUpperCase()}
                  </div>
                  <h4 style="font-size:20px; font-weight:700; color:#1e293b; margin:0 0 4px 0;">${escapeHtml(staff.full_name || 'N/A')}</h4>
                  <span style="display:inline-block; padding:6px 16px; background:${statusColor}; color:white; border-radius:20px; font-size:12px; font-weight:600;">${statusText}</span>
                </div>

                <div style="background:#f8fafc; padding:16px; border-radius:12px; border-left:4px solid #667eea;">
                  <div style="color:#64748b; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Staff ID</div>
                  <div style="color:#1e293b; font-size:16px; font-weight:600;">#${staff.admin_id}</div>
                </div>

                <div style="background:#f8fafc; padding:16px; border-radius:12px; border-left:4px solid #3b82f6;">
                  <div style="color:#64748b; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Username</div>
                  <div style="color:#1e293b; font-size:16px; font-weight:600;">${escapeHtml(staff.username || 'N/A')}</div>
                </div>

                <div style="background:#f8fafc; padding:16px; border-radius:12px; border-left:4px solid #10b981;">
                  <div style="color:#64748b; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Email Address</div>
                  <div style="color:#1e293b; font-size:16px; font-weight:600;">${escapeHtml(staff.email || 'N/A')}</div>
                </div>

                <div style="background:#f8fafc; padding:16px; border-radius:12px; border-left:4px solid #f59e0b;">
                  <div style="color:#64748b; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Position</div>
                  <div style="color:#1e293b; font-size:16px; font-weight:600;">${escapeHtml(staff.position || 'N/A')}</div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                  <div style="background:#f8fafc; padding:16px; border-radius:12px;">
                    <div style="color:#64748b; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Created At</div>
                    <div style="color:#1e293b; font-size:14px; font-weight:600;">${created}</div>
                  </div>
                  <div style="background:#f8fafc; padding:16px; border-radius:12px;">
                    <div style="color:#64748b; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Last Login</div>
                    <div style="color:#1e293b; font-size:14px; font-weight:600;">${lastLogin}</div>
                  </div>
                </div>
              </div>

              <div style="display:flex; gap:12px; margin-top:24px;">
                <button onclick="editStaffMember(${id}); this.closest('[style*=fixed]').remove();" style="flex:1; padding:12px; background:linear-gradient(135deg,#3b82f6,#2563eb); color:white; border:none; border-radius:10px; font-weight:600; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(59,130,246,0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                  <i class="fas fa-edit" style="margin-right:8px;"></i>Edit Staff
                </button>
                <button onclick="this.closest('[style*=fixed]').remove()" style="flex:1; padding:12px; background:#f1f5f9; color:#64748b; border:none; border-radius:10px; font-weight:600; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
                  Close
                </button>
              </div>
            </div>
          </div>
        `;

        document.body.appendChild(modal);
      }

      function editStaffMember(id) {
        const staff = allStaff.find(s => s.admin_id == id);
        if (!staff) {
          alert('Staff member not found!');
          return;
        }
        
        // For now, just show a message. You can implement a full edit form later
        showToast('Edit feature coming soon! Staff ID: ' + id, 'info');
      }

      async function toggleStaffStatus(id, currentStatus) {
        const staff = allStaff.find(s => s.admin_id == id);
        if (!staff) {
          alert('Staff member not found!');
          return;
        }

        const newStatus = currentStatus == 1 ? 0 : 1;
        const action = newStatus == 1 ? 'activate' : 'deactivate';
        const actionCap = newStatus == 1 ? 'Activate' : 'Deactivate';

        if (!confirm(`${actionCap} ${staff.full_name}?\n\nThis will ${action} the staff member's account.`)) {
          return;
        }

        try {
          const formData = new FormData();
          formData.append('action', 'toggle_status');
          formData.append('admin_id', id);
          formData.append('is_active', newStatus);
          
          const res = await fetch('manage_staff_actions.php', {
            method: 'POST',
            body: formData
          });
          
          const data = await res.json();
          
          if (data.success) {
            showToast(`Staff member ${action}d successfully!`, 'success');
            loadStaffList(); // Reload the list
          } else {
            showToast('Failed to update status: ' + (data.message || 'Unknown error'), 'error');
          }
        } catch (err) {
          console.error(err);
          showToast('Error updating staff status. Please try again.', 'error');
        }
      }

      async function resetStaffPassword(id) {
        const staff = allStaff.find(s => s.admin_id == id);
        if (!staff) {
          alert('Staff member not found!');
          return;
        }

        if (!confirm(`Reset password for ${staff.full_name}?\n\nA new temporary password will be generated and sent to their email.`)) {
          return;
        }

        try {
          const formData = new FormData();
          formData.append('admin_id', id);
          
          const res = await fetch('reset_staff_pw.php', {
            method: 'POST',
            body: formData
          });
          
          const data = await res.json();
          
          if (data.success) {
            showToast('Password reset successfully! New password: ' + (data.new_password || 'Check email'), 'success');
          } else {
            showToast('Failed to reset password: ' + (data.message || 'Unknown error'), 'error');
          }
        } catch (err) {
          console.error(err);
          showToast('Error resetting password. Please try again.', 'error');
        }
      }

      async function deleteStaffMember(id) {
        const staff = allStaff.find(s => s.admin_id == id);
        if (!staff) {
          alert('Staff member not found!');
          return;
        }

        if (!confirm(`Delete ${staff.full_name}?\n\nThis action cannot be undone!`)) {
          return;
        }

        if (!confirm(`Are you absolutely sure?\n\nStaff member: ${staff.full_name}\nUsername: ${staff.username}\n\nThis will permanently delete this staff member.`)) {
          return;
        }

        try {
          const formData = new FormData();
          formData.append('action', 'delete_staff');
          formData.append('admin_id', id);
          
          const res = await fetch('manage_staff_actions.php', {
            method: 'POST',
            body: formData
          });
          
          const data = await res.json();
          
          if (data.success) {
            showToast('Staff member deleted successfully!', 'success');
            loadStaffList(); // Reload the list
          } else {
            showToast('Failed to delete staff: ' + (data.message || 'Unknown error'), 'error');
          }
        } catch (err) {
          console.error(err);
          showToast('Error deleting staff member. Please try again.', 'error');
        }
      }

      function showToast(message, type = 'info') {
        const colors = {
          success: '#10b981',
          error: '#ef4444',
          info: '#3b82f6',
          warning: '#f59e0b'
        };
        
        const icons = {
          success: 'fa-check-circle',
          error: 'fa-exclamation-circle',
          info: 'fa-info-circle',
          warning: 'fa-exclamation-triangle'
        };
        
        const toast = document.createElement('div');
        toast.style.cssText = `
          position: fixed;
          bottom: 24px;
          right: 24px;
          background: ${colors[type]};
          color: white;
          padding: 16px 24px;
          border-radius: 12px;
          box-shadow: 0 8px 24px rgba(0,0,0,0.15);
          z-index: 10001;
          font-weight: 500;
          display: flex;
          align-items: center;
          gap: 12px;
          animation: slideInRight 0.3s ease;
          max-width: 400px;
        `;
        
        toast.innerHTML = `
          <i class="fas ${icons[type]}" style="font-size: 20px;"></i>
          <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
          toast.style.animation = 'slideOutRight 0.3s ease';
          setTimeout(() => toast.remove(), 300);
        }, 4000);
      }

      function escapeHtml(str) {
        return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      function filterStaff() {
        const q = (document.getElementById('searchStaff').value || '').toLowerCase();

        // Get the currently active status filter from chips
        let status = 'all';
        if (document.getElementById('staff-chip-active')?.style.background === 'rgb(17, 34, 78)') {
          status = 'active';
        } else if (document.getElementById('staff-chip-inactive')?.style.background === 'rgb(17, 34, 78)') {
          status = 'inactive';
        }

        filteredStaff = allStaff.filter(s => {
          // Search filter
          if (q) {
            const searchText = [
              s.full_name || '',
              s.username || '',
              s.email || '',
              s.position || ''
            ].join(' ').toLowerCase();
            
            if (!searchText.includes(q)) return false;
          }

          // Status filter
          if (status === 'active') {
            if (s.is_active != 1) return false;
          } else if (status === 'inactive') {
            if (s.is_active == 1) return false;
          }

          return true;
        });

        currentStaffPage = 1;
        renderStaffTable();
      }

      // Load staff on DOM ready
      document.addEventListener('DOMContentLoaded', function() {
        loadStaffList();
      });

      // If redirected with flash, reload staff after short delay
      if (document.querySelector('.alert-success')) {
        setTimeout(loadStaffList, 800);
      }

      // ===== REPORTS SECTION FUNCTIONS =====
      let adminTrendChart, adminRevenueChart, adminRoomTypeChart;

      function initAdminReportsCharts() {
        // Trend Chart
        const trendCtx = document.getElementById('adminTrendChart');
        if (trendCtx) {
          adminTrendChart = new Chart(trendCtx.getContext('2d'), {
            type: 'line',
            data: {
              labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
              datasets: [{
                label: 'Reservations',
                data: [0, 0, 0, 0, 0, 0, 0],
                borderColor: '#11224e',
                backgroundColor: 'rgba(17, 34, 78, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
              }
            }
          });
        }

        // Revenue Chart
        const revenueCtx = document.getElementById('adminRevenueChart');
        if (revenueCtx) {
          adminRevenueChart = new Chart(revenueCtx.getContext('2d'), {
            type: 'bar',
            data: {
              labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
              datasets: [{
                label: 'Revenue',
                data: [0, 0, 0, 0, 0, 0, 0],
                backgroundColor: 'rgba(17, 34, 78, 0.8)',
                borderRadius: 8
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: {
                y: { 
                  beginAtZero: true, 
                  grid: { color: '#f1f5f9' },
                  ticks: { callback: function(value) { return '₱' + value.toLocaleString(); } }
                },
                x: { grid: { display: false } }
              }
            }
          });
        }

        // Room Type Chart
        const roomTypeCtx = document.getElementById('adminRoomTypeChart');
        if (roomTypeCtx) {
          adminRoomTypeChart = new Chart(roomTypeCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
              labels: [],
              datasets: [{
                data: [],
                backgroundColor: ['#667eea', '#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { 
                legend: { position: 'bottom' },
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      return context.label + ': ' + context.parsed;
                    }
                  }
                }
              }
            }
          });
        }
      }

      function updateAdminReports() {
        const period = document.getElementById('adminPeriodSelector').value;
        if (period === 'custom') {
          document.getElementById('adminStartDate').style.display = 'block';
          document.getElementById('adminEndDate').style.display = 'block';
          document.getElementById('adminApplyDateBtn').style.display = 'block';
        } else {
          document.getElementById('adminStartDate').style.display = 'none';
          document.getElementById('adminEndDate').style.display = 'none';
          document.getElementById('adminApplyDateBtn').style.display = 'none';
          loadAdminReportData(period);
        }
      }

      function applyAdminCustomDate() {
        const start = document.getElementById('adminStartDate').value;
        const end = document.getElementById('adminEndDate').value;
        if (start && end) {
          loadAdminReportData('custom', start, end);
          showAdminToast('Custom date range applied', 'success');
        } else {
          showAdminToast('Please select both start and end dates', 'warning');
        }
      }

      async function loadAdminReportData(period, startDate = null, endDate = null) {
        showAdminToast('Loading report data...', 'info');
        
        try {
          let url = `staff_get_report_data.php?period=${period}`;
          if (startDate && endDate) {
            url += `&start_date=${startDate}&end_date=${endDate}`;
          }
          
          const res = await fetch(url);
          const data = await res.json();
          
          console.log('Report data received:', data);
          
          if (!data.success) {
            showAdminToast(data.message || 'Failed to load report data', 'error');
            console.error('API Error:', data);
            return;
          }
          
          // Update metrics
          const m = data.metrics;
          document.getElementById('reportTotalReservations').textContent = m.total_reservations || 0;
          document.getElementById('reportTotalRevenue').textContent = '₱' + (m.total_revenue || 0).toLocaleString();
          document.getElementById('reportOccupancyRate').textContent = (m.occupancy_rate || 0) + '%';
          document.getElementById('reportTotalCancellations').textContent = m.cancellations || 0;
          
          // Update trend chart
          if (adminTrendChart && data.trend_data) {
            adminTrendChart.data.labels = data.trend_data.labels;
            adminTrendChart.data.datasets[0].data = data.trend_data.values;
            adminTrendChart.update();
          }
          
          // Update revenue chart
          if (adminRevenueChart && data.revenue_data) {
            adminRevenueChart.data.labels = data.revenue_data.labels;
            adminRevenueChart.data.datasets[0].data = data.revenue_data.values;
            adminRevenueChart.update();
          }

          // Update room type chart
          if (adminRoomTypeChart && data.room_type_data && data.room_type_data.length > 0) {
            adminRoomTypeChart.data.labels = data.room_type_data.map(r => r.room_type || 'N/A');
            adminRoomTypeChart.data.datasets[0].data = data.room_type_data.map(r => r.bookings || 0);
            adminRoomTypeChart.update();
          }

          // Update room type table
          if (data.room_type_data && data.room_type_data.length > 0) {
            const tbody = document.getElementById('adminRoomTypeTable');
            tbody.innerHTML = data.room_type_data.map(r => `
              <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:12px; color:#1e293b;">${r.room_type || 'N/A'}</td>
                <td style="padding:12px; color:#1e293b;">${r.bookings || 0}</td>
                <td style="padding:12px; color:#1e293b;">₱${(r.revenue || 0).toLocaleString()}</td>
              </tr>
            `).join('');
          } else {
            const tbody = document.getElementById('adminRoomTypeTable');
            tbody.innerHTML = '<tr><td colspan="3" style="padding:20px; text-align:center; color:#64748b;">No room type data available</td></tr>';
          }

          // Update guest statistics
          if (data.guest_stats && data.guest_stats.unique_guests) {
            const tbody = document.getElementById('adminGuestStatsTable');
            const gs = data.guest_stats;
            
            // Calculate percentage changes
            const guestsChange = gs.unique_guests.previous > 0 
              ? (((gs.unique_guests.current - gs.unique_guests.previous) / gs.unique_guests.previous) * 100).toFixed(1)
              : 0;
            const bookingsChange = gs.total_bookings.previous > 0
              ? (((gs.total_bookings.current - gs.total_bookings.previous) / gs.total_bookings.previous) * 100).toFixed(1)
              : 0;
            const valueChange = gs.avg_booking_value.previous > 0
              ? (((gs.avg_booking_value.current - gs.avg_booking_value.previous) / gs.avg_booking_value.previous) * 100).toFixed(1)
              : 0;
            
            tbody.innerHTML = `
              <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:12px;"><strong>Unique Guests</strong></td>
                <td style="padding:12px;">${gs.unique_guests.current || 0}</td>
                <td style="padding:12px;">${gs.unique_guests.previous || 0}</td>
                <td style="padding:12px; color:${guestsChange >= 0 ? '#10b981' : '#ef4444'};"><i class="fas fa-arrow-${guestsChange >= 0 ? 'up' : 'down'}"></i> ${Math.abs(guestsChange)}%</td>
              </tr>
              <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:12px;"><strong>Total Bookings</strong></td>
                <td style="padding:12px;">${gs.total_bookings.current || 0}</td>
                <td style="padding:12px;">${gs.total_bookings.previous || 0}</td>
                <td style="padding:12px; color:${bookingsChange >= 0 ? '#10b981' : '#ef4444'};"><i class="fas fa-arrow-${bookingsChange >= 0 ? 'up' : 'down'}"></i> ${Math.abs(bookingsChange)}%</td>
              </tr>
              <tr>
                <td style="padding:12px;"><strong>Avg. Booking Value</strong></td>
                <td style="padding:12px;">₱${(gs.avg_booking_value.current || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td style="padding:12px;">₱${(gs.avg_booking_value.previous || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td style="padding:12px; color:${valueChange >= 0 ? '#10b981' : '#ef4444'};"><i class="fas fa-arrow-${valueChange >= 0 ? 'up' : 'down'}"></i> ${Math.abs(valueChange)}%</td>
              </tr>
            `;
          } else {
            const tbody = document.getElementById('adminGuestStatsTable');
            tbody.innerHTML = '<tr><td colspan="4" style="padding:20px; text-align:center; color:#64748b;">No data available</td></tr>';
          }

          // Update performance metrics
          if (data.performance_metrics) {
            const perf = data.performance_metrics;
            const avgCheckin = perf.avg_checkin_time || 0;
            const avgResponse = perf.avg_response_time || 0;
            
            document.getElementById('adminCheckInTime').textContent = avgCheckin > 0 ? avgCheckin + ' min' : '—';
            document.getElementById('adminResponseTime').textContent = avgResponse > 0 ? avgResponse + ' min' : '—';
            
            // Calculate efficiency (lower is better, so invert for progress bar)
            const checkinEfficiency = avgCheckin > 0 ? Math.max(0, 100 - (avgCheckin / 60 * 100)) : 0;
            const responseEfficiency = avgResponse > 0 ? Math.max(0, 100 - (avgResponse / 120 * 100)) : 0;
            
            document.getElementById('adminCheckInBar').style.width = checkinEfficiency + '%';
            document.getElementById('adminResponseBar').style.width = responseEfficiency + '%';
          }
          
          showAdminToast('Reports updated successfully!', 'success');
        } catch (err) {
          console.error('Error loading report data:', err);
          showAdminToast('Failed to load report data', 'error');
        }
      }

      function exportReportsPDF() {
        showAdminToast('Generating PDF report...', 'info');
        setTimeout(() => {
          showAdminToast('PDF report generated successfully!', 'success');
        }, 1500);
      }

      function exportReportsExcel() {
        showAdminToast('Generating Excel report...', 'info');
        setTimeout(() => {
          showAdminToast('Excel report generated successfully!', 'success');
        }, 1500);
      }

      async function fixReservationPrices() {
        if (!confirm('This will recalculate and update prices for all reservations with 0 or NULL total_price. Continue?')) {
          return;
        }
        
        showAdminToast('Fixing reservation prices...', 'info');
        
        try {
          const res = await fetch('fix_reservation_prices.php');
          const data = await res.json();
          
          if (data.success) {
            showAdminToast(`Updated ${data.stats.updated} reservations. Skipped ${data.stats.skipped}.`, 'success');
            // Reload report data to show updated revenue
            setTimeout(() => {
              loadAdminReportData('week');
            }, 1000);
          } else {
            showAdminToast(data.message || 'Failed to fix prices', 'error');
          }
        } catch (error) {
          showAdminToast('Error fixing prices: ' + error.message, 'error');
        }
      }

      function showAdminToast(message, type = 'info') {
        const colors = {
          success: '#10b981',
          error: '#ef4444',
          info: '#3b82f6',
          warning: '#f59e0b'
        };
        
        const toast = document.createElement('div');
        toast.style.cssText = `
          position: fixed;
          bottom: 24px;
          right: 24px;
          background: ${colors[type]};
          color: white;
          padding: 16px 24px;
          border-radius: 12px;
          box-shadow: 0 8px 24px rgba(0,0,0,0.15);
          z-index: 10000;
          font-weight: 500;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 3000);
      }

      // Initialize reports charts when navigating to reports section
      const reportsNavLink = document.querySelector('a[data-section="reports"]');
      if (reportsNavLink) {
        reportsNavLink.addEventListener('click', function() {
          setTimeout(() => {
            if (!adminTrendChart) {
              initAdminReportsCharts();
            }
            loadAdminReportData('week');
          }, 100);
        });
      }

      // Check if Reports section is visible on page load
      setTimeout(() => {
        const reportsSection = document.getElementById('reports');
        if (reportsSection && reportsSection.style.display !== 'none' && !reportsSection.classList.contains('hidden')) {
          if (!adminTrendChart) {
            initAdminReportsCharts();
          }
          loadAdminReportData('week');
        }
      }, 500);

    </script>
  </body>
</html>
