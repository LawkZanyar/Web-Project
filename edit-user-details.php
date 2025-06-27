<?php
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $required = ['username', 'name', 'newUsername', 'email', 'role'];
    $missing = [];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing fields: ' . implode(', ', $missing)
        ]);
        exit;
    }
    $username = $data['username'];
    $newUsername = $data['newUsername'];
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'] ?? '';
    $role = strtolower($data['role']);
    $action = $data['action'] ?? '';
    $roleMap = [
        'admin' => 'admins',
        'student' => 'students',
        'manager' => 'managers',
        'admins' => 'admins',
        'students' => 'students',
        'managers' => 'managers'
    ];
    if ($action === 'save' && $username && $name && $newUsername && $email && $role && isset($roleMap[$role])) {
        // Find current table
        $tables = ['admins', 'students', 'managers'];
        $user = null;
        $tableFound = null;
        foreach ($tables as $table) {
            $stmt = $conn->prepare("SELECT * FROM $table WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                $tableFound = $table;
                break;
            }
        }
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit;
        }
        $targetTable = $roleMap[$role];
        // If role/table changed, move user
        if ($tableFound !== $targetTable) {
            // Check for username/email conflict in target table
            $stmt = $conn->prepare("SELECT id FROM $targetTable WHERE (username = ? OR email = ?)");
            $stmt->bind_param("ss", $newUsername, $email);
            $stmt->execute();
            if ($stmt->get_result()->fetch_assoc()) {
                echo json_encode(['success' => false, 'message' => 'Username or email already exists in target role.']);
                exit;
            }
            // Insert into new table
            $hashed_password = $password ? password_hash($password, PASSWORD_DEFAULT) : $user['password'];
            $stmt = $conn->prepare("INSERT INTO $targetTable (username, name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $newUsername, $name, $email, $hashed_password);
            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Failed to move user.']);
                exit;
            }
            // Delete from old table
            $stmt = $conn->prepare("DELETE FROM $tableFound WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            echo json_encode(['success' => true]);
            exit;
        } else {
            // Update in same table
            // Check for username/email conflict (except for current user)
            $stmt = $conn->prepare("SELECT id FROM $tableFound WHERE (username = ? OR email = ?) AND username != ?");
            $stmt->bind_param("sss", $newUsername, $email, $username);
            $stmt->execute();
            if ($stmt->get_result()->fetch_assoc()) {
                echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
                exit;
            }
            $setPassword = $password ? ", password = ?" : "";
            $sql = "UPDATE $tableFound SET username = ?, name = ?, email = ?$setPassword WHERE username = ?";
            $stmt = $conn->prepare($password ? $sql : str_replace(', password = ?', '', $sql));
            if ($password) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt->bind_param("sssss", $newUsername, $name, $email, $hashed_password, $username);
            } else {
                $stmt->bind_param("ssss", $newUsername, $name, $email, $username);
            }
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update user.']);
            }
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing fields.']);
        exit;
    }
}
// GET: fetch user info
$username = $_GET['username'] ?? '';
$action = $_GET['action'] ?? '';
$tables = ['admins', 'students', 'managers'];
$user = null;
$tableFound = null;
foreach ($tables as $table) {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $tableFound = $table;
        break;
    }
}
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}
if ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM $tableFound WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}
echo json_encode(['success' => true, 'user' => [
    'username' => $user['username'],
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => rtrim($tableFound, 's')
]]);
?>
