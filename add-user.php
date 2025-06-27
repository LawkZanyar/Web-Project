<?php
header('Content-Type: application/json');
require 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
$fullname = trim($data['fullname'] ?? '');
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$role = strtolower(trim($data['role'] ?? ''));
// Accept both singular and plural for robustness
$roleMap = [
    'admin' => 'admins',
    'student' => 'students',
    'manager' => 'managers',
    'admins' => 'admins',
    'students' => 'students',
    'managers' => 'managers'
];
if (!isset($roleMap[$role])) {
    echo json_encode(['success' => false, 'message' => 'Invalid role.']);
    exit;
}
$table = $roleMap[$role];
if ($fullname === '' || $username === '' || $email === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}
// Check for existing username or email
$stmt = $conn->prepare("SELECT id FROM $table WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
    exit;
}
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO $table (username, name, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $fullname, $email, $hashed_password);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'username' => $username]);
} else {
    echo json_encode(['success' => false, 'message' => 'User creation failed.']);
}
?>
