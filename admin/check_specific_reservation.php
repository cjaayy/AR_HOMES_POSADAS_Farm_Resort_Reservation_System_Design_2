<?php
require_once '../config/connection.php';
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT 
    reservation_id, 
    total_amount, 
    downpayment_amount, 
    remaining_balance, 
    downpayment_paid, 
    downpayment_verified,
    full_payment_paid, 
    full_payment_verified, 
    status,
    downpayment_paid_at,
    full_payment_paid_at
FROM reservations 
WHERE reservation_id = 'RES-20260102-37J8A'");
$stmt->execute();
$res = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Reservation Payment Status for RES-20260102-37J8A\n";
echo str_repeat("=", 70) . "\n\n";

if ($res) {
    echo "Total Amount: ₱{$res['total_amount']}\n";
    echo "Downpayment Amount: ₱{$res['downpayment_amount']}\n";
    echo "Remaining Balance: ₱{$res['remaining_balance']}\n\n";
    
    echo "Payment Status:\n";
    echo "  Downpayment Paid: " . ($res['downpayment_paid'] ? 'YES' : 'NO') . "\n";
    echo "  Downpayment Verified: " . ($res['downpayment_verified'] ? 'YES' : 'NO') . "\n";
    echo "  Downpayment Paid At: " . ($res['downpayment_paid_at'] ?? 'N/A') . "\n\n";
    
    echo "  Full Payment Paid: " . ($res['full_payment_paid'] ? 'YES' : 'NO') . "\n";
    echo "  Full Payment Verified: " . ($res['full_payment_verified'] ? 'YES' : 'NO') . "\n";
    echo "  Full Payment Paid At: " . ($res['full_payment_paid_at'] ?? 'N/A') . "\n\n";
    
    echo "Reservation Status: {$res['status']}\n\n";
    
    echo str_repeat("=", 70) . "\n";
    echo "ACTUAL STATUS:\n";
    echo str_repeat("=", 70) . "\n";
    
    $fullyPaid = $res['downpayment_verified'] && $res['full_payment_verified'];
    $onlyDownpayment = $res['downpayment_verified'] && !$res['full_payment_verified'];
    
    if ($fullyPaid) {
        echo "✓ FULLY PAID - Both downpayment and remaining balance verified\n";
    } elseif ($onlyDownpayment) {
        echo "⚠ PARTIALLY PAID - Only downpayment verified\n";
        echo "✗ Remaining Balance: NOT PAID (₱{$res['remaining_balance']})\n";
    } else {
        echo "✗ NOT PAID - Downpayment not yet verified\n";
    }
} else {
    echo "Reservation not found!\n";
}
?>
