<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/footer.css">
    <title>Footer</title>
</head>
<body>
    <footer class="footer">
  <div class="footer-container">
    <div class="footer-logo">
      <h2>TurkmenLove</h2>
      <p>© 2025 TurkmenLove. Все права защищены.</p>
    </div>

    <div class="footer-contact">
      <h4>Контакты</h4>
      <p>Email: support@turkmenlove.com</p>
      <p>Телефон: +993 61 00 00 00</p>
      <p>WhatsApp: +993 61 00 00 00</p>
    </div>

   <div class="payment__icons">
                <p>Мы принимаем:</p>
                <img src="./img/visa.png" alt="Visa" width="24">
                <img src="./img/master.png" alt="Mastercard" width="24">
                <img src="./img/paypal (1).png" alt="PayPal" width="24">
                <img src="./img/apple-pay2.png" alt="Apple Pay" width="24">
            </div>

    <div class="footer-social">
      <h4>Мы в соцсетях</h4>
      <a href="#"><img src="img/instagram.png" alt="Instagram" width="24"></a>
      <a href="#"><img src="img/facebook.png" alt="Facebook" width="24"></a>
      <a href="#"><img src="img/telegram.png" alt="Telegram" width="24"></a>
      <a href="#"><img src="img/whatsapp.png" alt="WhatsApp" width="24"></a>
    </div>
  </div>
  <button id="scrollToTop" title="Наверх">↑</button>
  <a href="https://wa.me/13477615174" class="whatsapp-icon" target="_blank">
            <img src="./img/wa2.svg" alt="WhatsApp">
        </a>
</footer>
<script>
 
  window.onscroll = function () {
    const btn = document.getElementById("scrollToTop");
    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
      btn.style.display = "block";
    } else {
      btn.style.display = "none";
    }
  };

  
  document.getElementById("scrollToTop").addEventListener("click", function () {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
</script>

</body>
</html>