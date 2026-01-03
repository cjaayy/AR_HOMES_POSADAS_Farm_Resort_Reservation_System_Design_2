<?php
/**
 * Admin: Approve Rebooking
 * Staff approves or rejects rebooking requests
 */

session_start();
header('Content-Type: application/json');

require_once '../config/connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
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
    $db = new Database();
    $conn = $db->getConnection();
    
    $reservation_id = $_POST['reservation_id'] ?? null;
    $action = $_POST['action'] ?? 'approve';
    $rejection_reason = $_POST['reason'] ?? '';
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
    $admin_id = $_SESSION['admin_id'] ?? 0;
    $admin_name = $_SESSION['admin_full_name'] ?? $_SESSION['admin_username'] ?? 'Admin';
    
    if ($action === 'approve') {
        // Get rebooking details
        $stmt = $conn->prepare("
            SELECT r.*, u.email as user_email, u.full_name as user_name 
            FROM reservations r 
            LEFT JOIN users u ON r.user_id = u.user_id 
            WHERE r.reservation_id = :id
        ");
        $stmt->execute([':id' => $reservation_id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reservation) {
            throw new Exception('Reservation not found');
        }
        
        if ($reservation['rebooking_requested'] != 1) {
            throw new Exception('No rebooking request found for this reservation');
        }
        
        if (!$reservation['rebooking_new_date']) {
            throw new Exception('Rebooking new date is not set');
        }
        
        // Check if new date is still available (cross-booking type check for exclusive resort access)
        $new_check_out = $reservation['rebooking_new_date'];
        if ($reservation['booking_type'] === 'nighttime' || $reservation['booking_type'] === '22hours') {
            $new_check_out = date('Y-m-d', strtotime($reservation['rebooking_new_date'] . ' +1 day'));
        }
        
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count FROM reservations 
            WHERE status IN ('confirmed', 'checked_in', 'pending_confirmation')
            AND (downpayment_verified = 1 OR date_locked = 1)
            AND reservation_id != :id
            AND (
                (check_in_date <= :new_date AND check_out_date >= :new_date)
                OR (check_in_date <= :new_check_out AND check_out_date >= :new_check_out)
                OR (check_in_date >= :new_date AND check_out_date <= :new_check_out)
            )
        ");
        $stmt->execute([
            ':new_date' => $reservation['rebooking_new_date'],
            ':new_check_out' => $new_check_out,
            ':id' => $reservation_id
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            throw new Exception('The requested date is no longer available. Another reservation has been made for that date.');
        }
        
        // Calculate check_out_date based on booking type
        $check_out_date = $reservation['rebooking_new_date'];
        if ($reservation['booking_type'] === 'nighttime' || $reservation['booking_type'] === '22hours') {
            $check_out_date = date('Y-m-d', strtotime($reservation['rebooking_new_date'] . ' +1 day'));
        }
        
        // Store original date for reference
        $original_date = $reservation['check_in_date'];
        
        // Approve rebooking: Update dates, keep payment status, lock new date
        // Old date automatically becomes available for other users
        $stmt = $conn->prepare("
            UPDATE reservations 
            SET check_in_date = rebooking_new_date,
                check_out_date = :check_out_date,
                rebooking_approved = 1,
                rebooking_approved_by = :admin_id,
                rebooking_approved_at = NOW(),
                date_locked = 1,
                locked_until = DATE_ADD(rebooking_new_date, INTERVAL 1 DAY),
                status = 'confirmed',
                admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n[', NOW(), '] Rebooking approved by ', :admin_name, '. Original date: ', :original_date, ' → New date: ', rebooking_new_date),
                updated_at = NOW()
            WHERE reservation_id = :id
        ");
        
        $stmt->execute([
            ':check_out_date' => $check_out_date,
            ':admin_id' => $admin_id,
            ':admin_name' => $admin_name,
            ':original_date' => $original_date,
            ':id' => $reservation_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Rebooking approved! Reservation updated from ' . date('M d, Y', strtotime($original_date)) . ' to ' . date('M d, Y', strtotime($reservation['rebooking_new_date'])) . '.',
            'original_date' => $original_date,
            'new_date' => $reservation['rebooking_new_date']
        ]);
        
    } else {
        // Reject rebooking (set rebooking_approved to -1 to indicate rejection)
        $rejection_note = $rejection_reason ? " Reason: $rejection_reason" : "";
        
        $stmt = $conn->prepare("
            UPDATE reservations 
            SET rebooking_approved = -1,
                rebooking_approved_by = :admin_id,
                rebooking_approved_at = NOW(),
                admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n[', NOW(), '] Rebooking request rejected by ', :admin_name, '.', :reason),
                updated_at = NOW()
            WHERE reservation_id = :id
        ");
        
        $stmt->execute([
            ':admin_id' => $admin_id,
            ':admin_name' => $admin_name,
            ':reason' => $rejection_note,
            ':id' => $reservation_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Rebooking request has been rejected. The guest will be notified.'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
