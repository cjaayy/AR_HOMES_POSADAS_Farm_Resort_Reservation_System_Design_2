<?php
require_once '../config/connection.php';

$db = new Database();
$conn = $db->getConnection();

echo "Package-Based Venue Occupancy Analysis\n";
echo str_repeat("=", 60) . "\n\n";

// Current month stats
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');
$totalDaysInMonth = (int)date('t');

echo "Period: " . date('F Y') . " ({$totalDaysInMonth} days)\n";
echo str_repeat("-", 60) . "\n\n";

// Count booked days
$bookedDaysSql = "SELECT 
    COUNT(DISTINCT check_in_date) as booked_days,
    COUNT(*) as total_bookings
FROM reservations 
WHERE status IN ('confirmed', 'checked_in', 'completed')
  AND check_in_date BETWEEN :start AND :end";

$stmt = $conn->prepare($bookedDaysSql);
$stmt->execute([':start' => $monthStart, ':end' => $monthEnd]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$bookedDays = (int)$data['booked_days'];
$totalBookings = (int)$data['total_bookings'];
$availableDays = $totalDaysInMonth - $bookedDays;
$occupancyRate = $totalDaysInMonth > 0 ? round(($bookedDays / $totalDaysInMonth) * 100) : 0;

echo "Total Days in Month: {$totalDaysInMonth}\n";
echo "Days with Bookings: {$bookedDays}\n";
echo "Available Days: {$availableDays}\n";
echo "Total Bookings: {$totalBookings}\n";
echo "Venue Occupancy Rate: {$occupancyRate}%\n\n";

// Show bookings by date
echo "Bookings Breakdown:\n";
echo str_repeat("-", 60) . "\n";

$detailSql = "SELECT 
    check_in_date,
    COUNT(*) as booking_count,
    GROUP_CONCAT(CONCAT(guest_name, ' (', package_type, ')') SEPARATOR '; ') as bookings
FROM reservations 
WHERE status IN ('confirmed', 'checked_in', 'completed')
  AND check_in_date BETWEEN :start AND :end
GROUP BY check_in_date
ORDER BY check_in_date";

$detailStmt = $conn->prepare($detailSql);
$detailStmt->execute([':start' => $monthStart, ':end' => $monthEnd]);

while ($row = $detailStmt->fetch(PDO::FETCH_ASSOC)) {
    echo date('D, M d', strtotime($row['check_in_date'])) . ": ";
    echo $row['booking_count'] . " booking(s)\n";
    echo "  → " . substr($row['bookings'], 0, 100) . (strlen($row['bookings']) > 100 ? '...' : '') . "\n";
}

// Today's status
echo "\n" . str_repeat("=", 60) . "\n";
echo "TODAY'S STATUS (" . date('l, F j, Y') . ")\n";
echo str_repeat("=", 60) . "\n";

$todaySql = "SELECT 
    reservation_id,
    guest_name,
    package_type,
    check_in_time,
    check_out_time,
    number_of_guests,
    status
FROM reservations 
WHERE CURDATE() BETWEEN check_in_date AND check_out_date
  AND status IN ('confirmed', 'checked_in')
ORDER BY check_in_time";

$todayStmt = $conn->query($todaySql);
$todayBookings = $todayStmt->fetchAll(PDO::FETCH_ASSOC);

if (count($todayBookings) > 0) {
    echo "\nActive Bookings Today: " . count($todayBookings) . "\n\n";
    foreach ($todayBookings as $booking) {
        echo "• {$booking['guest_name']}\n";
        echo "  Package: {$booking['package_type']}\n";
        echo "  Time: {$booking['check_in_time']} - {$booking['check_out_time']}\n";
        echo "  Guests: {$booking['number_of_guests']} | Status: {$booking['status']}\n";
        echo "  Reservation: {$booking['reservation_id']}\n\n";
    }
} else {
    echo "\n✓ No active bookings today - Venue is available\n\n";
}
?>
