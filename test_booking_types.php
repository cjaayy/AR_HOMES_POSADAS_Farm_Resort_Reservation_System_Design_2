<?php
require_once 'config/database.php';

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

echo "Testing unavailable dates for each booking type:\n";
echo "=================================================\n\n";

$booking_types = ['daytime', 'nighttime', '22hours'];

foreach ($booking_types as $type) {
    echo "Booking Type: " . strtoupper($type) . "\n";
    echo "-------------------\n";
    
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
        WHERE booking_type = :type
        AND status IN ('confirmed', 'checked_in', 'checked_out', 'pending_confirmation')
        AND (downpayment_verified = 1 OR date_locked = 1)
        AND check_out_date >= CURDATE()
        ORDER BY check_in_date ASC
    ");
    
    $stmt->execute([':type' => $type]);
    
    $unavailable_dates = [];
    $reservations = $stmt->fetchAll();
    
    if (count($reservations) === 0) {
        echo "  No confirmed reservations found.\n\n";
        continue;
    }
    
    foreach ($reservations as $row) {
        echo "  Reservation: {$row['reservation_id']}\n";
        echo "  Guest: {$row['guest_name']}\n";
        echo "  Check-in: {$row['check_in_date']}\n";
        echo "  Check-out: {$row['check_out_date']}\n";
        
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
        
        echo "  Dates blocked: " . implode(', ', $unavailable_dates) . "\n\n";
    }
    
    echo "  TOTAL unavailable dates for {$type}: " . implode(', ', $unavailable_dates) . "\n";
    echo "\n\n";
}
