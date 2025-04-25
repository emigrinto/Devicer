<?php
session_start();
include '../backend/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Невірний ID продукту.");
}

$product_id = (int)$_GET['id'];

try {
    $sql = "SELECT p.product_id, p.name, p.price, p.category, p.description, p.manufacturer, p.stock, 
                   COALESCE(AVG(r.rating), 0) as average_rating,
                   (SELECT i.image_url 
                    FROM Images i 
                    WHERE i.product_id = p.product_id AND i.is_primary = TRUE 
                    LIMIT 1) as primary_image
            FROM Product p 
            LEFT JOIN Reviews r ON p.product_id = r.product_id 
            WHERE p.product_id = ?
            GROUP BY p.product_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Продукт не знайдено.");
    }

    $sql = "SELECT image_url, is_primary 
            FROM Images 
            WHERE product_id = ? 
            ORDER BY is_primary DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT characteristic_name, characteristic_value 
            FROM Characteristics 
            WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $characteristics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT r.rating, r.comment, r.review_date, c.first_name, c.last_name 
            FROM Reviews r 
            JOIN Customer c ON r.customer_id = c.customer_id 
            WHERE r.product_id = ? 
            ORDER BY r.review_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Помилка: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['customer_id'])) {
        header("Location: account.php");
        exit();
    }

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_wishlist'])) {
    if (!isset($_SESSION['customer_id'])) {
        header("Location: account.php");
        exit();
    }

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['customer_id'])) {
        header("Location: account.php");
        exit();
    }

    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if ($rating < 1 || $rating > 5) {
        $error = "Невірна оцінка. Виберіть від 1 до 5.";
    } else {
        try {
            $sql = "INSERT INTO Reviews (customer_id, product_id, rating, comment, review_date) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['customer_id'], $product_id, $rating, $comment]);
            $message = "Відгук додано!";
            header("Location: product.php?id=$product_id");
            exit();
        } catch (PDOException $e) {
            $error = 'Помилка при додаванні відгуку: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Devicer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .slideshow-container {
            position: relative;
            max-width: 300px;
            margin: auto;
        }
        .slideshow-image {
            display: none;
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        .slideshow-image.active {
            display: block;
        }
        .prev, .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            padding: 10px;
            color: white;
            background-color: rgba(0, 0, 0, 0.5);
            user-select: none;
        }
        .prev {
            left: 0;
        }
        .next {
            right: 0;
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
        .product-images {
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
        <?php if (isset($message)): ?>
            <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="product-details">
            <div class="product-images">
                <div class="slideshow-container">
                    <?php
                    $is_smartphone = strtolower($product['category']) === 'смартфони';
                    if ($is_smartphone): ?>
                        <span class="discount-badge">Знижка 30%</span>
                    <?php endif; ?>
                    <?php if (!empty($images)): ?>
                        <?php foreach ($images as $index => $image): ?>
                            <img src="<?php echo htmlspecialchars($image['image_url'] ?: 'https://via.placeholder.com/300'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="slideshow-image <?php echo $index === 0 ? 'active' : ''; ?>">
                        <?php endforeach; ?>
                        <?php if (count($images) > 1): ?>
                            <a class="prev" onclick="changeSlide(-1)">❮</a>
                            <a class="next" onclick="changeSlide(1)">❯</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Зображення відсутні.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
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
                <p><strong>Категорія:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                <p><strong>Виробник:</strong> <?php echo htmlspecialchars($product['manufacturer'] ?: 'Невідомо'); ?></p>
                <p><strong>Наявність:</strong> <?php echo $product['stock'] > 0 ? 'В наявності (' . htmlspecialchars($product['stock']) . ' шт.)' : 'Немає в наявності'; ?></p>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <form method="POST" action="product.php?id=<?php echo htmlspecialchars($product_id); ?>">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                    <button type="submit" name="add_to_cart">Додати до кошика</button>
                </form>
                <form method="POST" action="product.php?id=<?php echo htmlspecialchars($product_id); ?>">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                    <button type="submit" name="add_to_wishlist">Додати до списку бажань</button>
                </form>
            </div>
        </div>

        <div class="product-characteristics">
            <h2>Характеристики</h2>
            <?php if (!empty($characteristics)): ?>
                <ul>
                    <?php foreach ($characteristics as $char): ?>
                        <li><strong><?php echo htmlspecialchars($char['characteristic_name']); ?>:</strong> <?php echo htmlspecialchars($char['characteristic_value']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Характеристики відсутні.</p>
            <?php endif; ?>
        </div>

        <div class="product-reviews">
            <h2>Відгуки</h2>
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review">
                        <p><strong><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>:</strong> Оцінка: <?php echo htmlspecialchars($review['rating']); ?>/5</p>
                        <p><?php echo htmlspecialchars($review['comment'] ?: 'Без коментаря'); ?></p>
                        <p><small>Дата: <?php echo htmlspecialchars($review['review_date']); ?></small></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Поки що немає відгуків.</p>
            <?php endif; ?>

            <?php if (isset($_SESSION['customer_id'])): ?>
                <h3>Залишити відгук</h3>
                <form method="POST" action="product.php?id=<?php echo htmlspecialchars($product_id); ?>">
                    <label for="rating">Оцінка (1-5):</label>
                    <select name="rating" id="rating" required>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                    <label for="comment">Коментар:</label>
                    <textarea name="comment" id="comment"></textarea>
                    <button type="submit" name="submit_review">Надіслати відгук</button>
                </form>
            <?php else: ?>
                <p><a href="account.php">Увійдіть</a>, щоб залишити відгук.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slideshow-image');

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === index) {
                    slide.classList.add('active');
                }
            });
        }

        function changeSlide(direction) {
            currentSlide += direction;
            if (currentSlide < 0) {
                currentSlide = slides.length - 1;
            } else if (currentSlide >= slides.length) {
                currentSlide = 0;
            }
            showSlide(currentSlide);
        }

        // Initialize the slideshow
        showSlide(currentSlide);
    </script>
</body>
</html>