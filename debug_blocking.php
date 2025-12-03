<?php
require_once 'config/database.php';

$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$stmt = $pdo->query("
    SELECT 
        reservation_id, 
        booking_type, 
        check_in_date, 
        check_out_date,
        DATEDIFF(check_out_date, check_in_date) as days_difference
    FROM reservations 
    WHERE reservation_id = 'RES-20251203-RF8XH'
");

$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Reservation Details:\n";
echo "====================\n";
echo "ID: " . $row['reservation_id'] . "\n";
echo "Type: " . $row['booking_type'] . "\n";
echo "Check-in: " . $row['check_in_date'] . "\n";
echo "Check-out: " . $row['check_out_date'] . "\n";
echo "Days Difference: " . $row['days_difference'] . "\n\n";

echo "Testing blocking logic:\n";
echo "====================\n";
$check_in = new DateTime($row['check_in_date']);
$check_out = new DateTime($row['check_out_date']);

$dates_blocked = [];
$current = clone $check_in;

echo "Using WHILE <= logic:\n";
while ($current <= $check_out) {
    $date_str = $current->format('Y-m-d');
    $dates_blocked[] = $date_str;
    echo "  - Blocking: {$date_str}\n";
    $current->modify('+1 day');
    
    if ($check_in->format('Y-m-d') === $check_out->format('Y-m-d')) {
        echo "  - Breaking (same day booking)\n";
        break;
    }
}

echo "\nTotal dates blocked: " . count($dates_blocked) . "\n";
echo "Dates: " . implode(', ', $dates_blocked) . "\n";
