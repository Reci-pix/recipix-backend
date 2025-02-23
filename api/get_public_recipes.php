<?php
require '../config/config.php';
require '../includes/auth.php';
require '../includes/cors.php';

header("Content-Type: application/json");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$cuisine = isset($_GET['cuisine']) ? trim($_GET['cuisine']) : '';
$cook_time = isset($_GET['cook_time']) ? (int) $_GET['cook_time'] : 0;
$servings = isset($_GET['servings']) ? (int) $_GET['servings'] : 0;

try {
    // Base query
    $query = "
        SELECT 
            r.id, 
            r.name, 
            r.image_path, 
            r.cook_time, 
            r.servings,
            r.cuisine, 
            r.category, 
            r.user_id = ? AS is_owner,
            COALESCE(AVG(rv.rating), 0) AS avg_rating,
            COUNT(rv.id) AS total_reviews,
            (u.role = 'user') AS is_private
        FROM recipes r
        LEFT JOIN reviews rv ON r.id = rv.recipe_id
        JOIN users u ON r.user_id = u.id
        WHERE u.role = 'chef'";

    // Parameters for prepared statement
    $params = [$userData->id];

    // Apply search filter
    if (!empty($search)) {
        $query .= " AND r.name LIKE ?";
        $params[] = "%$search%";
    }

    // Apply category filter
    if (!empty($category)) {
        $query .= " AND r.category = ?";
        $params[] = $category;
    }

    // Apply cuisine filter
    if (!empty($cuisine)) {
        $query .= " AND r.cuisine = ?";
        $params[] = $cuisine;
    }

    // Apply cook time filter
    if ($cook_time > 0) {
        $query .= " AND r.cook_time <= ?";
        $params[] = $cook_time;
    }

    // Apply servings filter
    if ($servings > 0) {
        $query .= " AND r.servings >= ?";
        $params[] = $servings;
    }

    // Finalize query
    $query .= " GROUP BY r.id ORDER BY r.created_at DESC";

    // Prepare and execute
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "recipes" => $recipes]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Failed to fetch public recipes"]);
}
?>
