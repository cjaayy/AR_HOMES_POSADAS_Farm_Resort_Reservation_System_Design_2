<?php
require_once '../config/connection.php';
$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT reservation_id, guest_name, package_type, status, total_amount, downpayment_verified, created_at 
        FROM reservations 
        WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
        AND downpayment_verified = 0";

$stmt = $conn->query($sql);
echo "Unpaid/Unverified Reservations (Hidden from Reports):\n";
echo str_repeat('=', 70) . "\n";

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "\nReservation: {$row['reservation_id']}\n";
        echo "Guest: {$row['guest_name']}\n";
        echo "Package: {$row['package_type']}\n";
        echo "Status: {$row['status']}\n";
        echo "Amount: ₱{$row['total_amount']}\n";
        echo "Downpayment Verified: NO\n";
        echo "Created: {$row['created_at']}\n";
        echo "✗ This reservation is HIDDEN from all reports\n";
    }
} else {
    echo "\nNo unpaid/unverified reservations found.\n";
}
?>
