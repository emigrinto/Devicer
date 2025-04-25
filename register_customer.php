<?php
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO Customer (name, email, password) VALUES (?, ?, ?)");
$stmt->execute([$name, $email, $hash]);

echo json_encode(['success' => true]);
?>
