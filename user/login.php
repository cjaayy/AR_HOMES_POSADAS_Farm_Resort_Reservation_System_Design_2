<?php
/**
 * User Login Handler (WITH DEBUG LOGGING)
 * AR Homes Posadas Farm Resort Reservation System
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Log the request
file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - Login attempt started\n", FILE_APPEND);

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
file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - Raw input: " . $rawInput . "\n", FILE_APPEND);

$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $response['message'] = 'Invalid JSON: ' . json_last_error_msg();
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - ERROR: JSON decode failed\n", FILE_APPEND);
    echo json_encode($response);
    exit;
}

// Validate input - accept either 'username' or 'usernameOrEmail'
$usernameField = isset($input['usernameOrEmail']) ? 'usernameOrEmail' : 'username';
if (!isset($input[$usernameField]) || !isset($input['password'])) {
    $response['message'] = 'Username and password are required';
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - ERROR: Missing credentials\n", FILE_APPEND);
    echo json_encode($response);
    exit;
}

$username = trim($input[$usernameField]);
$password = trim($input['password']);

file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - Credentials: username='$username', password length=" . strlen($password) . "\n", FILE_APPEND);

// Validate not empty
if (empty($username) || empty($password)) {
    $response['message'] = 'Username and password cannot be empty';
    echo json_encode($response);
    exit;
}

try {
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - Connecting to database\n", FILE_APPEND);
    
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - Database connected\n", FILE_APPEND);

    // Prepare SQL statement to prevent SQL injection
    $sql = "SELECT user_id, username, email, password_hash, full_name, last_name, given_name, middle_name, 
                   phone_number, is_active, email_verified, last_login, created_at 
            FROM users 
            WHERE username = :username OR email = :email 
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':email', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - Query executed, rows found: " . $stmt->rowCount() . "\n", FILE_APPEND);

    // Check if user exists
    if ($stmt->rowCount() === 0) {
        $response['message'] = 'Invalid username or password';
        file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - ERROR: User not found\n", FILE_APPEND);
        echo json_encode($response);
        exit;
    }

    // Fetch user data
    $user = $stmt->fetch();
    
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - User found: ID={$user['user_id']}, username={$user['username']}, email={$user['email']}, email_verified={$user['email_verified']}\n", FILE_APPEND);

    // Check if account is active
    if ($user['is_active'] != 1) {
        $response['message'] = 'Account is inactive. Please contact support.';
        file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - ERROR: Account inactive\n", FILE_APPEND);
        echo json_encode($response);
        exit;
    }
    
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - Account is active\n", FILE_APPEND);

    // Check if email is verified
    if (!isset($user['email_verified']) || $user['email_verified'] != 1) {
        $response['message'] = 'Please verify your email address before logging in. Check your inbox for the verification link.';
        $response['data'] = [
            'email_verified' => false,
            'email' => $user['email']
        ];
        file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - ERROR: Email not verified\n", FILE_APPEND);
        echo json_encode($response);
        exit;
    }
    
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - Email is verified\n", FILE_APPEND);

    // Verify password
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - Verifying password\n", FILE_APPEND);
    
    if (!password_verify($password, $user['password_hash'])) {
        $response['message'] = 'Invalid username or password';
        file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - ERROR: Password verification failed\n", FILE_APPEND);
        echo json_encode($response);
        exit;
    }
    
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - Password verified successfully\n", FILE_APPEND);

    // Update last login time
    $updateSql = "UPDATE users SET last_login = NOW() WHERE user_id = :user_id";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);
    $updateStmt->execute();

    // Set session variables
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_username'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_full_name'] = $user['full_name'];
    $_SESSION['user_given_name'] = $user['given_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_middle_name'] = $user['middle_name'];
    $_SESSION['user_phone'] = $user['phone_number'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - Session created successfully\n", FILE_APPEND);

    // Prepare success response
    $response['success'] = true;
    $response['message'] = 'Login successful';
    $response['data'] = [
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'given_name' => $user['given_name'],
        'last_name' => $user['last_name'],
        'middle_name' => $user['middle_name'],
        'phone_number' => $user['phone_number']
    ];

    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - SUCCESS: Sending response\n", FILE_APPEND);
    
    echo json_encode($response);

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - PDO ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode($response);
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/login_debug.log', date('Y-m-d H:i:s') . " - EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode($response);
}
?>
