<?php
require '../config/config.php';
require '../includes/auth.php';
require '../includes/cors.php';

header("Content-Type: application/json");

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// Check if all required fields are set
if (
    !isset($_POST['name'], $_POST['description'], $_POST['cook_time'], $_POST['ingredients'],
        $_POST['servings'], $_POST['directions'], $_POST['category'], $_POST['cuisine'])
) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

// Get data from request
$user_id = $userData->id;
$name = trim($_POST['name']);
$description = trim($_POST['description']);
$cook_time = trim($_POST['cook_time']);
$ingredients = trim($_POST['ingredients']);
$servings = intval($_POST['servings']);
$directions = trim($_POST['directions']);
$category = trim($_POST['category']);
$cuisine = trim($_POST['cuisine']);
$youtube_link = isset($_POST['youtube_link']) ? trim($_POST['youtube_link']) : null;

// Handle image upload
if (!isset($_FILES['image'])) {
    echo json_encode(["status" => "error", "message" => "Recipe image is required"]);
    exit;
}

$image = $_FILES['image'];
$upload_dir = "../uploads/";
$image_name = time() . "_" . basename($image["name"]);
$image_path = $upload_dir . $image_name;

// Move image to server
if (!move_uploaded_file($image["tmp_name"], $image_path)) {
    echo json_encode(["status" => "error", "message" => "Failed to upload image"]);
    exit;
}

// Save to database
try {
    $stmt = $pdo->prepare("INSERT INTO recipes 
        (user_id, name, description, cook_time, ingredients, servings, directions, category, cuisine, image_path, youtube_link) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([$user_id, $name, $description, $cook_time, $ingredients, $servings, $directions, $category, $cuisine, $image_name, $youtube_link]);

    echo json_encode(["status" => "success", "message" => "Recipe added successfully"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Failed to add recipe"]);
}
?>
