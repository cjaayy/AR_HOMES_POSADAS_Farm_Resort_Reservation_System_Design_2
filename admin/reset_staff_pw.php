<?php
/**
 * Temporary script to reset a staff user's password (CLI use)
 * Usage (CLI): php reset_staff_pw.php
 * WARNING: remove or secure this file after use.
 */
require_once __DIR__ . '/../config/connection.php';

$username = 'cjay1515'; // change if needed
$newPassword = 'TempPass123!';

try {
    $db = new Database();
    $conn = $db->getConnection();
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE admin_users SET password_hash = :ph WHERE username = :u');
    $stmt->bindParam(':ph', $hash);
    $stmt->bindParam(':u', $username);
    $stmt->execute();
    echo "Updated: " . $stmt->rowCount() . " row(s).\n";
    echo "Username: $username\nNew password: $newPassword\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
