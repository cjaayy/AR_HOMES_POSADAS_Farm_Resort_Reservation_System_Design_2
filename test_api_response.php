<?php
session_start();
header('Content-Type: application/json');

require_once 'config/database.php';

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

echo "Testing API Response:\n\n";

// Simulate what the API returns for each booking type
$booking_types = ['daytime', 'nighttime', '22hours'];

foreach ($booking_types as $type) {
    echo "Booking Type: {$type}\n";
    echo "-------------------------\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            reservation_id, 
            guest_name, 
            booking_type,
            check_in_date, 
            check_out_date,
            status, 
            downpayment_verified, 
            date_locked
        FROM reservations 
        WHERE status IN ('confirmed', 'checked_in', 'checked_out', 'pending_confirmation')
        AND (downpayment_verified = 1 OR date_locked = 1)
        AND check_out_date >= CURDATE()
        ORDER BY check_in_date ASC
    ");
    
    $stmt->execute();
    
    $unavailable_dates = [];
    
    while ($row = $stmt->fetch()) {
        $check_in = new DateTime($row['check_in_date']);
        $check_out = new DateTime($row['check_out_date']);
        
        $current = clone $check_in;
        while ($current <= $check_out) {
            $date_str = $current->format('Y-m-d');
            if (!in_array($date_str, $unavailable_dates)) {
                $unavailable_dates[] = $date_str;
            }
            $current->modify('+1 day');
            
            if ($check_in->format('Y-m-d') === $check_out->format('Y-m-d')) {
                break;
            }
        }
    }
    
    sort($unavailable_dates);
    
    echo "Unavailable dates: " . json_encode($unavailable_dates) . "\n";
    echo "Count: " . count($unavailable_dates) . "\n\n";
}
