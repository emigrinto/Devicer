<?php
include '../backend/admin_auth.php';
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін-панель - Devicer</title>
    <link rel="stylesheet" href="styles.css"> <!-- Already correct -->
</head>
<body>
    <header>
        <div class="logo">DEVICER - Адмін</div>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Панель</a></li> <!-- Changed from dashboard.php -->
                <li><a href="admin_add_product.php">Додати товар</a></li> <!-- Changed from add_product.php -->
                <li><a href="admin_view_orders.php">Замовлення</a></li> <!-- Changed from view_orders.php -->
                <li><a href="admin_view_support.php">Запити підтримки</a></li> <!-- Changed from view_support.php -->
                <li><a href="admin_logout.php">Вийти</a></li> <!-- Changed from logout.php -->
            </ul>
        </nav>
    </header>

    <main>
        <h1>Адмін-панель</h1>
        <p>Ласкаво просимо, <?php echo htmlspecialchars($_SESSION['admin_first_name'] . ' ' . $_SESSION['admin_last_name']); ?>!</p>
        <p>Виберіть дію:</p>
        <ul>
            <li><a href="admin_add_product.php">Додати новий товар</a></li> <!-- Changed from add_product.php -->
            <li><a href="admin_view_orders.php">Переглянути замовлення</a></li> <!-- Changed from view_orders.php -->
            <li><a href="admin_view_support.php">Переглянути запити підтримки</a></li> <!-- Changed from view_support.php -->
        </ul>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>
</body>
</html>