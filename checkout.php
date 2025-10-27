<?php
include 'header.php';
require_once 'db.php';
require_once 'currency_rates.php';

if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $new_product = $stmt->fetch();

    if (!$new_product || !isset($new_product['price'])) {
        die("Ошибка: товар не найден или у него нет цены.");
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] === $new_product['id']) {
            $item['quantity'] += 1;
            $found = true;
            break;
        }
    }
    unset($item);

    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $new_product['id'],
            'name' => $new_product['name'],
            'price' => $new_product['price'],
            'image' => $new_product['image'],
            'quantity' => 1
        ];
    }
}

if (!isset($product_id)) {
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $cart_item) {
            if (isset($cart_item['id'])) {
                $product_id = $cart_item['id'];
                break;
            }
        }
    }
    if (!isset($product_id)) {
        echo '
        <div style="display: flex; justify-content: center; align-items: center; height: 60vh; text-align: center;">
            <div>
                <h2 style="color: #d44f68;">Ваша корзина пуста</h2>
                <p>Похоже, вы ещё не добавили товары в корзину.</p>
                <a href="main.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background-color: #d44f68; color: white; text-decoration: none; border-radius: 5px;">Вернуться на главную</a>
            </div>
        </div>';
        include 'footer.php';
        exit;
    }
}

$quantity = 1;
foreach ($_SESSION['cart'] as $item) {
    if ($item['id'] == $product_id) {
        $quantity = $item['quantity'];
        break;
    }
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product || !isset($product['price'])) {
    die("Ошибка: товар не найден или у него нет цены.");
}

$extra_info = '';
$category = $product['category'];

if ($category === 'flower') {
    $stmtDetails = $conn->prepare("SELECT flower_count FROM flower_details WHERE product_id = ?");
    $stmtDetails->execute([$product_id]);
    $flower = $stmtDetails->fetch();
    if ($flower) {
        $extra_info = "Количество цветов: " . $flower['flower_count'];
    }
} elseif ($category === 'cake') {
    $stmtDetails = $conn->prepare("SELECT weight FROM cake_details WHERE product_id = ?");
    $stmtDetails->execute([$product_id]);
    $cake = $stmtDetails->fetch();
    if ($cake) {
        $extra_info = "Вес торта: " . $cake['weight'];
    }
} elseif ($category === 'toy') {
    $stmtDetails = $conn->prepare("SELECT size FROM toy_details WHERE product_id = ?");
    $stmtDetails->execute([$product_id]);
    $toy = $stmtDetails->fetch();
    if ($toy) {
        $extra_info = "Размер игрушки: " . $toy['size'];
    }
}

$rates = $_SESSION['exchange_rates'] ?? [
    'USD' => 1,
    'RUB' => 90,
    'TRY' => 32
];
$currency = $_SESSION['currency'] ?? 'USD';
$symbols = [
    'USD' => '$',
    'RUB' => '₽',
    'TRY' => '₺',
    'MYR' => 'RM'
];
$symbol = $symbols[$currency] ?? '$';

if (!isset($rates[$currency])) {
    $currency = 'USD';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Оформление заказа</title>
  <link rel="stylesheet" href="./css/normalize.css">
  <link rel="stylesheet" href="./css/checkout.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.19/build/css/intlTelInput.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
<main>
  <?php if (isset($_SESSION['form_errors'])): ?>
    <div class="error-messages">
        <ul>
            <?php foreach ($_SESSION['form_errors'] as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['form_errors']); ?>
  <?php endif; ?>
  <form class="form__class" method="post" action="./orderdata.php">
    <div class="main__block">
      <div class="right__block">
        <h1>Оформление заказа</h1>
        <div class="form-group">
          <h3 style="margin: 10px;">Ваши контакты</h3>
          <div class="input-group">
            <label for="client_name">Ваше имя</label>
            <input id="client_name" type="text" name="client_name" value="<?= htmlspecialchars($_SESSION['form_data']['client_name'] ?? '') ?>" required>
            <span class="error-text"></span>
          </div>
        </div>
        <div class="double__input">
          <div class="input-group">
            <label for="client_email">E-mail</label>
            <input id="client_email" type="email" name="client_email" value="<?= htmlspecialchars($_SESSION['form_data']['client_email'] ?? '') ?>" required>
            <span class="error-text"></span>
          </div>
          <div class="input-group input-number">
            <label for="phone1">Телефон</label>
            <input id="phone1" name="client_phone" class="tel" type="tel" value="<?= htmlspecialchars($_SESSION['form_data']['client_phone'] ?? '') ?>" required data-intl-tel-input-id="0">
            <span class="error-text"></span>
          </div>
        </div>
        <div class="form-group">
          <h3 style="margin: 10px;">Контакты получателя</h3>
          <div class="input-group">
            <label for="reciever_name">Имя получателя</label>
            <input id="reciever_name" type="text" name="reciever_name" value="<?= htmlspecialchars($_SESSION['form_data']['reciever_name'] ?? '') ?>" required>
            <span class="error-text"></span>
          </div>
        </div>
        <div class="double__input">
          <div class="input-group input-number">
            <label for="phone2">Телефон получателя</label>
            <input id="phone2" class="tel" name="reciever_phone" type="tel" value="<?= htmlspecialchars($_SESSION['form_data']['reciever_phone'] ?? '') ?>" required data-intl-tel-input-id="1">
            <span class="error-text"></span>
          </div>
        </div>
        <div class="form-group">
          <div class="input-group">
            <label for="city">Город</label>
            <select id="city" class="select__time" name="city" required>
              <option value="" disabled <?php echo !isset($_SESSION['form_data']['city']) ? 'selected' : ''; ?>>-- Выберите город --</option>
              <option value="Ашхабад" <?php echo ($_SESSION['form_data']['city'] ?? '') === 'Ашхабад' ? 'selected' : ''; ?>>Ашхабад</option>
              <option value="Туркменабат" <?php echo ($_SESSION['form_data']['city'] ?? '') === 'Туркменабат' ? 'selected' : ''; ?>>Туркменабат</option>
            </select>
            <span class="error-text"></span>
          </div>
        </div>
        <div class="form-group checkbox-group">
            <label class="checkbox-label" for="no-address">
                <input name="no_address" type="checkbox" id="no-address" <?php echo isset($_SESSION['form_data']['no_address']) ? 'checked' : ''; ?>>
                <span style="font-weight: bold;">Не знаю адрес</span>
            </label>
            <p id="no-address-msg" class="no-address-msg" style="display: <?php echo isset($_SESSION['form_data']['no_address']) ? 'block' : 'none'; ?>;">Свяжемся с получателем и узнаем адрес</p>
        </div>
        <div id="address-fields" style="display: <?php echo isset($_SESSION['form_data']['no_address']) ? 'none' : 'block'; ?>;">
            <div class="input-group">
                <label for="address">Улица и номер дома</label>
                <input id="address" name="address" type="text" value="<?= htmlspecialchars($_SESSION['form_data']['address'] ?? '') ?>" <?php echo !isset($_SESSION['form_data']['no_address']) ? 'required' : ''; ?>>
                <span class="error-text"></span>
            </div>
            <div class="input-group">
                <label for="apartment">Квартира</label>
                <input id="apartment" name="apartment" type="number" value="<?= htmlspecialchars($_SESSION['form_data']['apartment'] ?? '') ?>" min="1" max="999" <?php echo !isset($_SESSION['form_data']['no_address']) ? 'required' : ''; ?>>
                <span class="error-text"></span>
            </div>
        </div>
        <div class="form-group">
          <div class="input-group">
            <label for="textarea">Комментарии (например, доп. контакт)</label>
            <textarea id="textarea" name="textarea" maxlength="500"><?= htmlspecialchars($_SESSION['form_data']['textarea'] ?? '') ?></textarea>
            <span class="error-text"></span>
          </div>
        </div>
        <div class="form-group">
          <h3 style="margin: 10px;">Когда доставить</h3>
          <div class="input-group">
            <label for="date">Дата доставки</label>
            <input id="date" type="text" name="delivery_date" value="<?= htmlspecialchars($_SESSION['form_data']['delivery_date'] ?? '') ?>" required readonly>
            <span class="error-text"></span>
          </div>
        </div>
        <div class="form-group">
          <h3 style="margin: 10px;">Укажите время доставки</h3>
          <div class="input-group">
            <label for="delivery_time">Время доставки</label>
            <select id="delivery_time" class="select__time" name="delivery_time" required>
              <option value="" disabled <?php echo !isset($_SESSION['form_data']['delivery_time']) ? 'selected' : ''; ?>>-- Выберите интервал --</option>
              <?php
              $times = ["08:00–12:00", "12:00–16:00", "16:00–20:00"];
              foreach ($times as $time) {
                  echo "<option value=\"$time\" " . (($_SESSION['form_data']['delivery_time'] ?? '') === $time ? 'selected' : '') . ">$time</option>";
              }
              ?>
            </select>
            <span class="error-text"></span>
          </div>
        </div>
      </div>
      <div class="left__block">
        <div class="left-column">
          <h3>Вы выбрали</h3>
          <?php
          $total_price = 0;
          foreach ($_SESSION['cart'] as $item):
              $converted_price = ceil($item['price'] * $rates[$currency] / 10) * 10;
              $item_total = $converted_price * $item['quantity'];
              $total_price += $item_total;
          ?>
          <div class="product-box">
            <a href="remove_item.php?id=<?= (int)$item['id'] ?>" class="remove-button" title="Удалить товар">&times;</a>
            <img src="./img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
            <div class="product-info">
              <h4><?= htmlspecialchars($item['name']) ?></h4>
              <p>Количество: <?= $item['quantity'] ?></p>
              <p>Цена: <?= $converted_price ?> <?= $symbol ?></p>
              <p><strong>Сумма: <?= $item_total ?> <?= $symbol ?></strong></p>
              <div class="payment-icons">
                <p>Мы принимаем:</p>
                <img src="./img/visa.png" alt="Visa">
                <img src="./img/master.png" alt="Mastercard">
                <img src="./img/paypal (1).png" alt="PayPal">
                <img src="./img/apple-pay.png" alt="Apple Pay">
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <p style="font-weight: bold; text-align:center; font-size: 24px;">Итого к оплате: <?= $total_price ?> <?= $symbol ?></p>
          <div class="btn__block">
            <button type="submit" class="button">Оплатить</button>
          </div>
          <h3 style="text-align: center;">🎁 Рекомендуемые подарки</h3>
<div class="recommendation-box">
    <?php
    $recommendedQuery = $conn->query("SELECT * FROM products WHERE category IN ('toy', 'cake') LIMIT 8");
    $formIndex = 0;
    while ($gift = $recommendedQuery->fetch(PDO::FETCH_ASSOC)) {
        $convertedPrice = ceil(($gift['price'] * $rates[$currency]) / 10) * 10;
        echo '
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
    ?>
</div>
        </div>
      </div>
    </div>
  </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.19/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.19/build/js/utils.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded: Страница загружена, инициализация скриптов');

    // Инициализация intl-tel-input
    window.intlTelInput(document.querySelector("#phone1"), { initialCountry: "us" });
    window.intlTelInput(document.querySelector("#phone2"), { initialCountry: "tm" });

    // Инициализация flatpickr
    flatpickr("#date", {
        locale: "ru",
        dateFormat: "d F Y",
        defaultDate: "today",
        minDate: "today",
        disableMobile: false,
        clickOpens: true,
        allowInput: false
    });

    // Обработка чекбокса "Не знаю адрес"
    const noAddressCheckbox = document.getElementById('no-address');
    const addressFields = document.getElementById('address-fields');
    const noAddressMsg = document.getElementById('no-address-msg');
    if (noAddressCheckbox && addressFields && noAddressMsg) {
        noAddressMsg.style.display = noAddressCheckbox.checked ? 'block' : 'none';
        noAddressCheckbox.addEventListener('change', function() {
            console.log('Чекбокс "Не знаю адрес":', this.checked);
            addressFields.style.display = this.checked ? 'none' : 'block';
            noAddressMsg.style.display = this.checked ? 'block' : 'none';
            const inputs = addressFields.querySelectorAll('input');
            inputs.forEach(input => {
                if (this.checked) {
                    input.removeAttribute('required');
                    input.value = '';
                } else {
                    input.setAttribute('required', '');
                }
            });
        });
    }

    // Обработка плавающих меток
    document.querySelectorAll('.input-group input, .input-group select, .input-group textarea').forEach(input => {
        const label = input.parentElement.querySelector('label');
        if (input.value || (input.tagName === 'SELECT' && input.value !== '')) {
            label.classList.add('active');
        }
        input.addEventListener('focus', () => label.classList.add('active'));
        input.addEventListener('blur', () => {
            if (!input.value && (input.tagName !== 'SELECT' || input.value === '')) {
                label.classList.remove('active');
            }
        });
        input.addEventListener('input', () => {
            if (input.value || (input.tagName === 'SELECT' && input.value !== '')) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
        });
    });

    // Проверка форм .gift-form
    const giftForms = document.querySelectorAll('.gift-form');
    console.log('Найдено форм .gift-form:', giftForms.length);
    giftForms.forEach((form, index) => {
        console.log(`Форма ${index + 1}: id=${form.id}, data-id=${form.dataset.id}`);
    });

    // Привязка событий для .gift-form
    function rebindGiftForms() {
        console.log('rebindGiftForms: Начало привязки событий, форм найдено:', document.querySelectorAll('.gift-form').length);
        document.querySelectorAll('.gift-form').forEach((form, index) => {
            // Удаляем старый обработчик
            if (form._submitHandler) {
                form.removeEventListener('submit', form._submitHandler);
            }
            form._submitHandler = function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log(`Отправка формы ${index + 1}, ID: ${form.dataset.id}, Form ID: ${form.id}`);
                console.log('Form elements:', Array.from(form.querySelectorAll('input, button')).map(el => el.outerHTML));
                const formData = new FormData(form);
                fetch('add_gift.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'text/html'
                    }
                })
                .then(response => {
                    console.log('Ответ сервера:', response.status, response.url);
                    if (response.redirected) {
                        console.error('Перенаправление:', response.url);
                        throw new Error('Сервер перенаправил на: ' + response.url);
                    }
                    if (!response.ok) {
                        throw new Error('Ошибка сервера: ' + response.status);
                    }
                    return response.text();
                })
                .then(data => {
                    console.log('Полученные данные:', data.substring(0, 100) + '...');
                    const cartBlock = document.querySelector('.left-column');
                    if (cartBlock) {
                        cartBlock.innerHTML = data;
                        rebindGiftForms();
                        console.log('Подарок добавлен, корзина обновлена');
                    } else {
                        console.error('Блок .left-column не найден');
                    }
                })
                .catch(error => {
                    console.error('Ошибка AJAX:', error);
                    alert('Не удалось добавить подарок: ' + error.message);
                });
            };
            form.addEventListener('submit', form._submitHandler);

            const decreaseBtn = form.querySelector('.decrease');
            const increaseBtn = form.querySelector('.increase');
            const quantityInput = form.querySelector('input[name="quantity"]');
            if (decreaseBtn && increaseBtn && quantityInput) {
                decreaseBtn.addEventListener('click', () => {
                    let current = parseInt(quantityInput.value);
                    if (current > 1) quantityInput.value = current - 1;
                });
                increaseBtn.addEventListener('click', () => {
                    let current = parseInt(quantityInput.value);
                    quantityInput.value = current + 1;
                });
            }
        });
    }

    rebindGiftForms();

    // Перехват всех submit-событий для отладки
    document.addEventListener('submit', function(e) {
        console.log('Глобальный submit перехвачен:', e.target);
        if (e.target.classList.contains('gift-form')) {
            console.warn('Форма .gift-form отправлена:', e.target.id, e.target.dataset.id);
        }
    });
});
</script>
<?php unset($_SESSION['form_data']); ?>
<?php include 'footer.php'; ?>
</body>
</html>