<?php
/**
 * Admin Dashboard
 * AR Homes Posadas Farm Resort Reservation System
 */

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
    <link rel="icon" href="../favicon.ico" type="image/x-icon" />
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="../logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="16x16"
      href="../logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="180x180"
      href="../logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
    />
    <link
      rel="shortcut icon"
      href="../logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
    />
    <link rel="manifest" href="../site.webmanifest" />

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
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
  </head>
  <body>
    <div class="admin-container">
      <!-- Header -->
      <header class="admin-header">
        <div class="header-left">
          <div class="logo">
            <img
              src="../logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
              alt="AR Homes Resort Logo"
            />
          </div>
          <div class="resort-info">
            <h1>AR Homes Posadas Farm Resort</h1>
            <p>Administration Dashboard</p>
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
            <li class="nav-item active">
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
            <!-- Manage Staff Members nav removed from sidebar per request -->
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
          <div class="section-header">
            <h2>Dashboard Overview</h2>
            <p>
              Welcome back, <?php echo htmlspecialchars($adminFullName); ?>! Here's what's happening at the resort
              today.
            </p>
          </div>

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

            <!-- VIP Members Card -->
            <div class="stat-card warning">
              <div class="stat-icon">
                <i class="fas fa-crown"></i>
              </div>
              <div class="stat-content">
                <div class="stat-value" id="vipUsersCount">
                  <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="stat-label">VIP Members</div>
                <div class="stat-change">
                  <i class="fas fa-star"></i>
                  Premium members
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
          <div class="recent-activities-section">
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

          <!-- Loyalty Breakdown Section -->
          <div class="loyalty-breakdown-section">
            <h3><i class="fas fa-chart-pie"></i> User Loyalty Levels</h3>
            <div class="loyalty-stats" id="loyaltyBreakdown">
              <div class="loading-loyalty">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading loyalty statistics...</p>
              </div>
            </div>
          </div>

          <!-- Database Info -->
          <div class="dashboard-info">
            <div class="info-card">
              <i class="fas fa-database"></i>
              <p>Connected to database: <strong><?php echo DB_NAME; ?></strong></p>
              <p style="margin-top: 0.5rem; font-size: 0.9rem; opacity: 0.8;">
                Last updated: <span id="lastUpdated">Loading...</span>
              </p>
            </div>
          </div>
        </section>

        <!-- Reservations Section -->
        <section id="reservations" class="content-section">
          <div class="section-header">
            <h2>Manage Reservations</h2>
            <p>View and manage all resort reservations</p>
          </div>

          <div class="users-container">
            <div class="users-header" style="margin-bottom:8px;">
              <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="adminSearchBox" placeholder="Search guest, room, or contact" oninput="adminApplyFilters()" />
              </div>
              <div class="filter-options">
                <select id="adminFilterStatus" onchange="adminApplyFilters()">
                  <option value="">All</option>
                  <option value="pending">Pending</option>
                  <option value="confirmed">Confirmed</option>
                  <option value="completed">Completed</option>
                  <option value="canceled">Canceled</option>
                </select>
                <label class="small">From</label>
                <input type="date" id="adminFilterFrom" onchange="adminApplyFilters()">
                <label class="small">To</label>
                <input type="date" id="adminFilterTo" onchange="adminApplyFilters()">
              </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px; margin-bottom:12px;">
              <button class="btn-primary" onclick="adminShowCreateForm()"><i class="fas fa-plus"></i> Add Walk-in</button>
              <button class="btn-secondary" onclick="adminExportCSV()">Export CSV</button>
            </div>

            <div class="table-container">
              <table class="users-table" id="adminReservationsTable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Guest</th>
                    <th>Contact</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Created</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="adminReservationsBody"><tr><td colspan="9" style="text-align:center;padding:2rem;">Loading...</td></tr></tbody>
              </table>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:10px; gap:8px; align-items:center;">
              <div id="adminPaginationInfo" style="font-size:13px;color:#666"></div>
              <button class="btn-secondary" id="adminPrevPage" onclick="adminChangePage(-1)" disabled>&larr; Prev</button>
              <button class="btn-secondary" id="adminNextPage" onclick="adminChangePage(1)" disabled>Next &rarr;</button>
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
            // Admin reservations client-side (mirrors staff reservations UI)
            let adminAllReservations = [];
            let adminFilteredReservations = [];
            let adminCurrentPage = 1;
            const adminPageSize = 15;

            async function adminFetchAllReservations(){
              try{
                const res = await fetch('staff_get_reservations.php?limit=1000', { credentials: 'include' });
                const data = await res.json();
                if(!data.success){ console.error('Failed to load reservations', data.message); document.getElementById('adminReservationsBody').innerHTML = `<tr><td colspan="9" style="text-align:center;color:#b00;">Failed to load reservations</td></tr>`; return; }
                adminAllReservations = data.reservations || [];
                adminApplyFilters();
              }catch(err){ console.error(err); document.getElementById('adminReservationsBody').innerHTML = `<tr><td colspan="9" style="text-align:center;color:#b00;">Error loading reservations</td></tr>`; }
            }

            function adminApplyFilters(){
              const status = document.getElementById('adminFilterStatus').value;
              const from = document.getElementById('adminFilterFrom').value;
              const to = document.getElementById('adminFilterTo').value;
              const q = (document.getElementById('adminSearchBox').value || '').toLowerCase();
              adminFilteredReservations = adminAllReservations.filter(r => {
                if(status && String(r.status) !== status) return false;
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
              if(!adminFilteredReservations || adminFilteredReservations.length===0){ tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:2rem; color:#666;">No reservations found</td></tr>`; document.getElementById('adminPaginationInfo').textContent=''; document.getElementById('adminPrevPage').disabled=true; document.getElementById('adminNextPage').disabled=true; return; }
              const total = adminFilteredReservations.length; const totalPages = Math.ceil(total / adminPageSize); const start = (adminCurrentPage-1)*adminPageSize; const pageRows = adminFilteredReservations.slice(start, start+adminPageSize);
              const rowsHtml = pageRows.map(r=> `<tr>
                <td>${r.reservation_id}</td>
                <td>${escapeHtml(r.guest_name||'')}</td>
                <td>${escapeHtml(r.guest_phone||'')}<br/><small>${escapeHtml(r.guest_email||'')}</small></td>
                <td>${escapeHtml(r.room||'')}</td>
                <td>${r.check_in_date||''}</td>
                <td>${r.check_out_date||''}</td>
                <td>${r.created_at||''}</td>
                <td><span class="status-badge ${r.status||''}">${escapeHtml(r.status||'')}</span></td>
                <td style="text-align:center">
                  <div class="action-buttons">
                    <button onclick="adminUpdateStatus(${r.reservation_id}, 'confirmed')" class="btn-action btn-approve" title="Approve" aria-label="Approve reservation"><i class="fas fa-check"></i></button>
                    <button onclick="adminUpdateStatus(${r.reservation_id}, 'canceled')" class="btn-action btn-cancel" title="Cancel" aria-label="Cancel reservation"><i class="fas fa-times"></i></button>
                    <button onclick="adminViewReservation(${r.reservation_id})" class="btn-action btn-view" title="View" aria-label="View reservation"><i class="fas fa-eye"></i></button>
                  </div>
                </td>
              </tr>`).join('');
              tbody.innerHTML = rowsHtml; document.getElementById('adminPaginationInfo').textContent = `Page ${adminCurrentPage} of ${totalPages} — ${total} reservations`; document.getElementById('adminPrevPage').disabled = adminCurrentPage<=1; document.getElementById('adminNextPage').disabled = adminCurrentPage>=totalPages;
            }

            function adminChangePage(dir){ adminCurrentPage += dir; if(adminCurrentPage<1) adminCurrentPage=1; adminRenderPage(); }

            function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

            async function adminShowCreateForm(){ document.getElementById('adminCreateModal').style.display='flex'; }
            function adminHideCreateForm(){ document.getElementById('adminCreateModal').style.display='none'; }

            async function adminSubmitCreate(e){ e.preventDefault(); const form = new FormData(e.target); form.append('action','create'); try{ const res = await fetch('staff_actions.php',{method:'POST', body: form, credentials:'include'}); const data = await res.json(); if(data.success){ adminHideCreateForm(); adminFetchAllReservations(); alert('Created'); } else { alert('Error: '+(data.message||'')); } }catch(err){ console.error(err); alert('Failed to create reservation'); } }

            async function adminUpdateStatus(id, status){ if(!confirm('Change status?')) return; const form = new FormData(); form.append('action','update_status'); form.append('reservation_id', id); form.append('status', status); try{ const res = await fetch('staff_actions.php',{method:'POST', body: form, credentials:'include'}); const data = await res.json(); if(data.success){ adminFetchAllReservations(); alert('Status updated'); } else { alert('Error: '+(data.message||'')); } }catch(err){ console.error(err); alert('Failed to update status'); } }

            function adminViewReservation(id){ const r = adminAllReservations.find(x=>Number(x.reservation_id)===Number(id)); if(!r) return alert('Not found'); const html = `<div style="padding:1rem;"><h3>Reservation #${r.reservation_id}</h3><p><strong>Guest:</strong> ${escapeHtml(r.guest_name||'')}</p><p><strong>Contact:</strong> ${escapeHtml(r.guest_phone||'')} / ${escapeHtml(r.guest_email||'')}</p><p><strong>Room:</strong> ${escapeHtml(r.room||'')}</p><p><strong>Check-in:</strong> ${r.check_in_date||''} — <strong>Check-out:</strong> ${r.check_out_date||''}</p><p><strong>Status:</strong> ${escapeHtml(r.status||'')}</p><p><strong>Created:</strong> ${r.created_at||''}</p></div>`; if(typeof window.showModal==='function'){ window.showModal('Reservation Details', html); } else { alert('Reservation:\n'+JSON.stringify(r)); } }

            function adminExportCSV(){ if(!adminFilteredReservations || adminFilteredReservations.length===0) return alert('No reservations to export'); const rows = adminFilteredReservations.map(r=>({ id:r.reservation_id, guest:r.guest_name, phone:r.guest_phone, email:r.guest_email, room:r.room, check_in:r.check_in_date, check_out:r.check_out_date, status:r.status, created_at:r.created_at })); const csv = [Object.keys(rows[0]).join(',')].concat(rows.map(r=>Object.values(r).map(v=>'"'+String((v||'')).replace(/"/g,'""')+'"').join(','))).join('\n'); const blob=new Blob([csv],{type:'text/csv'}); const url=URL.createObjectURL(blob); const a=document.createElement('a'); a.href=url; a.download='admin_reservations_export.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url); }

            document.addEventListener('DOMContentLoaded', adminFetchAllReservations);
          </script>
        </section>

        <!-- Users Section -->
        <section id="users" class="content-section">
          <div class="section-header">
            <h2>Manage Users</h2>
            <p>Manage user accounts and permissions</p>
          </div>
          
          <!-- Users Table -->
          <div class="users-container">
            <div class="users-header">
              <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchUsers" placeholder="Search users by name, email, or username..." />
              </div>
              <div class="filter-options">
                <select id="statusFilter" onchange="filterUsers()">
                  <option value="all">All Status</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
                <select id="loyaltyFilter" onchange="filterUsers()">
                  <option value="all">All Loyalty Levels</option>
                  <option value="Regular">Regular</option>
                  <option value="Silver">Silver</option>
                  <option value="Gold">Gold</option>
                  <option value="VIP">VIP</option>
                </select>
              </div>
            </div>

            <div class="users-stats">
              <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-info">
                  <h3 id="manageTotalUsersCount">
                    <i class="fas fa-spinner fa-spin"></i>
                  </h3>
                  <p>Total Users</p>
                </div>
              </div>
              <div class="stat-card">
                <i class="fas fa-user-check"></i>
                <div class="stat-info">
                  <h3 id="manageActiveUsersCount">
                    <i class="fas fa-spinner fa-spin"></i>
                  </h3>
                  <p>Active Users</p>
                </div>
              </div>
              <div class="stat-card">
                <i class="fas fa-crown"></i>
                <div class="stat-info">
                  <h3 id="manageVipUsersCount">
                    <i class="fas fa-spinner fa-spin"></i>
                  </h3>
                  <p>VIP Members</p>
                </div>
              </div>
              <div class="stat-card">
                <i class="fas fa-user-plus"></i>
                <div class="stat-info">
                  <h3 id="manageNewUsersCount">
                    <i class="fas fa-spinner fa-spin"></i>
                  </h3>
                  <p>New This Month</p>
                </div>
              </div>
            </div>

            <div class="table-container">
              <table class="users-table" id="usersTable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Loyalty Level</th>
                    <th>Member Since</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="usersTableBody">
                  <tr>
                    <td colspan="10" style="text-align: center; padding: 2rem;">
                      <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea;"></i>
                      <p style="margin-top: 1rem; color: #666;">Loading users...</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="pagination" id="usersPagination">
              <!-- Pagination will be generated dynamically -->
            </div>
          </div>
        </section>

        <!-- Staff Section -->
        <section id="staff" class="content-section">
          <div class="section-header" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
            <div>
              <h2>Staff Members Management</h2>
              <p>Manage resort staff and employee information</p>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
              <!-- Create Staff button opens the create_staff.php page in a new tab -->
              <button class="btn-primary" onclick="window.open('create_staff.php', '_blank')" title="Create new staff member">
                <i class="fas fa-user-plus"></i>
                <span style="margin-left:8px;">Create Staff</span>
              </button>
            </div>
          </div>

          <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success" style="margin-bottom:12px;">
              <?php echo htmlspecialchars($_SESSION['flash_success']); ?>
              <?php if (!empty($_SESSION['flash_staff_username'])): ?>
                <div style="font-weight:500; margin-top:6px; color:#334155;">Username: <?php echo htmlspecialchars($_SESSION['flash_staff_username']); ?></div>
              <?php endif; ?>
            </div>
            <?php unset($_SESSION['flash_success'], $_SESSION['flash_staff_username']); ?>
          <?php endif; ?>

          <div class="users-container" id="staffContainer">
            <div class="users-header">
              <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchStaff" placeholder="Search staff by name, email, or username..." oninput="filterStaff()" />
              </div>
              <div class="filter-options">
                <select id="statusFilterStaff" onchange="filterStaff()">
                  <option value="all">All Status</option>
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>

            <div class="table-container">
              <table class="users-table" id="staffTable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="staffTableBody">
                  <tr>
                    <td colspan="9" style="text-align:center; padding:2rem;">
                      <i class="fas fa-spinner fa-spin" style="font-size:2rem; color:#667eea"></i>
                      <p style="margin-top:1rem; color:#666;">Loading staff members...</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- Reports Section -->
        <section id="reports" class="content-section">
          <div class="section-header">
            <h2>Reports</h2>
            <p>Generate and view resort analytics</p>
          </div>
          <div class="placeholder-content">
            <i class="fas fa-chart-bar"></i>
            <h3>Analytics & Reports</h3>
            <p>
              This section will contain revenue reports, occupancy statistics,
              and performance analytics.
            </p>
          </div>
        </section>

        <!-- Settings Section -->
        <section id="settings" class="content-section">
          <div class="section-header">
            <h2>Settings</h2>
            <p>Configure system and resort settings</p>
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
            console.log('📊 Loading dashboard statistics...');
            
            fetch('get_dashboard_stats.php')
                .then(response => response.json())
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
            updateStatCard('vipUsersCount', stats.vip_users);
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
            
            // Update loyalty breakdown
            updateLoyaltyBreakdown(data.loyalty_breakdown);
            
            // Update timestamp
            const lastUpdated = new Date(data.timestamp);
            document.getElementById('lastUpdated').textContent = lastUpdated.toLocaleString();
            
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
        
        function updateLoyaltyBreakdown(loyaltyData) {
            const container = document.getElementById('loyaltyBreakdown');
            
            if (!loyaltyData || loyaltyData.length === 0) {
                container.innerHTML = `
                    <div class="no-data">
                        <i class="fas fa-chart-pie"></i>
                        <p>No loyalty data available</p>
                    </div>
                `;
                return;
            }
            
            // Define colors for each loyalty level
            const loyaltyColors = {
                'Regular': '#6c757d',
                'Silver': '#c0c0c0',
                'Gold': '#ffd700',
                'VIP': '#8b00ff'
            };
            
            const loyaltyIcons = {
                'Regular': 'fa-user',
                'Silver': 'fa-medal',
                'Gold': 'fa-trophy',
                'VIP': 'fa-crown'
            };
            
            const loyaltyHTML = loyaltyData.map(item => `
                <div class="loyalty-item" style="border-left: 4px solid ${loyaltyColors[item.loyalty_level] || '#667eea'}">
                    <div class="loyalty-icon" style="color: ${loyaltyColors[item.loyalty_level] || '#667eea'}">
                        <i class="fas ${loyaltyIcons[item.loyalty_level] || 'fa-star'}"></i>
                    </div>
                    <div class="loyalty-info">
                        <h4>${item.loyalty_level}</h4>
                        <div class="loyalty-count">${item.count} ${item.count === 1 ? 'member' : 'members'}</div>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = loyaltyHTML;
        }
        
        function showErrorState() {
            // Show error message in stat cards
            const statCards = ['totalUsersCount', 'activeUsersCount', 'vipUsersCount', 'newUsersToday',
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
        const style = document.createElement('style');
        style.textContent = `
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
        document.head.appendChild(style);
    </script>
    
    <script src="../admin-script.js"></script>
    <script>
      // Staff list loader
      async function loadStaffList() {
        try {
          const res = await fetch('get_staff.php');
          const data = await res.json();
          const tbody = document.getElementById('staffTableBody');
          if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:1.5rem; color:#b00;">Failed to load staff: ${data.message || 'Unknown'}</td></tr>`;
            return;
          }

          if (!data.staff || data.staff.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:1.5rem; color:#666;">No staff members found.</td></tr>`;
            return;
          }

          const rows = data.staff.map(s => {
            const status = s.is_active == 1 ? '<span class="status-badge active">Active</span>' : '<span class="status-badge inactive">Inactive</span>';
            const created = s.created_at ? s.created_at : '-';
            const lastLogin = s.last_login ? s.last_login : '-';
            return `
              <tr>
                <td>${s.admin_id}</td>
                <td>${escapeHtml(s.full_name || '')}</td>
                <td>${escapeHtml(s.username || '')}</td>
                <td>${escapeHtml(s.email || '')}</td>
                <td>${escapeHtml(s.position || '')}</td>
                <td style="text-align:center">${status}</td>
                <td style="text-align:center">${created}</td>
                <td style="text-align:center">${lastLogin}</td>
                <td style="text-align:center">
                  <div class="action-buttons">
                    <button class="btn-action btn-view" title="View" aria-label="View user"><i class="fas fa-eye"></i></button>
                    <button class="btn-action btn-edit" title="Edit" aria-label="Edit user"><i class="fas fa-edit"></i></button>
                    <button class="btn-action btn-delete" title="Delete" aria-label="Delete user"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
            `;
          }).join('');

          tbody.innerHTML = rows;
        } catch (err) {
          const tbody = document.getElementById('staffTableBody');
          tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:1.5rem; color:#b00;">Error loading staff.</td></tr>`;
          console.error(err);
        }
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
        const status = document.getElementById('statusFilterStaff').value;
        const tbody = document.getElementById('staffTableBody');
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(r => {
          const text = r.textContent.toLowerCase();
          let visible = true;
          if (q && !text.includes(q)) visible = false;
          if (status !== 'all') {
            const badge = r.querySelector('.status-badge');
            if (badge) {
              const isActive = badge.classList.contains('active') ? '1' : '0';
              if (isActive !== status) visible = false;
            }
          }
          r.style.display = visible ? '' : 'none';
        });
      }

      // Load staff on DOM ready
      document.addEventListener('DOMContentLoaded', function() {
        loadStaffList();
      });

      // If redirected with flash, reload staff after short delay
      if (document.querySelector('.alert-success')) {
        setTimeout(loadStaffList, 800);
      }
    </script>
  </body>
</html>
