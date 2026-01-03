<?php
/**
 * Get Current User Session Data API
 * Returns user data for dashboard.html
 */

session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated',
        'authenticated' => false
    ]);
    exit;
}

// Check session timeout (1 hour)
$timeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    // Session has expired
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Session expired',
        'authenticated' => false
    ]);
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Include database configuration
require_once '../config/database.php';

// Default values
$memberSince = date('Y');
$totalBookings = 0;
$reviewsGiven = 0;

// Fetch user data from database
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $user_id = $_SESSION['user_id'] ?? null;
    $user_email = $_SESSION['user_email'] ?? '';
    
    if ($user_id) {
        // Get user's created_at date (member since)
        $userStmt = $conn->prepare("SELECT created_at FROM users WHERE user_id = :user_id LIMIT 1");
        $userStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $userStmt->execute();
        $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData && !empty($userData['created_at'])) {
            $memberSince = date('Y', strtotime($userData['created_at']));
        }
        
        // Get total bookings count (by user_id or email)
        // Excludes pending_payment without verified downpayment (same as dashboard)
        $bookingsStmt = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM reservations 
            WHERE (user_id = :user_id OR guest_email = :email)
            AND NOT (status = 'pending_payment' AND (downpayment_verified = 0 OR downpayment_verified IS NULL))
        ");
        $bookingsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $bookingsStmt->bindParam(':email', $user_email, PDO::PARAM_STR);
        $bookingsStmt->execute();
        $bookingsData = $bookingsStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bookingsData) {
            $totalBookings = (int)$bookingsData['total'];
        }
        
        // Get total reviews given
        $reviewsStmt = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM reviews 
            WHERE user_id = :user_id
        ");
        $reviewsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $reviewsStmt->execute();
        $reviewsData = $reviewsStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reviewsData) {
            $reviewsGiven = (int)$reviewsData['total'];
        }
    }
} catch (PDOException $e) {
    // Log error but don't break the response
    error_log("Error fetching user stats: " . $e->getMessage());
}

echo json_encode([
    'success' => true,
    'authenticated' => true,
    'data' => [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['user_username'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'full_name' => $_SESSION['user_full_name'] ?? 'Guest',
        'given_name' => $_SESSION['user_given_name'] ?? 'Guest',
        'last_name' => $_SESSION['user_last_name'] ?? '',
        'middle_name' => $_SESSION['user_middle_name'] ?? '',
        'phone_number' => $_SESSION['user_phone'] ?? '',
        'memberSince' => $memberSince,
        'totalBookings' => $totalBookings,
        'reviewsGiven' => $reviewsGiven,
        'loyaltyLevel' => 'Regular'
    ]
]);
