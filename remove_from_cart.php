<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Будь ласка, увійдіть.']);
    exit();
}

if (isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];
    try {
        $sql = "DELETE FROM Cart WHERE cart_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cart_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Помилка: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Невірний ID кошика']);
}
?>