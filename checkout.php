<?php
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$customer_id = $data['customer_id'] ?? '';

$pdo->beginTransaction();

try {
    // Insert new order
    $stmt = $pdo->prepare("INSERT INTO `Order` (customer_id, order_date) VALUES (?, NOW())");
    $stmt->execute([$customer_id]);
    $order_id = $pdo->lastInsertId();

    // Get cart items
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM Cart WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process each cart item
    foreach ($cart_items as $item) {
        // Check if enough stock is available
        $stmt = $pdo->prepare("SELECT stock FROM Product WHERE product_id = ?");
        $stmt->execute([$item['product_id']]);
        $current_stock = $stmt->fetchColumn();

        if ($current_stock < $item['quantity']) {
            throw new Exception("Insufficient stock for product ID {$item['product_id']}");
        }

        // Insert into Order_details
        $stmt = $pdo->prepare("INSERT INTO Order_details (order_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity']]);

        // Update product stock
        $stmt = $pdo->prepare("UPDATE Product SET stock = stock - ? WHERE product_id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("SELECT price FROM Product WHERE product_id = ?");
        $stmt->execute([$item['product_id']]);
        $price = $stmt->fetchColumn();
        $total += $price * $item['quantity'];
    }

    // Insert invoice
    $stmt = $pdo->prepare("INSERT INTO Invoice (order_id, total, invoice_date) VALUES (?, ?, NOW())");
    $stmt->execute([$order_id, $total]);

    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM Cart WHERE customer_id = ?");
    $stmt->execute([$customer_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>