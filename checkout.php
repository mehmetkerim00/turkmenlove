
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $newQty = (int)$_POST['quantity'];
    $_SESSION['cart']['quantity'] = max(1, $newQty); 
    $quantity = $_SESSION['cart']['quantity'];
}

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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.19/build/css/intlTelInput.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>

<main>
  <form class="form__class" method="post" action="./orderdata.php">
    <div class="main__block">
    <div class="right__block">
  <h1>Оформление заказа</h1>
 
  <div class="form-group">
    <h3 style="margin: 10px;">Ваши контакты</h3>
    <input class="special__input" type="text" name="client_name" 
           placeholder="Имя" 
           pattern="[А-Яа-яЁёA-Za-z\s]{2,50}"
           title="Имя должно содержать только буквы (2-50 символов)"
           required>
  </div>

  <div class="double__input">
    <input type="email" name="client_email" 
           placeholder="E-mail"
           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
           title="Введите корректный email (например, example@mail.com)"
           required>
    
    <input id="phone1" name="client_phone" class="tel" type="tel"
           placeholder="Телефон"
           required
           data-intl-tel-input-id="0">
  </div>

 
  <div class="form-group">
    <h3 style="margin: 10px;">Контакты получателя</h3>
    <input type="text" name="reciever_name" 
           placeholder="Имя"
           pattern="[А-Яа-яЁёA-Za-z\s]{2,50}"
           title="Имя должно содержать только буквы (2-50 символов)"
           required>
  </div>

  <div class="double__input">
    <input id="phone2" class="tel" name="reciever_phone" type="tel"
           placeholder="Телефон получателя"
           required
           data-intl-tel-input-id="1">
  </div>


  <div class="form-group">
    <h3 style="margin: 10px;">Город</h3>
    <select class="select__time" name="city" required>
      <option value="" disabled selected>-- Выберите город --</option>
      <option value="Ашхабад">Ашхабад</option>
      <option value="Туркменабат">Туркменабат</option>
    </select>
  </div>


  <div class="form-group">
    <label style="display: flex; align-items: center; max-width: 200px; margin-bottom: 15px;">
    <span style="margin-left: 4px;">Не знаю адрес</span>
      <input name="no_address" type="checkbox" id="no-address">
   
    </label>
    <p id="no-address-msg" style="display: none; color: green; font-size: 20px">
      Свяжемся с получателем и узнаем адрес
    </p>
  </div>

  <div id="address-fields">
    <input name="address" type="text" class="special__input" 
           placeholder="Улица и номер дома"
           pattern="[\w\s.,-]{5,100}"
           title="Адрес должен содержать 5-100 символов"
           required>
    
    <input name="apartment" type="number" class="special__input" 
           placeholder="Квартира"
           min="1" max="999"
           title="Номер квартиры (1-999)"
           required>
    
    <input name="gate" type="number" class="special__input" 
           placeholder="Подъезд"
           min="1" max="50"
           title="Номер подъезда (1-50)"
           required>
    
    <input name="floor" type="number" class="special__input" 
           placeholder="Этаж"
           min="1" max="150"
           title="Номер этажа (1-150)"
           required>
  </div>


  <div class="form-group">
    <textarea name="textarea" placeholder="Комментарии (например, доп. контакт)"
              maxlength="500"></textarea>
  </div>

  
  <div class="form-group">
    <h3 style="margin: 10px;">Когда доставить</h3>
    <input type="text" id="date" name="delivery_date" class="special__input" 
           placeholder="Выберите дату"
           required
           readonly>
  </div>

  <div class="form-group">
    <h3 style="margin: 10px;">Укажите время доставки</h3>
    <select class="select__time" name="delivery_time" required>
      <option value="" disabled selected>-- Выберите интервал --</option>
      <?php
      $times = ["10:00–12:00", "11:00–13:00", "12:00–14:00", "13:00–15:00", 
               "14:00–16:00", "15:00–17:00", "16:00–18:00", "17:00–19:00", 
               "18:00–20:00", "19:00–21:00", "20:00–22:00", "21:00–23:00", 
               "22:00–00:00"];
      foreach ($times as $time) {
          echo "<option value=\"$time\">$time</option>";
      }
      ?>
    </select>
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
$currency = $_SESSION['currency'] ?? 'USD';
$rates = $_SESSION['exchange_rates'] ?? ['USD' => 1, 'RUB' => 90, 'TRY' => 32];
$symbols = ['USD' => '$', 'RUB' => '₽', 'TRY' => '₺', 'MYR' => 'RM'];
$symbol = $symbols[$currency] ?? '$';

$recommendedQuery = $conn->query("SELECT * FROM products WHERE category IN ('giftcard', 'toy') LIMIT 8");

while ($gift = $recommendedQuery->fetch(PDO::FETCH_ASSOC)) {
$convertedPrice = ceil(($gift['price'] * $rates[$currency]) / 10) * 10;
    echo '
    <div class="recommendation-item">
        <img src="./img/' . htmlspecialchars($gift['image']) . '" alt="' . htmlspecialchars($gift['name']) . '">
        <h4>' . htmlspecialchars($gift['name']) . '</h4>
        <p>' . $convertedPrice . ' ' . $symbol . '</p>

        <form method="post" action="add_gift.php" class="gift-form">
            <input type="hidden" name="id" value="' . (int)$gift['id'] . '">
            <div class="quantity-controls">
                <button type="button" class="decrease">−</button>
                <input class="strange__btn" type="number" name="quantity" value="1" min="1">
                <button type="button" class="increase">+</button>
            </div>
            <button type="submit" class="recommendation-btn">Добавить</button>
        </form>
    </div>';
}
?>
</div>
</div>
</div>
 
      </div>
    </div>
  </div>
</div>

<script>
  const quantityInput = document.getElementById("quantity");
  const totalPriceEl = document.getElementById("total-price");
  const unitPrice = parseFloat(document.getElementById("unit-price-hidden").value);

  function updateTotal() {
    let qty = parseInt(quantityInput.value);
    if (isNaN(qty) || qty < 1) qty = 1;
    const total = (qty * unitPrice).toFixed(2);
    totalPriceEl.textContent = total;
    quantityInput.value = qty;
  }

  function increaseQuantity() {
    quantityInput.value = parseInt(quantityInput.value || 1) + 1;
    updateTotal();
  }

  function decreaseQuantity() {
    let qty = parseInt(quantityInput.value);
    if (qty > 1) {
      quantityInput.value = qty - 1;
      updateTotal();
    }
  }

  document.addEventListener("DOMContentLoaded", updateTotal);
  quantityInput.addEventListener("input", updateTotal);
</script>

    </div>
  </form>
</main>




<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.19/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.19/build/js/utils.js"></script>
<script>
  window.intlTelInput(document.querySelector("#phone1"), { initialCountry: "us" });
  window.intlTelInput(document.querySelector("#phone2"), { initialCountry: "tm" });
</script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
<script>
 flatpickr("#date", {
  locale: "ru",
  dateFormat: "d F Y",
  defaultDate: "today",
  minDate: "today",
  disableMobile: false, 
  clickOpens: true,   
  allowInput: false   
});

  document.getElementById('no-address').addEventListener('change', function () {
    const checked = this.checked;
    document.getElementById('address-fields').style.display = checked ? 'none' : 'block';
    document.getElementById('no-address-msg').style.display = checked ? 'block' : 'none';
  });
  document.getElementById('no-address').addEventListener('change', function() {
    const addressFields = document.getElementById('address-fields');
    const inputs = addressFields.querySelectorAll('input[required]');
    
    if (this.checked) {
        
        inputs.forEach(input => input.removeAttribute('required'));
    } else {
    
        inputs.forEach(input => input.setAttribute('required', ''));
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.gift-form').forEach(form => {
        const decreaseBtn = form.querySelector('.decrease');
        const increaseBtn = form.querySelector('.increase');
        const quantityInput = form.querySelector('input[name="quantity"]');

        if (decreaseBtn && increaseBtn && quantityInput) {
            decreaseBtn.addEventListener('click', () => {
                let current = parseInt(quantityInput.value);
                if (current > 1) {
                    quantityInput.value = current - 1;
                }
            });

            increaseBtn.addEventListener('click', () => {
                let current = parseInt(quantityInput.value);
                quantityInput.value = current + 1;
            });
        }
    });
});
</script>


<?php include 'footer.php'; ?>
<script src="./index.js"></script>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
  const form = this;
  let isValid = true;

document.getElementById('no-address').addEventListener('change', function() {
    const addressFields = document.getElementById('address-fields');
    const inputs = addressFields.querySelectorAll('input[required]');
    
    if (this.checked) {
        
        inputs.forEach(input => input.removeAttribute('required'));
    } else {
    
        inputs.forEach(input => input.setAttribute('required', ''));
    }
});
  const phoneInputs = [
    document.querySelector('#phone1'),
    document.querySelector('#phone2')
  ];

  phoneInputs.forEach(input => {
    const iti = window.intlTelInputGlobals.getInstance(input);
    if (!iti.isValidNumber()) {
      input.style.borderColor = 'red';
      isValid = false;
    }
  });


  const dateInput = document.getElementById('date');
  if (!dateInput.value) {
    dateInput.style.borderColor = 'red';
    isValid = false;
  }

  const noAddress = document.getElementById('no-address');
  const addressFields = document.getElementById('address-fields');

  if (!noAddress.checked) {
    const addressInputs = addressFields.querySelectorAll('input[required]');
    addressInputs.forEach(input => {
      if (!input.value.trim()) {
        input.style.borderColor = 'red';
        isValid = false;
      }
    });
  }

  if (!isValid) {
    e.preventDefault();
    alert('Пожалуйста, заполните все обязательные поля корректно!');
  }
});


document.querySelectorAll('input, select').forEach(el => {
  el.addEventListener('input', () => {
    el.style.borderColor = '';
  });
});
</script>

</body>
</html> 