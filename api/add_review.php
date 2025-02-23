<?php
require '../config/config.php';
require '../includes/auth.php';
require '../includes/cors.php';

header("Content-Type: application/json");

if (!$userData) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['recipe_id'], $data['rating'], $data['review_text'])) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

$recipeId = $data['recipe_id'];
$rating = (float) $data['rating'];
$comment = trim($data['review_text']);
$userId = $userData->id;

if ($rating < 1 || $rating > 5) {
    echo json_encode(["status" => "error", "message" => "Rating must be between 1 and 5"]);
    exit;
}

// Prevent chefs from reviewing their own recipes
$stmt = $pdo->prepare("SELECT user_id FROM recipes WHERE id = ?");
$stmt->execute([$recipeId]);
$recipeOwner = $stmt->fetchColumn();

if ($recipeOwner == $userId) {
    echo json_encode(["status" => "error", "message" => "You cannot review your own recipe"]);
    exit;
}

// Prevent duplicate reviews
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE recipe_id = ? AND user_id = ?");
$stmt->execute([$recipeId, $userId]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(["status" => "error", "message" => "You have already reviewed this recipe"]);
    exit;
}

// Insert review
$stmt = $pdo->prepare("INSERT INTO reviews (recipe_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)");
if ($stmt->execute([$recipeId, $userId, $rating, $comment])) {
    echo json_encode(["status" => "success", "message" => "Review added successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add review"]);
}
?>
