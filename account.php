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
        $sql = "SELECT customer_id, customer_username, first_name, last_name, email, address, phone_number 
                FROM Customer 
                WHERE customer_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['customer_id']]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT o.order_id, o.order_date, i.total AS total_amount 
                FROM `Order` o 
                JOIN Invoice i ON o.order_id = i.order_id 
                WHERE o.customer_id = ? 
                ORDER BY o.order_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['customer_id']]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['customer_id']) && !isset($_POST['register'])) {
    $identifier = $_POST['identifier'];
    $password = $_POST['password'];

    try {
        $is_email = filter_var($identifier, FILTER_VALIDATE_EMAIL);
        $is_admin = false;

        if ($is_email && str_ends_with(strtolower($identifier), '@devicer.com')) {
            $is_admin = true;
        }

        if ($is_admin) {
            $sql = "SELECT admin_id, admin_username, admin_password, first_name, last_name FROM Admin WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$identifier]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && $password === $admin['admin_password']) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_first_name'] = $admin['first_name'];
                $_SESSION['admin_last_name'] = $admin['last_name'];
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = 'Невірний email або пароль для адміністратора.';
            }
        }

        if (!$is_admin) {
            $sql = "SELECT admin_id, admin_username, admin_password, first_name, last_name FROM Admin WHERE admin_username = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$identifier]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && $password === $admin['admin_password']) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_first_name'] = $admin['first_name'];
                $_SESSION['admin_last_name'] = $admin['last_name'];
                header("Location: admin_dashboard.php");
                exit();
            }
        }

        if ($is_email) {
            $sql = "SELECT customer_id, customer_username, customer_password, first_name, last_name FROM Customer WHERE email = ?";
        } else {
            $sql = "SELECT customer_id, customer_username, customer_password, first_name, last_name FROM Customer WHERE customer_username = ?";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$identifier]);
        $customer_login = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer_login && $password === $customer_login['customer_password']) {
            $_SESSION['customer_id'] = $customer_login['customer_id'];
            $_SESSION['first_name'] = $customer_login['first_name'];
            $_SESSION['last_name'] = $customer_login['last_name'];
            header("Location: account.php");
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
    <?php if (isset($_GET['registered'])): ?>
        <p style="color: green;">Реєстрація успішна! Увійдіть.</p>
    <?php endif; ?>
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
        <?php if (isset($_GET['register'])): ?>
            <h1>Реєстрація</h1>
            <form action="../backend/register_customer.php" method="POST">
                <label for="reg_first_name">Ім'я:</label>
                <input type="text" id="reg_first_name" name="first_name" required>
                <label for="reg_last_name">Прізвище:</label>
                <input type="text" id="reg_last_name" name="last_name" required>
                <label for="reg_email">Email:</label>
                <input type="email" id="reg_email" name="email" required>
                <label for="reg_password">Пароль:</label>
                <input type="password" id="reg_password" name="password" required>
                <label for="reg_address">Адреса:</label>
                <input type="text" id="reg_address" name="address" required>
                <label for="reg_gender">Стать:</label>
                <select id="reg_gender" name="gender" required>
                    <option value="Чоловік">Чоловік</option>
                    <option value="Жінка">Жінка</option>
                    <option value="Інше">Інше</option>
                </select>
                <label for="reg_birthdate">Дата народження:</label>
                <input type="date" id="reg_birthdate" name="birthdate" required>
                <label for="reg_phone">Номер телефону:</label>
                <input type="text" id="reg_phone" name="phone_number" required>
                <button type="submit" name="register">Зареєструватись</button>
            </form>
            <p>Вже є акаунт? <a href="account.php">Увійти</a>.</p>
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
            <p>Немає акаунта? <a href="account.php?register=1">Зареєструйтесь</a>.</p>
        <?php endif; ?>
    <?php endif; ?>
</main>

<footer>
    <p>© 2025 Devicer. Усі права захищені.</p>
</footer>

<script src="scripts.js"></script>
</body>
</html>
