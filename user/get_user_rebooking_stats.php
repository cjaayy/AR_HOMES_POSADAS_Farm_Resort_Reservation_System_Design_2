<?php
/**
 * Get user rebooking statistics
 */

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    $user_id = $_SESSION['user_id'];

    // Get rebooking statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN rebooking_approved IS NULL THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN rebooking_approved = 1 THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN rebooking_approved = -1 THEN 1 ELSE 0 END) as rejected
        FROM reservations 
        WHERE user_id = :user_id 
        AND rebooking_requested = 1
    ");
    
    $stmt->execute([':user_id' => $user_id]);
    $stats = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'stats' => [
            'total' => (int)($stats['total'] ?? 0),
            'pending' => (int)($stats['pending'] ?? 0),
            'approved' => (int)($stats['approved'] ?? 0),
            'rejected' => (int)($stats['rejected'] ?? 0)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'stats' => [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0
        ]
    ]);
}
