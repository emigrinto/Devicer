<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect admins to admin dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch wishlist items by calling the backend
$items = [];
$error = '';
$message = '';

try {
    $post_data = http_build_query(['action' => 'view']);
    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => $post_data
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents('../backend/wishlist_actions.php', false, $context);
    $response = json_decode($result, true);

    if ($response['success']) {
        $items = $response['items'];
    } else {
        $error = $response['error'];
    }
} catch (Exception $e) {
    $error = 'Помилка: ' . $e->getMessage();
}

// Handle messages from previous actions (e.g., add to cart)
if (isset($_SESSION['wishlist_message'])) {
    $message = $_SESSION['wishlist_message'];
    unset($_SESSION['wishlist_message']);
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список бажань - Devicer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .wishlist-items {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .wishlist-item {
            border: 1px solid #ddd;
            padding: 1rem;
            width: 200px;
            text-align: center;
        }
        .wishlist-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .wishlist-item button {
            margin: 0.5rem 0;
            padding: 0.5rem;
            cursor: pointer;
        }
        .message {
            margin: 1rem 0;
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
    <h1>Список бажань</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
        <p class="message" style="color: green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <div class="wishlist-items">
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <div class="wishlist-item">
                    <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'https://via.placeholder.com/100'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <h3><a href="product.php?id=<?php echo htmlspecialchars($item['product_id']); ?>"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                    <p>Ціна: <?php echo htmlspecialchars($item['price']); ?> грн</p>
                    <button class="add-to-cart-from-wishlist" data-id="<?php echo htmlspecialchars($item['product_id']); ?>">Додати до кошика</button>
                    <button class="remove-from-wishlist" data-id="<?php echo htmlspecialchars($item['wishlist_id']); ?>">Видалити</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Ваш список бажань порожній.</p>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>© 2025 Devicer. Усі права захищені.</p>
</footer>

<script src="scripts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Handle "Add to Cart" button clicks
    document.querySelectorAll('.add-to-cart-from-wishlist').forEach(button => {
        button.addEventListener('click', async () => {
            const productId = button.getAttribute('data-id');
            const response = await fetch('../backend/cart_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=add&product_id=${productId}`
            });
            const result = await response.json();

            if (result.success) {
                window.location.href = 'cart.php'; // Redirect to cart page
            } else {
                alert(result.message || 'Помилка при додаванні до кошика.');
            }
        });
    });

    // Handle "Remove from Wishlist" button clicks
    document.querySelectorAll('.remove-from-wishlist').forEach(button => {
        button.addEventListener('click', async () => {
            const wishlistId = button.getAttribute('data-id');
            const response = await fetch('../backend/wishlist_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=remove&wishlist_id=${wishlistId}`
            });
            const result = await response.json();

            if (result.success) {
                window.location.reload(); // Refresh to update the wishlist
            } else {
                alert(result.error || 'Помилка при видаленні зі списку бажань.');
            }
        });
    });
});
</script>
</body>
</html>
