<?php
session_start();
include '../backend/db_connect.php';

// Redirect admins to admin_dashboard.php
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch customer details if logged in
$customer = null;
$orders = [];
$reviews = [];
if (isset($_SESSION['customer_id'])) {
    try {
        // Fetch customer details
        $sql = "SELECT customer_id, customer_username, first_name, last_name, email, address, phone_number 
                FROM Customer 
                WHERE customer_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['customer_id']]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch order history
        $sql = "SELECT o.order_id, o.order_date, i.total AS total_amount 
                FROM `Order` o 
                JOIN Invoice i ON o.order_id = i.order_id 
                WHERE o.customer_id = ? 
                ORDER BY o.order_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['customer_id']]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch reviews
        $sql = "SELECT r.review_id, r.rating, r.comment, r.review_date, p.name AS product_name, p.product_id 
                FROM Reviews r 
                JOIN Product p ON r.product_id = p.product_id 
                WHERE r.customer_id = ? 
                ORDER BY r.review_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['customer_id']]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Помилка при отриманні даних: ' . $e->getMessage();
    }
}

// Handle login for non-logged-in users
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['customer_id'])) {
    $identifier = $_POST['identifier']; // Can be username or email
    $password = $_POST['password'];

    try {
        // Determine if the identifier is an email or username
        $is_email = filter_var($identifier, FILTER_VALIDATE_EMAIL);
        $is_admin = false;

        // Check if the identifier is an email with @devicer.com domain
        if ($is_email && str_ends_with(strtolower($identifier), '@devicer.com')) {
            $is_admin = true;
        }

        if ($is_admin) {
            // Try admin login using email
            $sql = "SELECT admin_id, admin_username, admin_password, first_name, last_name FROM Admin WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$identifier]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && $password === $admin['admin_password']) { // Plain text password comparison
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_first_name'] = $admin['first_name'];
                $_SESSION['admin_last_name'] = $admin['last_name'];
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = 'Невірний email або пароль для адміністратора.';
            }
        }

        // If not an admin by email, check if the username exists in the Admin table
        if (!$is_admin) {
            $sql = "SELECT admin_id, admin_username, admin_password, first_name, last_name FROM Admin WHERE admin_username = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$identifier]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && $password === $admin['admin_password']) { // Plain text password comparison
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_first_name'] = $admin['first_name'];
                $_SESSION['admin_last_name'] = $admin['last_name'];
                header("Location: admin_dashboard.php");
                exit();
            }
        }

        // If admin login fails, try customer login
        if ($is_email) {
            $sql = "SELECT customer_id, customer_username, customer_password, first_name, last_name FROM Customer WHERE email = ?";
        } else {
            $sql = "SELECT customer_id, customer_username, customer_password, first_name, last_name FROM Customer WHERE customer_username = ?";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$identifier]);
        $customer_login = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer_login && $password === $customer_login['customer_password']) { // Plain text password comparison
            $_SESSION['customer_id'] = $customer_login['customer_id'];
            $_SESSION['first_name'] = $customer_login['first_name'];
            $_SESSION['last_name'] = $customer_login['last_name'];
            header("Location: account.php"); // Redirect back to account.php to show details
            exit();
        } else {
            $error = 'Невірний логін або пароль.';
        }
    } catch (PDOException $e) {
        $error = 'Помилка: ' . $e->getMessage();
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: account.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Акаунт - Devicer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Add some basic styling for clickable rows */
        tr.clickable-row:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }
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
        <?php if (isset($customer)): ?>
            <h1>Ваш акаунт</h1>
            <p><strong>Ім'я:</strong> <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
            <p><strong>Адреса:</strong> <?php echo htmlspecialchars($customer['address']); ?></p>
            <p><strong>Телефон:</strong> <?php echo htmlspecialchars($customer['phone_number']); ?></p>

            <h2>Історія замовлень</h2>
            <?php if (!empty($orders)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID Замовлення</th>
                            <th>Дата замовлення</th>
                            <th>Сума</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr class="clickable-row" onclick="window.location='order_details.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>'">
                                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                <td><?php echo htmlspecialchars($order['total_amount']); ?> грн</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>У вас немає замовлень.</p>
            <?php endif; ?>

            <h2>Ваші відгуки</h2>
            <?php if (!empty($reviews)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th>Оцінка</th>
                            <th>Коментар</th>
                            <th>Дата відгуку</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><a href="product.php?id=<?php echo htmlspecialchars($review['product_id']); ?>"><?php echo htmlspecialchars($review['product_name']); ?></a></td>
                                <td><?php echo htmlspecialchars($review['rating']); ?>/5</td>
                                <td><?php echo htmlspecialchars($review['comment'] ?: 'Без коментаря'); ?></td>
                                <td><?php echo htmlspecialchars($review['review_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Ви ще не залишали відгуків.</p>
            <?php endif; ?>

            <p><a href="account.php?logout=true">Вийти</a></p>
        <?php else: ?>
            <h1>Вхід</h1>
            <?php if ($error): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form action="account.php" method="POST">
                <label for="identifier">Логін або Email:</label>
                <input type="text" id="identifier" name="identifier" required>
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">Увійти</button>
            </form>
            <p>Немає акаунта? <a href="register.php">Зареєструйтесь</a>.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>

    <script src="scripts.js"></script>
</body>
</html>