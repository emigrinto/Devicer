<?php
session_start();
include '../backend/db_connect.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$products = [];

if ($query) {
    try {
        // Search products by name, category, or description
        $sql = "SELECT product_id, name, price, category, description 
                FROM Product 
                WHERE name LIKE ? OR category LIKE ? OR description LIKE ?";
        $search_term = '%' . $query . '%';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$search_term, $search_term, $search_term]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Помилка при пошуку: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результати пошуку - Devicer</title>
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
        <h1>Результати пошуку для "<?php echo htmlspecialchars($query); ?>"</h1>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($query): ?>
            <?php if (!empty($products)): ?>
                <ul>
                    <?php foreach ($products as $product): ?>
                        <li>
                            <a href="product.php?id=<?php echo htmlspecialchars($product['product_id']); ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a> - <?php echo htmlspecialchars($product['price']); ?> грн
                            <p><strong>Категорія:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Нічого не знайдено за запитом "<?php echo htmlspecialchars($query); ?>".</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Будь ласка, введіть пошуковий запит.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>

    <script src="scripts.js"></script>
</body>
</html>