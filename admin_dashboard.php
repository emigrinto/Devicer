<?php
include '../backend/admin_auth.php';
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін-панель - Devicer</title>
    <link rel="stylesheet" href="styles.css">
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
        <h1>Адмін-панель</h1>
        <p>Ласкаво просимо, <?php echo htmlspecialchars($_SESSION['admin_first_name'] . ' ' . $_SESSION['admin_last_name']); ?>!</p>
        <p>Виберіть дію:</p>
        <ul>
            <li><a href="admin_add_product.php">Додати новий товар</a></li>
            <li><a href="admin_view_orders.php">Переглянути замовлення</a></li>
            <li><a href="admin_view_support.php">Переглянути запити підтримки</a></li>
        </ul>
        <h2>Вибірки</h2>
        <ul>
            <li><a href="admin_run_queries.php?query=1">Повна інформація про кожен продукт</a></li>
            <li><a href="admin_run_queries.php?query=2">Усі продукти з категорії ''</a></li>
            <li><a href="admin_run_queries.php?query=3">Топ-5 проданих продуктів</a></li>
            <li><a href="admin_run_queries.php?query=4">Усі продукти, куплені клієнтом</a></li>
            <li><a href="admin_run_queries.php?query=5">Топ категорії за продажами</a></li>
            <li><a href="admin_run_queries.php?query=6">Клієнти з більш ніж x замовленнь</a></li>
            <li><a href="admin_run_queries.php?query=7">Найкраще продаваний продукт від кожного виробника</a></li>
            <li><a href="admin_run_queries.php?query=8">Клієнти, які витратили понад x</a></li>
            <li><a href="admin_run_queries.php?query=9">Продукти, які ще не купувалися</a></li>
            <li><a href="admin_run_queries.php?query=10">Продукти з запасом менше 15 одиниць</a></li>
        </ul>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>
</body>
</html>
