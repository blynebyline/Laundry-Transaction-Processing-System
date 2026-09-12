<?php
require_once __DIR__ . '/includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = (int)$_POST['id'];
$orders = new Orders($db);

if ($orders->delete($id)) {
    header('Location: dashboard.php?msg=deleted');
    exit;
} else {
    header('Location: dashboard.php?msg=error');
    exit;
}