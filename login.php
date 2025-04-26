<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['customer_id'])) {
    header("Location: account.php");
    exit();
}
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';
    $is_admin = isset($_POST['is_admin']) && $_POST['is_admin'] === '1';

    $auth_script = $is_admin ? '../backend/auth_admin.php' : '../backend/auth_customer.php';
    $action = 'login';

    // Prepare POST data for the backend script
    $post_data = http_build_query([
        'action' => $action,
        'identifier' => $identifier,
        'password' => $password
    ]);

    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => $post_data
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($auth_script, false, $context);
    $response = json_decode($result, true);

    if ($response['success']) {
        if ($is_admin) {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: account.php");
        }
        exit();
    } else {
        $error = $response['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід - Devicer</title>
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
    <h1>Вхід</h1>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <label for="identifier">Логін або Email:</label>
        <input type="text" id="identifier" name="identifier" required>
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>
        <label for="is_admin">Вхід як адміністратор:</label>
        <input type="checkbox" id="is_admin" name="is_admin" value="1">
        <button type="submit">Увійти</button>
    </form>
    <p>Немає акаунта? <a href="register.php">Зареєструйтесь</a>.</p>
</main>

<footer>
    <p>© 2025 Devicer. Усі права захищені.</p>
</footer>

<script src="scripts.js"></script>
</body>
</html>