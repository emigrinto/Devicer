<?php
session_start();
include 'db_connect.php';

function handleCustomerAuth($action) {
    global $pdo;
    $response = ['success' => false, 'error' => ''];

    if ($action === 'login') {
        $identifier = $_POST['identifier'] ?? ''; // Can be username or email
        $password = $_POST['password'] ?? '';

        if (!$identifier || !$password) {
            $response['error'] = 'Заповніть усі поля.';
            return $response;
        }

        try {
            $is_email = filter_var($identifier, FILTER_VALIDATE_EMAIL);
            if ($is_email) {
                $sql = "SELECT customer_id, customer_username, customer_password, first_name, last_name 
                        FROM Customer 
                        WHERE email = ?";
            } else {
                $sql = "SELECT customer_id, customer_username, customer_password, first_name, last_name 
                        FROM Customer 
                        WHERE customer_username = ?";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$identifier]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($customer && $password === $customer['customer_password']) { // Plain text comparison (as per original code)
                $_SESSION['customer_id'] = $customer['customer_id'];
                $_SESSION['first_name'] = $customer['first_name'];
                $_SESSION['last_name'] = $customer['last_name'];
                $response['success'] = true;
            } else {
                $response['error'] = 'Невірний логін або пароль.';
            }
        } catch (PDOException $e) {
            $response['error'] = 'Помилка: ' . $e->getMessage();
        }
    } elseif ($action === 'register') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!$name || !$email || !$password) {
            $response['error'] = 'Заповніть усі поля.';
            return $response;
        }

        try {
            // Check if email already exists
            $sql = "SELECT customer_id FROM Customer WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $response['error'] = 'Email вже зареєстрований.';
                return $response;
            }

            // Use customer_username as the first part of the email (for consistency with login)
            $username = explode('@', $email)[0];
            $sql = "INSERT INTO Customer (customer_username, first_name, customer_password, email) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $name, $password, $email]); // Plain text password (as per original code)

            $response['success'] = true;
        } catch (PDOException $e) {
            $response['error'] = 'Помилка: ' . $e->getMessage();
        }
    } elseif ($action === 'logout') {
        session_destroy();
        $response['success'] = true;
    }

    return $response;
}

// Handle incoming requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = handleCustomerAuth($action);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}
?>
