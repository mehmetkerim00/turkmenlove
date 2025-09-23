<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

$csrf_token = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    $_SESSION['admin_error'] = 'Ошибка безопасности';
    header('Location: admin.php');
    exit;
}

$order_id = filter_var($_POST['order_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$order_id) {
    $_SESSION['admin_error'] = 'Некорректный ID заказа';
    header('Location: admin.php');
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = :id");
    $stmt->execute([':id' => $order_id]);
    $_SESSION['admin_success'] = 'Заказ успешно удалён';
    header('Location: admin.php');
    exit;
} catch (PDOException $e) {
    error_log("Delete Order Error: " . $e->getMessage());
    $_SESSION['admin_error'] = 'Ошибка при удалении заказа';
    header('Location: admin.php');
    exit;
}
?>