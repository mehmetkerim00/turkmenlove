document.addEventListener("DOMContentLoaded", function() {
  // Обработка чекбокса "Не знаю адрес"
  const checkbox = document.getElementById("no-address");
  const addressFields = document.getElementById("address-fields");
  const message = document.getElementById("no-address-msg");

  if (checkbox && addressFields && message) {
    checkbox.addEventListener("change", function() {
      if (this.checked) {
        addressFields.style.display = "none";
        message.style.display = "block";
        addressFields.querySelectorAll('input').forEach(input => {
          input.removeAttribute('required');
          input.value = '';
        });
      } else {
        addressFields.style.display = "block";
        message.style.display = "none";
        addressFields.querySelectorAll('input').forEach(input => {
          input.setAttribute('required', '');
        });
      }
    });
  }

  // Удаляем обработку quantity, так как она не нужна в checkout.php
});