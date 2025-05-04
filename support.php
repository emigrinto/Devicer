<?php
session_start();
include '../backend/db_connect.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: account.php");
    exit();
}

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $customer_id = $_SESSION['customer_id'];
    $submitted_date = date('Y-m-d H:i:s'); // Current date and time

    try {
        $sql = "INSERT INTO Support (customer_id, subject, message, submitted_date) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id, $subject, $message, $submitted_date]);
        $success = 'Запит на підтримку успішно надіслано!';
    } catch (PDOException $e) {
        $error = 'Помилка при відправленні запиту: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Підтримка - Devicer</title>
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
        <h1>Підтримка</h1>
        <?php if ($success): ?>
            <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <p>Заповніть форму нижче, щоб звернутися до служби підтримки.</p>
        <form action="support.php" method="POST">
            <label for="subject">Тема:</label>
            <input type="text" id="subject" name="subject" required>
            <label for="message">Повідомлення:</label>
            <textarea id="message" name="message" rows="5" required></textarea>
            <button type="submit">Надіслати</button>
        </form>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>

    <script src="scripts.js"></script>
</body>
</html>
