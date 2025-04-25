<?php
include 'db_connect.php';

try {
    $sql = "SELECT p.product_id, p.name, p.price, i.image_url 
            FROM Product p 
            LEFT JOIN Images i ON p.product_id = i.product_id AND i.is_primary = TRUE";
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}
?>