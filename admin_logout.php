<?php
session_start();
session_destroy();
header("Location: account.php"); // Changed from admin_login.php
exit();
?>