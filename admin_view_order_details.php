<?php
include '../backend/admin_auth.php';
include '../backend/db_connect.php';

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Невірний ID замовлення.");
}

$order_id = (int)$_GET['order_id'];

try {
    // Fetch order and customer info
    $sql = "SELECT o.order_id, o.order_date, i.total AS total_amount, c.first_name, c.last_name, c.email, c.address 
            FROM `Order` o 
            JOIN Customer c ON o.customer_id = c.customer_id 
            JOIN Invoice i ON o.order_id = i.order_id 
            WHERE o.order_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Замовлення не знайдено.");
    }

    // Fetch order items (products)
    $sql = "SELECT od.quantity, p.product_id, p.name, p.price 
            FROM Order_details od 
            JOIN Product p ON od.product_id = p.product_id 
            WHERE od.order_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Помилка при отриманні деталей замовлення: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Деталі замовлення #<?php echo htmlspecialchars($order_id); ?> - Devicer</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo">DEVICER - Адмін</div>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Панель</a></li>
                <li><a href="admin_add_product.php">Додати товар</a></li>
                <li><a href="admin_view_orders.php">Замовлення</a></li>
                <li><a href="admin_view_support.php">Запити підтримки</a></li>
                <li><a href="admin_logout.php">Вийти</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Деталі замовлення #<?php echo htmlspecialchars($order_id); ?></h1>
        <h2>Інформація про замовлення</h2>
        <p><strong>Дата замовлення:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
        <p><strong>Загальна сума:</strong> <?php echo htmlspecialchars($order['total_amount']); ?> грн</p>

        <h2>Інформація про клієнта</h2>
        <p><strong>Ім'я:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
        <p><strong>Адреса:</strong> <?php echo htmlspecialchars($order['address']); ?></p>

        <h2>Товари у замовленні</h2>
        <?php if (!empty($items)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Ціна за одиницю</th>
                        <th>Кількість</th>
                        <th>Вартість</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><a href="product.php?id=<?php echo htmlspecialchars($item['product_id']); ?>"><?php echo htmlspecialchars($item['name']); ?></a></td>
                            <td><?php echo htmlspecialchars($item['price']); ?> грн</td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($item['price'] * $item['quantity']); ?> грн</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Товари у замовленні відсутні.</p>
        <?php endif; ?>

        <p><a href="admin_view_orders.php">Повернутися до списку замовлень</a></p>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>
</body>
</html>