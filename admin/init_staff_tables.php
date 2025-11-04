<?php
/**
 * Initialize Staff Tables
 * Creates necessary tables for staff features (tasks, activity logs, etc.)
 */

require_once '../config/connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $messages = [];
    
    // Create staff_tasks table
    $sql = "CREATE TABLE IF NOT EXISTS staff_tasks (
        task_id INT AUTO_INCREMENT PRIMARY KEY,
        staff_id INT NOT NULL,
        staff_name VARCHAR(255) NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        status ENUM('todo', 'in-progress', 'completed') DEFAULT 'todo',
        due_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        INDEX idx_staff_id (staff_id),
        INDEX idx_status (status),
        INDEX idx_priority (priority),
        INDEX idx_due_date (due_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    $messages[] = "✓ Table 'staff_tasks' created/verified successfully";
    
    // Create activity_log table
    $sql = "CREATE TABLE IF NOT EXISTS activity_log (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        user_name VARCHAR(255),
        user_role VARCHAR(50),
        action_type VARCHAR(100) NOT NULL,
        action_description TEXT,
        related_id INT,
        related_type VARCHAR(50),
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_action_type (action_type),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    $messages[] = "✓ Table 'activity_log' created/verified successfully";
    
    // Create staff_notifications table
    $sql = "CREATE TABLE IF NOT EXISTS staff_notifications (
        notification_id INT AUTO_INCREMENT PRIMARY KEY,
        staff_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
        is_read TINYINT(1) DEFAULT 0,
        related_id INT,
        related_type VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        read_at TIMESTAMP NULL,
        INDEX idx_staff_id (staff_id),
        INDEX idx_is_read (is_read),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    $messages[] = "✓ Table 'staff_notifications' created/verified successfully";
    
    // Create room_inventory table (for occupancy tracking)
    $sql = "CREATE TABLE IF NOT EXISTS room_inventory (
        room_id INT AUTO_INCREMENT PRIMARY KEY,
        room_number VARCHAR(50) NOT NULL UNIQUE,
        room_type VARCHAR(100) NOT NULL,
        capacity INT DEFAULT 2,
        price_per_night DECIMAL(10,2) NOT NULL,
        status ENUM('available', 'occupied', 'maintenance', 'reserved') DEFAULT 'available',
        floor INT,
        description TEXT,
        amenities TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_room_type (room_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    $messages[] = "✓ Table 'room_inventory' created/verified successfully";
    
    // Insert sample rooms if table is empty
    $check = $conn->query("SELECT COUNT(*) as count FROM room_inventory");
    $count = $check->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count == 0) {
        $rooms = [
            ['101', 'Deluxe Room', 2, 420.00, 'available', 1],
            ['102', 'Deluxe Room', 2, 420.00, 'occupied', 1],
            ['103', 'Standard Room', 2, 300.00, 'available', 1],
            ['104', 'Standard Room', 2, 300.00, 'occupied', 1],
            ['201', 'Suite', 4, 440.00, 'available', 2],
            ['202', 'Suite', 4, 440.00, 'occupied', 2],
            ['203', 'Family Room', 6, 193.00, 'available', 2],
            ['204', 'Family Room', 6, 193.00, 'reserved', 2],
            ['301', 'Deluxe Room', 2, 420.00, 'available', 3],
            ['302', 'Standard Room', 2, 300.00, 'maintenance', 3],
        ];
        
        $stmt = $conn->prepare("INSERT INTO room_inventory (room_number, room_type, capacity, price_per_night, status, floor) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($rooms as $room) {
            $stmt->execute($room);
        }
        $messages[] = "✓ Sample rooms inserted successfully";
    }
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Database Initialization</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            .success { color: #10b981; margin: 10px 0; }
            .error { color: #ef4444; margin: 10px 0; }
            h1 { color: #1e293b; }
            .back-btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; }
            .back-btn:hover { background: #5a67d8; }
        </style>
    </head>
    <body>
        <h1>Database Initialization Complete!</h1>";
    
    foreach ($messages as $msg) {
        echo "<p class='success'>{$msg}</p>";
    }
    
    echo "
        <a href='staff_dashboard.php' class='back-btn'>Go to Staff Dashboard</a>
    </body>
    </html>";
    
} catch (PDOException $e) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Database Error</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            .error { color: #ef4444; margin: 10px 0; }
            h1 { color: #1e293b; }
        </style>
    </head>
    <body>
        <h1>Database Initialization Error</h1>
        <p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
    </body>
    </html>";
}
?>
