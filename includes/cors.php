<?php

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173"); // Allow specific origin (your frontend)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow headers like Content-Type and Authorization
header("Access-Control-Allow-Credentials: true"); // Allow credentials (cookies, authorization headers, etc.)

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

?>


