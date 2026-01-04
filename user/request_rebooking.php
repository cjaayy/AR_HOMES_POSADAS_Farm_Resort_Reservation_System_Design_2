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
    $check_in->setTime(0, 0, 0);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    // Calculate days until check-in (positive = future, negative = past)
    $interval = $today->diff($check_in);
    $days_until_checkin = $interval->days * ($interval->invert ? -1 : 1);
    
    // Check if check-in is in the past
    if ($days_until_checkin < 0) {
        throw new Exception('Cannot rebook a reservation with a past check-in date.');
    }
    
    // Check if within 7-day restriction
    if ($days_until_checkin < 7) {
        throw new Exception('Rebooking is only allowed 7 days or more before check-in date. You have ' . $days_until_checkin . ' days remaining.');
    }
    
    // Check if downpayment paid (downpayment is non-refundable, so rebooking is allowed)
    if ($reservation['downpayment_verified'] != 1) {
        throw new Exception('Rebooking is only available once downpayment is paid and verified');
    }
    
    // Check if status allows rebooking (pending_confirmation, confirmed, or rebooked)
    if (!in_array($reservation['status'], ['pending_confirmation', 'confirmed', 'rebooked'])) {
        throw new Exception('Rebooking is not available for this reservation status');
    }
    
    // Check if already requested rebooking (allow re-request if previously rejected)
    if ($reservation['rebooking_requested'] == 1 && $reservation['rebooking_approved'] != -1) {
        if ($reservation['rebooking_approved'] == 1) {
            throw new Exception('Your rebooking request has already been approved');
        }
        throw new Exception('Rebooking has already been requested for this reservation. Please wait for admin approval.');
    }
    
    // Validate new date is within 3 months from original date
    $original_date = new DateTime($reservation['check_in_date']);
    $new_date_obj = DateTime::createFromFormat('Y-m-d', $new_date);
    
    if (!$new_date_obj) {
        throw new Exception('Invalid date format');
    }
    
    $three_months_later = clone $original_date;
    $three_months_later->modify('+3 months');
    
    if ($new_date_obj > $three_months_later) {
        throw new Exception('New date must be within 3 months of original check-in date (' . $original_date->format('M d, Y') . ' - ' . $three_months_later->format('M d, Y') . ')');
    }
    
    // Validate new date is in the future
    if ($new_date_obj < $today) {
        throw new Exception('New date must be in the future');
    }
    
    // Check if new date is available (cross-package blocking)
    // Calculate check_out_date for new booking
    $new_check_out = $new_date;
    if ($reservation['booking_type'] === 'nighttime' || $reservation['booking_type'] === '22hours') {
        $new_check_out = date('Y-m-d', strtotime($new_date . ' +1 day'));
    }
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM reservations 
        WHERE status IN ('confirmed', 'checked_in', 'rebooked')
        AND reservation_id != ?
        AND (
                (check_in_date <= ? AND check_out_date >= ?)
                OR (check_in_date <= ? AND check_out_date >= ?)
                OR (check_in_date >= ? AND check_out_date <= ?)
        )
    ");
    $stmt->execute([
        $reservation_id,
        $new_date,
        $new_date,
        $new_check_out,
        $new_check_out,
        $new_date,
        $new_check_out
    ]);
    
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        throw new Exception('The selected date is not available. Please choose another date.');
    }
    
    // Submit rebooking request (original date remains occupied until admin approval)
    // Reset rebooking_approved to NULL for new/re-submitted requests
    $stmt = $pdo->prepare("
        UPDATE reservations 
        SET rebooking_requested = 1,
            rebooking_new_date = :new_date,
            rebooking_reason = :reason,
            rebooking_approved = NULL,
            rebooking_approved_by = NULL,
            rebooking_approved_at = NULL,
            updated_at = NOW()
        WHERE reservation_id = :reservation_id
    ");
        // Debug: log parameters to file
            // Ensure debug_logs directory exists and use correct path for debug log
            $debugDir = realpath(__DIR__ . '/../debug_logs') ?: (__DIR__ . '/../debug_logs');
            if (!is_dir($debugDir)) {
                mkdir($debugDir, 0755, true);
            }
            file_put_contents($debugDir . '/rebooking_debug.log', "UPDATE params: " . json_encode([
                ':new_date' => $new_date,
                ':reason' => $reason,
                ':reservation_id' => $reservation_id
            ]) . "\n", FILE_APPEND);
        $stmt->execute([
            ':new_date' => $new_date,
            ':reason' => $reason,
            ':reservation_id' => $reservation_id
        ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Rebooking request submitted successfully! Your original date will be released once admin approves your request.',
        'original_date' => $reservation['check_in_date'],
        'new_date' => $new_date,
        'note' => 'The new date will be confirmed by admin within 24-48 hours.'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    // Log error details for debugging
        // Ensure debug_logs directory exists and use correct path for debug log
        $debugDir = realpath(__DIR__ . '/../debug_logs') ?: (__DIR__ . '/../debug_logs');
        if (!is_dir($debugDir)) {
            mkdir($debugDir, 0755, true);
        }
        file_put_contents($debugDir . '/rebooking_debug.log', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
