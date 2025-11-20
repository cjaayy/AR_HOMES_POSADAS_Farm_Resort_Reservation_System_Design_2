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

// Return user data
$memberSince = isset($_SESSION['login_time']) ? date('Y', $_SESSION['login_time']) : date('Y');

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
        'loyaltyLevel' => 'Regular'
    ]
]);
