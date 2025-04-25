<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: account.php");
    exit();
}
include '../backend/view_wishlist.php';
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список бажань - Devicer</title>
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
        <h1>Список бажань</h1>
        <div class="wishlist-items">
            <?php
            if (!empty($wishlist_items)) {
                foreach ($wishlist_items as $item) {
                    echo '<div class="wishlist-item">';
                    echo '<img src="' . htmlspecialchars($item['image_url']) . '" alt="' . htmlspecialchars($item['name']) . '">';
                    echo '<h3><a href="product.php?id=' . htmlspecialchars($item['product_id']) . '">' . htmlspecialchars($item['name']) . '</a></h3>';
                    echo '<p>Ціна: ' . htmlspecialchars($item['price']) . ' грн</p>';
                    echo '<button class="add-to-cart-from-wishlist" data-id="' . htmlspecialchars($item['product_id']) . '">Додати до кошика</button>';
                    echo '<button class="remove-from-wishlist" data-id="' . htmlspecialchars($item['wishlist_id']) . '">Видалити</button>';
                    echo '</div>';
                }
            } else {
                echo '<p>Ваш список бажань порожній.</p>';
            }
            ?>
        </div>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>

    <script src="scripts.js"></script>
</body>
</html>