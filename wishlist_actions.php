<?php
session_start();
include 'db_connect.php';

function handleWishlistAction($action) {
    global $pdo;
    $response = ['success' => false, 'error' => '', 'message' => '', 'items' => []];

    if (!isset($_SESSION['customer_id'])) {
        $response['error'] = 'Будь ласка, увійдіть, щоб керувати списком бажань.';
        return $response;
    }

    $customer_id = $_SESSION['customer_id'];

    try {
        if ($action === 'add') {
            $product_id = $_POST['product_id'] ?? '';

            if (!$product_id) {
                $response['error'] = 'Не вказано ID товару.';
                return $response;
            }

            // Check if the product exists
            $sql = "SELECT product_id FROM Product WHERE product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_id]);
            if (!$stmt->fetch()) {
                $response['error'] = 'Товар не знайдено.';
                return $response;
            }

            // Check if the product is already in the wishlist
            $sql = "SELECT wishlist_id FROM Wishlist WHERE customer_id = ? AND product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$customer_id, $product_id]);
            if ($stmt->fetch()) {
                $response['message'] = 'Товар уже у списку бажань.';
                $response['success'] = true;
                return $response;
            }

            // Add to wishlist
            $sql = "INSERT INTO Wishlist (customer_id, product_id, added_date) VALUES (?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$customer_id, $product_id]);

            $response['message'] = 'Товар додано до списку бажань.';
            $response['success'] = true;
        } elseif ($action === 'remove') {
            $wishlist_id = $_POST['wishlist_id'] ?? '';

            if (!$wishlist_id) {
                $response['error'] = 'Не вказано ID списку бажань.';
                return $response;
            }

            // Remove from wishlist
            $sql = "DELETE FROM Wishlist WHERE wishlist_id = ? AND customer_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$wishlist_id, $customer_id]);

            if ($stmt->rowCount() > 0) {
                $response['message'] = 'Товар видалено зі списку бажань.';
                $response['success'] = true;
            } else {
                $response['error'] = 'Товар не знайдено у списку бажань.';
            }
        } elseif ($action === 'view') {
            // Fetch wishlist items with product details
            $sql = "SELECT w.wishlist_id, w.product_id, p.name, p.price, i.image_url 
                    FROM Wishlist w 
                    JOIN Product p ON w.product_id = p.product_id 
                    LEFT JOIN Images i ON p.product_id = i.product_id AND i.is_primary = TRUE 
                    WHERE w.customer_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$customer_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['items'] = $items;
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
    $result = handleWishlistAction($action);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}
?>
