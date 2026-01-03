<?php
/**
 * Staff Reservation Actions API - Approve, Cancel reservations
 * Staff can approve and cancel reservations but cannot modify user accounts
 */
session_start();
header('Content-Type: application/json');

// Accept both admin session (with staff role) OR staff-specific session
$isAdminAsStaff = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && ($_SESSION['admin_role'] ?? '') === 'staff';
$isStaffSession = isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in'] === true;

if (!$isAdminAsStaff && !$isStaffSession) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get staff ID and name from appropriate session
    $staffId = $isStaffSession ? ($_SESSION['staff_id'] ?? 0) : ($_SESSION['admin_id'] ?? 0);
    $staffName = $isStaffSession ? ($_SESSION['staff_full_name'] ?? 'Staff Member') : ($_SESSION['admin_full_name'] ?? 'Staff Member');
    
    $action = $_POST['action'] ?? '';
    $reservationId = $_POST['reservation_id'] ?? '';
    
    if (empty($reservationId)) {
        echo json_encode(['success' => false, 'message' => 'Reservation ID is required']);
        exit;
    }
    
    switch ($action) {
        case 'approve':
            // Check if reservation exists and is in pending status
            $checkStmt = $conn->prepare("SELECT * FROM reservations WHERE reservation_id = :id");
            $checkStmt->execute([':id' => $reservationId]);
            $reservation = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reservation) {
                echo json_encode(['success' => false, 'message' => 'Reservation not found']);
                exit;
            }
            
            $allowedStatuses = ['pending', 'pending_payment', 'pending_confirmation'];
            if (!in_array($reservation['status'], $allowedStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Only pending reservations can be approved']);
                exit;
            }
            
            // Update reservation status to confirmed
            $updateStmt = $conn->prepare("UPDATE reservations SET status = 'confirmed', updated_at = NOW() WHERE reservation_id = :id");
            $updateStmt->execute([':id' => $reservationId]);
            
            // Log the action
            logStaffActivity($conn, $staffId, $staffName, 'reservation_approved', "Approved reservation #{$reservationId}", $reservationId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Reservation approved successfully'
            ]);
            break;
            
        case 'cancel':
            // Check if reservation exists
            $checkStmt = $conn->prepare("SELECT * FROM reservations WHERE reservation_id = :id");
            $checkStmt->execute([':id' => $reservationId]);
            $reservation = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reservation) {
                echo json_encode(['success' => false, 'message' => 'Reservation not found']);
                exit;
            }
            
            $allowedStatuses = ['pending', 'pending_payment', 'pending_confirmation', 'confirmed'];
            if (!in_array($reservation['status'], $allowedStatuses)) {
                echo json_encode(['success' => false, 'message' => 'This reservation cannot be canceled']);
                exit;
            }
            
            // Update reservation status to canceled
            $updateStmt = $conn->prepare("UPDATE reservations SET status = 'canceled', updated_at = NOW() WHERE reservation_id = :id");
            $updateStmt->execute([':id' => $reservationId]);
            
            // Log the action
            logStaffActivity($conn, $staffId, $staffName, 'reservation_canceled', "Canceled reservation #{$reservationId}", $reservationId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Reservation canceled successfully'
            ]);
            break;
            
        case 'view':
            // Get reservation details (read-only)
            $stmt = $conn->prepare("SELECT r.*, u.full_name as guest_name, u.email as guest_email, u.phone as guest_phone 
                                    FROM reservations r 
                                    LEFT JOIN users u ON r.user_id = u.id 
                                    WHERE r.reservation_id = :id");
            $stmt->execute([':id' => $reservationId]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reservation) {
                echo json_encode(['success' => false, 'message' => 'Reservation not found']);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'reservation' => $reservation
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (PDOException $e) {
    error_log("Staff reservation action error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}

/**
 * Log staff activity for audit purposes
 */
function logStaffActivity($conn, $staffId, $staffName, $actionType, $description, $referenceId = null) {
    try {
        // Check if staff_activity_log table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'staff_activity_log'");
        if ($tableCheck->rowCount() > 0) {
            $sql = "INSERT INTO staff_activity_log (staff_id, staff_name, action_type, description, reference_id, created_at) 
                    VALUES (:staff_id, :staff_name, :action_type, :description, :reference_id, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':staff_id' => $staffId,
                ':staff_name' => $staffName,
                ':action_type' => $actionType,
                ':description' => $description,
                ':reference_id' => $referenceId
            ]);
        }
    } catch (Exception $e) {
        // Silently fail - logging shouldn't break the main action
        error_log("Failed to log staff activity: " . $e->getMessage());
    }
}
?>
