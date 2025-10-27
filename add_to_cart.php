<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Неверный метод запроса');
    }

    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($product_id <= 0 || $quantity <= 0) {
        throw new Exception('Некорректный ID продукта или количество');
    }

    // Проверяем, существует ли продукт
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product || !isset($product['price'])) {
        throw new Exception('Товар не найден или у него нет цены');
    }


    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] === $product['id']) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    unset($item);


    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity
        ];
    }

    $response['success'] = true;
    error_log("Товар ID $product_id добавлен в корзину, количество: $quantity"); 
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    error_log("Ошибка в add_to_cart.php: " . $e->getMessage()); 
}

echo json_encode($response);
exit;
?>