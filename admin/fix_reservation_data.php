<?php
require_once '../config/connection.php';
$db = new Database();
$conn = $db->getConnection();

// Fix the reservation with empty booking_type
$stmt = $conn->prepare("UPDATE reservations SET booking_type = 'daytime' WHERE reservation_id = 'RES-20260102-37J8A'");
$stmt->execute();

echo "Updated booking_type to 'daytime' for RES-20260102-37J8A\n";

// Verify
$stmt = $conn->prepare("SELECT booking_type, number_of_days, number_of_nights FROM reservations WHERE reservation_id = 'RES-20260102-37J8A'");
$stmt->execute();
$res = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nVerification:\n";
echo "  booking_type: {$res['booking_type']}\n";
echo "  number_of_days: {$res['number_of_days']}\n";
echo "  number_of_nights: " . ($res['number_of_nights'] ?? 'NULL') . "\n";
?>
