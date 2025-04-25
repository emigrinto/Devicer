<?php
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$customer_id = $data['customer_id'] ?? '';

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("INSERT INTO `Order` (customer_id, status, created_at) VALUES (?, 'Processing', NOW())");
    $stmt->execute([$customer_id]);
    $order_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("SELECT product_id, quantity FROM Cart WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("INSERT INTO Order_details (order_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity']]);
    }

    $total = 0;
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("SELECT price FROM Product WHERE id = ?");
        $stmt->execute([$item['product_id']]);
        $price = $stmt->fetchColumn();
        $total += $price * $item['quantity'];
    }

    $stmt = $pdo->prepare("INSERT INTO Invoice (order_id, total_amount, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$order_id, $total]);

    $stmt = $pdo->prepare("DELETE FROM Cart WHERE customer_id = ?");
    $stmt->execute([$customer_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Checkout failed']);
}
?>
