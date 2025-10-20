<?php
/**
 * User Database Initialization Script
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * This script creates the users table for guest accounts
 * Run this file once to set up the user database structure
 */

require_once '../config/database.php';

try {
    // Connect to database
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create users table
    $createTableSql = "
    CREATE TABLE IF NOT EXISTS users (
        user_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        given_name VARCHAR(50) NOT NULL,
        middle_name VARCHAR(50) NOT NULL,
        full_name VARCHAR(150) NOT NULL,
        phone_number VARCHAR(20) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        member_since VARCHAR(4) DEFAULT YEAR(NOW()),
        loyalty_level ENUM('Regular', 'Silver', 'Gold', 'VIP') DEFAULT 'Regular',
        last_login DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_email (email),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conn->exec($createTableSql);
    echo "✓ Users table created successfully or already exists<br><br>";

    echo "<hr>";
    echo "<h3 style='color: #28a745;'>✓ User database initialization completed successfully!</h3>";
    echo "<p><a href='../registration.html' style='color: #007bff;'>Go to Registration Page</a></p>";
    echo "<p><a href='../index.html' style='color: #007bff;'>Go to Login Page</a></p>";

} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0;'>";
    echo "<strong>❌ Database Error:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
    echo "<br><strong>Troubleshooting:</strong><br>";
    echo "1. Make sure XAMPP MySQL service is running<br>";
    echo "2. Check your database credentials in config/database.php<br>";
    echo "3. Make sure admin/init_database.php was run first to create the database<br>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Database Initialization - AR Homes Resort</title>
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
        <h1>🏨 AR Homes Posadas Farm Resort - User Database Setup</h1>
