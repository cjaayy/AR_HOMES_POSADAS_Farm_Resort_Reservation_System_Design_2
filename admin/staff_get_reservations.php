<?php
/**
 * Staff - Get Reservations (JSON)
 * Accepts optional GET params: status, limit, offset
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || ($_SESSION['admin_role'] ?? '') !== 'staff') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $status = isset($_GET['status']) ? trim($_GET['status']) : null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    $results = [];

    // Table existence
    $tableCheck = $conn->query("SHOW TABLES LIKE 'reservations'");
    if ($tableCheck->rowCount() === 0) {
        echo json_encode(['success' => true, 'reservations' => []]);
        exit;
    }

    $sql = "SELECT 
                reservation_id, 
                user_id,
                guest_name, 
                guest_email, 
                guest_phone, 
                room,
                booking_type,
                package_type,
                check_in_date, 
                check_in_time,
                check_out_date, 
                check_out_time,
                number_of_days,
                number_of_nights,
                total_amount,
                downpayment_amount,
                downpayment_paid,
                full_payment_paid,
                payment_method,
                security_bond_paid,
                status, 
                created_at 
            FROM reservations";
    $params = [];
    if ($status) {
        $sql .= " WHERE status = :status";
        $params[':status'] = $status;
    }
    $sql .= " ORDER BY created_at DESC LIMIT :offset, :limit";

    $stmt = $conn->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'reservations' => $rows]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}

?>
