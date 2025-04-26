<?php
session_start();
include 'db_connect.php';

function handleCartAction($action) {
    global $pdo;
    $response = ['success' => false, 'error' => '', 'message' => '', 'items' => [], 'total' => 0, 'address' => ''];

    if (!isset($_SESSION['customer_id'])) {
        $response['error'] = 'Будь ласка, увійдіть, щоб керувати кошиком.';
        return $response;
    }

    $customer_id = $_SESSION['customer_id'];

    try {
        if ($action === 'add') {
            $product_id = $_POST['product_id'] ?? '';
            $quantity = 1; // Default quantity

            if (!$product_id) {
                $response['error'] = 'Не вказано ID товару.';
                return $response;
            }

            // Verify customer_id exists
            $sql = "SELECT customer_id FROM Customer WHERE customer_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$customer_id]);
            if (!$stmt->fetch()) {
                $response['error'] = 'Користувач не знайдений.';
                return $response;
            }

            // Check if the product exists in the Product table
            $sql = "SELECT product_id FROM Product WHERE product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_id]);
            if (!$stmt->fetch()) {
                $response['error'] = 'Товар не знайдено в базі даних.';
                return $response;
            }

            // Check if the product is already in the cart
            $sql = "SELECT cart_id, quantity FROM Cart WHERE customer_id = ? AND product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$customer_id, $product_id]);
            $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cart_item) {
                // Update quantity if product exists in cart
                $new_quantity = $cart_item['quantity'] + $quantity;
                $sql = "UPDATE Cart SET quantity = ?, added_date = NOW() WHERE cart_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$new_quantity, $cart_item['cart_id']]);
            } else {
                // Insert new cart item
                $sql = "INSERT INTO Cart (customer_id, product_id, quantity, added_date) VALUES (?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$customer_id, $product_id, $quantity]);
            }

            $_SESSION['cart_message'] = 'Товар додано до кошика.';
            $response['message'] = 'Товар додано до кошика.';
            $response['success'] = true;
        } elseif ($action === 'remove') {
            $cart_id = $_POST['cart_id'] ?? '';

            if (!$cart_id) {
                $response['error'] = 'Не вказано ID кошика.';
                return $response;
            }

            // Remove from cart
            $sql = "DELETE FROM Cart WHERE cart_id = ? AND customer_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$cart_id, $customer_id]);

            if ($stmt->rowCount() > 0) {
                $response['message'] = 'Товар видалено з кошика.';
                $response['success'] = true;
            } else {
                $response['error'] = 'Товар не знайдено у кошику.';
            }
        } elseif ($action === 'view') {
            // Fetch cart items with product details
            $sql = "SELECT c.cart_id, c.product_id, c.quantity, p.name, p.price, i.image_url 
                    FROM Cart c 
                    JOIN Product p ON c.product_id = p.product_id 
                    LEFT JOIN Images i ON p.product_id = i.product_id AND i.is_primary = TRUE 
                    WHERE c.customer_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$customer_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate total
            $total = 0;
            foreach ($items as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            $response['items'] = $items;
            $response['total'] = $total;
            $response['success'] = true;
        } elseif ($action === 'get_checkout_data') {
            // Fetch cart items with product details
            $sql = "SELECT c.cart_id, c.product_id, c.quantity, p.name, p.price, i.image_url 
                    FROM Cart c 
                    JOIN Product p ON c.product_id = p.product_id 
                    LEFT JOIN Images i ON p.product_id = i.product_id AND i.is_primary = TRUE 
                    WHERE c.customer_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$customer_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate total
            $total = 0;
            foreach ($items as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            // Fetch customer address
            $sql = "SELECT address FROM Customer WHERE customer_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$customer_id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            $address = $customer['address'] ?? '';

            $response['items'] = $items;
            $response['total'] = $total;
            $response['address'] = $address;
            $response['success'] = true;
        } else {
            $response['error'] = 'Невідома дія.';
        }
    } catch (PDOException $e) {
        $response['error'] = 'Помилка: ' . $e->getMessage();
    }

    return $response;
}

// Handle incoming requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = handleCartAction($action);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}
?>
