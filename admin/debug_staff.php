<?php
/**
 * Temporary debug script: list recent staff accounts
 * Remove this file after debugging.
 */
require_once __DIR__ . '/../config/connection.php';
header('Content-Type: application/json');
try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT admin_id, username, email, role, is_active, position, created_at, last_login, password_hash FROM admin_users WHERE role = 'staff' ORDER BY created_at DESC LIMIT 20");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'count'=>count($rows),'rows'=>$rows], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

?>
