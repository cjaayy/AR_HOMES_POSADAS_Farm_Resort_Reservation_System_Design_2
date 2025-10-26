<?php
/**
 * Staff Reservations Management
 */
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || ($_SESSION['admin_role'] ?? '') !== 'staff') {
    header('Location: ../index.html');
    exit;
}
$staffName = $_SESSION['admin_full_name'] ?? 'Staff Member';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manage Reservations - Staff</title>
  <link rel="stylesheet" href="../admin-styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script>
    // Lightweight logout fallback available immediately. admin-script.js will overwrite
    window.logout = window.logout || function(){
      // Prefer fetch POST so browser doesn't render raw JSON; include credentials
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
      }catch(e){
        window.location.href = 'logout.php';
      }
    };
  </script>
  <style>.reservations-actions{display:flex;gap:8px;align-items:center}.small{font-size:13px}</style>
</head>
<body>
  <div class="admin-container">
    <?php include 'staff_header.php'; ?>

    <main class="main-content">
      <section class="content-section active">
        <div class="section-header">
          <h2>Reservations</h2>
          <p>View and manage reservations assigned to your team.</p>
        </div>

        <div class="users-container">
          <div class="users-header">
            <div class="search-box">
              <i class="fas fa-search"></i>
              <input id="searchBox" placeholder="Search guest, room, or contact" oninput="applyFilters()" />
            </div>
              <div class="filter-options">
              <select id="filterStatus" onchange="applyFilters()">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="canceled">Canceled</option>
              </select>

              <!-- Styled date inputs: From / To -->
              <div class="input-wrapper" style="min-width:170px;">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <input type="date" id="filterFrom" onchange="applyFilters()" aria-label="Filter from date">
              </div>

              <div class="input-wrapper" style="min-width:170px;">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <input type="date" id="filterTo" onchange="applyFilters()" aria-label="Filter to date">
              </div>
            </div>
          </div>

          <div style="display:flex; justify-content:flex-end; gap:8px; margin-bottom:12px;">
            <button class="btn-primary" onclick="showCreateForm()"><i class="fas fa-plus"></i> Add Walk-in</button>
            <button class="btn-primary" onclick="exportCSV()"><i class="fas fa-file-csv"></i> Export CSV</button>
          </div>

          <div class="table-container">
            <table class="users-table" id="reservationsTable">
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
              <tbody id="reservationsBody"><tr><td colspan="9" style="text-align:center;padding:2rem;">Loading...</td></tr></tbody>
            </table>
          </div>

          <div style="display:flex; justify-content:flex-end; margin-top:10px; gap:8px; align-items:center;">
            <div id="paginationInfo" style="font-size:13px;color:#666"></div>
            <button class="btn-secondary" id="prevPage" onclick="changePage(-1)" disabled>&larr; Prev</button>
            <button class="btn-secondary" id="nextPage" onclick="changePage(1)" disabled>Next &rarr;</button>
          </div>
        </div>

        <!-- Simple modal/create form -->
        <div id="createModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
          <div style="background:#fff; padding:18px; border-radius:8px; width:480px;">
            <h3>Create Walk-in / Phone Reservation</h3>
            <form id="createForm" onsubmit="return submitCreate(event)">
              <div class="form-group"><label>Guest Name</label><input name="guest_name" required></div>
              <div class="form-group"><label>Phone</label><input name="guest_phone"></div>
              <div class="form-group"><label>Room</label><input name="room"></div>
              <div class="form-group" style="display:flex;gap:8px;"><div><label>Check-in</label><input type="date" name="check_in_date"></div><div><label>Check-out</label><input type="date" name="check_out_date"></div></div>
              <div style="display:flex;gap:8px; justify-content:flex-end; margin-top:12px;"><button type="button" onclick="hideCreateForm()" class="btn-secondary">Cancel</button><button class="btn-primary">Create</button></div>
            </form>
          </div>
        </div>

      </section>
    </main>
  </div>

  <script>
    // Client-side state for reservations
    let allReservations = [];
    let filteredReservations = [];
    let currentPage = 1;
    const pageSize = 12;

    async function fetchAllReservations(){
      const url = `staff_get_reservations.php?limit=1000`;
      try{
        const res = await fetch(url, { credentials: 'include' });
        const data = await res.json();
        if(!data.success){ console.error('Failed to load reservations', data.message); return; }
        allReservations = data.reservations || [];
        applyFilters();
      }catch(err){ console.error('Error fetching reservations', err); document.getElementById('reservationsBody').innerHTML = `<tr><td colspan="9" style="text-align:center;color:#b00;">Error loading reservations</td></tr>`; }
    }

    function applyFilters(){
      const status = document.getElementById('filterStatus').value;
      const from = document.getElementById('filterFrom').value;
      const to = document.getElementById('filterTo').value;
      const q = (document.getElementById('searchBox').value || '').toLowerCase();

      filteredReservations = allReservations.filter(r => {
        if(status && String(r.status) !== status) return false;
        if(from && r.check_in_date && r.check_in_date < from) return false;
        if(to && r.check_in_date && r.check_in_date > to) return false;
        if(q){
          const hay = ((r.guest_name||'') + ' ' + (r.room||'') + ' ' + (r.guest_phone||'') + ' ' + (r.guest_email||'')).toLowerCase();
          if(!hay.includes(q)) return false;
        }
        return true;
      });

      // reset to first page
      currentPage = 1;
      renderPage();
    }

    function renderPage(){
      const tbody = document.getElementById('reservationsBody');
      if(!filteredReservations || filteredReservations.length===0){
        tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:2rem; color:#666;">No reservations found</td></tr>`;
        document.getElementById('paginationInfo').textContent = '';
        document.getElementById('prevPage').disabled = true;
        document.getElementById('nextPage').disabled = true;
        return;
      }

      const total = filteredReservations.length;
      const totalPages = Math.ceil(total / pageSize);
      const start = (currentPage - 1) * pageSize;
      const pageRows = filteredReservations.slice(start, start + pageSize);

      const rowsHtml = pageRows.map(r => `
        <tr>
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
              <button onclick="updateStatus(${r.reservation_id}, 'confirmed')" class="btn-action btn-approve" title="Approve" aria-label="Approve reservation"><i class="fas fa-check"></i></button>
              <button onclick="updateStatus(${r.reservation_id}, 'canceled')" class="btn-action btn-cancel" title="Cancel" aria-label="Cancel reservation"><i class="fas fa-times"></i></button>
              <button onclick="viewReservation(${r.reservation_id})" class="btn-action btn-view" title="View" aria-label="View reservation"><i class="fas fa-eye"></i></button>
            </div>
          </td>
        </tr>
      `).join('');

      tbody.innerHTML = rowsHtml;

      document.getElementById('paginationInfo').textContent = `Page ${currentPage} of ${totalPages} — ${total} reservations`;
      document.getElementById('prevPage').disabled = currentPage <= 1;
      document.getElementById('nextPage').disabled = currentPage >= totalPages;
    }

    function changePage(direction){
      currentPage += direction;
      if(currentPage < 1) currentPage = 1;
      renderPage();
    }

    function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    async function showCreateForm(){ document.getElementById('createModal').style.display='flex'; }
    function hideCreateForm(){ document.getElementById('createModal').style.display='none'; }

    async function submitCreate(e){ e.preventDefault(); const form = new FormData(e.target); form.append('action','create');
      try{
        const res = await fetch('staff_actions.php',{method:'POST', body: form, credentials: 'include'});
        const data = await res.json();
        if(data.success){ hideCreateForm(); await fetchAllReservations(); showNotification('Reservation created', 'success'); } else { showNotification('Error: '+(data.message||''),'error'); }
      }catch(err){ console.error(err); showNotification('Failed to create reservation','error'); }
    }

    async function updateStatus(id, status){ if(!confirm('Change status?')) return; const form = new FormData(); form.append('action','update_status'); form.append('reservation_id', id); form.append('status', status); try{ const res = await fetch('staff_actions.php',{method:'POST', body: form, credentials: 'include'}); const data = await res.json(); if(data.success){ await fetchAllReservations(); showNotification('Status updated','success'); } else { showNotification('Error: '+(data.message||''),'error'); } }catch(err){ console.error(err); showNotification('Failed to update status','error'); } }

    function viewReservation(id){
      const r = allReservations.find(x => Number(x.reservation_id) === Number(id));
      if(!r) return showNotification('Reservation not found','error');
      const html = `
        <div style="padding:1rem; max-width:640px;">
          <h3>Reservation #${r.reservation_id}</h3>
          <p><strong>Guest:</strong> ${escapeHtml(r.guest_name||'')}</p>
          <p><strong>Contact:</strong> ${escapeHtml(r.guest_phone||'')} / ${escapeHtml(r.guest_email||'')}</p>
          <p><strong>Room:</strong> ${escapeHtml(r.room||'')}</p>
          <p><strong>Check-in:</strong> ${r.check_in_date||''} — <strong>Check-out:</strong> ${r.check_out_date||''}</p>
          <p><strong>Status:</strong> ${escapeHtml(r.status||'')}</p>
          <p><strong>Created:</strong> ${r.created_at||''}</p>
        </div>
      `;
      showModal('Reservation Details', html);
    }

    function exportCSV(){
      if(!filteredReservations || filteredReservations.length===0) return showNotification('No reservations to export','warning');
      const rows = filteredReservations.map(r => ({
        id: r.reservation_id,
        guest: r.guest_name,
        phone: r.guest_phone,
        email: r.guest_email,
        room: r.room,
        check_in: r.check_in_date,
        check_out: r.check_out_date,
        status: r.status,
        created_at: r.created_at
      }));
      const csv = [Object.keys(rows[0]).join(',')].concat(rows.map(r => Object.values(r).map(v => '"'+String((v||'')).replace(/"/g,'""')+'"').join(','))).join('\n');
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a'); a.href = url; a.download = 'reservations_export.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    }

    // small toast notification helper
    function showNotification(msg, type='info'){
      const colors = { success:'#27ae60', error:'#e74c3c', info:'#3498db', warning:'#f39c12' };
      const n = document.createElement('div'); n.style.cssText = `position:fixed; right:20px; bottom:20px; background:${colors[type]||'#333'}; color:#fff; padding:10px 14px; border-radius:8px; z-index:9999;`; n.textContent = msg; document.body.appendChild(n); setTimeout(()=>n.remove(),3000);
    }

    // modal utility (reuses the user-modal styles from admin-script.js via showModal)
    function showModal(title, content){
      // reuse the existing showModal function if present
      if(typeof window.showModal === 'function') return window.showModal(title, content);
      // fallback small modal
      const modal = document.createElement('div'); modal.className = 'user-modal'; modal.innerHTML = `
        <div class="user-modal-overlay" onclick="closeUserModal()"></div>
        <div class="user-modal-content"><div class="user-modal-header"><h3>${title}</h3><button onclick="closeUserModal()">×</button></div><div class="user-modal-body">${content}</div></div>
      `; document.body.appendChild(modal); document.body.style.overflow='hidden';
    }

    function closeUserModal(){ const m = document.querySelector('.user-modal'); if(m){ m.remove(); document.body.style.overflow=''; } }

    document.addEventListener('DOMContentLoaded', function(){ fetchAllReservations(); });
  </script>
  <script src="../admin-script.js"></script>
</body>
</html>
