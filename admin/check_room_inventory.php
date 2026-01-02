<?php
require_once '../config/connection.php';

$db = new Database();
$conn = $db->getConnection();

// Check if table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'room_inventory'");
echo "Table exists: " . ($tableCheck->rowCount() > 0 ? "YES" : "NO") . "\n\n";

if ($tableCheck->rowCount() > 0) {
    // Show table structure
    echo "Table Structure:\n";
    $structure = $conn->query("DESCRIBE room_inventory");
    while ($col = $structure->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$col['Field']} ({$col['Type']})\n";
    }
    
    // Count total and occupied rooms
    echo "\n\nRoom Statistics:\n";
    $stats = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied,
        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available
        FROM room_inventory");
    $data = $stats->fetch(PDO::FETCH_ASSOC);
    echo "Total rooms: {$data['total']}\n";
    echo "Occupied: {$data['occupied']}\n";
    echo "Available: {$data['available']}\n";
    
    // Show all rooms
    echo "\n\nAll Rooms:\n";
    $rooms = $conn->query("SELECT * FROM room_inventory ORDER BY id");
    while ($room = $rooms->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$room['id']}, Room: {$room['room_number']}, Status: {$room['status']}\n";
    }
} else {
    echo "room_inventory table does not exist!\n";
    echo "You need to create this table to track room occupancy.\n";
}
?>
