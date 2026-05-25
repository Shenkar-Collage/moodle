<?php
require_once __DIR__ . '/src/bootstrap.php';
Auth::logout();
header('Location: ' . BASE_URL . '/login.php');
exit;
