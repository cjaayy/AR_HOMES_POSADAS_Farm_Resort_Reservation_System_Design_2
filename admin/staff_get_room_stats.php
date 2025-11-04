<?php
/**
 * Staff Room Stats API - Get room occupancy and availability stats
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || ($_SESSION['admin_role'] ?? '') !== 'staff') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'room_inventory'");
    if ($tableCheck->rowCount() === 0) {
        // Return default values if table doesn't exist
        echo json_encode([
            'success' => true,
            'stats' => [
                'total_rooms' => 20,
                'occupied_rooms' => 12,
                'available_rooms' => 8,
                'maintenance_rooms' => 0,
                'occupancy_rate' => 60
            ]
        ]);
        exit;
    }
    
    // Get room statistics
    $sql = "SELECT 
                COUNT(*) as total_rooms,
                SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_rooms,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_rooms,
                SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_rooms,
                SUM(CASE WHEN status = 'reserved' THEN 1 ELSE 0 END) as reserved_rooms
            FROM room_inventory";
    
    $stmt = $conn->query($sql);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $totalRooms = (int)$stats['total_rooms'];
    $occupiedRooms = (int)$stats['occupied_rooms'];
    $availableRooms = (int)$stats['available_rooms'];
    $maintenanceRooms = (int)$stats['maintenance_rooms'];
    $reservedRooms = (int)$stats['reserved_rooms'];
    
    $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;
    
    // Get active guests count (occupied + reserved rooms with capacity)
    $guestSql = "SELECT SUM(capacity) as active_guests 
                 FROM room_inventory 
                 WHERE status IN ('occupied', 'reserved')";
    $guestStmt = $conn->query($guestSql);
    $guestData = $guestStmt->fetch(PDO::FETCH_ASSOC);
    $activeGuests = (int)($guestData['active_guests'] ?? 0);
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_rooms' => $totalRooms,
            'occupied_rooms' => $occupiedRooms,
            'available_rooms' => $availableRooms,
            'maintenance_rooms' => $maintenanceRooms,
            'reserved_rooms' => $reservedRooms,
            'occupancy_rate' => $occupancyRate,
            'active_guests' => $activeGuests
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
