<?php
session_start(); // Must be first line
header('Content-Type: application/json');
require 'db.php';

// Debug: log request method and raw input for troubleshooting
file_put_contents("debug_log.txt", "Method: " . $_SERVER['REQUEST_METHOD'] . "\nRaw: " . file_get_contents("php://input") . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $username = $_SESSION['username'] ?? '';

    error_log("Username in session: " . $username);

    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
        exit;
    }

    if ($title && $description) {
        $stmt = $conn->prepare("INSERT INTO applications (username, title, description, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("sss", $username, $title, $description);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Title and description are required.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
