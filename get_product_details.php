<?php
require 'db_connect.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing product ID']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Product WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($product);
?>