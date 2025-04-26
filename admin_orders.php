<?php
session_start();

// Redirect to login if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$view = isset($_GET['order_id']) && is_numeric($_GET['order_id']) ? 'details' : 'list';
$orders = [];
$order = null;
$items = [];
$error = '';

try {
    if ($view === 'list') {
        $post_data = http_build_query(['action' => 'view_orders']);
        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => $post_data
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents('../backend/admin_order_actions.php', false, $context);
        $response = json_decode($result, true);

        if ($response['success']) {
            $orders = $response['orders'];
        } else {
            $error = $response['error'];
        }
    } else {
        $order_id = (int)$_GET['order_id'];
        $post_data = http_build_query(['action' => 'view_order_details', 'order_id' => $order_id]);
        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => $post_data
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents('../backend/admin_order_actions.php', false, $context);
        $response = json_decode($result, true);

        if ($response['success']) {
            $order = $response['order'];
            $items = $response['items'];
        } else {
            $error = $response['error'];
        }
    }
} catch (Exception $e) {
    $error = 'Помилка: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $view === 'list' ? 'Переглянути замовлення' : 'Деталі замовлення #' . htmlspecialchars($_GET['order_id']); ?> - Devicer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr.clickable-row:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <a href="admin_dashboard.php">DEVICER - Адмін</a>
    </div>
    <nav>
        <ul>
            <li><a href="admin_dashboard.php">Панель</a></li>
            <li><a href="admin_add_product.php">Додати товар</a></li>
            <li><a href="admin_orders.php">Замовлення</a></li>
            <li><a href="admin_view_support.php">Запити підтримки</a></li>
            <li><a href="../backend/auth_admin.php?action=logout">Вийти</a></li>
        </ul>
    </nav>
</header>

<main>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($view === 'list'): ?>
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
                        <tr class="clickable-row" onclick="window.location='admin_orders.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>'">
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
    <?php else: ?>
        <h1>Деталі замовлення #<?php echo htmlspecialchars($order['order_id']); ?></h1>
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

        <p><a href="admin_orders.php">Повернутися до списку замовлень</a></p>
    <?php endif; ?>
</main>

<footer>
    <p>© 2025 Devicer. Усі права захищені.</p>
</footer>
</body>
</html>
