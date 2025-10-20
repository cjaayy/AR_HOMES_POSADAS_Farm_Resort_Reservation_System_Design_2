<?php
/**
 * Database Initialization Script
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * This script creates the database and admin_users table
 * Run this file once to set up the database structure
 */

require_once '../config/database.php';

try {
    // Connect to MySQL server without selecting a database
    $conn = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->exec($sql);
    echo "✓ Database created successfully or already exists<br><br>";

    // Select the database
    $conn->exec("USE " . DB_NAME);

    // Create admin_users table
    $createTableSql = "
    CREATE TABLE IF NOT EXISTS admin_users (
        admin_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('super_admin', 'admin', 'staff') DEFAULT 'admin',
        is_active TINYINT(1) DEFAULT 1,
        last_login DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_by INT(11) DEFAULT NULL,
        INDEX idx_username (username),
        INDEX idx_email (email),
        INDEX idx_role (role),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conn->exec($createTableSql);
    echo "✓ Admin users table created successfully or already exists<br><br>";

    // Check if default admin exists
    $checkAdmin = $conn->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
    $adminExists = $checkAdmin->fetchColumn();

    if ($adminExists == 0) {
        // Create default admin account
        $defaultUsername = 'admin';
        $defaultEmail = 'admin@resort.com';
        $defaultPassword = 'admin123'; // Change this in production!
        $defaultFullName = 'Administrator';
        $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);

        $insertAdmin = "
        INSERT INTO admin_users 
        (username, email, password_hash, full_name, role, is_active) 
        VALUES 
        (:username, :email, :password_hash, :full_name, 'super_admin', 1)
        ";

        $stmt = $conn->prepare($insertAdmin);
        $stmt->bindParam(':username', $defaultUsername);
        $stmt->bindParam(':email', $defaultEmail);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':full_name', $defaultFullName);
        $stmt->execute();

        echo "✓ Default admin account created successfully<br><br>";
        echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0;'>";
        echo "<strong>⚠️ Default Admin Credentials:</strong><br>";
        echo "Username: <strong>admin</strong><br>";
        echo "Email: <strong>admin@resort.com</strong><br>";
        echo "Password: <strong>admin123</strong><br><br>";
        echo "<em style='color: #856404;'>⚠️ IMPORTANT: Please change the default password immediately after first login!</em>";
        echo "</div>";
    } else {
        echo "✓ Admin account already exists<br><br>";
    }

    echo "<hr>";
    echo "<h3 style='color: #28a745;'>✓ Database initialization completed successfully!</h3>";
    echo "<p><a href='../index.html' style='color: #007bff;'>Go to Login Page</a></p>";

} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0;'>";
    echo "<strong>❌ Database Error:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
    echo "<br><strong>Troubleshooting:</strong><br>";
    echo "1. Make sure XAMPP MySQL service is running<br>";
    echo "2. Check your database credentials in config/database.php<br>";
    echo "3. Verify that MySQL port 3306 is not blocked<br>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Initialization - AR Homes Resort</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🏨 AR Homes Posadas Farm Resort - Database Setup</h1>
