<?php
/**
 * Localhost-only script to create the `reservations` table and insert sample data.
 * Run from the server (open in browser on the machine running XAMPP) to initialize.
 */
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'])) {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

require_once '../config/database.php';

try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS reservations (
        reservation_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        guest_name VARCHAR(150) DEFAULT NULL,
        guest_email VARCHAR(150) DEFAULT NULL,
        guest_phone VARCHAR(50) DEFAULT NULL,
        room VARCHAR(100) DEFAULT NULL,
        check_in_date DATE DEFAULT NULL,
        check_out_date DATE DEFAULT NULL,
        status ENUM('pending','confirmed','completed','canceled') DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT NULL,
        INDEX idx_status (status),
        INDEX idx_checkin (check_in_date),
        INDEX idx_checkout (check_out_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conn->exec($sql);
    echo "✓ reservations table created or already exists\n";

    // Insert a sample reservation for today (only if table empty)
    $count = (int)$conn->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
    if ($count === 0) {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $stmt = $conn->prepare("INSERT INTO reservations (guest_name, guest_email, guest_phone, room, check_in_date, check_out_date, status) VALUES (:g, :e, :p, :r, :ci, :co, 'confirmed')");
        $stmt->execute([
            ':g' => 'Test Guest',
            ':e' => 'guest@example.com',
            ':p' => '09171234567',
            ':r' => 'Deluxe Room',
            ':ci' => $today,
            ':co' => $tomorrow
        ]);
        echo "✓ Inserted sample reservation for today\n";
    } else {
        echo "✓ reservations table already has $count rows\n";
    }

    echo "\nInitialization complete. You can now reload the staff dashboard to see stats.";

} catch (PDOException $e) {
    http_response_code(500);
    echo "Error: ".htmlspecialchars($e->getMessage());
}

?>
