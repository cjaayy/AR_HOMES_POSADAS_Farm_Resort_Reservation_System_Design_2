<?php
/**
 * Staff Reports Data API - Get report statistics for various periods
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || ($_SESSION['admin_role'] ?? '') !== 'staff') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $period = isset($_GET['period']) ? $_GET['period'] : 'week';
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    
    // Determine date range based on period
    switch ($period) {
        case 'today':
            $start = date('Y-m-d');
            $end = date('Y-m-d');
            break;
        case 'week':
            $start = date('Y-m-d', strtotime('-7 days'));
            $end = date('Y-m-d');
            break;
        case 'month':
            $start = date('Y-m-d', strtotime('-30 days'));
            $end = date('Y-m-d');
            break;
        case 'year':
            $start = date('Y-m-d', strtotime('-365 days'));
            $end = date('Y-m-d');
            break;
        case 'custom':
            $start = $startDate ?: date('Y-m-d', strtotime('-7 days'));
            $end = $endDate ?: date('Y-m-d');
            break;
        default:
            $start = date('Y-m-d', strtotime('-7 days'));
            $end = date('Y-m-d');
    }
    
    // Check if tables exist
    $tableCheck = $conn->query("SHOW TABLES LIKE 'reservations'");
    if ($tableCheck->rowCount() === 0) {
        // Return sample data if no tables
        echo json_encode([
            'success' => true,
            'period' => $period,
            'start_date' => $start,
            'end_date' => $end,
            'metrics' => [
                'total_reservations' => 127,
                'total_revenue' => 45230,
                'occupancy_rate' => 78,
                'cancellations' => 8
            ],
            'trend_data' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'values' => [12, 19, 15, 25, 22, 30, 28]
            ],
            'revenue_data' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'values' => [4200, 5800, 4500, 7200, 6800, 9500, 8600]
            ]
        ]);
        exit;
    }
    
    // Get reservations metrics
    $sql = "SELECT 
                COUNT(*) as total_reservations,
                SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as cancellations,
                SUM(CASE WHEN status = 'confirmed' OR status = 'completed' THEN 1 ELSE 0 END) as confirmed_reservations
            FROM reservations
            WHERE DATE(created_at) BETWEEN :start AND :end";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':start' => $start, ':end' => $end]);
    $metrics = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate estimated revenue (if price field exists)
    $totalRevenue = 0;
    try {
        $revSql = "SELECT SUM(total_price) as revenue FROM reservations 
                   WHERE DATE(created_at) BETWEEN :start AND :end 
                   AND status IN ('confirmed', 'completed')";
        $revStmt = $conn->prepare($revSql);
        $revStmt->execute([':start' => $start, ':end' => $end]);
        $revData = $revStmt->fetch(PDO::FETCH_ASSOC);
        $totalRevenue = $revData['revenue'] ?? 0;
    } catch (PDOException $e) {
        // total_price column might not exist, use estimated value
        $totalRevenue = ($metrics['confirmed_reservations'] ?? 0) * 350; // Estimated average
    }
    
    // Get room occupancy rate
    $roomCheck = $conn->query("SHOW TABLES LIKE 'room_inventory'");
    $occupancyRate = 0;
    if ($roomCheck->rowCount() > 0) {
        $occSql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied
                   FROM room_inventory";
        $occStmt = $conn->query($occSql);
        $occData = $occStmt->fetch(PDO::FETCH_ASSOC);
        $total = $occData['total'] ?? 0;
        $occupied = $occData['occupied'] ?? 0;
        $occupancyRate = $total > 0 ? round(($occupied / $total) * 100) : 0;
    }
    
    // Get daily trend data
    $trendSql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as count
                 FROM reservations
                 WHERE DATE(created_at) BETWEEN :start AND :end
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC";
    
    $trendStmt = $conn->prepare($trendSql);
    $trendStmt->execute([':start' => $start, ':end' => $end]);
    $trendData = $trendStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $trendLabels = [];
    $trendValues = [];
    $revenueValues = [];
    
    foreach ($trendData as $row) {
        $trendLabels[] = date('D', strtotime($row['date']));
        $trendValues[] = (int)$row['count'];
        $revenueValues[] = (int)$row['count'] * 350; // Estimated revenue per reservation
    }
    
    // If no data, generate sample for display
    if (empty($trendLabels)) {
        $trendLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $trendValues = [0, 0, 0, 0, 0, 0, 0];
        $revenueValues = [0, 0, 0, 0, 0, 0, 0];
    }
    
    echo json_encode([
        'success' => true,
        'period' => $period,
        'start_date' => $start,
        'end_date' => $end,
        'metrics' => [
            'total_reservations' => (int)($metrics['total_reservations'] ?? 0),
            'total_revenue' => $totalRevenue,
            'occupancy_rate' => $occupancyRate,
            'cancellations' => (int)($metrics['cancellations'] ?? 0)
        ],
        'trend_data' => [
            'labels' => $trendLabels,
            'values' => $trendValues
        ],
        'revenue_data' => [
            'labels' => $trendLabels,
            'values' => $revenueValues
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
