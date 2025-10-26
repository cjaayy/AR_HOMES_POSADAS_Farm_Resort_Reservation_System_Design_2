<?php
/**
 * Staff actions for reservations: create, update_status
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

    $action = $_POST['action'] ?? null;
    if ($action === 'create') {
        $guest_name = trim($_POST['guest_name'] ?? '');
        $guest_phone = trim($_POST['guest_phone'] ?? '');
        $guest_email = trim($_POST['guest_email'] ?? '');
        $room = trim($_POST['room'] ?? '');
        $check_in = $_POST['check_in_date'] ?? null;
        $check_out = $_POST['check_out_date'] ?? null;

        if ($guest_name === '') {
            echo json_encode(['success' => false, 'message' => 'Guest name required']); exit;
        }

        $stmt = $conn->prepare("INSERT INTO reservations (guest_name, guest_phone, guest_email, room, check_in_date, check_out_date, status, created_at) VALUES (:g, :p, :e, :r, :ci, :co, 'pending', NOW())");
        $stmt->bindParam(':g', $guest_name);
        $stmt->bindParam(':p', $guest_phone);
        $stmt->bindParam(':e', $guest_email);
        $stmt->bindParam(':r', $room);
        $stmt->bindParam(':ci', $check_in);
        $stmt->bindParam(':co', $check_out);
        $stmt->execute();

        echo json_encode(['success' => true, 'reservation_id' => $conn->lastInsertId()]);
        exit;
    } elseif ($action === 'update_status') {
        $id = (int)($_POST['reservation_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if ($id <= 0 || $status === '') { echo json_encode(['success'=>false,'message'=>'Invalid']); exit; }
        $stmt = $conn->prepare("UPDATE reservations SET status = :s WHERE reservation_id = :id");
        $stmt->bindParam(':s', $status);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(['success' => true]); exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}

?>
