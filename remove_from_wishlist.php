<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Будь ласка, увійдіть.']);
    exit();
}
$customer_id = $_SESSION['customer_id'];

if (isset($_POST['wishlist_id'])) {
    $wishlist_id = $_POST['wishlist_id'];

    try {
        $sql = "DELETE FROM Wishlist WHERE wishlist_id = ? AND customer_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$wishlist_id, $customer_id]);
        echo json_encode(['success' => true, 'message' => 'Товар видалено зі списку бажань.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Помилка: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Невірний ID списку бажань']);
}
?>