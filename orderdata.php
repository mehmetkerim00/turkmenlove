<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  die('Неверный метод запроса.');
}

session_start();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
  die('Корзина пуста.');
}


$client_name      = trim(filter_var($_POST['client_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$client_email     = trim(filter_var($_POST['client_email'], FILTER_SANITIZE_EMAIL));
$client_phone     = trim(filter_var($_POST['client_phone'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));

$reciever_name    = trim(filter_var($_POST['reciever_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$reciever_phone   = trim(filter_var($_POST['reciever_phone'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));

$city             = trim(filter_var($_POST['city'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$address          = trim(filter_var($_POST['address'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$apartment        = trim(filter_var($_POST['apartment'], FILTER_SANITIZE_NUMBER_INT));
$gate             = trim(filter_var($_POST['gate'], FILTER_SANITIZE_NUMBER_INT));
$floor            = trim(filter_var($_POST['floor'], FILTER_SANITIZE_NUMBER_INT));
$comment          = trim(filter_var($_POST['textarea'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));

$delivery_date    = trim(filter_var($_POST['delivery_date'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$delivery_time    = trim(filter_var($_POST['delivery_time'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));

$sql = "INSERT INTO orders (
  client_name, client_email, client_phone,
  reciever_name, reciever_phone, city,
  address, apartment, gate, floor,
  comment, delivery_date, delivery_time,
  quantity, product_id
) VALUES (
  :client_name, :client_email, :client_phone,
  :reciever_name, :reciever_phone, :city,
  :address, :apartment, :gate, :floor,
  :comment, :delivery_date, :delivery_time,
  :quantity, :product_id
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
    ':gate'            => $gate,
    ':floor'           => $floor,
    ':comment'         => $comment,
    ':delivery_date'   => $delivery_date,
    ':delivery_time'   => $delivery_time,
    ':quantity'        => $item['quantity'],
    ':product_id'      => $item['id']
  ]);
}

unset($_SESSION['cart']);
header("Location: pay.php");
exit;
