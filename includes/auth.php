<?php
require 'jwt.php';
require 'cors.php';

header("Content-Type: application/json");

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace("Bearer ", "", $headers['Authorization']) : "";

if (!$token || !JWTHandler::verifyToken($token)) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    http_response_code(401);
    exit;
}

// Extract user data
$userData = JWTHandler::verifyToken($token)->data;
?>

