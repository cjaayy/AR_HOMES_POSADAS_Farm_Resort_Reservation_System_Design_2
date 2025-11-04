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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      /* Enhanced Staff Dashboard Styles */
      .staff-quick-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
        gap: 20px; 
        margin-bottom: 30px;
      }
      
      .staff-card { 
        padding: 24px; 
        border-radius: 16px; 
        background: #fff; 
        box-shadow: 0 8px 24px rgba(15,23,42,0.08); 
        position: relative;
        overflow: hidden;
      }
      
      .staff-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
      }
      
      .stat-centered {
        display: flex;
        align-items: center;
        gap: 16px;
      }
      
      .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
        flex-shrink: 0;
      }
      
      .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1;
        margin-top: 8px;
      }
      
      .staff-card h3 { 
        margin: 0 0 4px; 
        font-size: 14px; 
        color: #64748b;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
      
      .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-top: 24px;
      }
      
      .chart-card {
        background: #fff;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15,23,42,0.08);
      }
      
      .chart-card h3 {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
      }
      
      .notification-item {
        padding: 16px;
        border-radius: 12px;
        background: #f8fafc;
        margin-bottom: 12px;
        border-left: 4px solid #667eea;
        cursor: pointer;
      }
      
      .notification-item:hover {
        background: #f1f5f9;
      }
      
      .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 8px;
      }
      
      .notification-title {
        font-weight: 600;
        color: #1e293b;
        font-size: 14px;
      }
      
      .notification-time {
        font-size: 12px;
        color: #94a3b8;
      }
      
      .notification-body {
        font-size: 13px;
        color: #64748b;
        line-height: 1.5;
      }
      
      .quick-action-card {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        cursor: pointer;
        text-decoration: none;
        display: block;
      }
      
      .quick-action-card:hover {
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
      }
      
      .quick-action-icon {
        font-size: 32px;
        margin-bottom: 12px;
      }
      
      .quick-action-title {
        font-weight: 600;
        font-size: 16px;
      }
      
      .activity-feed {
        max-height: 400px;
        overflow-y: auto;
      }
      
      .activity-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
      }
      
      .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
      }
      
      .activity-content {
        flex: 1;
      }
      
      .activity-title {
        font-weight: 600;
        color: #1e293b;
        font-size: 14px;
        margin-bottom: 4px;
      }
      
      .activity-desc {
        font-size: 13px;
        color: #64748b;
      }
      
      .activity-time {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
      }
      
      .notifications { margin-top: 12px; }
      
      .stat-trend {
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
      }
      
      .stat-trend.up {
        color: #10b981;
      }
      
      .stat-trend.down {
        color: #ef4444;
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

  <main class="main-content" style="padding:20px;">
      <section class="content-section active">
        <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:30px;">
          <div>
            <h2 style="font-size:32px; font-weight:700; color:#1e293b; margin-bottom:8px;">Welcome back, <?php echo htmlspecialchars($staffName); ?>! 👋</h2>
            <p style="margin-top:6px; color:#64748b; font-size:16px;">Here's what's happening with your resort today</p>
          </div>
          <div style="display:flex; gap:12px;">
            <button onclick="refreshDashboard()" class="btn-primary" style="display:flex; align-items:center; gap:8px; background:linear-gradient(135deg, #667eea, #764ba2); border:none;">
              <i class="fas fa-sync-alt"></i> Refresh
            </button>
          </div>
        </div>

        <!-- Enhanced Stats Cards -->
        <div class="staff-quick-grid">
          <div class="staff-card">
            <div class="stat-centered">
              <div class="stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                <i class="fas fa-calendar-day"></i>
              </div>
              <div style="flex:1;">
                <h3>Today's Reservations</h3>
                <div id="statTodayReservations" class="stat-value">—</div>
              </div>
            </div>
          </div>

          <div class="staff-card">
            <div class="stat-centered">
              <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);">
                <i class="fas fa-user-check"></i>
              </div>
              <div style="flex:1;">
                <h3>Check-ins Today</h3>
                <div id="statArrivals" class="stat-value">—</div>
              </div>
            </div>
          </div>

          <div class="staff-card">
            <div class="stat-centered">
              <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <i class="fas fa-door-open"></i>
              </div>
              <div style="flex:1;">
                <h3>Check-outs Today</h3>
                <div id="statCheckouts" class="stat-value">—</div>
              </div>
            </div>
          </div>

          <div class="staff-card">
            <div class="stat-centered">
              <div class="stat-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);">
                <i class="fas fa-clock"></i>
              </div>
              <div style="flex:1;">
                <h3>Pending Requests</h3>
                <div id="statPending" class="stat-value">—</div>
              </div>
            </div>
          </div>
        </div>

        <div id="statsError" style="display:none; margin-top:8px;"></div>

        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
          <!-- Left Column: Charts and Activity -->
          <div>
            <!-- Reservations Chart -->
            <div class="chart-card">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="margin:0;">Reservation Trends</h3>
                <select id="chartPeriod" onchange="updateChart()" style="padding:8px 12px; border-radius:8px; border:1px solid #e2e8f0;">
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="year">This Year</option>
                </select>
              </div>
              <canvas id="reservationsChart" height="100"></canvas>
            </div>

            <!-- Recent Activity -->
            <div class="chart-card" style="margin-top:24px;">
              <h3>Recent Activity</h3>
              <div class="activity-feed" id="activityFeed">
                <div class="activity-item">
                  <div class="activity-icon" style="background:#dbeafe; color:#3b82f6;">
                    <i class="fas fa-spinner fa-spin"></i>
                  </div>
                  <div class="activity-content">
                    <div class="activity-title">Loading activities...</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Notifications and Quick Actions -->
          <div>
            <!-- Notifications -->
            <div class="chart-card">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="margin:0;">Notifications <span id="notifBadge" style="background:#ef4444; color:#fff; padding:2px 8px; border-radius:12px; font-size:12px; margin-left:8px;">0</span></h3>
                <button onclick="markAllRead()" style="background:linear-gradient(135deg, #667eea, #764ba2); border:none; color:#fff; cursor:pointer; font-size:13px; padding:6px 12px; border-radius:8px; font-weight:600;">Mark all read</button>
              </div>
              <div id="notifications" class="notifications">Loading...</div>
            </div>

            <!-- Quick Stats -->
            <div class="chart-card" style="margin-top:24px;">
              <h3>Today's Summary</h3>
              <div style="margin-top:16px;">
                <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f1f5f9;">
                  <span style="color:#64748b;">Occupied Rooms</span>
                  <strong id="occupiedRooms">—</strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f1f5f9;">
                  <span style="color:#64748b;">Available Rooms</span>
                  <strong id="availableRooms">—</strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f1f5f9;">
                  <span style="color:#64748b;">Occupancy Rate</span>
                  <strong id="occupancyRate">—</strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding:12px 0;">
                  <span style="color:#64748b;">Active Guests</span>
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
          <div class="notification-item" onclick="viewNotification(${r.reservation_id})">
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
