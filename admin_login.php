<?php
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