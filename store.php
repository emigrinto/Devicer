<?php
session_start();
include '../backend/db_connect.php';

$where_clauses = [];
$params = [];

$category = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : null;
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$manufacturer = isset($_GET['manufacturer']) && $_GET['manufacturer'] !== '' ? $_GET['manufacturer'] : null;
$screen_size = isset($_GET['screen_size']) && $_GET['screen_size'] !== '' ? $_GET['screen_size'] : null;
$storage = isset($_GET['storage']) && $_GET['storage'] !== '' ? $_GET['storage'] : null;

if ($category) {
    $where_clauses[] = "p.category = ?";
    $params[] = $category;
}

if ($min_price !== null) {
    $where_clauses[] = "p.price >= ?";
    $params[] = $min_price;
}

if ($max_price !== null) {
    $where_clauses[] = "p.price <= ?";
    $params[] = $max_price;
}

if ($manufacturer) {
    $where_clauses[] = "p.manufacturer = ?";
    $params[] = $manufacturer;
}

if ($screen_size || $storage) {
    $subquery_conditions = [];
    $subquery_params = [];

    if ($screen_size) {
        $subquery_conditions[] = "c.characteristic_name = 'Screen Size' AND c.characteristic_value = ?";
        $subquery_params[] = $screen_size;
    }

    if ($storage) {
        $subquery_conditions[] = "c.characteristic_name = 'Storage Capacity' AND c.characteristic_value = ?";
        $subquery_params[] = $storage;
    }

    $subquery = "SELECT product_id FROM Characteristics c WHERE " . implode(' OR ', $subquery_conditions);
    $where_clauses[] = "p.product_id IN ($subquery)";
    $params = array_merge($params, $subquery_params);
}

$where = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
$order_by = '';
switch ($sort) {
    case 'name_asc':
        $order_by = 'p.name ASC';
        break;
    case 'name_desc':
        $order_by = 'p.name DESC';
        break;
    case 'price_asc':
        $order_by = 'p.price ASC';
        break;
    case 'price_desc':
        $order_by = 'p.price DESC';
        break;
    case 'rating_desc':
        $order_by = 'average_rating DESC';
        break;
    default:
        $order_by = 'p.name ASC';
}

try {
    $sql = "SELECT p.product_id, p.name, p.price, p.category, p.description, 
                   COALESCE(AVG(r.rating), 0) as average_rating,
                   (SELECT i.image_url 
                    FROM Images i 
                    WHERE i.product_id = p.product_id AND i.is_primary = TRUE 
                    LIMIT 1) as image_url
            FROM Product p 
            LEFT JOIN Reviews r ON p.product_id = r.product_id
            $where
            GROUP BY p.product_id
            ORDER BY $order_by";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT DISTINCT category FROM Product";
    $stmt = $pdo->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT DISTINCT manufacturer FROM Product WHERE manufacturer IS NOT NULL";
    $stmt = $pdo->query($sql);
    $manufacturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT DISTINCT characteristic_value FROM Characteristics WHERE characteristic_name = 'Screen Size'";
    $stmt = $pdo->query($sql);
    $screen_sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT DISTINCT characteristic_value FROM Characteristics WHERE characteristic_name = 'Storage Capacity'";
    $stmt = $pdo->query($sql);
    $storages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Помилка: ' . $e->getMessage();
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
    <title>Магазин - Devicer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .filter-sort-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .filter-button {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .filter-button:hover {
            background-color: #2980b9;
        }
        .filter-panel {
            display: none;
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filter-panel.active {
            display: block;
        }
        .filter-panel label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .filter-panel select, .filter-panel input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .filter-panel button {
            background-color: #2ecc71;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .filter-panel button:hover {
            background-color: #27ae60;
        }
        .sort-form select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .product img {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .product button[name="add_to_cart"] {
            padding: 0.5rem 1.5rem; /* Default size */
            font-size: 0.9rem;
        }
        .product button[name="add_to_wishlist"] {
            padding: 0.4rem 1rem; /* Smaller size */
            font-size: 0.8rem;
        }
        .discount-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #e74c3c;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .product {
            position: relative;
        }
    </style>
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
        <h1>Магазин</h1>

        <div class="filter-sort-container">
            <button class="filter-button" onclick="toggleFilterPanel()">Фільтри</button>
            <div class="sort-form">
                <form method="GET" action="store.php">
                    <select name="sort" onchange="this.form.submit()">
                        <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Назва (А-Я)</option>
                        <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Назва (Я-А)</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Ціна (зростання)</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Ціна (спадання)</option>
                        <option value="rating_desc" <?php echo $sort === 'rating_desc' ? 'selected' : ''; ?>>Рейтинг (спадання)</option>
                    </select>
                    <?php foreach ($_GET as $key => $value): ?>
                        <?php if ($key !== 'sort' && $value !== ''): ?>
                            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </form>
            </div>
        </div>

        <div class="filter-panel" id="filterPanel">
            <form method="GET" action="store.php">
                <label for="category">Категорія:</label>
                <select name="category" id="category">
                    <option value="">Усі категорії</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="min_price">Мінімальна ціна (грн):</label>
                <input type="number" name="min_price" id="min_price" value="<?php echo htmlspecialchars($min_price ?? ''); ?>" placeholder="Напр. 5000">

                <label for="max_price">Максимальна ціна (грн):</label>
                <input type="number" name="max_price" id="max_price" value="<?php echo htmlspecialchars($max_price ?? ''); ?>" placeholder="Напр. 15000">

                <label for="manufacturer">Виробник:</label>
                <select name="manufacturer" id="manufacturer">
                    <option value="">Усі виробники</option>
                    <?php foreach ($manufacturers as $man): ?>
                        <option value="<?php echo htmlspecialchars($man['manufacturer']); ?>" <?php echo $manufacturer === $man['manufacturer'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($man['manufacturer']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="screen_size">Розмір екрану:</label>
                <select name="screen_size" id="screen_size">
                    <option value="">Усі розміри</option>
                    <?php foreach ($screen_sizes as $size): ?>
                        <option value="<?php echo htmlspecialchars($size['characteristic_value']); ?>" <?php echo $screen_size === $size['characteristic_value'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($size['characteristic_value']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="storage">Обсяг пам'яті:</label>
                <select name="storage" id="storage">
                    <option value="">Усі обсяги</option>
                    <?php foreach ($storages as $stor): ?>
                        <option value="<?php echo htmlspecialchars($stor['characteristic_value']); ?>" <?php echo $storage === $stor['characteristic_value'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($stor['characteristic_value']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Застосувати фільтри</button>
            </form>
        </div>

        <?php if (isset($message)): ?>
            <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="products">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product">
                        <a href="product.php?id=<?php echo htmlspecialchars($product['product_id']); ?>">
                            <?php
                            $is_smartphone = strtolower($product['category']) === 'смартфони';
                            if ($is_smartphone): ?>
                                <span class="discount-badge">Знижка 30%</span>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/150'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p><?php echo htmlspecialchars(number_format($product['price'], 2, '.', ' ')); ?> грн</p>
                            <div class="product-rating">
                                <?php
                                $average_rating = round($product['average_rating']);
                                for ($i = 1; $i <= 5; $i++):
                                    if ($i <= $average_rating):
                                ?>
                                    <span class="star filled">★</span>
                                <?php else: ?>
                                    <span class="star">☆</span>
                                <?php endif; endfor; ?>
                                <span>(<?php echo number_format($product['average_rating'], 1); ?>)</span>
                            </div>
                        </a>
                        <form method="POST" action="store.php">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                            <button type="submit" name="add_to_cart">Додати до кошика</button>
                        </form>
                        <form method="POST" action="store.php">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                            <button type="submit" name="add_to_wishlist">Додати до списку бажань</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Немає продуктів для відображення.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>

    <script>
        function toggleFilterPanel() {
            const panel = document.getElementById('filterPanel');
            panel.classList.toggle('active');
        }
    </script>
</body>
</html>