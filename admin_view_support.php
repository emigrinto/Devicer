<?php
include '../backend/admin_auth.php';
include '../backend/db_connect.php';

// Handle status update (mark as resolved)
if (isset($_GET['resolve_id']) && is_numeric($_GET['resolve_id'])) {
    $support_id = (int)$_GET['resolve_id'];
    try {
        $sql = "UPDATE Support SET status = 'Resolved' WHERE support_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$support_id]);
        header("Location: admin_view_support.php?success=Запит успішно позначено як вирішений");
        exit();
    } catch (PDOException $e) {
        $error = "Помилка при оновленні статусу: " . $e->getMessage();
    }
}

try {
    $sql = "SELECT s.support_id, s.customer_id, s.subject, s.message, s.status, s.submitted_date, 
                   c.first_name, c.last_name 
            FROM Support s 
            JOIN Customer c ON s.customer_id = c.customer_id 
            ORDER BY s.submitted_date DESC";
    $stmt = $pdo->query($sql);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Помилка при отриманні запитів на підтримку: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запити підтримки - Devicer</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo">DEVICER - Адмін</div>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Панель</a></li>
                <li><a href="admin_add_product.php">Додати товар</a></li>
                <li><a href="admin_view_orders.php">Замовлення</a></li>
                <li><a href="admin_view_support.php">Запити підтримки</a></li>
                <li><a href="admin_logout.php">Вийти</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Запити підтримки</h1>
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($requests)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Запиту</th>
                        <th>Клієнт</th>
                        <th>Тема</th>
                        <th>Повідомлення</th>
                        <th>Статус</th>
                        <th>Дата подання</th>
                        <th>Дія</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['support_id']); ?></td>
                            <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['subject']); ?></td>
                            <td><?php echo htmlspecialchars($request['message']); ?></td>
                            <td><?php echo htmlspecialchars($request['status']); ?></td>
                            <td><?php echo htmlspecialchars($request['submitted_date']); ?></td>
                            <td>
                                <?php if ($request['status'] === 'Open'): ?>
                                    <a href="admin_view_support.php?resolve_id=<?php echo htmlspecialchars($request['support_id']); ?>">Позначити як вирішений</a>
                                <?php else: ?>
                                    Вирішено
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Запитів на підтримку немає.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>
</body>
</html>