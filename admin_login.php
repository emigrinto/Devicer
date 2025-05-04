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

$error = '';<?php
session_start();
include '../backend/db_connect.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php"); // Changed from dashboard.php
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $sql = "SELECT admin_id, admin_password, first_name, last_name FROM Admin WHERE admin_username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['admin_password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_first_name'] = $admin['first_name'];
            $_SESSION['admin_last_name'] = $admin['last_name'];
            header("Location: admin_dashboard.php"); // Changed from dashboard.php
            exit();
        } else {
            $error = 'Невірний логін або пароль.';
        }
    } catch (PDOException $e) {
        $error = 'Помилка: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід для адміністратора - Devicer</title>
    <link rel="stylesheet" href="styles.css"> <!-- Already correct, as styles.css is in frontend/ -->
</head>
<body>
    <header>
        <div class="logo">DEVICER - Адмін</div>
    </header>

    <main>
        <h1>Вхід для адміністратора</h1>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="admin_login.php" method="POST"> <!-- Changed from login.php -->
            <label for="username">Логін:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Увійти</button>
        </form>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>
</body>
</html>
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
