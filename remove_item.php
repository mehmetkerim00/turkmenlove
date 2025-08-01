<?php
session_start();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['id'] == $id) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // сброс индексов
            break;
        }
    }
}

header("Location: checkout.php");
exit;
