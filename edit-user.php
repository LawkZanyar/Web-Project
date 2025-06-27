<?php
header('Content-Type: application/json');
require 'db.php';
$query = strtolower($_GET['query'] ?? '');
$tables = ['admins', 'students', 'managers'];
foreach ($tables as $table) {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'user' => [
            'username' => $user['username'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => rtrim($table, 's')
        ]]);
        exit;
    }
}
echo json_encode(['success' => false, 'message' => 'User not found.']);
?>
