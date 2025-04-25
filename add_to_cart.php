<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

// Log the start of the script for debugging
file_put_contents('/var/www/html/Devicer/backend/debug_log.txt', "Script started at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

if (!isset($_SESSION['customer_id'])) {
    file_put_contents('/var/www/html/Devicer/backend/debug_log.txt', "User not logged in\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Будь ласка, увійдіть.']);
    exit();
}
$customer_id = $_SESSION['customer_id'];

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1; // Default quantity
    $added_date = date('Y-m-d H:i:s');

    try {
        // Verify customer_id exists
        $sql = "SELECT customer_id FROM Customer WHERE customer_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id]);
        if (!$stmt->fetch()) {
            file_put_contents('/var/www/html/Devicer/backend/debug_log.txt', "Customer not found: $customer_id\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Користувач не знайдений.']);
            exit();
        }

        // Check if the product exists in the Product table
        $sql = "SELECT product_id FROM Product WHERE product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        if (!$stmt->fetch()) {
            file_put_contents('/var/www/html/Devicer/backend/debug_log.txt', "Product not found: $product_id\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Товар не знайдено в базі даних.']);
            exit();
        }

        // Check if the product is already in the cart
        $sql = "SELECT cart_id, quantity FROM Cart WHERE customer_id = ? AND product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id, $product_id]);
        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart_item) {
            // Update quantity if product exists in cart
            $new_quantity = $cart_item['quantity'] + $quantity;
            $sql = "UPDATE Cart SET quantity = ?, added_date = ? WHERE cart_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_quantity, $added_date, $cart_item['cart_id']]);
        } else {
            // Insert new cart item
            $sql = "INSERT INTO Cart (customer_id, product_id, quantity, added_date) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$customer_id, $product_id, $quantity, $added_date]);
        }

        file_put_contents('/var/www/html/Devicer/backend/debug_log.txt', "Cart updated successfully for customer $customer_id, product $product_id\n", FILE_APPEND);
        echo json_encode(['success' => true, 'message' => 'Товар додано до кошика.']);
    } catch (PDOException $e) {
        file_put_contents('/var/www/html/Devicer/backend/error_log.txt', $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Помилка при додаванні товару: ' . $e->getMessage()]);
    } catch (Exception $e) {
        file_put_contents('/var/www/html/Devicer/backend/error_log.txt', "General error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Загальна помилка: ' . $e->getMessage()]);
    }
} else {
    file_put_contents('/var/www/html/Devicer/backend/debug_log.txt', "Invalid product ID\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Невірний ID товару']);
}
?>