<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Orders.php';
require_once __DIR__ . '/../classes/User.php';

$database = new Database();
$db = $database->conn;

function h($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}