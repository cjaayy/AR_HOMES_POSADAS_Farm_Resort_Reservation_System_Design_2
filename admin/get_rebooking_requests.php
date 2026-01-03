<?php
// Always return JSON, never HTML
ob_start();
session_start();
header('Content-Type: application/json');

require_once '../config/connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    $status = $_GET['status'] ?? 'pending';
    $sql = "
        SELECT 
            r.reservation_id,
            r.guest_name,
            r.guest_email,
            r.guest_phone,
            r.user_id,
            r.booking_type,
            r.package_type,
            r.check_in_date,
            r.check_out_date,
            r.check_in_time,
            r.check_out_time,
            r.total_amount,
            r.downpayment_amount,
            r.remaining_balance,
            r.status,
            r.rebooking_requested,
            r.rebooking_new_date,
            r.rebooking_reason,
            r.rebooking_approved,
            r.rebooking_approved_by,
            r.rebooking_approved_at,
            r.created_at,
            u.full_name as user_full_name,
            u.email as user_email_account,
            a.full_name as approved_by_name
        FROM reservations r
        LEFT JOIN users u ON r.user_id = u.user_id
        LEFT JOIN admin_users a ON r.rebooking_approved_by = a.admin_id
        WHERE r.rebooking_requested = 1
    ";
    if ($status === 'pending') {
        $sql .= " AND (r.rebooking_approved IS NULL OR r.rebooking_approved = 0)";
    } elseif ($status === 'approved') {
        $sql .= " AND r.rebooking_approved = 1";
    } elseif ($status === 'rejected') {
        $sql .= " AND r.rebooking_approved = -1";
    }
    $sql .= " ORDER BY r.updated_at DESC, r.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get counts for stats
    $countStmt = $conn->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN rebooking_approved IS NULL OR rebooking_approved = 0 THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN rebooking_approved = 1 THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN rebooking_approved = -1 THEN 1 ELSE 0 END) as rejected
        FROM reservations 
        WHERE rebooking_requested = 1
    ");
    $countStmt->execute();
    $counts = $countStmt->fetch(PDO::FETCH_ASSOC);
    // Format data
    foreach ($requests as &$request) {
        $request['check_in_date_formatted'] = date('M d, Y', strtotime($request['check_in_date']));
        $request['rebooking_new_date_formatted'] = date('M d, Y', strtotime($request['rebooking_new_date']));
        $request['rebooking_approved_at_formatted'] = $request['rebooking_approved_at'] 
            ? date('M d, Y g:i A', strtotime($request['rebooking_approved_at'])) 
            : null;
        $booking_labels = [
            'daytime' => 'Daytime',
            'nighttime' => 'Nighttime',
            '22hours' => '22 Hours',
            'venue-daytime' => 'Venue Daytime',
            'venue-nighttime' => 'Venue Nighttime',
            'venue-22hours' => 'Venue 22 Hours'
        ];
        $request['booking_type_label'] = $booking_labels[$request['booking_type']] ?? $request['booking_type'];
    }
    // Clean output buffer (remove any accidental whitespace/errors)
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'counts' => $counts
    ]);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'requests' => [],
        'counts' => []
    ]);
}
        // ...existing code...
