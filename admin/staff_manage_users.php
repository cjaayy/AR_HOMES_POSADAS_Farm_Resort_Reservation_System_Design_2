<?php
/**
 * Staff Manage Users - UI copied from admin manage users but read-only for staff
 */
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || ($_SESSION['admin_role'] ?? '') !== 'staff') {
    header('Location: ../index.html'); exit;
}
$staffName = $_SESSION['admin_full_name'] ?? 'Staff Member';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manage Users — Staff</title>
  <link rel="stylesheet" href="../admin-styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script>
    // Lightweight logout fallback available immediately. admin-script.js will overwrite
    window.logout = window.logout || function(){
      try{
        fetch('logout.php', { method: 'POST', credentials: 'include' })
          .then(res => res.json().catch(() => null))
          .then(() => {
            const isInAdmin = window.location.pathname.includes('/admin/');
            const indexPath = isInAdmin ? '../index.html' : 'index.html';
            window.location.href = indexPath;
          })
          .catch(() => {
            const isInAdmin = window.location.pathname.includes('/admin/');
            window.location.href = isInAdmin ? '../index.html' : 'index.html';
          });
      }catch(e){ window.location.href = 'logout.php'; }
    };
  </script>
  <style>
    table{width:100%;border-collapse:collapse}
    th,td{padding:8px;border-bottom:1px solid #eee}
    .action-buttons .btn-action[disabled]{opacity:0.5; cursor:not-allowed}
  </style>
</head>
<body>
  <div class="admin-container">
    <?php include 'staff_header.php'; ?>

    <main class="main-content">
      <section class="content-section active">
        <div class="section-header">
          <h2>Manage Users</h2>
          <p>All users are shown. Staff can view details but cannot edit or delete users from this view.</p>
        </div>

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
            <div class="stat-card"><i class="fas fa-users"></i><div class="stat-info"><h3 id="manageTotalUsersCount">0</h3><p>Total Users</p></div></div>
            <div class="stat-card"><i class="fas fa-user-check"></i><div class="stat-info"><h3 id="manageActiveUsersCount">0</h3><p>Active Users</p></div></div>
            <div class="stat-card"><i class="fas fa-crown"></i><div class="stat-info"><h3 id="manageVipUsersCount">0</h3><p>VIP Members</p></div></div>
            <div class="stat-card"><i class="fas fa-user-plus"></i><div class="stat-info"><h3 id="manageNewUsersCount">0</h3><p>New This Month</p></div></div>
          </div>

          <div class="table-container">
            <table class="users-table" id="usersTable">
              <thead>
                <tr>
                  <th>ID</th><th>Full Name</th><th>Username</th><th>Email</th><th>Phone</th><th>Status</th><th>Loyalty Level</th><th>Member Since</th><th>Last Login</th><th>Actions</th>
                </tr>
              </thead>
              <tbody id="usersTableBody">
                <tr><td colspan="10" style="text-align:center; padding:2rem;"><i class="fas fa-spinner fa-spin" style="font-size:2rem; color:#667eea;"></i><p style="margin-top:1rem; color:#666">Loading users...</p></td></tr>
              </tbody>
            </table>
          </div>

          <div class="pagination" id="usersPagination"></div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // Staff-specific fetch and render logic
    let allUsers = [];
    let filteredUsers = [];
    let currentPage = 1;
    const usersPerPage = 10;

    async function loadUsersForStaff() {
      const tbody = document.getElementById('usersTableBody');
      tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:2rem;"><i class="fas fa-spinner fa-spin" style="font-size:2rem; color:#667eea;"></i><p style="margin-top:1rem; color:#666">Loading users...</p></td></tr>`;
      try {
        const res = await fetch('staff_get_users.php');
        const data = await res.json();
        if (!data.success) {
          tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:2rem; color:#b00;">Failed to load users: ${data.message || 'Unknown'}</td></tr>`;
          return;
        }
        allUsers = data.users || [];
        filteredUsers = [...allUsers];
        updateUserStatistics();
        displayUsers();
      } catch (err) {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:2rem; color:#b00;">Error loading users.</td></tr>`;
      }
    }

    function updateUserStatistics(){
      const totalUsers = allUsers.length;
      const activeUsers = allUsers.filter(u => u.is_active == 1).length;
      const vipUsers = allUsers.filter(u => u.loyalty_level === 'VIP').length;
      const currentDate = new Date(); const currentMonth = currentDate.getMonth(); const currentYear = currentDate.getFullYear();
      const newUsers = allUsers.filter(user => {
        const d = new Date(user.created_at); return d.getMonth() === currentMonth && d.getFullYear() === currentYear;
      }).length;
      document.getElementById('manageTotalUsersCount').textContent = totalUsers;
      document.getElementById('manageActiveUsersCount').textContent = activeUsers;
      document.getElementById('manageVipUsersCount').textContent = vipUsers;
      document.getElementById('manageNewUsersCount').textContent = newUsers;
    }

    function displayUsers(){
      const tbody = document.getElementById('usersTableBody');
      if (filteredUsers.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:2rem;"><i class="fas fa-users" style="font-size:2rem; color:#999;"></i><p style="margin-top:1rem; color:#666;">No users found</p></td></tr>`; return;
      }
      const start = (currentPage-1)*usersPerPage; const end = start + usersPerPage; const pageUsers = filteredUsers.slice(start,end);
      const rows = pageUsers.map(u => {
        const statusClass = u.is_active == 1 ? 'active' : 'inactive';
        const statusText = u.is_active == 1 ? 'Active' : 'Inactive';
        const loyalty = u.loyalty_level || '';
        return `
          <tr data-user-id="${u.user_id}">
            <td><strong>#${u.user_id}</strong></td>
            <td><strong>${escapeHtml(u.full_name || '')}</strong></td>
            <td>${escapeHtml(u.username || '')}</td>
            <td>${escapeHtml(u.email || '')}</td>
            <td>${escapeHtml(u.phone_formatted || '')}</td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td><span class="loyalty-badge">${escapeHtml(loyalty)}</span></td>
            <td>${escapeHtml(u.member_since || '')}</td>
            <td>${u.last_login_formatted || ''}</td>
            <td>
              <div class="action-buttons">
                <button class="btn-action btn-view" onclick="viewUser(${u.user_id})" title="View Details" aria-label="View user details"><i class="fas fa-eye"></i></button>
                <button class="btn-action" disabled title="Edit (admin only)" aria-label="Edit user (admin only)"><i class="fas fa-edit"></i></button>
                <button class="btn-action" disabled title="Toggle Status (admin only)" aria-label="Toggle status (admin only)"><i class="fas fa-power-off"></i></button>
                <button class="btn-action" disabled title="Delete (admin only)" aria-label="Delete user (admin only)"><i class="fas fa-trash"></i></button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
      tbody.innerHTML = rows;
      updatePagination();
    }

    function escapeHtml(text){ const d=document.createElement('div'); d.textContent = text; return d.innerHTML; }

    function filterUsers(){
      const q = (document.getElementById('searchUsers').value||'').toLowerCase();
      const status = document.getElementById('statusFilter').value;
      const loyalty = document.getElementById('loyaltyFilter').value;
      filteredUsers = allUsers.filter(u => {
        const matchesSearch = (u.full_name||'').toLowerCase().includes(q) || (u.username||'').toLowerCase().includes(q) || (u.email||'').toLowerCase().includes(q) || (u.phone_formatted||'').includes(q);
        const matchesStatus = status === 'all' || (status === 'active' && u.is_active==1) || (status === 'inactive' && u.is_active==0);
        const matchesLoyalty = loyalty === 'all' || (u.loyalty_level === loyalty);
        return matchesSearch && matchesStatus && matchesLoyalty;
      }); currentPage = 1; displayUsers();
    }

    function updatePagination(){
      const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
      const p = document.getElementById('usersPagination'); if (totalPages <= 1) { p.innerHTML=''; return; }
      let html = `<button onclick="changePage(${currentPage-1})" ${currentPage===1?'disabled':''}><i class="fas fa-chevron-left"></i> Previous</button>`;
      for (let i=1;i<=totalPages;i++){ if (i===1 || i===totalPages || (i>=currentPage-2 && i<=currentPage+2)) html += `<button onclick="changePage(${i})" class="${i===currentPage?'active':''}">${i}</button>`; else if (i===currentPage-3||i===currentPage+3) html += '<span style="padding:0 0.5rem;">...</span>'; }
      html += `<button onclick="changePage(${currentPage+1})" ${currentPage===totalPages?'disabled':''}>Next <i class="fas fa-chevron-right"></i></button>`;
      p.innerHTML = html;
    }

    function changePage(page){ const totalPages = Math.ceil(filteredUsers.length / usersPerPage); if (page<1||page>totalPages) return; currentPage=page; displayUsers(); document.querySelector('.users-container').scrollIntoView({behavior:'smooth'}); }

    function viewUser(userId){ const u = allUsers.find(x=>x.user_id===userId); if(!u) return; const content = `
      <div style="padding:1rem;">
        <h3 style="color:#667eea;">User Details</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div><strong>User ID:</strong><br>#${u.user_id}</div>
          <div><strong>Status:</strong><br><span class="status-badge ${u.is_active==1?'active':'inactive'}">${u.is_active==1?'Active':'Inactive'}</span></div>
          <div><strong>Full Name:</strong><br>${escapeHtml(u.full_name||'')}</div>
          <div><strong>Username:</strong><br>${escapeHtml(u.username||'')}</div>
          <div><strong>Email:</strong><br>${escapeHtml(u.email||'')}</div>
          <div><strong>Phone:</strong><br>${escapeHtml(u.phone_formatted||'')}</div>
          <div><strong>Loyalty Level:</strong><br>${escapeHtml(u.loyalty_level||'')}</div>
          <div><strong>Member Since:</strong><br>${escapeHtml(u.member_since||'')}</div>
          <div><strong>Last Login:</strong><br>${u.last_login_formatted||''}</div>
          <div><strong>Created:</strong><br>${u.created_at_formatted||''}</div>
        </div>
      </div>
    `; showModal('User Details', content);
    }

    // Modal helper
    function showModal(title, content){ const modal = document.createElement('div'); modal.className='user-modal'; modal.innerHTML = `<div class="user-modal-overlay" onclick="closeModal()"></div><div class="user-modal-content"><div class="user-modal-header"><h3>${title}</h3><button onclick="closeModal()"><i class="fas fa-times"></i></button></div><div class="user-modal-body">${content}</div></div>`; document.body.appendChild(modal); document.body.style.overflow='hidden'; }
    function closeModal(){ const m=document.querySelector('.user-modal'); if(m){ m.remove(); document.body.style.overflow=''; } }

    document.addEventListener('DOMContentLoaded', function(){ document.getElementById('searchUsers').addEventListener('input', filterUsers); loadUsersForStaff(); });
  </script>
  <script src="../admin-script.js"></script>
</body>
</html>
