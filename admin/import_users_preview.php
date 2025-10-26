<?php
/**
 * Import Users Preview
 * Shows existing guest users and allows previewing import to admin_users as staff.
 * This script does NOT perform writes. Use it to select rows and then run the import after confirmation.
 */
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.html'); exit;
}
require_once '../config/connection.php';

try {
    $db = new Database(); $conn = $db->getConnection();
    // Read users table
    $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
    $users = [];
    if ($tableCheck->rowCount() > 0) {
        $stmt = $conn->prepare("SELECT id, full_name, email, phone, created_at FROM users ORDER BY created_at DESC LIMIT 200");
        $stmt->execute(); $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $users = [];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Import Users Preview</title>
  <link rel="stylesheet" href="../admin-styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>table{width:100%;border-collapse:collapse}th,td{padding:8px;border-bottom:1px solid #eee}</style>
</head>
<body>
  <div class="admin-container">
    <header class="admin-header">
      <div class="header-left">
        <div class="logo"><img src="../logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png" alt="Logo"></div>
        <div class="resort-info"><h1>Import Users</h1><p>Preview guest users to import as staff</p></div>
      </div>
      <div class="header-right">
        <div class="admin-profile">
          <div class="profile-info">
            <span class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_full_name'] ?? 'Administrator'); ?></span>
            <span class="admin-role">Admin</span>
          </div>
          <div class="profile-avatar"><i class="fas fa-user-shield"></i></div>
        </div>
        <button class="logout-btn" onclick="location.href='staff_dashboard.php'">Back</button>
      </div>
      <div class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></div>
    </header>

    <?php include 'staff_sidebar.php'; ?>

    <main class="main-content">
      <section class="content-section active">
        <div class="section-header" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
          <div>
            <h2>Import Users (Preview)</h2>
            <p>Preview guest users to import as staff — no database writes will happen until you confirm.</p>
          </div>
          <div style="display:flex; gap:8px; align-items:center;">
            <button class="btn-primary" onclick="window.open('create_staff.php', '_blank')"><i class="fas fa-user-plus"></i><span style="margin-left:8px;">Create Staff</span></button>
            <button class="btn-secondary" onclick="location.href='staff_dashboard.php'"><i class="fas fa-arrow-left"></i><span style="margin-left:8px;">Back to Staff</span></button>
          </div>
        </div>
        <div class="users-container">
          <h3>Guest Users (preview)</h3>
          <?php if (empty($users)): ?>
            <p>No users table or no users found.</p>
          <?php else: ?>
            <p>Select users to import as staff. This preview will not modify the database.</p>
            <table>
              <thead><tr><th></th><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Created</th></tr></thead>
              <tbody>
                <?php foreach ($users as $u): ?>
                  <tr>
                    <td><input type="checkbox" class="sel" data-id="<?php echo $u['id']; ?>"></td>
                    <td><?php echo htmlspecialchars($u['id']); ?></td>
                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['phone'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <div style="margin-top:12px; display:flex; gap:8px;">
              <button id="previewBtn" class="btn-primary"><i class="fas fa-search"></i><span style="margin-left:8px;">Preview Import</span></button>
              <button id="importBtn" class="btn-secondary" disabled><i class="fas fa-upload"></i><span style="margin-left:8px;">Import Selected</span></button>
            </div>
            <div id="previewArea" style="margin-top:12px;"></div>
          <?php endif; ?>
        </div>
      </section>
    </main>
  </div>
  <script>
    document.getElementById('previewBtn').addEventListener('click', function(){
      const selected = Array.from(document.querySelectorAll('.sel:checked')).map(cb=>cb.dataset.id);
      const area = document.getElementById('previewArea');
      if(selected.length===0){ area.innerHTML='<div class="alert">No users selected.</div>'; return; }
      area.innerHTML = '<div class="alert">Preview will create staff accounts for user IDs: '+selected.join(', ')+'</div>'; document.getElementById('importBtn').disabled=false;
    });
    document.getElementById('importBtn').addEventListener('click', function(){ alert('To protect your data this preview tool does not perform import automatically. Reply to confirm and I will run the import script that maps selected users into admin_users as staff.'); });
  </script>
  <script src="../admin-script.js"></script>
</body>
</html>
