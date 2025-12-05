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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&family=Bungee+Spice&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      /* Staff Dashboard Styles - Matching User Dashboard Design */
      .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
      }

      .stats-card {
        background: #eeeeee;
        backdrop-filter: blur(20px);
        border-radius: 16px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
      }

      .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      }

      .stats-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        background: #11224e;
      }

      .stats-content h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0;
        line-height: 1;
      }

      .stats-content p {
        font-size: 0.85rem;
        color: #666;
        font-weight: 500;
        margin-top: 0.1rem;
        line-height: 1.2;
      }

      /* Recent Activity Section */
      .recent-activity {
        background: #eeeeee;
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        margin-top: 2rem;
      }

      .recent-activity h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1.5rem;
      }

      .activity-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
      }

      .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        background: transparent;
        border-radius: 12px;
        transition: all 0.3s ease;
      }

      .activity-item:hover {
        background: rgba(255, 255, 255, 0.5);
      }

      .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #11224e;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
        flex-shrink: 0;
      }

      .activity-content h4 {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.3rem;
      }

      .activity-content p {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.3rem;
      }

      .activity-date {
        font-size: 0.8rem;
        color: #999;
      }

      /* Dashboard Grid for Charts/Notifications */
      .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-top: 24px;
      }

      .chart-card {
        background: #eeeeee;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      }

      .chart-card h3 {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
      }

      .notification-item {
        padding: 16px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.5);
        margin-bottom: 12px;
        border-left: 4px solid #11224e;
        cursor: pointer;
      }

      .notification-item:hover {
        background: rgba(255, 255, 255, 0.8);
      }

      .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 8px;
      }

      .notification-title {
        font-weight: 600;
        color: #333;
        font-size: 14px;
      }

      .notification-time {
        font-size: 12px;
        color: #999;
      }

      .notification-body {
        font-size: 13px;
        color: #666;
        line-height: 1.5;
      }

      .notifications { margin-top: 12px; }

      @media (max-width: 1024px) {
        .dashboard-grid {
          grid-template-columns: 1fr;
        }
      }
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

  <main class="main-content" style="padding:20px; padding-top:100px;">
      <section class="content-section active">
        <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:30px;">
          <div>
            <h2 style="font-size:32px; font-weight:700; color:#333; margin-bottom:8px;">Welcome back, <?php echo htmlspecialchars($staffName); ?>! 👋</h2>
            <p style="margin-top:6px; color:#666; font-size:16px;">Here's what's happening with your resort today</p>
          </div>
          <div style="display:flex; gap:12px;">
            <button onclick="refreshDashboard()" class="btn-primary" style="display:flex; align-items:center; gap:8px; background:#11224e; border:none; color:white; padding:10px 20px; border-radius:8px; cursor:pointer;">
              <i class="fas fa-sync-alt"></i> Refresh
            </button>
          </div>
        </div>

        <!-- Stats Cards - User Dashboard Style -->
        <div class="stats-grid">
          <div class="stats-card">
            <div class="stats-icon">
              <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stats-content">
              <h3 id="statTodayReservations">—</h3>
              <p>Today's Reservations</p>
            </div>
          </div>

          <div class="stats-card">
            <div class="stats-icon">
              <i class="fas fa-user-check"></i>
            </div>
            <div class="stats-content">
              <h3 id="statArrivals">—</h3>
              <p>Check-ins Today</p>
            </div>
          </div>

          <div class="stats-card">
            <div class="stats-icon">
              <i class="fas fa-door-open"></i>
            </div>
            <div class="stats-content">
              <h3 id="statCheckouts">—</h3>
              <p>Check-outs Today</p>
            </div>
          </div>

          <div class="stats-card">
            <div class="stats-icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="stats-content">
              <h3 id="statPending">—</h3>
              <p>Pending Requests</p>
            </div>
          </div>
        </div>

        <div id="statsError" style="display:none; margin-top:8px;"></div>

        <!-- Recent Activity Section - User Dashboard Style -->
        <div class="recent-activity">
          <h3>Recent Activity</h3>
          <div class="activity-list" id="activityFeed">
            <div class="activity-item">
              <div class="activity-icon">
                <i class="fas fa-spinner fa-spin"></i>
              </div>
              <div class="activity-content">
                <h4>Loading activities...</h4>
                <p>Please wait while we fetch recent activity</p>
                <span class="activity-date">Just now</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
          <!-- Left Column: Charts -->
          <div>
            <!-- Reservations Chart -->
            <div class="chart-card">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="margin:0;">Reservation Trends</h3>
                <select id="chartPeriod" onchange="updateChart()" style="padding:8px 12px; border-radius:8px; border:1px solid #ddd; background:#fff;">
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="year">This Year</option>
                </select>
              </div>
              <canvas id="reservationsChart" height="100"></canvas>
            </div>
          </div>

          <!-- Right Column: Notifications and Quick Stats -->
          <div>
            <!-- Notifications -->
            <div class="chart-card">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="margin:0;">Notifications <span id="notifBadge" style="background:#11224e; color:#fff; padding:2px 8px; border-radius:12px; font-size:12px; margin-left:8px;">0</span></h3>
                <button onclick="markAllRead()" style="background:#11224e; border:none; color:#fff; cursor:pointer; font-size:13px; padding:6px 12px; border-radius:8px; font-weight:600;">Mark all read</button>
              </div>
              <div id="notifications" class="notifications">Loading...</div>
            </div>

            <!-- Quick Stats -->
            <div class="chart-card" style="margin-top:24px;">
              <h3>Today's Summary</h3>
              <div style="margin-top:16px;">
                <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #ddd;">
                  <span style="color:#666;">Occupied Rooms</span>
                  <strong id="occupiedRooms">—</strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #ddd;">
                  <span style="color:#666;">Available Rooms</span>
                  <strong id="availableRooms">—</strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #ddd;">
                  <span style="color:#666;">Occupancy Rate</span>
                  <strong id="occupancyRate">—</strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding:12px 0;">
                  <span style="color:#666;">Active Guests</span>
                  <strong id="activeGuests">—</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    let reservationsChart = null;
    
    // Initialize Chart
    function initChart() {
      const ctx = document.getElementById('reservationsChart').getContext('2d');
      reservationsChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [{
            label: 'Reservations',
            data: [0, 0, 0, 0, 0, 0, 0],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: '#f1f5f9'
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          }
        }
      });
    }

    async function loadStats() {
      const statsErrorEl = document.getElementById('statsError');
      statsErrorEl.style.display = 'none';
      try {
        let res = await fetch('staff_get_stats.php', { credentials: 'include' });
        
        if (!res.ok) {
          const fallbackRes = await fetch('get_dashboard_stats.php', { credentials: 'include' });
          if (!fallbackRes.ok) {
            throw new Error('Failed to load stats');
          }
          const fallbackData = await fallbackRes.json().catch(() => null);
          if (fallbackData && fallbackData.success && fallbackData.stats) {
            const s2 = fallbackData.stats;
            document.getElementById('statTodayReservations').textContent = s2.total_reservations ?? '—';
            document.getElementById('statArrivals').textContent = '—';
            document.getElementById('statCheckouts').textContent = '—';
            document.getElementById('statPending').textContent = s2.pending_reservations ?? '—';
            
            // Update quick stats
            updateQuickStats();
            return;
          }
        }

        const rawBody = await res.text().catch(() => null);
        let data = null;
        try {
          data = rawBody ? JSON.parse(rawBody) : null;
        } catch (parseErr) {
          statsErrorEl.textContent = 'Failed to parse stats response';
          statsErrorEl.style.display = 'block';
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
        
        updateQuickStats();
      } catch (err) {
        console.error(err);
        statsErrorEl.textContent = 'Error loading stats';
        statsErrorEl.style.display = 'block';
      }
    }

    async function updateQuickStats() {
      // Keep showing placeholder dash
      document.getElementById('occupiedRooms').textContent = '—';
      document.getElementById('availableRooms').textContent = '—';
      document.getElementById('occupancyRate').textContent = '—';
      document.getElementById('activeGuests').textContent = '—';
    }

    async function loadNotifications() {
      try {
        const res = await fetch('staff_get_reservations.php?status=pending&limit=5');
        const data = await res.json();
        const el = document.getElementById('notifications');
        const badge = document.getElementById('notifBadge');
        
        if (!data.success) {
          el.innerHTML = '<div style="color:#ef4444; text-align:center; padding:20px;">Failed to load notifications</div>';
          return;
        }
        
        if (!data.reservations || data.reservations.length === 0) {
          el.innerHTML = '<div style="color:#94a3b8; text-align:center; padding:20px;"><i class="fas fa-bell-slash" style="font-size:24px; margin-bottom:8px; display:block;"></i>No new notifications</div>';
          badge.textContent = '0';
          return;
        }
        
        badge.textContent = data.reservations.length;
        
        el.innerHTML = data.reservations.map(r => `
          <div class="notification-item" onclick="viewNotification('${r.reservation_id}')">
            <div class="notification-header">
              <div class="notification-title">New ${r.status || 'pending'} reservation</div>
              <div class="notification-time">${timeAgo(r.created_at)}</div>
            </div>
            <div class="notification-body">
              <strong>${r.guest_name || 'Guest'}</strong> - ${r.room || 'Room TBD'}
              <br>Check-in: ${r.check_in_date || 'TBD'}
            </div>
          </div>
        `).join('');
      } catch (err) {
        console.error(err);
      }
    }

    async function loadActivityFeed() {
      const feed = document.getElementById('activityFeed');
      feed.innerHTML = '<div style="text-align:center; padding:20px; color:#94a3b8;"><i class="fas fa-history" style="font-size:32px; margin-bottom:12px; display:block; opacity:0.5;"></i><p>No recent activity</p></div>';
    }

    function timeAgo(dateString) {
      if (!dateString) return 'Just now';
      const date = new Date(dateString);
      const now = new Date();
      const seconds = Math.floor((now - date) / 1000);
      
      if (seconds < 60) return 'Just now';
      if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
      if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
      return Math.floor(seconds / 86400) + ' days ago';
    }

    function viewNotification(id) {
      window.location.href = `staff_reservations.php?highlight=${id}`;
    }

    function markAllRead() {
      showToast('All notifications marked as read', 'success');
      document.getElementById('notifBadge').textContent = '0';
    }

    function refreshDashboard() {
      showToast('Refreshing dashboard...', 'info');
      loadStats();
      loadNotifications();
      loadActivityFeed();
      setTimeout(() => {
        showToast('Dashboard refreshed successfully!', 'success');
      }, 500);
    }

    function updateChart() {
      // This would update the chart based on selected period
      showToast('Chart updated', 'success');
    }

    function showToast(message, type = 'info') {
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
        animation: slideIn 0.3s ease-out;
      `;
      toast.textContent = message;
      document.body.appendChild(toast);
      
      setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
      initChart();
      loadStats();
      loadNotifications();
      loadActivityFeed();
      
      // Refresh every 30 seconds
      setInterval(() => {
        loadStats();
        loadNotifications();
        loadActivityFeed();
      }, 30000);
    });
  </script>
    <script src="../admin-script.js"></script>
</body>
</html>
