<?php
include '../backend/admin_auth.php';
include '../backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $manufacturer = $_POST['manufacturer'];
    $subcategory = $_POST['subcategory'] ?: null;
    $color = $_POST['color'] ?: null;
    $weight = $_POST['weight'] ?: null;
    $warranty = $_POST['warranty'] ?: null;

    try {
        $sql = "INSERT INTO Product (name, price, description, category, manufacturer, subcategory, color, weight, warranty) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $price, $description, $category, $manufacturer, $subcategory, $color, $weight, $warranty]);

        header("Location: admin_add_product.php?success=Товар успішно додано"); // Changed from add_product.php
        exit();
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
    <title>Додати товар - Devicer</title>
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
        <h1>Додати товар</h1>
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;"><?php echo htmlspecialchars(urldecode($_GET['success'])); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="admin_add_product.php" method="POST"> <!-- Changed from add_product.php -->
            <label for="name">Назва товару:</label>
            <input type="text" id="name" name="name" required>
            <label for="price">Ціна (грн):</label>
            <input type="number" id="price" name="price" step="0.01" required>
            <label for="description">Опис:</label>
            <textarea id="description" name="description" required></textarea>
            <label for="category">Категорія:</label>
            <input type="text" id="category" name="category" required>
            <label for="manufacturer">Виробник:</label>
            <input type="text" id="manufacturer" name="manufacturer" required>
            <label for="subcategory">Підкатегорія (необов’язково):</label>
            <input type="text" id="subcategory" name="subcategory">
            <label for="color">Колір (необов’язково):</label>
            <input type="text" id="color" name="color">
            <label for="weight">Вага (кг, необов’язково):</label>
            <input type="number" id="weight" name="weight" step="0.01">
            <label for="warranty">Гарантія (необов’язково):</label>
            <input type="text" id="warranty" name="warranty">
            <button type="submit">Додати товар</button>
        </form>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>
</body>
</html>