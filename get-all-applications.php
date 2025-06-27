<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'managers') {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

// Get all applications with applicant name (students only)
$stmt = $conn->prepare("SELECT a.created_at, u.name, a.title, a.status FROM applications a JOIN students u ON a.username = u.username ORDER BY a.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
echo json_encode(['success' => true, 'applications' => $applications]);
?>
