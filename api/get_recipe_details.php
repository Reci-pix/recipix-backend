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

$recipeId = $_GET['id'] ?? null;

if (!$recipeId) {
    echo json_encode(["status" => "error", "message" => "Recipe ID is required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT r.*, 
               (r.user_id = ?) AS is_owner, 
               (u.role = 'user') AS is_private  -- If the owner is a user, it's private
        FROM recipes r
        JOIN users u ON r.user_id = u.id  -- Join with users to check the role
        WHERE r.id = ?
    ");
    $stmt->execute([$user_id, $recipeId]); // Compare with logged-in user ID
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($recipe) {
        // Check if the user has reviewed this recipe
        $hasReviewed = false;
        if ($user_id) {
            $reviewStmt = $pdo->prepare("SELECT 1 FROM reviews WHERE recipe_id = ? AND user_id = ?");
            $reviewStmt->execute([$recipeId, $user_id]);
            $hasReviewed = $reviewStmt->fetchColumn() ? true : false;
        }

        $recipe['hasReviewed'] = $hasReviewed; // Add the hasReviewed flag to the response
        echo json_encode(["status" => "success", "recipe" => $recipe]);
    } else {
        echo json_encode(["status" => "error", "message" => "Recipe not found"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Failed to fetch recipe details"]);
}
?>
