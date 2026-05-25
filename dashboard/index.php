<?php
require_once __DIR__ . '/src/bootstrap.php';

if (Auth::isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/login.php');
}
exit;
