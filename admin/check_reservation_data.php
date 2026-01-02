<?php
require_once '../config/connection.php';
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT 
    reservation_id, 
    package_type,
    booking_type,
    number_of_days,
    number_of_nights,
    check_in_date,
    check_out_date,
    base_price,
    total_amount,
    downpayment_amount,
    remaining_balance,
    downpayment_paid,
    downpayment_verified,
    full_payment_paid,
    full_payment_verified
FROM reservations 
WHERE reservation_id = 'RES-20260102-37J8A'");
$stmt->execute();
$res = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Reservation Details Check\n";
echo str_repeat("=", 70) . "\n\n";

if ($res) {
    echo "Package Type: {$res['package_type']}\n";
    echo "Booking Type: {$res['booking_type']}\n";
    echo "Number of Days: " . ($res['number_of_days'] === null ? 'NULL' : $res['number_of_days']) . "\n";
    echo "Number of Nights: " . ($res['number_of_nights'] === null ? 'NULL' : $res['number_of_nights']) . "\n";
    echo "Check-in: {$res['check_in_date']}\n";
    echo "Check-out: {$res['check_out_date']}\n\n";
    
    echo "Payment Details:\n";
    echo "  Base Price: ₱{$res['base_price']}\n";
    echo "  Total Amount: ₱{$res['total_amount']}\n";
    echo "  Downpayment: ₱{$res['downpayment_amount']}\n";
    echo "  Remaining Balance: ₱{$res['remaining_balance']}\n\n";
    
    echo "Payment Status:\n";
    echo "  Downpayment Paid: " . ($res['downpayment_paid'] ? 'YES' : 'NO') . "\n";
    echo "  Downpayment Verified: " . ($res['downpayment_verified'] ? 'YES' : 'NO') . "\n";
    echo "  Full Payment Paid: " . ($res['full_payment_paid'] ? 'YES' : 'NO') . "\n";
    echo "  Full Payment Verified: " . ($res['full_payment_verified'] ? 'YES' : 'NO') . "\n\n";
    
    echo str_repeat("=", 70) . "\n";
    echo "ISSUES FOUND:\n";
    
    $issues = [];
    
    if ($res['number_of_days'] === null && $res['number_of_nights'] === null) {
        $issues[] = "Both number_of_days and number_of_nights are NULL - this causes 'null Night(s)' display";
    }
    
    if (empty($res['booking_type'])) {
        $issues[] = "booking_type is empty";
    }
    
    if (count($issues) > 0) {
        foreach ($issues as $issue) {
            echo "⚠ {$issue}\n";
        }
    } else {
        echo "✓ No issues found\n";
    }
}
?>
