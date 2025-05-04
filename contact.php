<?php
session_start();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакти - Devicer</title>
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
    <section class="contact-content">
        <h1>Зв’яжіться з нами</h1>
        <div class="contact-details">
            <p><strong>Адреса:</strong> вул. Шевченка, 1, Львів, Україна</p>
            <p><strong>Телефон:</strong> +380 96 053 2888</p>
            <p><strong>Email:</strong> support@devicer.com</p>
            <p><strong>Соціальні мережі:</strong> 
                <a href="https://instagram.com/devicer_ua">Instagram</a> | 
                <a href="https://facebook.com/devicer.ua">Facebook</a>
            </p>
        </div>
    </section>
</main>

<footer>
    <p>© 2025 Devicer. Усі права захищені.</p>
</footer>

<script src="scripts.js"></script>
</body>
</html>