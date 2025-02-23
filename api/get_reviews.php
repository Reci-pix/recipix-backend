<?php
require '../config/config.php';
require '../includes/auth.php';
require '../includes/cors.php';

header("Content-Type: application/json");

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace("Bearer ", "", $headers['Authorization']) : "";

$user_id = null;
if ($token && JWTHandler::verifyToken($token)) {
    $userData = JWTHandler::verifyToken($token)->data;
    $user_id = $userData->id;
}

$recipeId = $_GET['recipe_id'] ?? null;

if (!$recipeId) {
    echo json_encode(["status" => "error", "message" => "Recipe ID is required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT r.id, r.rating, r.review_text, u.name AS reviewer, r.created_at, r.user_id
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.recipe_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$recipeId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($reviews as &$review) {
        $review['is_owner'] = ($user_id && $review['user_id'] == $user_id);
        unset($review['user_id']); // Remove user_id for security
    }

    echo json_encode([
        "status" => "success",
        "reviews" => $reviews
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Failed to fetch reviews"]);
}
?>
