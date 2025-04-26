<?php
session_start();
include 'db_connect.php';

function handleAdminAuth($action) {
    global $pdo;
    $response = ['success' => false, 'error' => ''];

    if ($action === 'check') {
        if (isset($_SESSION['admin_id'])) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Не авторизований.';
        }
    } elseif ($action === 'login') {
        $identifier = $_POST['identifier'] ?? ''; // Can be username or email
        $password = $_POST['password'] ?? '';

        if (!$identifier || !$password) {
            $response['error'] = 'Заповніть усі поля.';
            return $response;
        }

        try {
            $is_email = filter_var($identifier, FILTER_VALIDATE_EMAIL);
            if ($is_email) {
                $sql = "SELECT admin_id, admin_username, admin_password, first_name, last_name 
                        FROM Admin 
                        WHERE email = ?";
            } else {
                $sql = "SELECT admin_id, admin_username, admin_password, first_name, last_name 
                        FROM Admin 
                        WHERE admin_username = ?";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$identifier]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && $password === $admin['admin_password']) { // Plain text comparison (as per original code in account.php)
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_first_name'] = $admin['first_name'];
                $_SESSION['admin_last_name'] = $admin['last_name'];
                $response['success'] = true;
            } else {
                $response['error'] = 'Невірний логін або пароль.';
            }
        } catch (PDOException $e) {
            $response['error'] = 'Помилка: ' . $e->getMessage();
        }
    } elseif ($action === 'logout') {
        session_destroy();
        $response['success'] = true;
    } elseif ($action === 'create') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';

        if (!$username || !$password || !$first_name || !$last_name || !$email) {
            $response['error'] = 'Заповніть усі поля.';
            return $response;
        }

        try {
            $sql = "INSERT INTO Admin (admin_username, admin_password, first_name, last_name, email) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $password, $first_name, $last_name, $email]); // Plain text password (as per original code)
            $response['success'] = true;
        } catch (PDOException $e) {
            $response['error'] = 'Помилка: ' . $e->getMessage();
        }
    }

    return $response;
}

// Handle incoming requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = handleAdminAuth($action);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check') {
    $result = handleAdminAuth('check');
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}
?>
