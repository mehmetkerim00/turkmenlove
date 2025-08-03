<?php
require_once 'db.php';
session_start();


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Неверный метод запроса.');
}


if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['form_errors'] = ['Корзина пуста'];
    header('Location: checkout.php');
    exit;
}


$errors = [];

if (empty($_POST['client_name'])) {
    $errors[] = "Имя обязательно";
} elseif (!preg_match('/^[А-Яа-яЁёA-Za-z\s]{2,50}$/u', $_POST['client_name'])) {
    $errors[] = "Некорректное имя (только буквы, 2-50 символов)";
}

if (!filter_var($_POST['client_email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Некорректный email";
}


$no_address = isset($_POST['no_address']) && $_POST['no_address'] == 'on';


$client_name      = trim(htmlspecialchars($_POST['client_name']));
$client_email     = trim(filter_var($_POST['client_email'], FILTER_SANITIZE_EMAIL));
$client_phone     = trim(htmlspecialchars($_POST['client_phone']));
$reciever_name    = trim(htmlspecialchars($_POST['reciever_name']));
$reciever_phone   = trim(htmlspecialchars($_POST['reciever_phone']));
$city             = trim(htmlspecialchars($_POST['city']));
$comment          = trim(htmlspecialchars($_POST['textarea']));
$delivery_date    = trim(htmlspecialchars($_POST['delivery_date']));
$delivery_time    = trim(htmlspecialchars($_POST['delivery_time']));


$address = $apartment = $gate = $floor = null;

if (!$no_address) {
    $address = trim(htmlspecialchars($_POST['address']));
    
  
    if (!empty($_POST['apartment'])) {
        $apartment = filter_var($_POST['apartment'], FILTER_VALIDATE_INT, 
            ['options' => ['min_range' => 1, 'max_range' => 999]]);
        if ($apartment === false) $errors[] = "Некорректный номер квартиры";
    }
    
    if (!empty($_POST['gate'])) {
        $gate = filter_var($_POST['gate'], FILTER_VALIDATE_INT, 
            ['options' => ['min_range' => 1, 'max_range' => 50]]);
        if ($gate === false) $errors[] = "Некорректный номер подъезда";
    }
    
    if (!empty($_POST['floor'])) {
        $floor = filter_var($_POST['floor'], FILTER_VALIDATE_INT, 
            ['options' => ['min_range' => 1, 'max_range' => 150]]);
        if ($floor === false) $errors[] = "Некорректный номер этажа";
    }
}


if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: checkout.php');
    exit;
}

try {

    $sql = "INSERT INTO orders (
        client_name, client_email, client_phone,
        reciever_name, reciever_phone, city,
        address, apartment, gate, floor,
        comment, delivery_date, delivery_time,
        quantity, product_id, no_address
    ) VALUES (
        :client_name, :client_email, :client_phone,
        :reciever_name, :reciever_phone, :city,
        :address, :apartment, :gate, :floor,
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
            ':gate'           => $gate,
            ':floor'          => $floor,
            ':comment'        => $comment,
            ':delivery_date'  => $delivery_date,
            ':delivery_time'  => $delivery_time,
            ':quantity'       => $item['quantity'],
            ':product_id'     => $item['id'],
            ':no_address'     => $no_address ? 1 : 0
        ]);
    }
    

    unset($_SESSION['cart']);
    header("Location: pay.php");
    exit;
    
} catch (PDOException $e) {

    error_log("Order Error: " . $e->getMessage());
    $_SESSION['form_errors'] = ['Произошла ошибка при оформлении заказа'];
    header('Location: checkout.php');
    exit;
}