<?php
/**
 * Update room_inventory status based on active reservations
 * This syncs room occupancy with actual confirmed reservations
 */

require_once '../../config/connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "Syncing room occupancy with reservations...\n\n";
    
    // First, mark all rooms as available
    $conn->exec("UPDATE room_inventory SET status = 'available', current_reservation_id = NULL");
    echo "✓ Reset all rooms to available\n";
    
    // Since this is a VENUE booking system (not individual room bookings),
    // we'll mark rooms as occupied based on active reservations TODAY
    // Each reservation = 1 occupied "room slot"
    
    $activeReservations = $conn->query("
        SELECT reservation_id, guest_name, booking_type, check_in_date, check_out_date, status
        FROM reservations 
        WHERE status IN ('confirmed', 'checked_in')
          AND CURDATE() BETWEEN check_in_date AND check_out_date
        ORDER BY check_in_date
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $occupiedCount = 0;
    foreach ($activeReservations as $reservation) {
        // Mark one room as occupied per active reservation
        $updated = $conn->exec("
            UPDATE room_inventory 
            SET status = 'occupied', 
                current_reservation_id = '{$reservation['reservation_id']}'
            WHERE status = 'available' 
            LIMIT 1
        ");
        
        if ($updated > 0) {
            $occupiedCount++;
            echo "  • Room occupied by: {$reservation['guest_name']} ({$reservation['booking_type']})\n";
        }
    }
    
    echo "\n✓ Marked {$occupiedCount} rooms as occupied based on active reservations\n\n";
    
    // Display current stats
    $stats = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available
        FROM room_inventory
    ")->fetch(PDO::FETCH_ASSOC);
    
    $occupancyRate = $stats['total'] > 0 ? round(($stats['occupied'] / $stats['total']) * 100) : 0;
    
    echo "Current Occupancy Statistics:\n";
    echo str_repeat("-", 40) . "\n";
    echo "  Total Rooms: {$stats['total']}\n";
    echo "  Occupied: {$stats['occupied']}\n";
    echo "  Available: {$stats['available']}\n";
    echo "  Occupancy Rate: {$occupancyRate}%\n\n";
    
    // Show occupied rooms
    if ($stats['occupied'] > 0) {
        echo "Occupied Rooms:\n";
        echo str_repeat("-", 60) . "\n";
        $occupied = $conn->query("
            SELECT ri.room_number, ri.room_type, ri.current_reservation_id, r.guest_name
            FROM room_inventory ri
            LEFT JOIN reservations r ON ri.current_reservation_id = r.reservation_id
            WHERE ri.status = 'occupied'
            ORDER BY ri.room_number
        ");
        while ($room = $occupied->fetch(PDO::FETCH_ASSOC)) {
            printf("%-10s %-20s (Reservation #%s - %s)\n", 
                $room['room_number'], 
                $room['room_type'],
                $room['current_reservation_id'] ?? 'N/A',
                $room['guest_name'] ?? 'N/A'
            );
        }
    }
    
    echo "\n✓ Sync completed successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
