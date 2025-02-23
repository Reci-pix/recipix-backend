<?php
header("Access-Control-Allow-Origin: http://localhost:5173"); // Allow specific origin (your frontend)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow headers like Content-Type and Authorization
header("Access-Control-Allow-Credentials: true"); // Allow credentials (cookies, authorization headers, etc.)
require '../config/config.php';
require '../includes/auth.php'; // Ensures authentication

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}
header("Content-Type: application/json");

// Ensure only DELETE requests are allowed
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"));

// Check if the payload has 'recipe_id'
if (!isset($data->recipe_id)) {
    echo json_encode(["status" => "error", "message" => "Recipe ID is required"]);
    exit;
}

// Get recipe ID and validate it
$recipe_id = intval($data->recipe_id);

if ($recipe_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid recipe ID"]);
    exit;
}

// Step 1: Retrieve the image path before deleting the recipe
$image_query = "SELECT image_path FROM recipes WHERE id = :recipe_id";
$image_stmt = $pdo->prepare($image_query);
$image_stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
$image_stmt->execute();
$image_result = $image_stmt->fetch(PDO::FETCH_ASSOC);

if (!$image_result) {
    echo json_encode(["status" => "error", "message" => "Recipe not found"]);
    exit;
}

$image_path = $image_result['image_path'];
$image_full_path = __DIR__ . "/../uploads/" . $image_path; // Adjust the path if needed

// Step 2: Delete the recipe from the database
$query = "DELETE FROM recipes WHERE id = :recipe_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);

if ($stmt->execute()) {
    // Step 3: Delete the image file from the uploads folder
    if (!empty($image_path) && file_exists($image_full_path)) {
        unlink($image_full_path); // Delete the file
    }

    echo json_encode(["status" => "success", "message" => "Recipe and its image deleted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete recipe"]);
}
?>
