<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../frontend/account.php"); // Changed from admin_login.php
    exit();
}
?>