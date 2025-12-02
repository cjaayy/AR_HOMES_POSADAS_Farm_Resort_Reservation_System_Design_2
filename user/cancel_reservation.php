<?php
/**
 * User: Cancel Reservation
 * User can cancel their booking (no refund for downpayment)
 */

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $reservation_id = $data['reservation_id'] ?? null;
    $reason = $data['reason'] ?? '';
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get reservation details
    $stmt = $conn->prepare("
        SELECT * FROM reservations 
        WHERE reservation_id = :id 
        AND user_id = :user_id
    ");
    $stmt->execute([
        ':id' => $reservation_id,
        ':user_id' => $user_id
    ]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        throw new Exception('Reservation not found');
    }
    
    // Check if reservation can be cancelled
    if (!in_array($reservation['status'], ['pending_payment', 'pending_confirmation', 'confirmed'])) {
        throw new Exception('This reservation cannot be cancelled');
    }
    
    if ($reservation['checked_in'] == 1) {
        throw new Exception('Cannot cancel a reservation after check-in');
    }
    
    // Cancel reservation: no refund policy
    $cancellation_notes = "User cancelled reservation. Reason: " . $reason;
    if ($reservation['downpayment_paid'] == 1) {
        $cancellation_notes .= " | No refund for downpayment (₱" . $reservation['downpayment_amount'] . ")";
    }
    
    $stmt = $conn->prepare("
        UPDATE reservations 
        SET status = 'cancelled',
            date_locked = 0,
            locked_until = NULL,
            admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n[', NOW(), '] ', :notes),
            updated_at = NOW()
        WHERE reservation_id = :id
    ");
    
    $stmt->bindParam(':notes', $cancellation_notes, PDO::PARAM_STR);
    $stmt->bindParam(':id', $reservation_id);
    $stmt->execute();
    
    $message = 'Reservation cancelled successfully.';
    if ($reservation['downpayment_paid'] == 1) {
        $message .= ' Please note: Downpayment is non-refundable as per booking policy.';
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'downpayment_forfeited' => $reservation['downpayment_paid'] == 1,
        'downpayment_amount' => $reservation['downpayment_amount']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
