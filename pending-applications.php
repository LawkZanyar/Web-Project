<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'managers') {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Approve application
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    if ($id) {
        $stmt = $conn->prepare("UPDATE applications SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to approve.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid application ID.']);
    }
    exit;
}

// GET: fetch all pending applications
        $stmt = $conn->prepare("SELECT a.id, u.name, a.title, a.description, a.created_at FROM applications a JOIN students u ON a.username = u.username WHERE a.status = 'pending' ORDER BY a.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $applications = [];
        while ($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }
        echo json_encode(['success' => true, 'applications' => $applications]);
?>
