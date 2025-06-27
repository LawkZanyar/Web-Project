<?php
$conn = new mysqli("localhost", "root", "", "registration_db");
if ($conn->connect_error) {
    die(json_encode(["success"=>false, "message"=>"Failed to connect: " . $conn->connect_error]));
}
?>
