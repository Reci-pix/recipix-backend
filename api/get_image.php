<?php
require '../config/config.php';

if (!isset($_GET['filename'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Bad request"]);
    exit;
}

$filename = basename($_GET['filename']); // Prevent directory traversal
$filepath = realpath("../uploads/" . $filename);

if (!$filepath || strpos($filepath, realpath("../uploads/")) !== 0 || !file_exists($filepath)) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "File not found"]);
    exit;
}

header("Content-Type: " . mime_content_type($filepath));
readfile($filepath);
exit;
?>