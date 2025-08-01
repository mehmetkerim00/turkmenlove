<?php
$host = 'localhost';
$db   = 'u285551875_turkmenlove';
$user = 'u285551875_kerim';
$pass = 'Kerim65807499@';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $conn = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}

?>
