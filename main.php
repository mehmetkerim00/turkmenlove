<?php
include 'header.php';
require_once 'db.php';
require_once 'currency_rates.php';

try {
    $stmt = $conn->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка запроса: " . $e->getMessage());
}

$selectedCity = $_SESSION['selected_city'] ?? 'Ашхабад'; // Дефолтный город
?>
<div class="discount-banner">
    Скидка 10% на первый заказ! 
</div>
<div class="city-selector">
    <div class="city-icon" id="city-toggle">
        <span class="city-icon-symbol">📍</span>
        <span class="city-name"><?= htmlspecialchars($selectedCity) ?></span>
        <span class="city-arrow">▼</span>
    </div>
    <div class="city-dropdown" id="city-dropdown">
        <div class="city-option active" data-city="Ашхабад">Ашхабад</div>
        <div class="city-option" data-city="Туркменабат">Туркменабат</div>
        <div class="city-option disabled" data-city="Мары">Мары (скоро)</div>
    </div>
</div>
<main>
    <section class="products">
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php
                $priceUSD = floatval($product['price']);
                $rate = $rates[$currency] ?? 1;
                $symbol = $symbols[$currency] ?? '$';
                $convertedPrice = ceil(($priceUSD * $rate) / 10) * 10;
                ?>
                <div class="product-card">
                    <img src="img/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <p><?= htmlspecialchars($product['name']) ?></p>
                    <span class="price__span">Цена: <?= $symbol . $convertedPrice ?> (<?= $currency ?>)</span>
                    <form class="product-form" data-id="<?= $product['id'] ?>">
                        <button type="submit" class="main__btn">Оформить заказ</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Проверка первого посещения и показ попапа
    const visited = localStorage.getItem('citySelected');
    if (!visited) {
        showCityPopup();
    }

    // Иконка геолокации
    const cityToggle = document.getElementById('city-toggle');
    const cityDropdown = document.getElementById('city-dropdown');
    const cityOptions = document.querySelectorAll('.city-option:not(.disabled)');
    const cityName = document.querySelector('.city-name');
    const selectedCity = localStorage.getItem('selectedCity') || 'Ашхабад';

    cityName.textContent = selectedCity;

    cityToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        cityDropdown.classList.toggle('open');
    });

    cityOptions.forEach(option => {
        option.addEventListener('click', function() {
            const city = this.dataset.city;
            cityName.textContent = city;
            localStorage.setItem('selectedCity', city);
            localStorage.setItem('citySelected', 'true');
            cityDropdown.classList.remove('open');
            // Обновить сессию на сервере, если нужно
            fetch('update_city.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ city: city })
            }).catch(error => console.error('Ошибка обновления города:', error));
        });
    });

    // Закрытие выпадающего списка при клике вне
    document.addEventListener('click', function() {
        cityDropdown.classList.remove('open');
    });

    // Попап выбора города
    function showCityPopup() {
        const popup = document.createElement('div');
        popup.id = 'city-popup';
        popup.className = 'city-popup';
        popup.innerHTML = `
            <div class="city-popup-content">
                <h3>Выберите город доставки</h3>
                <div class="city-option active" data-city="Ашхабад">Ашхабад</div>
                <div class="city-option" data-city="Туркменабат">Туркменабат</div>
                <div class="city-option disabled" data-city="Мары">Мары (скоро)</div>
                <div class="city-popup-buttons">
                    <button class="popup-btn" onclick="selectCityAndClose('Ашхабад')">Ашхабад</button>
                    <button class="popup-btn" onclick="selectCityAndClose('Туркменабат')">Туркменабат</button>
                </div>
            </div>
        `;
        document.body.appendChild(popup);

        // Закрытие попапа при клике вне
        popup.addEventListener('click', function(e) {
            if (e.target === popup) {
                popup.remove();
            }
        });
    }

    window.selectCityAndClose = function(city) {
        const cityName = document.querySelector('.city-name');
        cityName.textContent = city;
        localStorage.setItem('selectedCity', city);
        localStorage.setItem('citySelected', 'true');
        document.getElementById('city-popup').remove();
        // Обновить сессию на сервере
        fetch('update_city.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ city: city })
        }).catch(error => console.error('Ошибка обновления города:', error));
    };

    // Обработка форм товаров
    document.querySelectorAll('.product-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const productId = form.dataset.id;
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            console.log('Отправка запроса на add_to_cart.php с product_id:', productId);
            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Статус ответа:', response.status);
                if (!response.ok) {
                    throw new Error('Ошибка сервера: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Ответ сервера:', data);
                if (data.success) {
                    console.log('Товар добавлен в корзину, перенаправление на checkout.php');
                    window.location.href = 'checkout.php';
                } else {
                    console.error('Ошибка:', data.error);
                    alert('Не удалось добавить товар в корзину: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Ошибка fetch:', error);
                alert('Не удалось добавить товар в корзину. Попробуйте снова.');
            });
        });
    });
});
</script>
</body>
</html>
</body>
</html>