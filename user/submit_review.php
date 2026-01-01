<?php
/**
 * User: Submit Review
 * Submit a new review for a reservation
 */

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    // Try form data
    $data = $_POST;
}

// Validate required fields
$required = ['reservation_id', 'rating', 'title', 'content'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$reservation_id = trim($data['reservation_id']);
$rating = intval($data['rating']);
$title = trim($data['title']);
$content = trim($data['content']);
$user_id = $_SESSION['user_id'];

// Validate reservation_id
if (empty($reservation_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID']);
    exit;
}

// Validate rating
if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
}

// Validate title and content length
if (strlen($title) < 3 || strlen($title) > 255) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title must be between 3 and 255 characters']);
    exit;
}

if (strlen($content) < 10) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Review content must be at least 10 characters']);
    exit;
}

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if reservation exists and belongs to user
    $checkSql = "SELECT reservation_id, status FROM reservations WHERE reservation_id = :res_id AND user_id = :user_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute(['res_id' => $reservation_id, 'user_id' => $user_id]);
    $reservation = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Reservation not found or does not belong to you']);
        exit;
    }
    
    // Check if reservation is completed/checked out
    if (!in_array($reservation['status'], ['completed', 'checked_out'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'You can only review completed reservations']);
        exit;
    }
    
    // Check if review already exists
    $existsSql = "SELECT review_id FROM reviews WHERE reservation_id = :res_id";
    $existsStmt = $conn->prepare($existsSql);
    $existsStmt->execute(['res_id' => $reservation_id]);
    
    if ($existsStmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'You have already reviewed this reservation']);
        exit;
    }
    
    // Insert the review
    $insertSql = "
        INSERT INTO reviews (user_id, reservation_id, rating, title, content, status, created_at)
        VALUES (:user_id, :reservation_id, :rating, :title, :content, 'active', NOW())
    ";
    
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->execute([
        'user_id' => $user_id,
        'reservation_id' => $reservation_id,
        'rating' => $rating,
        'title' => $title,
        'content' => $content
    ]);
    
    $review_id = $conn->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully!',
        'review_id' => $review_id
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
