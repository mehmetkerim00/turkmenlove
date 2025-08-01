
  const quantityInput = document.getElementById("quantity");
  const unitPriceInput = document.getElementById("unit-price");
  const totalPriceEl = document.getElementById("total-price");
  const checkbox = document.getElementById("no-address");
  const addressFields = document.getElementById("address-fields");
  const message = document.getElementById("no-address-msg");

  const unitPrice = parseFloat(unitPriceInput.value) || 0;

  function updateTotal() {
    let qty = parseInt(quantityInput.value);
    if (isNaN(qty) || qty < 1) {
      qty = 1;
      quantityInput.value = 1;
    }
    const total = qty * unitPrice;
    totalPriceEl.textContent = total.toFixed(2);
  }

  function increaseQuantity() {
    quantityInput.value = parseInt(quantityInput.value || 1) + 1;
    updateTotal();
  }

  function decreaseQuantity() {
    const current = parseInt(quantityInput.value || 1);
    if (current > 1) {
      quantityInput.value = current - 1;
      updateTotal();
    }
  }

  checkbox.addEventListener("change", function () {
    if (this.checked) {
      addressFields.style.display = "none";
      message.style.display = "block";
    } else {
      addressFields.style.display = "block";
      message.style.display = "none";
    }
  });

  document.addEventListener("DOMContentLoaded", updateTotal);

  
