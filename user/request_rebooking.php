<?php
/**
 * Request Rebooking
 * User can request to change their reservation date
 * Allowed only if check-in is 7+ days away
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
    $new_date = $data['new_date'] ?? null;
    $reason = $data['reason'] ?? '';
    
    if (!$reservation_id || !$new_date) {
        throw new Exception('Reservation ID and new date are required');
    }
    
    // Get reservation details
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
    
    // Check if rebooking is allowed (7 days before check-in)
    $check_in = new DateTime($reservation['check_in_date']);
    $today = new DateTime();
    $days_until_checkin = $today->diff($check_in)->days;
    
    if ($days_until_checkin < 7) {
        throw new Exception('Rebooking is only allowed 7 days or more before check-in date. You have ' . $days_until_checkin . ' days remaining.');
    }
    
    // Check if already confirmed
    if ($reservation['status'] !== 'confirmed') {
        throw new Exception('Only confirmed reservations can be rebooked');
    }
    
    // Check if new date is available
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM reservations 
        WHERE check_in_date = :date 
        AND booking_type = :type
        AND status IN ('confirmed', 'checked_in', 'pending_confirmation')
        AND (downpayment_verified = 1 OR date_locked = 1)
        AND reservation_id != :id
    ");
    $stmt->execute([
        ':date' => $new_date,
        ':type' => $reservation['booking_type'],
        ':id' => $reservation_id
    ]);
    
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        throw new Exception('New date is already booked. Please choose another date.');
    }
    
    // Submit rebooking request
    $stmt = $pdo->prepare("
        UPDATE reservations 
        SET rebooking_requested = 1,
            rebooking_new_date = :new_date,
            rebooking_reason = :reason,
            updated_at = NOW()
        WHERE reservation_id = :id
    ");
    
    $stmt->execute([
        ':new_date' => $new_date,
        ':reason' => $reason,
        ':id' => $reservation_id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Rebooking request submitted! Waiting for admin approval.',
        'original_date' => $reservation['check_in_date'],
        'new_date' => $new_date
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
