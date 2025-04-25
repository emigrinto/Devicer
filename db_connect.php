<?php
$host = '192.168.159.131'; // IP Ubuntu
$db = 'devicer';  // database in ubuntu
$user = 'alina';        // username used for workbench connection
$pass = '990062658481dD!';  // database pass

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
