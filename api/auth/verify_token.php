<?php
require '../../includes/cors.php';
require '../../includes/auth.php';

header("Content-Type: application/json");

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace("Bearer ", "", $headers['Authorization']) : "";

if (!$token) {
    echo json_encode(["success" => false, "message" => "No token provided"]);
    http_response_code(401);
    exit;
}

$decodedToken = JWTHandler::verifyToken($token);
if (!$decodedToken) {
    echo json_encode(["success" => false, "message" => "Invalid or expired token"]);
    http_response_code(401);
    exit;
}

// Send user ID and role in the response
echo json_encode([
    "success" => true,
    "message" => "Token is valid",
    "user" => [
        "id" => $decodedToken->data->id,
        "role" => $decodedToken->data->role
    ]
]);
exit;
?>
