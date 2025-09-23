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

$product_id = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$product_id) {
    $_SESSION['admin_error'] = 'Некорректный ID товара';
    header('Location: admin.php');
    exit;
}

try {
    // Проверка, есть ли заказы с этим товаром
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM orders WHERE product_id = :id");
    $stmt_check->execute([':id' => $product_id]);
    $order_count = $stmt_check->fetchColumn();

    if ($order_count > 0) {
        $_SESSION['admin_error'] = 'Нельзя удалить товар, так как он используется в заказах';
        header('Location: admin.php');
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute([':id' => $product_id]);
    $_SESSION['admin_success'] = 'Товар успешно удалён';
    header('Location: admin.php');
    exit;
} catch (PDOException $e) {
    error_log("Delete Product Error: " . $e->getMessage());
    $_SESSION['admin_error'] = 'Ошибка при удалении товара';
    header('Location: admin.php');
    exit;
}
?>