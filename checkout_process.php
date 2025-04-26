<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../frontend/login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $address = $_POST['address'] ?? '';

    if (empty($address)) {
        $_SESSION['checkout_message'] = 'Будь ласка, вкажіть адресу доставки.';
        header("Location: ../frontend/checkout.php");
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Fetch cart items
        $sql = "SELECT product_id, quantity FROM Cart WHERE customer_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cart_items)) {
            $pdo->rollBack();
            $_SESSION['checkout_message'] = 'Ваш кошик порожній.';
            header("Location: ../frontend/checkout.php");
            exit();
        }

        // Create order
        $order_date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `Order` (customer_id, order_date) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id, $order_date]);
        $order_id = $pdo->lastInsertId();

        // Add order details
        $total = 0;
        foreach ($cart_items as $item) {
            $sql = "INSERT INTO Order_details (order_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$order_id, $item['product_id'], $item['quantity']]);

            // Fetch product price
            $sql = "SELECT price FROM Product WHERE product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$item['product_id']]);
            $price = $stmt->fetchColumn();
            $total += $price * $item['quantity'];
        }

        // Create invoice
        $invoice_date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO Invoice (order_id, total, invoice_date) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$order_id, $total, $invoice_date]);

        // Clear cart
        $sql = "DELETE FROM Cart WHERE customer_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id]);

        // Update customer address
        $sql = "UPDATE Customer SET address = ? WHERE customer_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$address, $customer_id]);

        $pdo->commit();
        $_SESSION['checkout_message'] = 'Замовлення успішно оформлено!';
        header("Location: ../frontend/store.php?order_success=1");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['checkout_message'] = 'Помилка при оформленні замовлення: ' . $e->getMessage();
        header("Location: ../frontend/checkout.php");
        exit();
    }
} else {
    header("Location: ../frontend/checkout.php");
    exit();
}
?>
