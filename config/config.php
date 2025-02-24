<?php
$host = "DB_HOST";
$db_name = "DB_NAME";
$username = "DB_USER";  // Change if using another user
$password = "DB_PASS";      // Change if using a password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}
?>
