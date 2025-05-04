<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';

// Handle both JSON and form data
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST; // Fallback to form data
}

$first_name = $data['first_name'] ?? '';
$last_name = $data['last_name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$address = $data['address'] ?? 'Not provided';
$gender = $data['gender'] ?? 'Інше';
$birthdate = $data['birthdate'] ?? '2000-01-01';
$phone_number = $data['phone_number'] ?? '0000000000';
$username = ($first_name && $last_name) ? $first_name . '_' . $last_name : null;
$discount = 0; // Explicitly set to 0 (false) for BOOLEAN NOT NULL

if (!$first_name || !$last_name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO Customer (customer_username, customer_password, discount, first_name, last_name, email, address, gender, birthdate, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $hash, $discount, $first_name, $last_name, $email, $address, $gender, $birthdate, $phone_number]);

    header("Location: ../frontend/account.php?registered=1");
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>