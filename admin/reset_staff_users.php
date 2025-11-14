<?php
/**
 * Reset Staff and Users Database Script
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * This script clears all staff (admin_users) and user (users) data
 * and recreates the default admin account
 */

require_once '../config/database.php';

try {
    // Connect to database
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Reset Staff & Users - AR Homes Resort</title>
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
                padding: 15px;
                border-left: 4px solid #28a745;
                margin: 10px 0;
                color: #155724;
            }
            .warning {
                background: #fff3cd;
                padding: 15px;
                border-left: 4px solid #ffc107;
                margin: 10px 0;
                color: #856404;
            }
            .info {
                background: #d1ecf1;
                padding: 15px;
                border-left: 4px solid #17a2b8;
                margin: 10px 0;
                color: #0c5460;
            }
            .link-btn {
                display: inline-block;
                padding: 10px 20px;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin: 10px 5px;
            }
            .link-btn:hover {
                background: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🔄 Reset Staff & Users Database</h1>";

    // Start transaction
    $conn->beginTransaction();

    // Add position column if it doesn't exist
    try {
        $conn->exec("ALTER TABLE admin_users ADD COLUMN position VARCHAR(100) DEFAULT NULL AFTER full_name");
        echo "<div class='success'>✓ Position column added to admin_users table</div>";
    } catch (PDOException $e) {
        // Column might already exist, ignore error
        if ($e->getCode() != '42S21') { // Duplicate column error code
            throw $e;
        }
    }

    // Truncate admin_users table (staff) and reset AUTO_INCREMENT
    $conn->exec("TRUNCATE TABLE admin_users");
    $conn->exec("ALTER TABLE admin_users AUTO_INCREMENT = 1");
    echo "<div class='success'>✓ All staff accounts have been removed from admin_users table</div>";
    echo "<div class='success'>✓ Staff ID counter reset to 1</div>";

    // Truncate users table (guests) and reset AUTO_INCREMENT
    $conn->exec("TRUNCATE TABLE users");
    $conn->exec("ALTER TABLE users AUTO_INCREMENT = 1");
    echo "<div class='success'>✓ All user accounts have been removed from users table</div>";
    echo "<div class='success'>✓ User ID counter reset to 1</div>";

    // Create default admin account
    $defaultUsername = 'admin';
    $defaultEmail = 'admin@resort.com';
    $defaultPassword = 'admin123';
    $defaultFullName = 'Administrator';
    $defaultPosition = 'System Administrator';
    $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);

    $insertAdmin = "
    INSERT INTO admin_users 
    (username, email, password_hash, full_name, position, role, is_active) 
    VALUES 
    (:username, :email, :password_hash, :full_name, :position, 'super_admin', 1)
    ";

    $stmt = $conn->prepare($insertAdmin);
    $stmt->bindParam(':username', $defaultUsername);
    $stmt->bindParam(':email', $defaultEmail);
    $stmt->bindParam(':password_hash', $passwordHash);
    $stmt->bindParam(':full_name', $defaultFullName);
    $stmt->bindParam(':position', $defaultPosition);
    $stmt->execute();

    echo "<div class='success'>✓ Default admin account created successfully</div>";

    // Commit transaction
    $conn->commit();

    echo "<div class='warning'>
            <strong>⚠️ Default Admin Credentials:</strong><br>
            Username: <strong>admin</strong><br>
            Email: <strong>admin@resort.com</strong><br>
            Password: <strong>admin123</strong><br><br>
            <em>⚠️ IMPORTANT: Please change the default password immediately after first login!</em>
          </div>";

    echo "<div class='info'>
            <strong>📊 Reset Summary:</strong><br>
            • All staff accounts deleted<br>
            • All user accounts deleted<br>
            • ID counters reset to 1 for both tables<br>
            • Default admin account recreated (will have ID = 1)<br>
            • Other tables (reservations, rooms, etc.) remain unchanged
          </div>";

    echo "<hr>
            <h3 style='color: #28a745;'>✓ Staff and Users reset completed successfully!</h3>
            <a href='../index.html' class='link-btn'>Go to Login Page</a>
            <a href='dashboard.php' class='link-btn'>Go to Admin Dashboard</a>
          </div>
        </body>
    </html>";

} catch (PDOException $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Reset Error - AR Homes Resort</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .error {
                background: #f8d7da;
                padding: 15px;
                border-left: 4px solid #dc3545;
                margin: 10px 0;
                color: #721c24;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>❌ Database Reset Error</h1>
            <div class='error'>
                <strong>Error:</strong><br>" . htmlspecialchars($e->getMessage()) . "
            </div>
            <br><strong>Troubleshooting:</strong><br>
            1. Make sure XAMPP MySQL service is running<br>
            2. Check your database credentials in config/database.php<br>
            3. Verify that the tables exist<br>
            4. Ensure you have proper permissions<br>
        </div>
    </body>
    </html>";
}
?>
