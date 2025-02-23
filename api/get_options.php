<?php
require '../config/config.php';
require '../includes/cors.php';

header("Content-Type: application/json");

$options = [
    "categories" => ["Breakfast", "Lunch", "Dinner", "Snacks", "Dessert"],
    "cuisines" => ["Italian", "Chinese", "Indian", "Mexican", "French"]
];

echo json_encode(["status" => "success", "options" => $options]);
?>
