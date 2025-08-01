<?php
session_start();

$allowedCurrencies = ['USD', 'RUB', 'TRY', 'MYR', 'BYN', 'EUR', 'GBP'];

if (isset($_POST['currency']) && in_array($_POST['currency'], $allowedCurrencies)) {
    $_SESSION['currency'] = $_POST['currency'];
}


$referrer = filter_var($_SERVER['HTTP_REFERER'] ?? 'main.php', FILTER_SANITIZE_URL);
header("Location: $referrer");
exit;
