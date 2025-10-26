<?php
// Localhost-only session dump for debugging. Only active when ?debug=1
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

if (!isset($_GET['debug']) || $_GET['debug'] != '1') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing debug flag']);
    exit;
}

header('Content-Type: application/json');
session_start();
echo json_encode(['success' => true, 'session' => $_SESSION]);
