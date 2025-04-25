<?php
include '../backend/db_connect.php';

// Replace these with your desired admin credentials
$username = 'admin';
$password = 'admin123'; // Password will be hashed
$first_name = 'Admin';
$last_name = 'User';
$email = 'admin@devicer.com';

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO Admin (admin_username, admin_password, first_name, last_name, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $hashed_password, $first_name, $last_name, $email]);
    echo "Admin account created successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>