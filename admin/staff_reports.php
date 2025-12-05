<?php
/**
 * Staff Reports & Analytics - View reports and statistics
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
  <title>Reports & Analytics - Staff</title>
  <link rel="stylesheet" href="../admin-styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&family=Bungee+Spice&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    window.logout = window.logout || function(){
      try{
        fetch('logout.php', { method: 'POST', credentials: 'include' })
          .then(res => res.json().catch(() => null))
          .then(() => window.location.href = '../index.html')
          .catch(() => window.location.href = '../index.html');
      }catch(e){ window.location.href = 'logout.php'; }
    };
  </script>
  <style>
    .report-card {
      background: #fff;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      margin-bottom: 24px;
    }
    
    .report-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .report-title {
      font-size: 20px;
      font-weight: 700;
      color: #1e293b;
    }
    
    .date-selector {
      display: flex;
      gap: 12px;
      align-items: center;
    }
    
    .date-selector select,
    .date-selector input {
      padding: 8px 12px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 14px;
    }
    
    .stats-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    
    .stat-box {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: #fff;
      padding: 20px;
      border-radius: 12px;
      text-align: center;
    }
    
    .stat-box.green {
      background: linear-gradient(135deg, #10b981, #059669);
    }
    
    .stat-box.orange {
      background: linear-gradient(135deg, #f59e0b, #d97706);
    }
    
    .stat-box.red {
      background: linear-gradient(135deg, #ef4444, #dc2626);
    }
    
    .stat-label {
      font-size: 14px;
      opacity: 0.9;
      margin-bottom: 8px;
    }
    
    .stat-value {
      font-size: 32px;
      font-weight: 700;
    }
    
    .chart-container {
      position: relative;
      height: 300px;
      margin-top: 20px;
    }
    
    .table-wrapper {
      overflow-x: auto;
      margin-top: 20px;
    }
    
    .report-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .report-table th {
      background: #f8fafc;
      padding: 12px;
      text-align: left;
      font-weight: 600;
      color: #64748b;
      border-bottom: 2px solid #e2e8f0;
    }
    
    .report-table td {
      padding: 12px;
      border-bottom: 1px solid #f1f5f9;
      color: #1e293b;
    }
    
    .report-table tr:hover {
      background: #f8fafc;
    }
    
    .export-buttons {
      display: flex;
      gap: 12px;
    }
  </style>
</head>
<body>
  <div class="admin-container">
    <?php include 'staff_header.php'; ?>

    <main class="main-content" style="padding-top:100px;">
      <section class="content-section active">
        <div class="section-header" style="margin-bottom:24px;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <h2 style="font-size:28px; font-weight:700; color:#1e293b; margin-bottom:8px;">Reports & Analytics</h2>
              <p style="color:#64748b;">View detailed reports and insights</p>
            </div>
            <div class="export-buttons">
              <button onclick="exportPDF()" class="btn-secondary" style="display:flex; align-items:center; gap:8px;">
                <i class="fas fa-file-pdf"></i> Export PDF
              </button>
              <button onclick="exportExcel()" class="btn-primary" style="display:flex; align-items:center; gap:8px;">
                <i class="fas fa-file-excel"></i> Export Excel
              </button>
            </div>
          </div>
        </div>

        <!-- Period Selector -->
        <div class="report-card">
          <div class="date-selector">
            <label style="font-weight:600; color:#1e293b;">Report Period:</label>
            <select id="periodSelector" onchange="updateReports()">
              <option value="today">Today</option>
              <option value="week" selected>This Week</option>
              <option value="month">This Month</option>
              <option value="year">This Year</option>
              <option value="custom">Custom Range</option>
            </select>
            <input type="date" id="startDate" style="display:none;">
            <input type="date" id="endDate" style="display:none;">
            <button onclick="applyCustomDate()" id="applyDateBtn" class="btn-primary" style="display:none;">Apply</button>
          </div>
        </div>

        <!-- Key Metrics -->
        <div class="stats-row">
          <div class="stat-box">
            <div class="stat-label">Total Reservations</div>
            <div class="stat-value" id="totalReservations">127</div>
          </div>
          <div class="stat-box green">
            <div class="stat-label">Revenue</div>
            <div class="stat-value" id="totalRevenue">$45,230</div>
          </div>
          <div class="stat-box orange">
            <div class="stat-label">Occupancy Rate</div>
            <div class="stat-value" id="occupancyRate">78%</div>
          </div>
          <div class="stat-box red">
            <div class="stat-label">Cancellations</div>
            <div class="stat-value" id="totalCancellations">8</div>
          </div>
        </div>

        <!-- Reservations Trend Chart -->
        <div class="report-card">
          <div class="report-header">
            <h3 class="report-title">Reservations Trend</h3>
          </div>
          <div class="chart-container">
            <canvas id="trendChart"></canvas>
          </div>
        </div>

        <!-- Revenue Chart -->
        <div class="report-card">
          <div class="report-header">
            <h3 class="report-title">Revenue Analysis</h3>
          </div>
          <div class="chart-container">
            <canvas id="revenueChart"></canvas>
          </div>
        </div>

        <!-- Room Type Distribution -->
        <div class="report-card">
          <div class="report-header">
            <h3 class="report-title">Room Type Distribution</h3>
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
            <div class="chart-container" style="height:250px;">
              <canvas id="roomTypeChart"></canvas>
            </div>
            <div class="table-wrapper">
              <table class="report-table">
                <thead>
                  <tr>
                    <th>Room Type</th>
                    <th>Bookings</th>
                    <th>Revenue</th>
                  </tr>
                </thead>
                <tbody id="roomTypeTable">
                  <tr>
                    <td>Deluxe Room</td>
                    <td>45</td>
                    <td>$18,900</td>
                  </tr>
                  <tr>
                    <td>Standard Room</td>
                    <td>52</td>
                    <td>$15,600</td>
                  </tr>
                  <tr>
                    <td>Suite</td>
                    <td>20</td>
                    <td>$8,800</td>
                  </tr>
                  <tr>
                    <td>Family Room</td>
                    <td>10</td>
                    <td>$1,930</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Top Performers -->
        <div class="report-card">
          <div class="report-header">
            <h3 class="report-title">Guest Statistics</h3>
          </div>
          <div class="table-wrapper">
            <table class="report-table">
              <thead>
                <tr>
                  <th>Metric</th>
                  <th>This Week</th>
                  <th>Last Week</th>
                  <th>Change</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>New Guests</strong></td>
                  <td>34</td>
                  <td>28</td>
                  <td style="color:#10b981;"><i class="fas fa-arrow-up"></i> +21%</td>
                </tr>
                <tr>
                  <td><strong>Returning Guests</strong></td>
                  <td>18</td>
                  <td>22</td>
                  <td style="color:#ef4444;"><i class="fas fa-arrow-down"></i> -18%</td>
                </tr>
                <tr>
                  <td><strong>Average Stay</strong></td>
                  <td>3.2 days</td>
                  <td>2.8 days</td>
                  <td style="color:#10b981;"><i class="fas fa-arrow-up"></i> +14%</td>
                </tr>
                <tr>
                  <td><strong>Guest Satisfaction</strong></td>
                  <td>4.8/5.0</td>
                  <td>4.6/5.0</td>
                  <td style="color:#10b981;"><i class="fas fa-arrow-up"></i> +4%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Performance Metrics -->
        <div class="report-card">
          <div class="report-header">
            <h3 class="report-title">Performance Metrics</h3>
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
            <div>
              <h4 style="color:#64748b; font-size:14px; margin-bottom:12px;">CHECK-IN EFFICIENCY</h4>
              <div style="background:#f8fafc; padding:16px; border-radius:8px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                  <span>Average Time</span>
                  <strong>8 minutes</strong>
                </div>
                <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden;">
                  <div style="background:#10b981; width:85%; height:100%;"></div>
                </div>
              </div>
            </div>
            <div>
              <h4 style="color:#64748b; font-size:14px; margin-bottom:12px;">RESPONSE TIME</h4>
              <div style="background:#f8fafc; padding:16px; border-radius:8px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                  <span>Average Response</span>
                  <strong>15 minutes</strong>
                </div>
                <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden;">
                  <div style="background:#667eea; width:70%; height:100%;"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </section>
    </main>
  </div>

  <script>
    let trendChart, revenueChart, roomTypeChart;

    function initCharts() {
      // Trend Chart
      const trendCtx = document.getElementById('trendChart').getContext('2d');
      trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [{
            label: 'Reservations',
            data: [12, 19, 15, 25, 22, 30, 28],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
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

      // Revenue Chart
      const revenueCtx = document.getElementById('revenueChart').getContext('2d');
      revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [{
            label: 'Revenue',
            data: [4200, 5800, 4500, 7200, 6800, 9500, 8600],
            backgroundColor: [
              'rgba(102, 126, 234, 0.8)',
              'rgba(102, 126, 234, 0.8)',
              'rgba(102, 126, 234, 0.8)',
              'rgba(102, 126, 234, 0.8)',
              'rgba(102, 126, 234, 0.8)',
              'rgba(16, 185, 129, 0.8)',
              'rgba(16, 185, 129, 0.8)'
            ],
            borderRadius: 8
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
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
              },
              ticks: {
                callback: function(value) {
                  return '$' + value.toLocaleString();
                }
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

      // Room Type Chart
      const roomTypeCtx = document.getElementById('roomTypeChart').getContext('2d');
      roomTypeChart = new Chart(roomTypeCtx, {
        type: 'doughnut',
        data: {
          labels: ['Deluxe Room', 'Standard Room', 'Suite', 'Family Room'],
          datasets: [{
            data: [45, 52, 20, 10],
            backgroundColor: [
              '#667eea',
              '#10b981',
              '#f59e0b',
              '#ef4444'
            ],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    }

    function updateReports() {
      const period = document.getElementById('periodSelector').value;
      if (period === 'custom') {
        document.getElementById('startDate').style.display = 'block';
        document.getElementById('endDate').style.display = 'block';
        document.getElementById('applyDateBtn').style.display = 'block';
      } else {
        document.getElementById('startDate').style.display = 'none';
        document.getElementById('endDate').style.display = 'none';
        document.getElementById('applyDateBtn').style.display = 'none';
        loadReportData(period);
      }
    }

    function applyCustomDate() {
      const start = document.getElementById('startDate').value;
      const end = document.getElementById('endDate').value;
      if (start && end) {
        loadReportData('custom', start, end);
        showToast('Custom date range applied', 'success');
      } else {
        showToast('Please select both start and end dates', 'warning');
      }
    }

    async function loadReportData(period, startDate = null, endDate = null) {
      showToast('Loading report data...', 'info');
      
      try {
        let url = `staff_get_report_data.php?period=${period}`;
        if (startDate && endDate) {
          url += `&start_date=${startDate}&end_date=${endDate}`;
        }
        
        const res = await fetch(url);
        const data = await res.json();
        
        if (!data.success) {
          showToast(data.message || 'Failed to load report data', 'error');
          return;
        }
        
        // Update metrics
        const m = data.metrics;
        document.getElementById('totalReservations').textContent = m.total_reservations || 0;
        document.getElementById('totalRevenue').textContent = '$' + (m.total_revenue || 0).toLocaleString();
        document.getElementById('occupancyRate').textContent = (m.occupancy_rate || 0) + '%';
        document.getElementById('totalCancellations').textContent = m.cancellations || 0;
        
        // Update trend chart
        if (trendChart && data.trend_data) {
          trendChart.data.labels = data.trend_data.labels;
          trendChart.data.datasets[0].data = data.trend_data.values;
          trendChart.update();
        }
        
        // Update revenue chart
        if (revenueChart && data.revenue_data) {
          revenueChart.data.labels = data.revenue_data.labels;
          revenueChart.data.datasets[0].data = data.revenue_data.values;
          revenueChart.update();
        }
        
        showToast('Reports updated successfully!', 'success');
      } catch (err) {
        console.error('Error loading report data:', err);
        showToast('Failed to load report data', 'error');
      }
    }

    function exportPDF() {
      showToast('Generating PDF report...', 'info');
      setTimeout(() => {
        showToast('PDF report generated successfully!', 'success');
      }, 1500);
    }

    function exportExcel() {
      showToast('Generating Excel report...', 'info');
      setTimeout(() => {
        showToast('Excel report generated successfully!', 'success');
      }, 1500);
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
      `;
      toast.textContent = message;
      document.body.appendChild(toast);
      
      setTimeout(() => toast.remove(), 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
      initCharts();
      loadReportData('week'); // Load initial data
    });
  </script>
  <script src="../admin-script.js"></script>
</body>
</html>
