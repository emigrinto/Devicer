<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Будь ласка, увійдіть.']);
    exit();
}
$customer_id = $_SESSION['customer_id'];

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $added_date = date('Y-m-d H:i:s');

    try {
        // Check if the product exists
        $sql = "SELECT product_id FROM Product WHERE product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Товар не знайдено.']);
            exit();
        }

        // Check if the product is already in the wishlist
        $sql = "SELECT wishlist_id FROM Wishlist WHERE customer_id = ? AND product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id, $product_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Товар уже у списку бажань.']);
            exit();
        }

        // Add to wishlist
        $sql = "INSERT INTO Wishlist (customer_id, product_id, added_date) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id, $product_id, $added_date]);

        echo json_encode(['success' => true, 'message' => 'Товар додано до списку бажань.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Помилка: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Невірний ID товару']);
}
?>