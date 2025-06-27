<?php
session_start();
header('Content-Type: application/json');
require 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$tables = ['admins', 'students', 'managers'];
foreach ($tables as $table) {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        // Accept both hashed and plain text passwords for compatibility
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['user_id'] = $user['id']; // Set user_id for session-check.php
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $table;
            $_SESSION['email'] = $user['email'] ?? '';
            $_SESSION['name'] = $user['name'] ?? $user['username'];
            echo json_encode(['success' => true, 'role' => $table, 'name' => $user['name']]);
            exit;
        }
    }
}
echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
?>
