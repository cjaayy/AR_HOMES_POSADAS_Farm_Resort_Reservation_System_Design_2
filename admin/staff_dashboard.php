<?php
/**
 * Staff Dashboard - Simplified view for staff users
 */
session_start();

// Allow only staff role
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Dashboard – AR Homes Posadas Farm Resort</title>
    <link rel="stylesheet" href="../admin-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
      /* Small overrides to keep staff UI compact */
      .staff-quick-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; }
      .staff-card { padding: 16px; border-radius: 8px; background: #fff; box-shadow: 0 6px 18px rgba(15,23,42,0.06); }
      .staff-card h3 { margin: 0 0 6px; font-size: 18px; }
      .notifications { margin-top: 12px; }
    </style>
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
        }catch(e){ window.location.href='logout.php'; }
      };
    </script>
</head>
<body>
  <div class="admin-container">
    <?php include 'staff_header.php'; ?>

  <main class="main-content" style="padding:20px;">
      <section class="content-section active">
        <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
          <div>
            <h2>Welcome, <?php echo htmlspecialchars($staffName); ?></h2>
            <p style="margin-top:6px; color:#6b7280;">This view is tailored for staff: reservations, check-ins, check-outs and guest contacts. Admin-only data is hidden.</p>
          </div>
          
        </div>

        <!-- Compact stats row (staff-focused). These IDs are used by the JS loader. -->
        <div class="staff-quick-grid" style="margin-top:12px;">
          <div class="staff-card">
            <div class="stat-centered">
              <div class="stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2); width:44px; height:44px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff;">
                <i class="fas fa-calendar-day"></i>
              </div>
              <div>
                <h3>Today's Reservations</h3>
                <div id="statTodayReservations" class="stat-value">1</div>
              </div>
            </div>
          </div>

          <div class="staff-card">
            <div class="stat-centered">
              <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#059669); width:44px; height:44px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff;">
                <i class="fas fa-user-check"></i>
              </div>
              <div>
                <h3>Arrivals Today</h3>
                <div id="statArrivals" class="stat-value">1</div>
              </div>
            </div>
          </div>

          <div class="staff-card">
            <div class="stat-centered">
              <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706); width:44px; height:44px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff;">
                <i class="fas fa-flag-checkered"></i>
              </div>
              <div>
                <h3>Check-outs</h3>
                <div id="statCheckouts" class="stat-value">0</div>
              </div>
            </div>
          </div>

          <div class="staff-card">
            <div class="stat-centered">
              <div class="stat-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626); width:44px; height:44px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff;">
                <i class="fas fa-clock"></i>
              </div>
              <div>
                <h3>Pending Requests</h3>
                <div id="statPending" class="stat-value">—</div>
              </div>
            </div>
          </div>
        </div>

        <div id="statsError" style="display:none; margin-top:8px;"></div>

        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:16px; margin-top:18px;">
          <div class="recent-activity">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <h3 style="margin:0">Recent Reservations</h3>
              <div><a class="btn-secondary" href="staff_reservations.php">Open Reservations</a></div>
            </div>
            <div id="recentReservations" style="margin-top:12px;">Loading...</div>
          </div>

          <div>
            <div class="recent-activity">
              <h3 style="margin:0 0 8px 0">Notifications</h3>
              <div id="notifications" class="notifications" style="margin-top:8px;">Loading...</div>
            </div>

            <div class="recent-activity" style="margin-top:12px;">
              <h3 style="margin:0 0 8px 0">Quick Actions</h3>
              <div class="quick-actions staff" style="margin-top:8px;">
                <div class="action-buttons">
                  <a class="btn-secondary" href="staff_reservations.php"><i class="fas fa-calendar-check"></i><span>Manage Reservations</span></a>
                  <a class="btn-secondary" href="staff_manage_users.php"><i class="fas fa-users"></i><span>Manage Users</span></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    async function loadStats() {
      const statsErrorEl = document.getElementById('statsError');
      statsErrorEl.style.display = 'none';
      try {
        // Try staff-focused stats endpoint first (include credentials)
        // Use 'include' to ensure cookies are sent in all environments
        let res = await fetch('staff_get_stats.php', { credentials: 'include' });
        if (window.__debugAdminScript) console.log('Fetching staff_get_stats.php ->', res.status, res.statusText);
        if (!res.ok) {
          // Try to surface more info for debugging
          const text = await res.text().catch(() => '<<no-response-body>>');
          const statusText = `staff_get_stats.php returned ${res.status} ${res.statusText} | body: ${text}`;
          console.warn(statusText);
          // Try admin dashboard stats as fallback
          const fallbackRes = await fetch('get_dashboard_stats.php', { credentials: 'include' });
          if (window.__debugAdminScript) console.log('Fallback get_dashboard_stats.php ->', fallbackRes.status, fallbackRes.statusText);
          if (!fallbackRes.ok) {
            const fbText = await fallbackRes.text().catch(() => '<<no-response-body>>');
            const fbStatus = `get_dashboard_stats.php returned ${fallbackRes.status} ${fallbackRes.statusText} | body: ${fbText}`;
            console.error(fbStatus);
            statsErrorEl.textContent = 'Failed to load stats: ' + fbStatus;
            statsErrorEl.style.display = 'block';
            return;
          }
          const fallbackData = await fallbackRes.json().catch(() => null);
          if (fallbackData && fallbackData.success && fallbackData.stats) {
            const s2 = fallbackData.stats;
            document.getElementById('statTodayReservations').textContent = s2.total_reservations ?? '—';
            document.getElementById('statArrivals').textContent = '—';
            document.getElementById('statCheckouts').textContent = '—';
            document.getElementById('statPending').textContent = s2.pending_reservations ?? '—';
            return;
          }
          statsErrorEl.textContent = 'Failed to load stats (no usable data returned)';
          statsErrorEl.style.display = 'block';
          return;
        }

        // Try to parse JSON but show raw body on parse failure so we can diagnose server errors
        let data = null;
        const rawBody = await res.text().catch(() => null);
        try {
          data = rawBody ? JSON.parse(rawBody) : null;
        } catch (parseErr) {
          // Show raw response to help diagnose server-side warnings/errors
          statsErrorEl.textContent = 'Failed to parse stats response: ' + (rawBody || '<<empty response>>');
          statsErrorEl.style.display = 'block';
          if (window.__debugAdminScript) console.error('Failed to parse JSON from staff_get_stats.php; raw body:', rawBody, parseErr);
          return;
        }

        if (!data || !data.success) {
          const msg = data && data.message ? data.message : 'Unknown error';
          statsErrorEl.textContent = 'Failed to load staff stats: ' + msg;
          statsErrorEl.style.display = 'block';
          return;
        }

        const s = data.stats || {};
        document.getElementById('statTodayReservations').textContent = typeof s.today_reservations !== 'undefined' ? s.today_reservations : '—';
        document.getElementById('statArrivals').textContent = typeof s.arrivals_today !== 'undefined' ? s.arrivals_today : '—';
        document.getElementById('statCheckouts').textContent = typeof s.checkouts_today !== 'undefined' ? s.checkouts_today : '—';
        document.getElementById('statPending').textContent = typeof s.pending_requests !== 'undefined' ? s.pending_requests : '—';
      } catch (err) {
        console.error(err);
        const statsErrorEl = document.getElementById('statsError');
        statsErrorEl.textContent = 'Error loading stats: ' + (err && err.message ? err.message : err);
        statsErrorEl.style.display = 'block';
      }
    }

    async function loadNotifications() {
      try {
        // Simple notifications: pending reservations (limit 6)
        const res = await fetch('staff_get_reservations.php?status=pending&limit=6');
        const data = await res.json();
        const el = document.getElementById('notifications');
        if (!data.success) {
          el.innerHTML = '<div style="color:#b00;">Failed to load notifications</div>';
          return;
        }
        if (!data.reservations || data.reservations.length === 0) {
          el.innerHTML = '<div style="color:#666;">No new notifications</div>';
          return;
        }
        el.innerHTML = data.reservations.map(r => `
          <div style="padding:8px 0; border-bottom:1px solid #f1f5f9;">
            <strong>${r.guest_name || 'Guest'}</strong>
            <div style="font-size:13px; color:#6b7280;">${r.room || 'N/A'} — ${new Date(r.created_at).toLocaleString()}</div>
          </div>
        `).join('');
      } catch (err) {
        console.error(err);
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      loadNotifications();
      loadStats();
      // refresh notifications every 25s
      setInterval(loadNotifications, 25000);
      // refresh stats every 30s
      setInterval(loadStats, 30000);
    });
  </script>
    <script src="../admin-script.js"></script>
</body>
</html>
