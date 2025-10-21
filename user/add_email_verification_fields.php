<?php
/**
 * Add Email Verification Fields to Users Table
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * This script adds email verification columns to the users table
 * Run this file once to update the database structure
 */

require_once '../config/database.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Update - Email Verification Fields</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 10px 0;
            color: #155724;
            border-radius: 5px;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 10px 0;
            color: #721c24;
            border-radius: 5px;
        }
        .info {
            background: #d1ecf1;
            border-left: 4px solid #0c5460;
            padding: 15px;
            margin: 10px 0;
            color: #0c5460;
            border-radius: 5px;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 10px 0;
            color: #856404;
            border-radius: 5px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
            font-size: 14px;
        }
        th {
            background: #667eea;
            color: white;
            padding: 12px 8px;
            text-align: left;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Verification Database Update</h1>
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

    $updatesNeeded = false;
    $updatesMade = [];

    // Check if email_verified column exists
    $checkSql = "SHOW COLUMNS FROM users LIKE 'email_verified'";
    $result = $conn->query($checkSql);
    
    if ($result->rowCount() == 0) {
        // Add email_verified column
        $alterSql1 = "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER email";
        $conn->exec($alterSql1);
        $updatesMade[] = "Added 'email_verified' column (0 = not verified, 1 = verified)";
        $updatesNeeded = true;
    }

    // Check if email_verification_token column exists
    $checkSql = "SHOW COLUMNS FROM users LIKE 'email_verification_token'";
    $result = $conn->query($checkSql);
    
    if ($result->rowCount() == 0) {
        // Add email_verification_token column
        $alterSql2 = "ALTER TABLE users ADD COLUMN email_verification_token VARCHAR(255) NULL DEFAULT NULL AFTER email_verified";
        $conn->exec($alterSql2);
        $updatesMade[] = "Added 'email_verification_token' column";
        $updatesNeeded = true;
    }

    // Check if email_verification_expires column exists
    $checkSql = "SHOW COLUMNS FROM users LIKE 'email_verification_expires'";
    $result = $conn->query($checkSql);
    
    if ($result->rowCount() == 0) {
        // Add email_verification_expires column
        $alterSql3 = "ALTER TABLE users ADD COLUMN email_verification_expires DATETIME NULL DEFAULT NULL AFTER email_verification_token";
        $conn->exec($alterSql3);
        $updatesMade[] = "Added 'email_verification_expires' column";
        $updatesNeeded = true;
    }

    // Add index for email_verification_token if needed
    $checkIndex = "SHOW INDEX FROM users WHERE Key_name = 'idx_email_verification_token'";
    $result = $conn->query($checkIndex);
    
    if ($result->rowCount() == 0) {
        $indexSql = "ALTER TABLE users ADD INDEX idx_email_verification_token (email_verification_token)";
        $conn->exec($indexSql);
        $updatesMade[] = "Added index for 'email_verification_token' column";
        $updatesNeeded = true;
    }

    if ($updatesNeeded) {
        echo "<div class='success'>";
        echo "<strong>✓ Database update completed successfully!</strong><br><br>";
        echo "<strong>Updates made:</strong><br>";
        foreach ($updatesMade as $update) {
            echo "✓ " . $update . "<br>";
        }
        echo "<br>The users table now supports email verification functionality.";
        echo "</div>";
    } else {
        echo "<div class='info'>";
        echo "✓ Email verification fields already exist in the database.<br>";
        echo "No update needed.";
        echo "</div>";
    }

    // Show important note
    echo "<div class='warning'>";
    echo "<strong>⚠️ Important Note:</strong><br>";
    echo "Existing users in the database have 'email_verified' set to 0 (not verified).<br>";
    echo "You may want to manually set existing users to verified (1) or require them to verify their email.";
    echo "</div>";

    // Show current table structure
    echo "<hr>";
    echo "<h3>📋 Current Users Table Structure:</h3>";
    echo "<table>";
    echo "<tr>";
    echo "<th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th>";
    echo "</tr>";
    
    $showSql = "SHOW COLUMNS FROM users";
    $columns = $conn->query($showSql);
    
    foreach ($columns as $column) {
        $highlight = in_array($column['Field'], ['email_verified', 'email_verification_token', 'email_verification_expires']);
        echo "<tr" . ($highlight ? " style='background: #fff3cd; font-weight: bold;'" : "") . ">";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";

    echo "<hr>";
    echo "<h3 style='color: #28a745;'>✓ Email Verification Feature Ready!</h3>";
    echo "<p>Users will now receive a verification email upon registration.</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Configure SMTP settings in <code>config/mail.php</code></li>";
    echo "<li>Test registration with email verification</li>";
    echo "<li>Update existing users if needed</li>";
    echo "</ol>";
    
    echo "<a href='../registration.html' class='btn'>→ Test Registration</a> ";
    echo "<a href='../index.html' class='btn'>→ Go to Login</a>";

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
