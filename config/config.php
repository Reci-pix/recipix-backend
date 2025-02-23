<?php
$host = "sql300.infinityfree.com";
$db_name = "if0_38380085_recipix";
$username = "if0_38380085";  // Change if using another user
$password = "kZ50NLGTlg0TomT";      // Change if using a password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}
?>
