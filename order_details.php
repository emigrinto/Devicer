<?php
session_start();
include '../backend/db_connect.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: account.php");
    exit();
}

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Невірний ID замовлення.");
}

$order_id = (int)$_GET['order_id'];
$customer_id = $_SESSION['customer_id'];

try {
    // Fetch order and ensure it belongs to the customer
    $sql = "SELECT o.order_id, o.order_date, i.total AS total_amount, c.first_name, c.last_name, c.email, c.address 
            FROM `Order` o 
            JOIN Customer c ON o.customer_id = c.customer_id 
            JOIN Invoice i ON o.order_id = i.order_id 
            WHERE o.order_id = ? AND o.customer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id, $customer_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Замовлення не знайдено або ви не маєте доступу до нього.");
    }

    // Fetch order items
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
        <div class="logo">
            <a href="index.php">DEVICER</a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Головна</a></li>
                <li><a href="store.php">Магазин</a></li>
                <li class="dropdown">
                    <a href="cart.php" class="dropbtn">Кошик</a>
                    <div class="dropdown-content">
                        <a href="wishlist.php">Список бажань</a>
                    </div>
                </li>
                <li><a href="account.php">Акаунт</a></li>
                <li><a href="support.php">Підтримка</a></li>
                <li class="dropdown">
                    <a href="about.php" class="dropbtn">Про нас</a>
                    <div class="dropdown-content">
                        <a href="contact.php">Контакти</a>
                    </div>
                </li>
            </ul>
        </nav>
        <div class="search-lang">
        <form action="search.php" method="GET" class="search-form">
                 <span class="search-icon">🔍︎</span>
                <input type="text" name="query" placeholder="Пошук..." class="search-input" value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
        </form>
            <select class="language-select">
                <option value="uk">UKR</option>
                <option value="en">ENG</option>
            </select>
        </div>
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

        <p><a href="account.php">Повернутися до акаунта</a></p>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>
</body>
</html>
