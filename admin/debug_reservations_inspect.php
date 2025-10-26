<?php
/**
 * Temporary debug endpoint to inspect reservations table counts and sample rows.
 * Usage (from localhost): /admin/debug_reservations_inspect.php?debug=1
 * This file is safe for local debugging only and will not run unless accessed from localhost with debug=1.
 */
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

if (!isset($_GET['debug']) || $_GET['debug'] != '1') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing debug flag']);
    exit;
}

header('Content-Type: application/json');
require_once __DIR__ . '/..//config/connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Check table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'reservations'");
    if ($tableCheck->rowCount() === 0) {
        echo json_encode(['success' => true, 'message' => 'reservations table not found', 'counts' => [], 'samples' => []]);
        exit;
    }

    $counts = [];
    $q = $conn->query("SELECT COUNT(*) AS total FROM reservations");
    $counts['total'] = (int)$q->fetch(PDO::FETCH_ASSOC)['total'];

    $q = $conn->query("SELECT COUNT(*) AS total FROM reservations WHERE DATE(created_at) = CURDATE()");
    $counts['created_today'] = (int)$q->fetch(PDO::FETCH_ASSOC)['total'];

    $q = $conn->query("SELECT COUNT(*) AS total FROM reservations WHERE status = 'pending'");
    $counts['pending'] = (int)$q->fetch(PDO::FETCH_ASSOC)['total'];

    $q = $conn->query("SELECT COUNT(*) AS total FROM reservations WHERE DATE(check_in_date) = CURDATE()");
    $counts['arrivals_today'] = (int)$q->fetch(PDO::FETCH_ASSOC)['total'];

    $q = $conn->query("SELECT COUNT(*) AS total FROM reservations WHERE DATE(check_out_date) = CURDATE()");
    $counts['checkouts_today'] = (int)$q->fetch(PDO::FETCH_ASSOC)['total'];

    // earliest and latest
    $q = $conn->query("SELECT MIN(created_at) AS first, MAX(created_at) AS last FROM reservations");
    $r = $q->fetch(PDO::FETCH_ASSOC);
    $counts['first_created_at'] = $r['first'];
    $counts['last_created_at'] = $r['last'];

    // sample rows
    $stmt = $conn->prepare("SELECT reservation_id, guest_name, guest_email, guest_phone, room, check_in_date, check_out_date, status, created_at FROM reservations ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'counts' => $counts, 'samples' => $samples]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}

?>
