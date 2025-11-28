<?php
/**
 * Get Dashboard Statistics API Endpoint
 * AR Homes Posadas Farm Resort Reservation System
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Include database connection
require_once '../config/connection.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Get total users count
    $totalUsersQuery = "SELECT COUNT(*) as total FROM users";
    $stmt = $conn->prepare($totalUsersQuery);
    $stmt->execute();
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get active users count
    $activeUsersQuery = "SELECT COUNT(*) as total FROM users WHERE is_active = 1";
    $stmt = $conn->prepare($activeUsersQuery);
    $stmt->execute();
    $activeUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get new users this month
    $newUsersQuery = "SELECT COUNT(*) as total FROM users 
                      WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                      AND YEAR(created_at) = YEAR(CURRENT_DATE())";
    $stmt = $conn->prepare($newUsersQuery);
    $stmt->execute();
    $newUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total reservations count (if table exists)
    $totalReservations = 0;
    $pendingReservations = 0;
    $confirmedReservations = 0;
    $completedReservations = 0;
    
    // Check if reservations table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'reservations'");
    if ($tableCheck->rowCount() > 0) {
        // Total reservations
        $reservationsQuery = "SELECT COUNT(*) as total FROM reservations";
        $stmt = $conn->prepare($reservationsQuery);
        $stmt->execute();
        $totalReservations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Pending reservations
        $pendingQuery = "SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'";
        $stmt = $conn->prepare($pendingQuery);
        $stmt->execute();
        $pendingReservations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Confirmed reservations
        $confirmedQuery = "SELECT COUNT(*) as total FROM reservations WHERE status = 'confirmed'";
        $stmt = $conn->prepare($confirmedQuery);
        $stmt->execute();
        $confirmedReservations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Completed reservations
        $completedQuery = "SELECT COUNT(*) as total FROM reservations WHERE status = 'completed'";
        $stmt = $conn->prepare($completedQuery);
        $stmt->execute();
        $completedReservations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Get users by loyalty level
    $loyaltyQuery = "SELECT loyalty_level, COUNT(*) as count 
                     FROM users 
                     GROUP BY loyalty_level";
    $stmt = $conn->prepare($loyaltyQuery);
    $stmt->execute();
    $loyaltyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent activities (last 10 users registered)
    $recentUsersQuery = "SELECT 
                            full_name,
                            email,
                            created_at,
                            loyalty_level
                         FROM users 
                         ORDER BY created_at DESC 
                         LIMIT 10";
    $stmt = $conn->prepare($recentUsersQuery);
    $stmt->execute();
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format recent users for display
    $recentActivities = [];
    foreach ($recentUsers as $user) {
        $timeAgo = getTimeAgo(strtotime($user['created_at']));
        $recentActivities[] = [
            'type' => 'user_registration',
            'icon' => 'fa-user-plus',
            'title' => 'New User Registration',
            'description' => $user['full_name'] . ' joined as ' . $user['loyalty_level'] . ' member',
            'time' => $timeAgo,
            'date' => date('M d, Y', strtotime($user['created_at']))
        ];
    }

    // Get users registered today
    $todayUsersQuery = "SELECT COUNT(*) as total FROM users 
                        WHERE DATE(created_at) = CURDATE()";
    $stmt = $conn->prepare($todayUsersQuery);
    $stmt->execute();
    $todayUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get users registered this week
    $weekUsersQuery = "SELECT COUNT(*) as total FROM users 
                       WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
    $stmt = $conn->prepare($weekUsersQuery);
    $stmt->execute();
    $weekUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'inactive_users' => $totalUsers - $activeUsers,
            'new_users_this_month' => $newUsers,
            'new_users_today' => $todayUsers,
            'new_users_this_week' => $weekUsers,
            'total_reservations' => $totalReservations,
            'pending_reservations' => $pendingReservations,
            'confirmed_reservations' => $confirmedReservations,
            'completed_reservations' => $completedReservations
        ],
        'loyalty_breakdown' => $loyaltyStats,
        'recent_activities' => $recentActivities,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

/**
 * Helper function to convert timestamp to human-readable time ago
 */
function getTimeAgo($timestamp) {
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        $months = floor($diff / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    }
}
