<?php
/**
 * Migration: Create room_inventory table
 * Run this once to set up room inventory tracking for occupancy rates
 */

require_once '../../config/connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "Creating room_inventory table...\n\n";
    
    // Create table
    $createTable = "
    CREATE TABLE IF NOT EXISTS `room_inventory` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `room_number` varchar(50) NOT NULL,
      `room_type` varchar(100) DEFAULT NULL,
      `status` enum('available','occupied','maintenance','reserved') DEFAULT 'available',
      `current_reservation_id` int(11) DEFAULT NULL,
      `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_room_number` (`room_number`),
      KEY `idx_status` (`status`),
      KEY `fk_reservation` (`current_reservation_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    
    $conn->exec($createTable);
    echo "✓ Table created successfully\n\n";
    
    // Check if table is empty
    $count = $conn->query("SELECT COUNT(*) FROM room_inventory")->fetchColumn();
    
    if ($count == 0) {
        echo "Inserting sample room data...\n";
        
        $insertRooms = "
        INSERT INTO `room_inventory` (`room_number`, `room_type`, `status`) VALUES
        ('R101', 'Standard Room', 'available'),
        ('R102', 'Standard Room', 'available'),
        ('R103', 'Standard Room', 'available'),
        ('R104', 'Standard Room', 'available'),
        ('R105', 'Standard Room', 'available'),
        ('R201', 'Deluxe Room', 'available'),
        ('R202', 'Deluxe Room', 'available'),
        ('R203', 'Deluxe Room', 'available'),
        ('R204', 'Deluxe Room', 'available'),
        ('R205', 'Deluxe Room', 'available'),
        ('R301', 'Suite', 'available'),
        ('R302', 'Suite', 'available'),
        ('R303', 'Suite', 'available'),
        ('R304', 'Suite', 'available'),
        ('R305', 'Suite', 'available')
        ";
        
        $conn->exec($insertRooms);
        echo "✓ Inserted 15 sample rooms\n\n";
    } else {
        echo "✓ Table already has {$count} rooms\n\n";
    }
    
    // Display current inventory
    echo "Current Room Inventory:\n";
    echo str_repeat("-", 60) . "\n";
    $rooms = $conn->query("SELECT room_number, room_type, status FROM room_inventory ORDER BY room_number");
    while ($room = $rooms->fetch(PDO::FETCH_ASSOC)) {
        printf("%-10s %-20s %-15s\n", $room['room_number'], $room['room_type'], $room['status']);
    }
    
    // Show occupancy stats
    echo "\n" . str_repeat("-", 60) . "\n";
    $stats = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available
        FROM room_inventory
    ")->fetch(PDO::FETCH_ASSOC);
    
    $occupancyRate = $stats['total'] > 0 ? round(($stats['occupied'] / $stats['total']) * 100) : 0;
    
    echo "\nOccupancy Statistics:\n";
    echo "  Total Rooms: {$stats['total']}\n";
    echo "  Occupied: {$stats['occupied']}\n";
    echo "  Available: {$stats['available']}\n";
    echo "  Occupancy Rate: {$occupancyRate}%\n\n";
    
    echo "✓ Migration completed successfully!\n";
    echo "\nNOTE: Update room numbers and types to match your actual resort rooms.\n";
    echo "      You can also add logic to automatically update room status based on reservations.\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
