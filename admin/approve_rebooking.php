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
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
    $admin_id = $_SESSION['admin_id'] ?? 0;
    
    if ($action === 'approve') {
        // Get rebooking details
        $stmt = $conn->prepare("SELECT rebooking_new_date, check_in_date, booking_type FROM reservations WHERE reservation_id = :id");
        $stmt->execute([':id' => $reservation_id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reservation) {
            throw new Exception('Reservation not found');
        }
        
        // Check if new date is still available
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count FROM reservations 
            WHERE check_in_date = :date 
            AND booking_type = :type
            AND status IN ('confirmed', 'checked_in')
            AND date_locked = 1
            AND reservation_id != :id
        ");
        $stmt->execute([
            ':date' => $reservation['rebooking_new_date'],
            ':type' => $reservation['booking_type'],
            ':id' => $reservation_id
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            throw new Exception('New date is no longer available');
        }
        
        // Approve rebooking: Update date, keep payment, lock new date
        $stmt = $conn->prepare("
            UPDATE reservations 
            SET check_in_date = rebooking_new_date,
                rebooking_approved = 1,
                rebooking_approved_by = :admin_id,
                rebooking_approved_at = NOW(),
                date_locked = 1,
                locked_until = DATE_ADD(rebooking_new_date, INTERVAL 1 DAY),
                status = 'rebooked',
                updated_at = NOW()
            WHERE reservation_id = :id
        ");
        
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Rebooking approved! Date updated successfully.',
            'new_date' => $reservation['rebooking_new_date']
        ]);
        
    } else {
        // Reject rebooking
        $stmt = $conn->prepare("
            UPDATE reservations 
            SET rebooking_requested = 0,
                rebooking_new_date = NULL,
                rebooking_reason = NULL,
                admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n[', NOW(), '] Rebooking request rejected'),
                updated_at = NOW()
            WHERE reservation_id = :id
        ");
        
        $stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Rebooking request rejected'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
