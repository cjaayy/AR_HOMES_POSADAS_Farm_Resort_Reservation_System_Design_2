<?php
/**
 * Reset Reservations - Clear all reservations from database
 * WARNING: This will delete ALL reservations!
 */

require_once 'config/database.php';

try {
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
    
    // Count existing reservations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations");
    $count = $stmt->fetch()['count'];
    
    echo "<h2>🗑️ Reset Reservations Database</h2>";
    echo "<p><strong>Current reservations:</strong> $count</p>";
    
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        // Delete all reservations
        $pdo->exec("TRUNCATE TABLE reservations");
        
        echo "<div style='background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>✅ Success!</h3>";
        echo "<p>All reservations have been deleted.</p>";
        echo "<p><a href='dashboard.html'>Go to Dashboard</a></p>";
        echo "</div>";
    } else {
        // Show confirmation form
        echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>⚠️ Warning!</h3>";
        echo "<p>This will delete <strong>ALL $count reservations</strong> from the database.</p>";
        echo "<p>This action cannot be undone!</p>";
        
        echo "<form method='POST' style='margin-top: 20px;'>";
        echo "<input type='hidden' name='confirm' value='yes'>";
        echo "<button type='submit' style='background: #f44336; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px;'>";
        echo "🗑️ Yes, Delete All Reservations";
        echo "</button>";
        echo "</form>";
        
        echo "<p style='margin-top: 20px;'><a href='dashboard.html'>Cancel and go back</a></p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Reservations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
    </style>
</head>
<body>
</body>
</html>
