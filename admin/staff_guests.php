<?php
/**
 * Staff - Guest Management (limited)
 * Read-only view of guest profiles and reservation count
 */
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || ($_SESSION['admin_role'] ?? '') !== 'staff') {
    header('Location: ../index.html'); exit;
}
require_once '../config/connection.php';

try {
    $db = new Database(); $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT id, full_name, email, phone, created_at FROM users ORDER BY created_at DESC LIMIT 500");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
}

$staffName = $_SESSION['admin_full_name'] ?? 'Staff Member';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manage Guests — Staff</title>
  <link rel="stylesheet" href="../admin-styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>table{width:100%;border-collapse:collapse}th,td{padding:8px;border-bottom:1px solid #eee}</style>
  <script>
    window.logout = window.logout || function(){
      try{
        fetch('logout.php', { method: 'POST', credentials: 'include' })
          .then(res => res.json().catch(() => null))
          .then(() => {
            const isInAdmin = window.location.pathname.includes('/admin/');
            window.location.href = isInAdmin ? '../index.html' : 'index.html';
          })
          .catch(() => { const isInAdmin = window.location.pathname.includes('/admin/'); window.location.href = isInAdmin ? '../index.html' : 'index.html'; });
      }catch(e){ window.location.href = 'logout.php'; }
    };
  </script>
</head>
<body>
  <div class="admin-container">
    <?php include 'staff_header.php'; ?>

    <main class="main-content">
      <section class="content-section active">
        <div class="section-header" style="display:flex; justify-content:space-between; align-items:center">
          <div>
            <h2>Guests</h2>
            <p>View guest contact info and account creation date. Editing and deletion are admin-only.</p>
          </div>
          <div>
            <input id="searchGuest" placeholder="Search by name or email" style="padding:8px; width:260px; border-radius:6px; border:1px solid #e5e7eb"> 
          </div>
        </div>

        <div style="margin-top:12px;">
          <table id="guestsTable">
            <thead>
              <tr><th>ID</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Member Since</th></tr>
            </thead>
            <tbody>
              <?php if (empty($users)): ?>
                <tr><td colspan="5" style="text-align:center; padding:16px; color:#666">No guests found.</td></tr>
              <?php else: foreach ($users as $u): ?>
                <tr>
                  <td><?php echo htmlspecialchars($u['id']); ?></td>
                  <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                  <td><?php echo htmlspecialchars($u['email']); ?></td>
                  <td><?php echo htmlspecialchars($u['phone'] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>
  <script>
    document.getElementById('searchGuest').addEventListener('input', function(e){
      const q = e.target.value.toLowerCase();
      const rows = document.querySelectorAll('#guestsTable tbody tr');
      rows.forEach(r => {
        const text = r.textContent.toLowerCase();
        r.style.display = text.includes(q) ? '' : 'none';
      });
    });
  </script>
  <script src="../admin-script.js"></script>
</body>
</html>
