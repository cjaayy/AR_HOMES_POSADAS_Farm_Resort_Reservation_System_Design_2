<?php
require_once 'config/database.php';

echo "Checking reservations for December 4, 2025...\n\n";

// Create PDO connection
$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
    DB_USER,
    DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);

$stmt = $pdo->query("
    SELECT 
        reservation_id, 
        booking_type, 
        check_in_date, 
        check_out_date, 
        status, 
        downpayment_verified,
        full_payment_verified,
        date_locked
    FROM reservations 
    WHERE check_in_date = '2025-12-04' 
    OR check_out_date = '2025-12-04'
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Reservation ID: " . $row['reservation_id'] . "\n";
    echo "Booking Type: " . $row['booking_type'] . "\n";
    echo "Check-in: " . $row['check_in_date'] . "\n";
    echo "Check-out: " . $row['check_out_date'] . "\n";
    echo "Status: " . $row['status'] . "\n";
    echo "Downpayment Verified: " . $row['downpayment_verified'] . "\n";
    echo "Full Payment Verified: " . $row['full_payment_verified'] . "\n";
    echo "Date Locked: " . $row['date_locked'] . "\n";
    echo "---\n\n";
}

echo "\nChecking what get_unavailable_dates.php returns for DAYTIME:\n\n";

// Simulate the query from get_unavailable_dates.php
$stmt = $pdo->prepare("
    SELECT 
        reservation_id, 
        guest_name, 
        check_in_date, 
        check_out_date,
        status, 
        downpayment_verified, 
        date_locked
    FROM reservations 
    WHERE booking_type = 'daytime'
    AND status IN ('confirmed', 'checked_in', 'checked_out', 'pending_confirmation')
    AND (downpayment_verified = 1 OR date_locked = 1)
    AND check_out_date >= CURDATE()
    ORDER BY check_in_date ASC
");

$stmt->execute();

$unavailable_dates = [];

while ($row = $stmt->fetch()) {
    echo "Found reservation: {$row['reservation_id']}\n";
    echo "  Check-in: {$row['check_in_date']}\n";
    echo "  Check-out: {$row['check_out_date']}\n";
    echo "  Status: {$row['status']}\n";
    echo "  Downpayment Verified: {$row['downpayment_verified']}\n";
    echo "  Date Locked: {$row['date_locked']}\n";
    
    $check_in = new DateTime($row['check_in_date']);
    $check_out = new DateTime($row['check_out_date']);
    
    $current = clone $check_in;
    while ($current <= $check_out) {
        $date_str = $current->format('Y-m-d');
        if (!in_array($date_str, $unavailable_dates)) {
            $unavailable_dates[] = $date_str;
        }
        $current->modify('+1 day');
        
        // Break after first iteration if check_in equals check_out (single day booking)
        if ($check_in->format('Y-m-d') === $check_out->format('Y-m-d')) {
            break;
        }
    }
    echo "  Dates blocked: " . implode(', ', $unavailable_dates) . "\n\n";
}

echo "\nFinal unavailable dates for DAYTIME: " . implode(', ', $unavailable_dates) . "\n";
