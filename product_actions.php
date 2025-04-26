<?php
session_start();
include 'db_connect.php';

function handleProductAction($action) {
    global $pdo;
    $response = ['success' => false, 'error' => '', 'products' => [], 'product' => null, 'images' => [], 'characteristics' => [], 'reviews' => [], 'categories' => [], 'manufacturers' => [], 'screen_sizes' => [], 'storages' => []];

    try {
        if ($action === 'get_products') {
            $sql = "SELECT p.product_id, p.name, p.price, p.category, p.description, i.image_url 
                    FROM Product p 
                    LEFT JOIN Images i ON p.product_id = i.product_id AND i.is_primary = TRUE";
            $stmt = $pdo->query($sql);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['products'] = $products;
            $response['success'] = true;
        } elseif ($action === 'get_filtered_products') {
            $where = $_POST['where'] ?? '';
            $sort = $_POST['sort'] ?? 'name_asc';
            $order_by = '';
            switch ($sort) {
                case 'name_asc':
                    $order_by = 'p.name ASC';
                    break;
                case 'name_desc':
                    $order_by = 'p.name DESC';
                    break;
                case 'price_asc':
                    $order_by = 'p.price ASC';
                    break;
                case 'price_desc':
                    $order_by = 'p.price DESC';
                    break;
                case 'rating_desc':
                    $order_by = 'average_rating DESC';
                    break;
                default:
                    $order_by = 'p.name ASC';
            }

            $sql = "SELECT p.product_id, p.name, p.price, p.category, p.description, 
                           COALESCE(AVG(r.rating), 0) as average_rating,
                           (SELECT i.image_url 
                            FROM Images i 
                            WHERE i.product_id = p.product_id AND i.is_primary = TRUE 
                            LIMIT 1) as image_url
                    FROM Product p 
                    LEFT JOIN Reviews r ON p.product_id = r.product_id
                    " . ($where ? "WHERE $where" : "") . "
                    GROUP BY p.product_id
                    ORDER BY $order_by";
            $stmt = $pdo->query($sql);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT DISTINCT category FROM Product";
            $stmt = $pdo->query($sql);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT DISTINCT manufacturer FROM Product WHERE manufacturer IS NOT NULL";
            $stmt = $pdo->query($sql);
            $manufacturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT DISTINCT characteristic_value FROM Characteristics WHERE characteristic_name = 'Screen Size'";
            $stmt = $pdo->query($sql);
            $screen_sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT DISTINCT characteristic_value FROM Characteristics WHERE characteristic_name = 'Storage Capacity'";
            $stmt = $pdo->query($sql);
            $storages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['products'] = $products;
            $response['categories'] = $categories;
            $response['manufacturers'] = $manufacturers;
            $response['screen_sizes'] = $screen_sizes;
            $response['storages'] = $storages;
            $response['success'] = true;
        } elseif ($action === 'get_product_details') {
            $product_id = $_POST['product_id'] ?? '';

            if (!$product_id) {
                $response['error'] = 'Не вказано ID товару.';
                return $response;
            }

            $sql = "SELECT p.product_id, p.name, p.price, p.category, p.description, p.manufacturer, p.stock, 
                           COALESCE(AVG(r.rating), 0) as average_rating,
                           (SELECT i.image_url 
                            FROM Images i 
                            WHERE i.product_id = p.product_id AND i.is_primary = TRUE 
                            LIMIT 1) as primary_image
                    FROM Product p 
                    LEFT JOIN Reviews r ON p.product_id = r.product_id 
                    WHERE p.product_id = ?
                    GROUP BY p.product_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                $response['error'] = 'Продукт не знайдено.';
                return $response;
            }

            $sql = "SELECT image_url, is_primary 
                    FROM Images 
                    WHERE product_id = ? 
                    ORDER BY is_primary DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_id]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT characteristic_name, characteristic_value 
                    FROM Characteristics 
                    WHERE product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_id]);
            $characteristics = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT r.rating, r.comment, r.review_date, c.first_name, c.last_name 
                    FROM Reviews r 
                    JOIN Customer c ON r.customer_id = c.customer_id 
                    WHERE r.product_id = ? 
                    ORDER BY r.review_date DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_id]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['product'] = $product;
            $response['images'] = $images;
            $response['characteristics'] = $characteristics;
            $response['reviews'] = $reviews;
            $response['success'] = true;
        } elseif ($action === 'submit_review') {
            if (!isset($_SESSION['customer_id'])) {
                $response['error'] = 'Будь ласка, увійдіть, щоб залишити відгук.';
                return $response;
            }

            $product_id = $_POST['product_id'] ?? '';
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
            $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
            $redirect = $_POST['redirect'] ?? '../frontend/product.php?id=' . $product_id;

            if (!$product_id) {
                $response['error'] = 'Не вказано ID товару.';
                return $response;
            }

            if ($rating < 1 || $rating > 5) {
                $response['error'] = 'Невірна оцінка. Виберіть від 1 до 5.';
                return $response;
            }

            $sql = "INSERT INTO Reviews (customer_id, product_id, rating, comment, review_date) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['customer_id'], $product_id, $rating, $comment]);

            $_SESSION['message'] = 'Відгук додано!';
            header("Location: $redirect");
            exit();
        } else {
            $response['error'] = 'Невідома дія.';
        }
    } catch (PDOException $e) {
        $response['error'] = 'Помилка: ' . $e->getMessage();
    }

    return $response;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = handleProductAction($action);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}
?>
