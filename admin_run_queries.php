<?php
// Start output buffering to prevent premature output
ob_start();

include '../backend/admin_auth.php';
include '../backend/db_connect.php';

// Load PhpSpreadsheet
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$query = isset($_GET['query']) ? (int)$_GET['query'] : 0;
$results = [];
$columns = [];
$title = '';
$show_form = false;
$category = isset($_POST['category']) ? $_POST['category'] : '';
$customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
$order_count = isset($_POST['order_count']) ? (int)$_POST['order_count'] : 0;
$spending_threshold = isset($_POST['spending_threshold']) ? (float)$_POST['spending_threshold'] : 0;

try {
    switch ($query) {
        case 1: // Full info on each product
            $title = 'Повна інформація про кожен продукт';
            $stmt = $pdo->query("
                SELECT 
                    p.product_id,
                    p.name AS product_name,
                    p.price,
                    p.category,
                    p.manufacturer,
                    p.subcategory,
                    p.description,
                    p.stock,
                    p.color,
                    p.weight,
                    p.warranty,
                    GROUP_CONCAT(CONCAT(c.characteristic_name, ': ', c.characteristic_value) SEPARATOR '; ') AS characteristics
                FROM Product p
                LEFT JOIN Characteristics c ON p.product_id = c.product_id
                GROUP BY p.product_id
            ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columns = ['product_id' => 'ID', 'product_name' => 'Назва', 'price' => 'Ціна', 'category' => 'Категорія', 
                        'manufacturer' => 'Виробник', 'subcategory' => 'Підкатегорія', 'description' => 'Опис', 
                        'stock' => 'Запас', 'color' => 'Колір', 'weight' => 'Вага', 'warranty' => 'Гарантія', 
                        'characteristics' => 'Характеристики'];
            break;

        case 2: // All products from a selected category
            $title = 'Усі продукти з обраної категорії';
            if (empty($category)) {
                $show_form = true;
                $form = '<form method="POST">
                            <label for="category">Виберіть категорію:</label>
                            <input type="text" id="category" name="category" required>
                            <button type="submit">Показати</button>
                         </form>';
            } else {
                $stmt = $pdo->prepare("
                    SELECT 
                        product_id,
                        name,
                        price,
                        manufacturer,
                        subcategory,
                        description,
                        stock
                    FROM Product
                    WHERE category = ?
                ");
                $stmt->execute([$category]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $columns = ['product_id' => 'ID', 'name' => 'Назва', 'price' => 'Ціна', 'manufacturer' => 'Виробник', 
                            'subcategory' => 'Підкатегорія', 'description' => 'Опис', 'stock' => 'Запас'];
            }
            break;

        case 3: // Top 5 products sold
            $title = 'Топ-5 проданих продуктів';
            $stmt = $pdo->query("
                SELECT 
                    p.product_id,
                    p.name,
                    p.category,
                    p.manufacturer,
                    SUM(od.quantity) AS total_quantity_sold
                FROM Product p
                JOIN Order_details od ON p.product_id = od.product_id
                GROUP BY p.product_id, p.name, p.category, p.manufacturer
                ORDER BY total_quantity_sold DESC
                LIMIT 5
            ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columns = ['product_id' => 'ID', 'name' => 'Назва', 'category' => 'Категорія', 
                        'manufacturer' => 'Виробник', 'total_quantity_sold' => 'Кількість проданих'];
            break;

        case 4: // All products bought by a selected client
            $title = 'Усі продукти, куплені обраним клієнтом';
            if (empty($customer_id)) {
                $show_form = true;
                $form = '<form method="POST">
                            <label for="customer_id">Введіть ID клієнта:</label>
                            <input type="number" id="customer_id" name="customer_id" required>
                            <button type="submit">Показати</button>
                         </form>';
            } else {
                $stmt = $pdo->prepare("
                    SELECT 
                        c.customer_id,
                        c.first_name,
                        c.last_name,
                        p.product_id,
                        p.name AS product_name,
                        od.quantity,
                        p.price,
                        (od.quantity * p.price) AS total_per_product,
                        SUM(od.quantity * p.price) OVER (PARTITION BY c.customer_id) AS total_spent
                    FROM Customer c
                    JOIN `Order` o ON c.customer_id = o.customer_id
                    JOIN Order_details od ON o.order_id = od.order_id
                    JOIN Product p ON od.product_id = p.product_id
                    WHERE c.customer_id = ?
                ");
                $stmt->execute([$customer_id]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $columns = ['customer_id' => 'ID Клієнта', 'first_name' => 'Ім’я', 'last_name' => 'Прізвище', 
                            'product_id' => 'ID Продукту', 'product_name' => 'Назва продукту', 'quantity' => 'Кількість', 
                            'price' => 'Ціна', 'total_per_product' => 'Вартість', 'total_spent' => 'Загальна сума'];
            }
            break;

        case 5: // Top categories
            $title = 'Топ категорії за продажами';
            $stmt = $pdo->query("
                SELECT 
                    p.category,
                    SUM(od.quantity) AS total_quantity_sold
                FROM Product p
                JOIN Order_details od ON p.product_id = od.product_id
                GROUP BY p.category
                ORDER BY total_quantity_sold DESC
            ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columns = ['category' => 'Категорія', 'total_quantity_sold' => 'Кількість проданих'];
            break;

        case 6: // Clients with more than a specified number of purchases
            $title = 'Клієнти з кількістю замовлень більше вказаної';
            if (empty($order_count)) {
                $show_form = true;
                $form = '<form method="POST">
                            <label for="order_count">Введіть мінімальну кількість замовлень:</label>
                            <input type="number" id="order_count" name="order_count" required min="1">
                            <button type="submit">Показати</button>
                         </form>';
            } else {
                $stmt = $pdo->prepare("
                    SELECT 
                        c.customer_id,
                        c.first_name,
                        c.last_name,
                        c.email,
                        COUNT(DISTINCT o.order_id) AS order_count
                    FROM Customer c
                    JOIN `Order` o ON c.customer_id = o.customer_id
                    GROUP BY c.customer_id, c.first_name, c.last_name, c.email
                    HAVING order_count > ?
                ");
                $stmt->execute([$order_count]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $columns = ['customer_id' => 'ID Клієнта', 'first_name' => 'Ім’я', 'last_name' => 'Прізвище', 
                            'email' => 'Email', 'order_count' => 'Кількість замовлень'];
            }
            break;

        case 7: // Best selling product from each manufacturer
            $title = 'Найкраще продаваний продукт від кожного виробника';
            $stmt = $pdo->query("
                WITH RankedProducts AS (
                    SELECT 
                        p.manufacturer,
                        p.product_id,
                        p.name,
                        SUM(od.quantity) AS total_quantity_sold,
                        ROW_NUMBER() OVER (PARTITION BY p.manufacturer ORDER BY SUM(od.quantity) DESC) AS rn
                    FROM Product p
                    JOIN Order_details od ON p.product_id = od.product_id
                    GROUP BY p.manufacturer, p.product_id, p.name
                )
                SELECT 
                    manufacturer,
                    product_id,
                    name,
                    total_quantity_sold
                FROM RankedProducts
                WHERE rn = 1
            ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columns = ['manufacturer' => 'Виробник', 'product_id' => 'ID Продукту', 'name' => 'Назва', 
                        'total_quantity_sold' => 'Кількість проданих'];
            break;

        case 8: // Clients who spent over a specified amount
            $title = 'Клієнти, які витратили понад вказану суму';
            if (empty($spending_threshold)) {
                $show_form = true;
                $form = '<form method="POST">
                            <label for="spending_threshold">Введіть мінімальну суму витрат:</label>
                            <input type="number" id="spending_threshold" name="spending_threshold" required min="0" step="1000">
                            <button type="submit">Показати</button>
                         </form>';
            } else {
                $stmt = $pdo->prepare("
                    SELECT 
                        c.customer_id,
                        c.first_name,
                        c.last_name,
                        c.email,
                        SUM(od.quantity * p.price) AS total_spent
                    FROM Customer c
                    JOIN `Order` o ON c.customer_id = o.customer_id
                    JOIN Order_details od ON o.order_id = od.order_id
                    JOIN Product p ON od.product_id = p.product_id
                    GROUP BY c.customer_id, c.first_name, c.last_name, c.email
                    HAVING total_spent > ?
                ");
                $stmt->execute([$spending_threshold]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $columns = ['customer_id' => 'ID Клієнта', 'first_name' => 'Ім’я', 'last_name' => 'Прізвище', 
                            'email' => 'Email', 'total_spent' => 'Загальна сума'];
            }
            break;

        case 9: // Products that were never bought
            $title = 'Продукти, які ще не купувалися';
            $stmt = $pdo->query("
                SELECT 
                    p.product_id,
                    p.name,
                    p.category,
                    p.manufacturer,
                    p.stock
                FROM Product p
                LEFT JOIN Order_details od ON p.product_id = od.product_id
                WHERE od.product_id IS NULL
            ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columns = ['product_id' => 'ID', 'name' => 'Назва', 'category' => 'Категорія', 
                        'manufacturer' => 'Виробник', 'stock' => 'Запас'];
            break;

        case 10: // Products with less than 15 items in stock
            $title = 'Продукти з запасом менше 15 одиниць';
            $stmt = $pdo->query("
                SELECT 
                    product_id,
                    name,
                    category,
                    manufacturer,
                    stock
                FROM Product
                WHERE stock < 15
            ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columns = ['product_id' => 'ID', 'name' => 'Назва', 'category' => 'Категорія', 
                        'manufacturer' => 'Виробник', 'stock' => 'Запас'];
            break;

        default:
            $title = 'Помилка';
            throw new Exception('Невідомий запит');
    }

    // Handle XLSX export AFTER query execution (so $results and $columns are populated)
    if (isset($_POST['export_xlsx']) && !empty($results)) {
        // Clear any output buffers to prevent corruption
        ob_end_clean();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column headers
        $colIndex = 'A';
        foreach ($columns as $key => $label) {
            $sheet->setCellValue($colIndex . '1', $label);
            $sheet->getColumnDimension($colIndex)->setAutoSize(true);
            $colIndex++;
        }

        // Fill data
        $row = 2;
        foreach ($results as $data) {
            $colIndex = 'A';
            foreach ($columns as $key => $label) {
                $sheet->setCellValue($colIndex . $row, $data[$key] ?? '');
                $colIndex++;
            }
            $row++;
        }

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . htmlspecialchars($title) . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        // Save and output the file
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

} catch (Exception $e) {
    $error = 'Помилка: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Devicer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Print-specific styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #printableTable, #printableTable * {
                visibility: visible;
            }
            #printableTable {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                table-layout: fixed;
            }
            #printableTable th,
            #printableTable td {
                word-wrap: break-word;
                overflow-wrap: break-word;
                max-width: 150px; /* Adjust as needed */
            }
            .no-print {
                display: none;
            }
        }

        /* Ensure table fits within printable area */
        #printableTable table {
            width: 100%;
            max-width: 100%;
            table-layout: auto;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    <header class="no-print">
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
        <h1><?php echo htmlspecialchars($title); ?></h1>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($show_form): ?>
            <?php echo $form; ?>
        <?php elseif (empty($results)): ?>
            <p>Немає даних для відображення.</p>
        <?php else: ?>
            <div class="no-print">
                <button class="action-btn print-btn" onclick="printTable()">Друкувати</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="export_xlsx" value="1">
                    <button type="submit" class="action-btn export-btn">Експортувати в XLSX</button>
                </form>
            </div>
            <div id="printableTable">
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($columns as $key => $label): ?>
                                <th><?php echo htmlspecialchars($label); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <?php foreach ($columns as $key => $label): ?>
                                    <td><?php echo htmlspecialchars($row[$key] ?? ''); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <p class="no-print"><a href="admin_dashboard.php">Повернутися до панелі</a></p>
    </main>

    <footer class="no-print">
        <p>© 2025 Devicer. Усі права захищені.</p>
    </footer>

    <script>
        function printTable() {
            window.print();
        }
    </script>
</body>
</html>
<?php
// End output buffering and flush
ob_end_flush();
?>
