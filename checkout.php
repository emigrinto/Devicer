<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect admins to admin dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch checkout data (cart items and address) by calling the backend
$items = [];
$total = 0;
$address = '';
$error = '';
$message = '';

try {
    $post_data = http_build_query(['action' => 'get_checkout_data']);
    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => $post_data
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents('../backend/cart_actions.php', false, $context);
    $response = json_decode($result, true);

    if ($response['success']) {
        $items = $response['items'];
        $total = $response['total'];
        $address = $response['address'];
    } else {
        $error = $response['error'];
    }
} catch (Exception $e) {
    $error = 'Помилка: ' . $e->getMessage();
}

// Handle messages from previous actions (e.g., order confirmation)
if (isset($_SESSION['checkout_message'])) {
    $message = $_SESSION['checkout_message'];
    unset($_SESSION['checkout_message']);
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення - Devicer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .cart-items {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .cart-item {
            border: 1px solid #ddd;
            padding: 1rem;
            width: 200px;
            text-align: center;
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        form {
            margin-top: 2rem;
        }
        label {
            display: block;
            margin: 0.5rem 0;
        }
        input[type="text"] {
            width: 100%;
            max-width: 400px;
            padding: 0.5rem;
            margin-bottom: 1rem;
        }
        button {
            padding: 0.5rem 1rem;
            cursor: pointer;
        }
        .message {
            margin: 1rem 0;
        }
    </style>
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
            <li><a href="cart.php">Кошик</a></li>
            <li><a href="wishlist.php">Список бажань</a></li>
            <li><a href="account.php">Акаунт</a></li>
            <li><a href="support.php">Підтримка</a></li>
        </ul>
    </nav>
    <div class="search-lang">
        <form action="search.php" method="GET" class="search-form">
            <input type="text" name="query" placeholder="Пошук..." class="search-input" value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
            <button type="submit" class="search-button">Шукати</button>
        </form>
        <select class="language-select">
            <option value="uk">UKR</option>
            <option value="en">ENG</option>
        </select>
    </div>
</header>

<main>
    <h1>Оформлення замовлення</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
        <p class="message" style="color: green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <div class="cart-items">
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'https://via.placeholder.com/100'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p>Ціна: <?php echo htmlspecialchars($item['price']); ?> грн</p>
                    <p>Кількість: <?php echo htmlspecialchars($item['quantity']); ?></p>
                    <p>Сума: <?php echo htmlspecialchars($item['price'] * $item['quantity']); ?> грн</p>
                </div>
            <?php endforeach; ?>
            <h2>Загальна сума: <?php echo htmlspecialchars($total); ?> грн</h2>
        <?php else: ?>
            <p>Ваш кошик порожній.</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($items)): ?>
        <form method="POST" action="../backend/checkout_process.php">
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
