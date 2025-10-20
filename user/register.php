<?php
/**
 * User Registration Handler (WITH DEBUG LOGGING)
 * AR Homes Posadas Farm Resort Reservation System
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/registration_errors.log');

session_start();

// Log the request
file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - Registration attempt started\n", FILE_APPEND);

require_once '../config/connection.php';

header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Get JSON input
$rawInput = file_get_contents('php://input');
file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - Raw input: " . $rawInput . "\n", FILE_APPEND);

$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $response['message'] = 'Invalid JSON: ' . json_last_error_msg();
    file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - ERROR: JSON decode failed\n", FILE_APPEND);
    echo json_encode($response);
    exit;
}

// Validate required fields
$requiredFields = ['lastName', 'givenName', 'middleName', 'email', 'phoneNumber', 'username', 'password'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        $response['message'] = ucfirst($field) . ' is required';
        file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - ERROR: Missing $field\n", FILE_APPEND);
        echo json_encode($response);
        exit;
    }
}

file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - All fields present\n", FILE_APPEND);

// Sanitize inputs
$lastName = trim($input['lastName']);
$givenName = trim($input['givenName']);
$middleName = trim($input['middleName']);
$email = trim($input['email']);
$phoneNumber = trim($input['phoneNumber']);
$username = trim($input['username']);
$password = $input['password'];

file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - Data: username=$username, email=$email\n", FILE_APPEND);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email format';
    echo json_encode($response);
    exit;
}

// Validate password strength (minimum 6 characters)
if (strlen($password) < 6) {
    $response['message'] = 'Password must be at least 6 characters long';
    echo json_encode($response);
    exit;
}

try {
    file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - Connecting to database\n", FILE_APPEND);
    
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - Database connected\n", FILE_APPEND);

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
    $checkEmail->bindParam(':email', $email);
    $checkEmail->execute();
    
    if ($checkEmail->rowCount() > 0) {
        $response['message'] = 'Email address is already registered';
        echo json_encode($response);
        exit;
    }

    // Check if username already exists
    $checkUsername = $conn->prepare("SELECT user_id FROM users WHERE username = :username");
    $checkUsername->bindParam(':username', $username);
    $checkUsername->execute();
    
    if ($checkUsername->rowCount() > 0) {
        $response['message'] = 'Username is already taken';
        echo json_encode($response);
        exit;
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Create full name
    $fullName = $lastName . ', ' . $givenName . ' ' . $middleName;

    // Insert new user
    $sql = "INSERT INTO users 
            (username, email, password_hash, last_name, given_name, middle_name, full_name, phone_number, is_active) 
            VALUES 
            (:username, :email, :password_hash, :last_name, :given_name, :middle_name, :full_name, :phone_number, 1)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password_hash', $passwordHash);
    $stmt->bindParam(':last_name', $lastName);
    $stmt->bindParam(':given_name', $givenName);
    $stmt->bindParam(':middle_name', $middleName);
    $stmt->bindParam(':full_name', $fullName);
    $stmt->bindParam(':phone_number', $phoneNumber);
    
    file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - Executing INSERT\n", FILE_APPEND);
    
    $stmt->execute();

    // Get the new user ID
    $userId = $conn->lastInsertId();
    
    file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - SUCCESS: User ID $userId created\n", FILE_APPEND);

    // Prepare success response
    $response['success'] = true;
    $response['message'] = 'Registration successful! You can now login.';
    $response['data'] = [
        'user_id' => $userId,
        'username' => $username,
        'email' => $email,
        'full_name' => $fullName
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - PDO ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode($response);
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/registration_debug.log', date('Y-m-d H:i:s') . " - EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode($response);
}
?>
