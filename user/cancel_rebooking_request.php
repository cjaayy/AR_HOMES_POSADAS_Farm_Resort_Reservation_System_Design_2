<?php
/**
 * Cancel rebooking request
 */

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
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

    $data = json_decode(file_get_contents('php://input'), true);
    $reservation_id = $data['reservation_id'] ?? null;
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }

    // Check if user owns this reservation
    $stmt = $pdo->prepare("
        SELECT * FROM reservations 
        WHERE reservation_id = :id AND user_id = :user_id
    ");
    $stmt->execute([
        ':id' => $reservation_id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    $reservation = $stmt->fetch();
    
    if (!$reservation) {
        throw new Exception('Reservation not found');
    }

    if ($reservation['rebooking_requested'] != 1 || $reservation['rebooking_approved'] !== null) {
        throw new Exception('Cannot cancel this rebooking request');
    }

    // Cancel the rebooking request
    $stmt = $pdo->prepare("
        UPDATE reservations 
        SET rebooking_requested = 0,
            rebooking_new_date = NULL,
            rebooking_reason = NULL,
            rebooking_approved = NULL,
            rebooking_approved_by = NULL,
            rebooking_approved_at = NULL,
            updated_at = NOW()
        WHERE reservation_id = :id
    ");
    
    $stmt->execute([':id' => $reservation_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Rebooking request cancelled successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
