<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['customer_id'])) {
    $cart_items = [];
    return;
}
$customer_id = $_SESSION['customer_id'];

try {
    $sql = "SELECT c.cart_id, c.product_id, c.quantity, p.name, p.price, i.image_url 
            FROM Cart c 
            JOIN Product p ON c.product_id = p.product_id 
            LEFT JOIN Images i ON p.product_id = i.product_id AND i.is_primary = TRUE 
            WHERE c.customer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching cart items: " . $e->getMessage());
}
?>
