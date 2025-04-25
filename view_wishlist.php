<?php
include 'db_connect.php';

if (!isset($_SESSION['customer_id'])) {
    $wishlist_items = [];
    return;
}
$customer_id = $_SESSION['customer_id'];

try {
    $sql = "SELECT w.wishlist_id, w.product_id, p.name, p.price, i.image_url 
            FROM Wishlist w 
            JOIN Product p ON w.product_id = p.product_id 
            LEFT JOIN Images i ON p.product_id = i.product_id AND i.is_primary = TRUE 
            WHERE w.customer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_id]);
    $wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching wishlist items: " . $e->getMessage());
}
?>