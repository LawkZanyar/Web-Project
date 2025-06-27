<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}
require 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
$new_name = trim($data['name'] ?? '');
$new_username = trim($data['username'] ?? '');
$new_email = trim($data['email'] ?? '');
$table = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
if ($new_name === '' || $new_username === '' || $new_email === '') {
    echo json_encode(['success' => false, 'message' => 'All fields required.']);
    exit;
}
// Check for username/email conflicts (except for current user)
$stmt = $conn->prepare("SELECT id FROM $table WHERE (username = ? OR email = ?) AND id != ?");
$stmt->bind_param("ssi", $new_username, $new_email, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
    exit;
}
// Update user
$stmt = $conn->prepare("UPDATE $table SET name = ?, username = ?, email = ? WHERE id = ?");
$stmt->bind_param("sssi", $new_name, $new_username, $new_email, $user_id);
if ($stmt->execute()) {
    // Update session variables
    $_SESSION['name'] = $new_name;
    $_SESSION['username'] = $new_username;
    $_SESSION['email'] = $new_email;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
