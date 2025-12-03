<?php
require_once 'config/database.php';

$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
    DB_USER,
    DB_PASS
);

echo "Checking your DAYTIME reservation details:\n";
echo "==========================================\n\n";

$stmt = $pdo->query("
    SELECT 
        reservation_id,
        booking_type,
        check_in_date,
        check_out_date,
        status,
        downpayment_verified,
        full_payment_verified,
        date_locked,
        number_of_days,
        number_of_nights
    FROM reservations 
    WHERE reservation_id = 'RES-20251203-RF8XH'
");

$r = $stmt->fetch(PDO::FETCH_ASSOC);

if ($r) {
    echo "Reservation ID: {$r['reservation_id']}\n";
    echo "Booking Type: {$r['booking_type']}\n";
    echo "Check-in Date: {$r['check_in_date']}\n";
    echo "Check-out Date: {$r['check_out_date']}\n";
    echo "Status: {$r['status']}\n";
    echo "Downpayment Verified: {$r['downpayment_verified']}\n";
    echo "Full Payment Verified: {$r['full_payment_verified']}\n";
    echo "Date Locked: {$r['date_locked']}\n";
    echo "Number of Days: {$r['number_of_days']}\n";
    echo "Number of Nights: {$r['number_of_nights']}\n";
    
    // Calculate what dates SHOULD be blocked
    echo "\n\nDate Blocking Analysis:\n";
    echo "========================\n";
    $check_in = new DateTime($r['check_in_date']);
    $check_out = new DateTime($r['check_out_date']);
    $diff = $check_in->diff($check_out);
    
    echo "Days between check-in and check-out: {$diff->days}\n";
    
    if ($r['check_in_date'] === $r['check_out_date']) {
        echo "✅ Same-day booking (DAYTIME) - Should block ONLY: {$r['check_in_date']}\n";
    } else {
        echo "Spans multiple dates - Should block:\n";
        $current = clone $check_in;
        while ($current <= $check_out) {
            echo "  - " . $current->format('Y-m-d') . "\n";
            $current->modify('+1 day');
        }
    }
} else {
    echo "Reservation not found!\n";
}
