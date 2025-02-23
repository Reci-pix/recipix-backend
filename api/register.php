<?php
require '../config/config.php';
require '../includes/cors.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['name'], $data['email'], $data['password'], $data['role'])) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

$name = trim($data['name']);
$email = trim($data['email']);
$password = password_hash($data['password'], PASSWORD_BCRYPT);
$role = $data['role'];

if (!in_array($role, ['user', 'chef'])) {
    echo json_encode(["status" => "error", "message" => "Invalid role"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $role]);

    echo json_encode(["status" => "success", "message" => "Registration successful"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Email already exists"]);
}
?>
