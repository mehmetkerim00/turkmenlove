<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'currency_rates.php';
require_once 'db.php';

$currency = $_SESSION['currency'] ?? 'USD';

$rates = $_SESSION['exchange_rates'] ?? [
    'USD' => 1,
    'RUB' => 90,
    'TRY' => 32,
    'MYR' => 4.7,

];

$symbols = [
    'USD' => '$',
    'RUB' => '₽',
    'TRY' => '₺',
    'MYR' => 'RM',

];

if (isset($_POST['currency']) && array_key_exists($_POST['currency'], $rates)) {
    $_SESSION['currency'] = $_POST['currency'];
    $currency = $_POST['currency'];
}

$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item) && isset($item['quantity'])) {
            $cartCount += $item['quantity'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доставка цветов</title>
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>
<body>

<header class="header">
  <div class="logo">
    <a href="main.php">
      <img class="logo__img" src="./img/logo2.png" alt="Логотип">
    </a> 
    <a href="main.php" title="На главную">
      <img src="./img/home.png" alt="Домой" style="width: 24px; height: 24px;">
    </a>
  </div>

  <!-- Валюта и корзина — вынесены отдельно -->
  <div class="top-controls">
    <form method="post" action="">
      <select name="currency" id="currency" class="styled-select" onchange="this.form.submit()">
        <option value="USD" <?= $currency == 'USD' ? 'selected' : '' ?>>🇺🇸 USD $</option>
        <option value="RUB" <?= $currency == 'RUB' ? 'selected' : '' ?>>🇷🇺 RUB ₽</option>
        <option value="TRY" <?= $currency == 'TRY' ? 'selected' : '' ?>>🇹🇷 TRY ₺</option>
        <option value="MYR" <?= $currency == 'MYR' ? 'selected' : '' ?>>🇲🇾 MYR RM</option>
 
      </select>
    </form>

    <a href="checkout.php" class="cart-icon" style="position: relative; display: inline-block;">
      <img src="img/cart.png" alt="Cart" style="width: 24px;">
      <?php if ($cartCount > 0): ?>
        <span style="
            position: absolute;
            top: -8px;
            right: -8px;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        ">
          <?= $cartCount ?>
        </span>
      <?php endif; ?>
    </a>
  </div>

  <!-- Бургер -->
  <div id="burger" class="burger-menu">
    <span></span>
    <span></span>
    <span></span>
  </div>

  <!-- Навигация -->
  <nav>
    <ul id="navLinks" class="header__list">
      <li class="header__link"><a href="./flowers.php">Цветы</a></li>
      <li class="header__link"><a href="./cakes.php">Торты</a></li>
      <li class="header__link"><a href="./toys.php">Игрушки</a></li>
      <li class="header__link"><a href="./gift_card.php">Подарочные карты</a></li>
    </ul>
  </nav>
</header>

<script>
  const burger = document.getElementById('burger');
  const nav = document.getElementById('navLinks');

  burger.addEventListener('click', () => {
    nav.classList.toggle('active');
  });
</script>
