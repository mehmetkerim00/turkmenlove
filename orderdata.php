<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Неверный метод запроса.');
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['form_errors'] = ['Корзина пуста'];
    $_SESSION['form_data'] = $_POST;
    header('Location: checkout.php');
    exit;
}

$client_name = trim(htmlspecialchars($_POST['client_name'] ?? ''));
$client_email = trim(htmlspecialchars($_POST['client_email'] ?? ''));
$client_phone = trim(htmlspecialchars($_POST['client_phone'] ?? ''));
$reciever_name = trim(htmlspecialchars($_POST['reciever_name'] ?? ''));
$reciever_phone = trim(htmlspecialchars($_POST['reciever_phone'] ?? ''));
$city = trim(htmlspecialchars($_POST['city'] ?? ''));
$no_address = isset($_POST['no_address']) && $_POST['no_address'] === 'on';
$address = $no_address ? null : trim(htmlspecialchars($_POST['address'] ?? ''));
$apartment = $no_address ? null : (isset($_POST['apartment']) ? filter_var($_POST['apartment'], FILTER_VALIDATE_INT) : null);
$comment = trim(htmlspecialchars($_POST['textarea'] ?? ''));
$delivery_date = trim(htmlspecialchars($_POST['delivery_date'] ?? ''));
$delivery_time = trim(htmlspecialchars($_POST['delivery_time'] ?? ''));

try {
    $sql = "INSERT INTO orders (
        client_name, client_email, client_phone,
        reciever_name, reciever_phone, city,
        address, apartment,
        comment, delivery_date, delivery_time,
        quantity, product_id, no_address
    ) VALUES (
        :client_name, :client_email, :client_phone,
        :reciever_name, :reciever_phone, :city,
        :address, :apartment, 
        :comment, :delivery_date, :delivery_time,
        :quantity, :product_id, :no_address
    )";

    $stmt = $conn->prepare($sql);

    foreach ($_SESSION['cart'] as $item) {
        $stmt->execute([
            ':client_name'     => $client_name,
            ':client_email'    => $client_email,
            ':client_phone'    => $client_phone,
            ':reciever_name'   => $reciever_name,
            ':reciever_phone'  => $reciever_phone,
            ':city'            => $city,
            ':address'         => $address,
            ':apartment'       => $apartment,
            ':comment'         => $comment,
            ':delivery_date'   => $delivery_date,
            ':delivery_time'   => $delivery_time,
            ':quantity'        => $item['quantity'],
            ':product_id'      => $item['id'],
            ':no_address'      => $no_address ? 1 : 0
        ]);
    }

    unset($_SESSION['cart']);
    header("Location: pay.php");
    exit;
} catch (PDOException $e) {
    error_log("Order Error: " . $e->getMessage());
    $_SESSION['form_errors'] = ['Произошла ошибка при оформлении заказа'];
    $_SESSION['form_data'] = $_POST;
    header('Location: checkout.php');
    exit;
}
?>