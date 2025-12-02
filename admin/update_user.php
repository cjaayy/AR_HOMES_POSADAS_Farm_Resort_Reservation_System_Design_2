<?php
/**
 * Update User API Endpoint
 * AR Homes Posadas Farm Resort Reservation System
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Include database connection
require_once '../config/connection.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing user ID'
    ]);
    exit;
}

$userId = trim($input['user_id']);

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Build update query dynamically based on provided fields
    $updateFields = [];
    $params = [':user_id' => $userId];

    if (isset($input['full_name'])) {
        $updateFields[] = "full_name = :full_name";
        $params[':full_name'] = $input['full_name'];
    }
    
    if (isset($input['username'])) {
        $updateFields[] = "username = :username";
        $params[':username'] = $input['username'];
    }
    
    if (isset($input['email'])) {
        $updateFields[] = "email = :email";
        $params[':email'] = $input['email'];
    }
    
    if (isset($input['phone_number'])) {
        $updateFields[] = "phone_number = :phone_number";
        $params[':phone_number'] = $input['phone_number'];
    }
    
    if (isset($input['loyalty_level'])) {
        $updateFields[] = "loyalty_level = :loyalty_level";
        $params[':loyalty_level'] = $input['loyalty_level'];
    }

    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No fields to update'
        ]);
        exit;
    }

    // Add updated_at timestamp
    $updateFields[] = "updated_at = NOW()";

    $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully',
            'user_id' => $userId
        ]);
    } else {
        throw new Exception('Failed to update user');
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
