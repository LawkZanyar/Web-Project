<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$username = $_SESSION['username'] ?? '';
if (!$username) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$stmt = $conn->prepare("SELECT created_at, title, status FROM applications WHERE username = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
echo json_encode(['success' => true, 'applications' => $applications]);
?>
