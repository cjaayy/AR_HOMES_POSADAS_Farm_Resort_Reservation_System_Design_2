<?php
/**
 * Reset Staff Password - Admin Only
 * Generates a new random password for a staff member
 */
session_start();
header('Content-Type: application/json');

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if user is admin (not staff)
if (($_SESSION['admin_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

require_once '../config/connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $admin_id = (int)($_POST['admin_id'] ?? 0);

    if ($admin_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid staff ID']);
        exit;
    }

    // Check if staff member exists
    $checkStmt = $conn->prepare("SELECT admin_id, full_name, email FROM admin_users WHERE admin_id = :id AND role = 'staff'");
    $checkStmt->bindParam(':id', $admin_id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Staff member not found']);
        exit;
    }

    $staff = $checkStmt->fetch(PDO::FETCH_ASSOC);

    // Generate a random password (8 characters)
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $newPassword = '';
    for ($i = 0; $i < 8; $i++) {
        $newPassword .= $chars[random_int(0, strlen($chars) - 1)];
    }

    // Hash the new password
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password
    $stmt = $conn->prepare("UPDATE admin_users SET password_hash = :password_hash WHERE admin_id = :id");
    $stmt->bindParam(':password_hash', $passwordHash);
    $stmt->bindParam(':id', $admin_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'success' => true, 
        'message' => 'Password reset successfully',
        'new_password' => $newPassword,
        'staff_name' => $staff['full_name']
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
