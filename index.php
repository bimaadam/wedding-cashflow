<?php
session_start();

// Cek apakah user sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: auth/login.php");
    exit();
}
