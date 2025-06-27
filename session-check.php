<?php
session_start();
header('Content-Type: application/json');
$response = ['loggedIn' => false];
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    $response['loggedIn'] = true;
    $response['username'] = $_SESSION['username'];
    $response['role'] = $_SESSION['role'] ?? null;
    $response['email'] = $_SESSION['email'] ?? null;
    $response['name'] = $_SESSION['name'] ?? $_SESSION['username'];
}
echo json_encode($response);
