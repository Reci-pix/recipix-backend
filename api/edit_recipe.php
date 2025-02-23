<?php
require '../includes/cors.php';
require '../config/config.php';
require '../includes/auth.php'; // User authentication

header("Content-Type: application/json");

// Ensure only POST requests are allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// Validate required fields (image can be optional)
$required_fields = ['id', 'name', 'description', 'cook_time', 'category', 'cuisine', 'ingredients', 'directions', 'servings'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode(["status" => "error", "message" => ucfirst($field) . " is required"]);
        exit;
    }
}

// Sanitize inputs
$recipe_id = intval($_POST['id']);
$name = trim($_POST['name']);
$description = trim($_POST['description']);
$cook_time = intval($_POST['cook_time']);
$category = trim($_POST['category']);
$cuisine = trim($_POST['cuisine']);
$ingredients = trim($_POST['ingredients']);
$directions = trim($_POST['directions']);
$youtube_link = isset($_POST['youtube_link']) ? trim($_POST['youtube_link']) : null;
$servings = intval($_POST['servings']);

// Ensure the recipe exists and belongs to the logged-in user
$query = "SELECT image_path FROM recipes WHERE id = :recipe_id AND user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $userData->id, PDO::PARAM_INT);
$stmt->execute();
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    echo json_encode(["status" => "error", "message" => "Recipe not found or unauthorized"]);
    exit;
}

$upload_dir = '../uploads/';
$uploadedFileName = $recipe['image_path']; // Keep old image by default

// Handle image upload if a new one is provided
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageOriginalName = basename($_FILES['image']['name']);
    $imageExt = strtolower(pathinfo($imageOriginalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif','webp'];

    // Validate image extension
    if (!in_array($imageExt, $allowedExtensions)) {
        echo json_encode(["status" => "error", "message" => "Invalid image format. Allowed: jpg, jpeg, png, gif"]);
        exit;
    }

    // Generate unique image name
    $imageName = time() . "_" . $imageOriginalName;
    $imagePath = $upload_dir . $imageName;

    // Ensure upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Delete old image if exists
    if (!empty($recipe['image_path']) && file_exists($upload_dir . $recipe['image_path'])) {
        unlink($upload_dir . $recipe['image_path']);
    }

    // Save the new image
    if (move_uploaded_file($imageTmpName, $imagePath)) {
        $uploadedFileName = $imageName; // Set new image file name
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to upload image"]);
        exit;
    }
}

// Update the recipe
$updateQuery = "UPDATE recipes SET 
                    name = :name, 
                    description = :description, 
                    cook_time = :cook_time, 
                    category = :category, 
                    cuisine = :cuisine, 
                    ingredients = :ingredients, 
                    directions = :directions, 
                    youtube_link = :youtube_link, 
                    servings = :servings, 
                    image_path = :image 
                WHERE id = :recipe_id AND user_id = :user_id";
$updateStmt = $pdo->prepare($updateQuery);
$updateStmt->bindParam(':name', $name);
$updateStmt->bindParam(':description', $description);
$updateStmt->bindParam(':cook_time', $cook_time, PDO::PARAM_INT);
$updateStmt->bindParam(':category', $category);
$updateStmt->bindParam(':cuisine', $cuisine);
$updateStmt->bindParam(':ingredients', $ingredients);
$updateStmt->bindParam(':directions', $directions);
$updateStmt->bindParam(':youtube_link', $youtube_link);
$updateStmt->bindParam(':servings', $servings, PDO::PARAM_INT);
$updateStmt->bindParam(':image', $uploadedFileName);
$updateStmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
$updateStmt->bindParam(':user_id', $userData->id, PDO::PARAM_INT);

if ($updateStmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Recipe updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update recipe"]);
}
?>
