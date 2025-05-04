<?php
session_start();
include '../backend/db_connect.php';

try {
    // Fetch featured products with primary image using a subquery
    $sql = "SELECT p.product_id, p.name, p.price, p.category, p.description, 
                   (SELECT i.image_url 
                    FROM Images i 
                    WHERE i.product_id = p.product_id AND i.is_primary = TRUE 
                    LIMIT 1) as image_url
            FROM Product p 
            ORDER BY RAND() 
            LIMIT 4";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Помилка при отриманні продуктів: ' . $e->getMessage();
}

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['customer_id'])) {
        header("Location: account.php");
        exit();
    }

    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    try {
        $sql = "SELECT quantity FROM Cart WHERE customer_id = ? AND product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['customer_id'], $product_id]);
        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart_item) {
            $new_quantity = $cart_item['quantity'] + $quantity;
            $sql = "UPDATE Cart SET quantity = ? WHERE customer_id = ? AND product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_quantity, $_SESSION['customer_id'], $product_id]);
        } else {
            $sql = "INSERT INTO Cart (customer_id, product_id, quantity, added_date) VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['customer_id'], $product_id, $quantity]);
        }

        $message = "Товар додано до кошика!";
    } catch (PDOException $e) {
        $error = 'Помилка при додаванні до кошика: ' . $e->getMessage();
    }
}

// Handle adding to wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_wishlist'])) {
    if (!isset($_SESSION['customer_id'])) {
        header("Location: account.php");
        exit();
    }

    $product_id = $_POST['product_id'];

    try {
        $sql = "SELECT * FROM Wishlist WHERE customer_id = ? AND product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['customer_id'], $product_id]);
        $wishlist_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$wishlist_item) {
            $sql = "INSERT INTO Wishlist (customer_id, product_id, added_date) VALUES (?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['customer_id'], $product_id]);
            $message = "Товар додано до списку бажань!";
        } else {
            $message = "Товар вже у списку бажань!";
        }
    } catch (PDOException $e) {
        $error = 'Помилка при додаванні до списку бажань: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devicer - Головна</title>
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
    <div class="banner">
            <h2>Ласкаво просимо до Devicer!</h2>
            <p>Ознайомтесь з нашими новітніми технологіями та пропозиціями.</p>
            <a href="../frontend/store.php"><button>Переглянути магазин</button></a>
        </div>

        <div class="categories">
            <h2>Рекомендовані Категорії</h2>
            <div class="category-list">
                <a href="store.php?category=смартфони" class="category-item">Смартфони</a>
                <a href="store.php?category=аксесуари" class="category-item">Аксесуари</a>
                <a href="store.php?manufacturer=Apple" class="category-item">Apple</a>
            </div>
        </div>

        <div class="featured-products">
            <h2>Рекомендовані продукти</h2>
            <div class="products">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product">
                            <?php
                            $is_smartphone = strtolower($product['category']) === 'смартфони';
                            if ($is_smartphone): ?>
                                <span class="discount-badge">Знижка 30%</span>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/150'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h3><a href="product.php?id=<?php echo htmlspecialchars($product['product_id']); ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                            <p><?php echo htmlspecialchars(number_format($product['price'], 2, '.', ' ')); ?> грн</p>
                            <form method="POST" action="index.php">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                                <button type="submit" name="add_to_cart">Додати до кошика</button>
                            </form>
                            <form method="POST" action="index.php">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                                <button type="submit" name="add_to_wishlist">Додати до списку бажань</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Немає рекомендованих продуктів.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>

    <script src="scripts.js"></script>
</body>
</html>
