<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $sql = "SELECT customer_id, customer_password FROM Customer WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer && $password === $customer['customer_password']) {
            $_SESSION['customer_id'] = $customer['customer_id'];
            header("Location: ../frontend/store.php");
            exit();
        } else {
            echo "Невірна пошта або пароль.";
        }
    } catch (PDOException $e) {
        echo "Помилка: " . $e->getMessage();
    }
}
?>