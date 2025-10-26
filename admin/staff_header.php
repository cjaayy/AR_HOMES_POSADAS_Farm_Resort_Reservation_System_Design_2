<?php
/**
 * Shared header + sidebar for staff pages
 * Usage: include 'staff_header.php'; Ensure session is available.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

// Only allow staff
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || ($_SESSION['admin_role'] ?? '') !== 'staff') {
    header('Location: ../index.html');
    exit;
}

$staffName = $_SESSION['admin_full_name'] ?? 'Staff Member';
?>
<!-- Header -->
<header class="admin-header">
  <div class="header-left">
    <div class="logo"><img src="../logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png" alt="Logo"></div>
    <div class="resort-info"><h1>AR Homes Posadas Farm Resort</h1><p>Staff</p></div>
  </div>
  <div class="header-right">
    <div class="admin-profile">
      <div class="profile-info"><span class="admin-name"><?php echo htmlspecialchars($staffName); ?></span><span class="admin-role">Staff</span></div>
    </div>
    <button class="logout-btn" onclick="logout()"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></button>
  </div>
</header>

<?php include 'staff_sidebar.php'; ?>
