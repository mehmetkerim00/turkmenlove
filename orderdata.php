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


$allowed_cities = ['Ашхабад', 'Туркменабат'];
$allowed_times = [
    '10:00–12:00', '11:00–13:00', '12:00–14:00', '13:00–15:00',
    '14:00–16:00', '15:00–17:00', '16:00–18:00', '17:00–19:00',
    '18:00–20:00', '19:00–21:00', '20:00–22:00', '21:00–23:00', '22:00–00:00'
];


$client_name = trim(htmlspecialchars($_POST['client_name'] ?? ''));
if (empty($client_name)) {
    $errors[] = 'Имя обязательно';
} elseif (!preg_match('/^[А-Яа-яЁёA-Za-z\s]{2,50}$/u', $client_name)) {
    $errors[] = 'Некорректное имя (только буквы и пробелы, 2–50 символов)';
}

$client_email = trim(filter_var($_POST['client_email'] ?? '', FILTER_SANITIZE_EMAIL));
if (empty($client_email)) {
    $errors[] = 'Email обязателен';
} elseif (!filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Некорректный email';
}

$client_phone = trim(htmlspecialchars($_POST['client_phone'] ?? ''));
if (empty($client_phone)) {
    $errors[] = 'Телефон клиента обязателен';
} elseif (!preg_match('/^\+\d{1,3}\d{6,14}$/', $client_phone)) {
    $errors[] = 'Некорректный формат телефона клиента (например, +1234567890)';
}

$reciever_name = trim(htmlspecialchars($_POST['reciever_name'] ?? ''));
if (empty($reciever_name)) {
    $errors[] = 'Имя получателя обязательно';
} elseif (!preg_match('/^[А-Яа-яЁёA-Za-z\s]{2,50}$/u', $reciever_name)) {
    $errors[] = 'Некорректное имя получателя (только буквы и пробелы, 2–50 символов)';
}

$reciever_phone = trim(htmlspecialchars($_POST['reciever_phone'] ?? ''));
if (empty($reciever_phone)) {
    $errors[] = 'Телефон получателя обязателен';
} elseif (!preg_match('/^\+\d{1,3}\d{6,14}$/', $reciever_phone)) {
    $errors[] = 'Некорректный формат телефона получателя (например, +1234567890)';
}

$city = trim(htmlspecialchars($_POST['city'] ?? ''));
if (empty($city)) {
    $errors[] = 'Город обязателен';
} elseif (!in_array($city, $allowed_cities)) {
    $errors[] = 'Выберите город из списка (Ашхабад или Туркменабат)';
}

$no_address = isset($_POST['no_address']) && $_POST['no_address'] === 'on';

$address = $apartment = $gate = $floor = null;
if (!$no_address) {
    $address = trim(htmlspecialchars($_POST['address'] ?? ''));
    if (empty($address)) {
        $errors[] = 'Адрес обязателен, если не выбран "Не знаю адрес"';
    } elseif (!preg_match('/^[\w\s.,-]{5,100}$/u', $address)) {
        $errors[] = 'Некорректный адрес (5–100 символов, буквы, цифры, пробелы, точки, запятые, дефис)';
    }

    $apartment = !empty($_POST['apartment']) ? filter_var($_POST['apartment'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999]]) : null;
    if (!empty($_POST['apartment']) && $apartment === false) {
        $errors[] = 'Некорректный номер квартиры (1–999)';
    }

    $gate = !empty($_POST['gate']) ? filter_var($_POST['gate'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]) : null;
    if (!empty($_POST['gate']) && $gate === false) {
        $errors[] = 'Некорректный номер подъезда (1–50)';
    }

    $floor = !empty($_POST['floor']) ? filter_var($_POST['floor'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 150]]) : null;
    if (!empty($_POST['floor']) && $floor === false) {
        $errors[] = 'Некорректный номер этажа (1–150)';
    }
}

$comment = trim(htmlspecialchars($_POST['textarea'] ?? ''));
if (strlen($comment) > 500) {
    $errors[] = 'Комментарий не должен превышать 500 символов';
} elseif (!empty($comment) && !preg_match('/^[\w\s.,!?-]{0,500}$/u', $comment)) {
    $errors[] = 'Комментарий содержит недопустимые символы';
}

$delivery_date = trim(htmlspecialchars($_POST['delivery_date'] ?? ''));
if (empty($delivery_date)) {
    $errors[] = 'Дата доставки обязательна';
} else {
    $date = DateTime::createFromFormat('d F Y', $delivery_date, new DateTimeZone('UTC'));
    $today = (new DateTime())->setTime(0, 0, 0);
    if ($date === false || $date < $today) {
        $errors[] = 'Некорректная или прошедшая дата доставки';
    }
}

$delivery_time = trim(htmlspecialchars($_POST['delivery_time'] ?? ''));
if (empty($delivery_time)) {
    $errors[] = 'Время доставки обязательно';
} elseif (!in_array($delivery_time, $allowed_times)) {
    $errors[] = 'Выберите время доставки из списка';
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
?>