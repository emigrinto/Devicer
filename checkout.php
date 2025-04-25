<?php
session_start();
include '../backend/db_connect.php';
include '../backend/view_cart.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: account.php");
    exit();
}
$customer_id = $_SESSION['customer_id'];

// Fetch customer address
try {
    $sql = "SELECT address FROM Customer WHERE customer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    $address = $customer['address'];
} catch (PDOException $e) {
    die("Error fetching address: " . $e->getMessage());
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    try {
        $pdo->beginTransaction();

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
            $total += $item['price'] * $item['quantity'];
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

        $pdo->commit();
        header("Location: store.php?order_success=1");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error processing order: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення - Devicer</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <div class="logo">DEVICER</div>
    <nav>
        <ul>
            <li><a href="index.php">Головна</a></li>
            <li><a href="store.php">Магазин</a></li>
            <li><a href="cart.php">Кошик</a></li>
            <li><a href="wishlist.php">Список бажань</a></li>
            <li><a href="account.php">Акаунт</a></li>
            <li><a href="support.php">Підтримка</a></li>
        </ul>
    </nav>
    <div class="search-lang">
        <input type="text" placeholder="Пошук...">
        <select>
            <option value="uk">UKR</option>
            <option value="en">ENG</option>
        </select>
    </div>
</header>

    <main>
        <h1>Оформлення замовлення</h1>
        <div class="cart-items">
            <?php
            if (!empty($cart_items)) {
                $total = 0;
                foreach ($cart_items as $item) {
                    echo '<div class="cart-item">';
                    echo '<img src="' . htmlspecialchars($item['image_url']) . '" alt="' . htmlspecialchars($item['name']) . '">';
                    echo '<h3>' . htmlspecialchars($item['name']) . '</h3>';
                    echo '<p>Ціна: ' . htmlspecialchars($item['price']) . ' грн</p>';
                    echo '<p>Кількість: ' . htmlspecialchars($item['quantity']) . '</p>';
                    echo '<p>Сума: ' . ($item['price'] * $item['quantity']) . ' грн</p>';
                    echo '</div>';
                    $total += $item['price'] * $item['quantity'];
                }
                echo '<h2>Загальна сума: ' . $total . ' грн</h2>';
            } else {
                echo '<p>Ваш кошик порожній.</p>';
            }
            ?>
        </div>

        <?php if (!empty($cart_items)): ?>
            <form method="POST">
                <h2>Адреса доставки</h2>
                <label for="address">Адреса:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
                <button type="submit" name="confirm_order">Підтвердити замовлення</button>
            </form>
        <?php endif; ?>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>

    <script src="scripts.js"></script>
</body>
</html>