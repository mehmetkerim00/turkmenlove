<?php include 'header.php'; ?>
<style>
  .payment-container {
    max-width: 600px;
    margin: 50px auto;
    padding: 30px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-align: center;
  }
  .payment-title {
    font-size: 28px;
    margin-bottom: 30px;
    font-weight: bold;
  }
  .payment-methods {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
  }
  .payment-method {
    flex: 0 0 120px;
    padding: 20px;
    border: 2px solid #ddd;
    border-radius: 15px;
    cursor: pointer;
    transition: 0.3s;
  }
  .payment-method:hover {
    border-color: #444;
    background: #f9f9f9;
  }
  .payment-method img {
    width: 60px;
    height: auto;
  }
  .payment-label {
    margin-top: 10px;
    font-size: 14px;
  }
  /* Медиазапросы для адаптации */
@media (max-width: 768px) {
  .payment-container {
    margin: 30px 20px;
    padding: 25px;
    border-radius: 15px;
  }
  
  .payment-title {
    font-size: 24px;
    margin-bottom: 25px;
  }
  
  .payment-method {
    flex: 0 0 100px;
    padding: 15px;
  }
  
  .payment-method img {
    width: 50px;
  }
}

@media (max-width: 576px) {
  .payment-container {
    margin: 20px 15px;
    padding: 20px;
  }
  
  .payment-title {
    font-size: 20px;
    margin-bottom: 20px;
  }
  
  .payment-methods {
    gap: 15px;
  }
  
  .payment-method {
    flex: 0 0 80px;
    padding: 12px;
    border-radius: 12px;
  }
  
  .payment-method img {
    width: 40px;
  }
  
  .payment-label {
    font-size: 12px;
    margin-top: 8px;
  }
}

@media (max-width: 400px) {
  .payment-methods {
    gap: 10px;
  }
  
  .payment-method {
    flex: 0 0 70px;
    padding: 10px;
  }
  
  .payment-method img {
    width: 35px;
  }
}
</style>

<div class="payment-container">
  <div class="payment-title">Выберите способ оплаты</div>
  <div class="payment-methods">

    <div class="payment-method" onclick="alert('Оплата через Visa выбрана')">
      <img src="img/visa.png" alt="Visa">
      <div class="payment-label">Visa</div>
    </div>

    <div class="payment-method" onclick="alert('Оплата через MasterCard выбрана')">
      <img src="img/master.png" alt="MasterCard">
      <div class="payment-label">MasterCard</div>
    </div>

    <div class="payment-method" onclick="alert('Оплата через PayPal выбрана')">
      <img src="img/paypal (1).png" alt="PayPal">
      <div class="payment-label">PayPal</div>
    </div>

    <div class="payment-method" onclick="alert('Оплата через Apple Pay выбрана')">
      <img src="./img/apple-pay.png" alt="Apple Pay">
      <div class="payment-label">Apple Pay</div>
    </div>

  </div>
</div>

<?php include 'footer.php'; ?>
