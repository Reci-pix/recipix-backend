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
$reviewId = $data['review_id'] ?? null;

if (!$reviewId) {
    echo json_encode(["status" => "error", "message" => "Review ID is required"]);
    exit;
}

// Ensure the user owns the review before deleting
$stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
if ($stmt->execute([$reviewId, $userData->id])) {
    echo json_encode(["status" => "success", "message" => "Review deleted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete review"]);
}
?>
