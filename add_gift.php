<?php
session_start();
require_once 'db.php';
require_once 'currency_rates.php';

header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    error_log("add_gift.php: Неверный запрос, метод: {$_SERVER['REQUEST_METHOD']}, ID: " . (isset($_POST['id']) ? $_POST['id'] : 'не указан'));
    http_response_code(400);
    echo 'Неверный запрос';
    exit;
}

$gift_id = (int)$_POST['id'];
$quantity = (int)($_POST['quantity'] ?? 1);

if ($gift_id <= 0 || $quantity <= 0) {
    error_log("add_gift.php: Некорректные параметры, ID=$gift_id, quantity=$quantity");
    http_response_code(400);
    echo 'Некорректные параметры';
    exit;
}

error_log("add_gift.php: Обработка подарка ID=$gift_id, quantity=$quantity");

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$gift_id]);
$gift = $stmt->fetch();

if (!$gift || !isset($gift['price'])) {
    error_log("add_gift.php: Подарок не найден для ID=$gift_id");
    http_response_code(404);
    echo 'Товар не найден';
    exit;
}

error_log("add_gift.php: Подарок найден: " . json_encode($gift));

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['id'] === $gift_id) {
        $item['quantity'] += $quantity;
        $found = true;
        break;
    }
}
unset($item);

if (!$found) {
    $_SESSION['cart'][] = [
        'id' => $gift['id'],
        'name' => $gift['name'],
        'price' => $gift['price'],
        'image' => $gift['image'],
        'quantity' => $quantity
    ];
}

$currency = $_SESSION['currency'] ?? 'USD';
$rates = $_SESSION['exchange_rates'] ?? ['USD' => 1, 'RUB' => 90, 'TRY' => 32, 'MYR' => 4.7];
$symbols = ['USD' => '$', 'RUB' => '₽', 'TRY' => '₺', 'MYR' => 'RM'];
$symbol = $symbols[$currency] ?? '$';

$total_price = 0;
$html = '<div class="left-column"><h3>Вы выбрали</h3>';
foreach ($_SESSION['cart'] as $item) {
    $converted_price = ceil($item['price'] * $rates[$currency] / 10) * 10;
    $item_total = $converted_price * $item['quantity'];
    $total_price += $item_total;
    $html .= '
    <div class="product-box">
        <a href="remove_item.php?id=' . (int)$item['id'] . '" class="remove-button" title="Удалить товар">&times;</a>
        <img src="./img/' . htmlspecialchars($item['image']) . '" alt="' . htmlspecialchars($item['name']) . '" class="product-image">
        <div class="product-info">
            <h4>' . htmlspecialchars($item['name']) . '</h4>
            <p>Количество: ' . $item['quantity'] . '</p>
            <p>Цена: ' . $converted_price . ' ' . $symbol . '</p>
            <p><strong>Сумма: ' . $item_total . ' ' . $symbol . '</strong></p>
            <div class="payment-icons">
                <p>Мы принимаем:</p>
                <img src="./img/visa.png" alt="Visa">
                <img src="./img/master.png" alt="Mastercard">
                <img src="./img/paypal (1).png" alt="PayPal">
                <img src="./img/apple-pay.png" alt="Apple Pay">
            </div>
        </div>
    </div>';
}
$html .= '<p style="font-weight: bold; text-align:center; font-size: 24px;">Итого к оплате: ' . $total_price . ' ' . $symbol . '</p>';
$html .= '<div class="btn__block"><button type="submit" class="button">Оплатить</button></div>';
$html .= '<h3 style="text-align: center;">🎁 Рекомендуемые подарки</h3>';
$html .= '<div class="recommendation-box">';
$recommendedQuery = $conn->query("SELECT * FROM products WHERE category IN ('toy', 'cake') LIMIT 8");
$formIndex = 0;
while ($gift = $recommendedQuery->fetch(PDO::FETCH_ASSOC)) {
    $convertedPrice = ceil(($gift['price'] * $rates[$currency]) / 10) * 10;
    $html .= '
    <div class="recommendation-item">
        <img src="./img/' . htmlspecialchars($gift['image']) . '" alt="' . htmlspecialchars($gift['name']) . '">
        <h4>' . htmlspecialchars($gift['name']) . '</h4>
        <p>' . $convertedPrice . ' ' . $symbol . '</p>
        <form class="gift-form" id="gift-form-' . $formIndex . '" data-id="' . (int)$gift['id'] . '">
            <input type="hidden" name="id" value="' . (int)$gift['id'] . '">
            <div class="quantity-controls">
                <button type="button" class="decrease">−</button>
                <input class="strange__btn" type="number" name="quantity" value="1" min="1">
                <button type="button" class="increase">+</button>
            </div>
            <button type="submit" class="recommendation-btn">Добавить</button>
        </form>
    </div>';
    $formIndex++;
}
$html .= '</div></div>';

error_log("add_gift.php: Успешно сгенерирован HTML для ID=$gift_id");
echo $html;
?>