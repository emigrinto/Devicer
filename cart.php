<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: account.php");
    exit();
}

include '../backend/view_cart.php';
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кошик - Devicer</title>
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
        <h1>Кошик</h1>
        <div class="cart-items">
            <?php
            if (!empty($cart_items)) {
                $total = 0;
                foreach ($cart_items as $item) {
                    echo '<div class="cart-item">';
                    echo '<img src="' . htmlspecialchars($item['image_url']) . '" alt="' . htmlspecialchars($item['name']) . '">';
                    echo '<h3>' . htmlspecialchars($item['name']) . '</h3>';
                    echo '<p>Ціна: ' . htmlspecialchars($item['price']) . ' грн</p>';
                    echo '<p>Кількість: ' . htmlspecialchars($item['quantity']) . '</p>';
                    echo '<p>Сума: ' . ($item['price'] * $item['quantity']) . ' грн</p>';
                    echo '<button class="remove-from-cart" data-id="' . htmlspecialchars($item['cart_id']) . '">Видалити</button>';
                    echo '</div>';
                    $total += $item['price'] * $item['quantity'];
                }
                echo '<h2>Загальна сума: ' . $total . ' грн</h2>';
                echo '<a href="checkout.php"><button>Оформити замовлення</button></a>';
            } else {
                echo '<p>Ваш кошик порожній.</p>';
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
