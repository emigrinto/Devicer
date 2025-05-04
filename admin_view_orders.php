<?php
include '../backend/admin_auth.php';
include '../backend/db_connect.php';

try {
    $sql = "SELECT o.order_id, o.customer_id, o.order_date, i.total AS total_amount, c.first_name, c.last_name 
            FROM `Order` o 
            JOIN Customer c ON o.customer_id = c.customer_id 
            JOIN Invoice i ON o.order_id = i.order_id 
            ORDER BY o.order_date DESC";
    $stmt = $pdo->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Переглянути замовлення - Devicer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Add some basic styling for clickable rows */
        tr.clickable-row:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }
    </style>
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
        <h1>Замовлення</h1>
        <?php if (!empty($orders)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Замовлення</th>
                        <th>Клієнт</th>
                        <th>Дата замовлення</th>
                        <th>Сума</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr class="clickable-row" onclick="window.location='admin_view_order_details.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>'">
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                            <td><?php echo htmlspecialchars($order['total_amount']); ?> грн</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Замовлень немає.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>
</body>
</html>
