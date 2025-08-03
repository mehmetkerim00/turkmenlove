<?php
$host = 'localhost';
$db   = 'turkmenlove';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $conn = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}

?>
