<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../frontend/account.php");
    exit();
}
$customer_id = $_SESSION['customer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $review_date = date('Y-m-d H:i:s');

    // Validate rating
    if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        header("Location: ../frontend/product.php?id=$product_id&error=Невірний рейтинг");
        exit();
    }

    try {
        // Check if the user has already reviewed this product
        $sql = "SELECT review_id FROM Reviews WHERE customer_id = ? AND product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id, $product_id]);
        if ($stmt->fetch()) {
            header("Location: ../frontend/product.php?id=$product_id&error=Ви вже залишили відгук для цього товару");
            exit();
        }

        // Insert the review
        $sql = "INSERT INTO Reviews (product_id, customer_id, rating, comment, review_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id, $customer_id, $rating, $comment, $review_date]);

        header("Location: ../frontend/product.php?id=$product_id&success=Відгук успішно додано");
        exit();
    } catch (PDOException $e) {
        header("Location: ../frontend/product.php?id=$product_id&error=Помилка: " . urlencode($e->getMessage()));
        exit();
    }
}
?>