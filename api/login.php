<?php
require '../includes/cors.php'; // CORS must be the first thing included!
require '../config/config.php';
require '../includes/jwt.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email'], $data['password'])) {
    echo json_encode(["status" => "error", "message" => "Email and password are required"]);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $token = JWTHandler::generateToken([
        'id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role']
    ]);

    echo json_encode(["status" => "success", "token" => $token]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid email or password"]);
}
?>
