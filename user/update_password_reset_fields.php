<?php
/**
 * Add Password Reset Fields to Users Table
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * This script adds reset_token and reset_token_expires columns to the users table
 * Run this file once to update the database structure
 */

require_once '../config/database.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Update - Password Reset Fields</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 10px 0;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 10px 0;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border-left: 4px solid #0c5460;
            padding: 15px;
            margin: 10px 0;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Password Reset Database Update</h1>
        <p><strong>AR Homes Posadas Farm Resort - Reservation System</strong></p>
        <hr>
        
<?php

try {
    // Connect to database
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<div class='info'>";
    echo "📊 Checking current database structure...";
    echo "</div>";

    // Check if columns already exist
    $checkSql = "SHOW COLUMNS FROM users LIKE 'reset_token'";
    $result = $conn->query($checkSql);
    
    if ($result->rowCount() > 0) {
        echo "<div class='info'>";
        echo "✓ Password reset fields already exist in the database.<br>";
        echo "No update needed.";
        echo "</div>";
    } else {
        // Add reset_token column
        $alterSql1 = "ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL DEFAULT NULL AFTER password_hash";
        $conn->exec($alterSql1);
        echo "<div class='success'>";
        echo "✓ Added 'reset_token' column successfully<br>";
        echo "</div>";

        // Add reset_token_expires column
        $alterSql2 = "ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL DEFAULT NULL AFTER reset_token";
        $conn->exec($alterSql2);
        echo "<div class='success'>";
        echo "✓ Added 'reset_token_expires' column successfully<br>";
        echo "</div>";

        // Add index for reset_token
        $indexSql = "ALTER TABLE users ADD INDEX idx_reset_token (reset_token)";
        $conn->exec($indexSql);
        echo "<div class='success'>";
        echo "✓ Added index for 'reset_token' column<br>";
        echo "</div>";

        echo "<div class='success'>";
        echo "<strong>✓ Database update completed successfully!</strong><br>";
        echo "The users table now supports password reset functionality.";
        echo "</div>";
    }

    // Show current table structure
    echo "<hr>";
    echo "<h3>📋 Current Users Table Structure:</h3>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; margin-top: 15px;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";
    
    $showSql = "SHOW COLUMNS FROM users";
    $columns = $conn->query($showSql);
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($column['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";

    echo "<hr>";
    echo "<h3 style='color: #28a745;'>✓ Password Reset Feature Ready!</h3>";
    echo "<p>You can now use the 'Forgot Password' feature on the login page.</p>";
    echo "<p><a href='../index.html' style='color: #007bff; text-decoration: none; font-weight: bold;'>→ Go to Login Page</a></p>";

} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<strong>❌ Database Error:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
    echo "<br><strong>Troubleshooting:</strong><br>";
    echo "<ol>";
    echo "<li>Make sure XAMPP MySQL service is running</li>";
    echo "<li>Check your database credentials in config/database.php</li>";
    echo "<li>Make sure the 'users' table exists (run user/init_users.php first)</li>";
    echo "</ol>";
}
?>
    </div>
</body>
</html>
