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
          <button class="logout-btn" onclick="logout()">
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
            <li class="nav-item">
              <a href="#staff" class="nav-link" data-section="staff">
                <i class="fas fa-user-tie"></i>
                <span>Manage Staff Members</span>
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
          <div class="section-header">
            <h2>Dashboard Overview</h2>
            <p>
              Welcome back, <?php echo htmlspecialchars($adminFullName); ?>! Here's what's happening at the resort
              today.
            </p>
          </div>

          <!-- Dashboard content area -->
          <div class="dashboard-info">
            <div class="info-card">
              <i class="fas fa-info-circle"></i>
              <p>Connected to database: <strong><?php echo DB_NAME; ?></strong></p>
            </div>
          </div>
        </section>

        <!-- Reservations Section -->
        <section id="reservations" class="content-section">
          <div class="section-header">
            <h2>Manage Reservations</h2>
            <p>View and manage all resort reservations</p>
          </div>
          <div class="placeholder-content">
            <i class="fas fa-calendar-check"></i>
            <h3>Reservations Management</h3>
            <p>
              This section will contain reservation management tools, booking
              calendar, and guest information.
            </p>
          </div>
        </section>

        <!-- Users Section -->
        <section id="users" class="content-section">
          <div class="section-header">
            <h2>Manage Users</h2>
            <p>Manage user accounts and permissions</p>
          </div>
          <div class="placeholder-content">
            <i class="fas fa-users"></i>
            <h3>User Management</h3>
            <p>
              This section will contain user management tools, account settings,
              and permission controls.
            </p>
          </div>
        </section>

        <!-- Staff Section -->
        <section id="staff" class="content-section">
          <div class="section-header">
            <h2>Staff Members Management</h2>
            <p>Manage resort staff and employee information</p>
          </div>
          <div class="placeholder-content">
            <i class="fas fa-user-tie"></i>
            <h3>Staff Management</h3>
            <p>
              This section will contain staff management tools, employee
              records, scheduling, and role assignments.
            </p>
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
                        placeholder="Enter full name"
                        required
                      />
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
  </body>
</html>
