<?php
/**
 * Forgot Password Handler
 * AR Homes Posadas Farm Resort Reservation System
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

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
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $response['message'] = 'Invalid JSON: ' . json_last_error_msg();
    echo json_encode($response);
    exit;
}

// Validate input
if (!isset($input['email']) || empty(trim($input['email']))) {
    $response['message'] = 'Email is required';
    echo json_encode($response);
    exit;
}

$email = trim($input['email']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email format';
    echo json_encode($response);
    exit;
}

try {
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();

    // Check if email exists
    $sql = "SELECT user_id, username, email, full_name FROM users WHERE email = :email AND is_active = 1 LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    // Always return success message to prevent email enumeration
    if ($stmt->rowCount() === 0) {
        // Don't reveal that email doesn't exist
        $response['success'] = true;
        $response['message'] = 'If an account exists with this email, a password reset link will be sent.';
        echo json_encode($response);
        exit;
    }

    $user = $stmt->fetch();

    // Generate reset token
    $resetToken = bin2hex(random_bytes(32));
    $resetTokenHash = hash('sha256', $resetToken);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

    // Store reset token in database
    $updateSql = "UPDATE users SET 
                  reset_token = :token, 
                  reset_token_expires = :expires 
                  WHERE user_id = :user_id";
    
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bindParam(':token', $resetTokenHash, PDO::PARAM_STR);
    $updateStmt->bindParam(':expires', $expiresAt, PDO::PARAM_STR);
    $updateStmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);
    $updateStmt->execute();

    // Create reset link
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the base path dynamically from the current request
    $scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /Design_2.../user/forgot_password.php
    $basePath = dirname(dirname($scriptName)); // Go up two levels to get the base directory
    
    $resetLink = "{$protocol}://{$host}{$basePath}/reset_password.html?token={$resetToken}";

    // Log the reset link for development (in production, send via email)
    $logDir = __DIR__ . '/password_resets';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/reset_log_' . date('Y-m-d') . '.txt';
    $logMessage = "\n" . str_repeat('=', 80) . "\n";
    $logMessage .= "Password Reset Request\n";
    $logMessage .= "Time: " . date('Y-m-d H:i:s') . "\n";
    $logMessage .= "User: {$user['full_name']} ({$user['username']})\n";
    $logMessage .= "Email: {$email}\n";
    $logMessage .= "Reset Link: {$resetLink}\n";
    $logMessage .= "Expires: {$expiresAt}\n";
    $logMessage .= str_repeat('=', 80) . "\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    // In production, send email here
    // For development, we'll return the link in the response
    $response['success'] = true;
    $response['message'] = 'Password reset link has been generated. Check the console for the link.';
    $response['data'] = [
        'reset_link' => $resetLink,
        'expires_at' => $expiresAt,
        'note' => 'In production, this link would be sent via email. For development, use this link to reset your password.'
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
