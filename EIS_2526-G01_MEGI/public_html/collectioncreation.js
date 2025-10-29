// =============================
// Notification Popup
// =============================
document.addEventListener("DOMContentLoaded", () => {
  const bellBtn = document.querySelector('.icon-btn[aria-label="Notificações"]');
  const popup = document.getElementById('notification-popup');

  if (bellBtn && popup) {
    bellBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', (e) => {
      if (!popup.contains(e.target) && !bellBtn.contains(e.target)) {
        popup.style.display = 'none';
      }
    });
  }

  // =============================
  // Custom Multi-select Dropdown
  // =============================
  const dropdownBtn = document.getElementById("dropdownBtn");
  const dropdownContent = document.getElementById("dropdownContent");

  dropdownBtn.addEventListener("click", () => {
    dropdownContent.style.display =
      dropdownContent.style.display === "block" ? "none" : "block";
  });

  document.addEventListener("click", (e) => {
    if (!dropdownContent.contains(e.target) && e.target !== dropdownBtn) {
      dropdownContent.style.display = "none";
    }
  });

  dropdownContent.addEventListener("change", () => {
    const checked = [
      ...dropdownContent.querySelectorAll("input[type='checkbox']:checked"),
    ].map((c) => c.parentElement.textContent.trim());

    dropdownBtn.textContent =
      checked.length > 0
        ? checked.join(", ")
        : "Select Collections ⮟";
  });

  // =============================
  // FORM VALIDATION
  // =============================
  const form = document.getElementById("itemForm");
  const formMessage = document.getElementById("formMessage");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const checkedCollections = [
      ...dropdownContent.querySelectorAll("input[type='checkbox']:checked"),
    ];
    const name = document.getElementById("itemName");
    const price = document.getElementById("itemPrice");
    const date = document.getElementById("acquisitionDate");

    [name, price, date].forEach((el) => el.classList.remove("error"));
    formMessage.textContent = "";
    formMessage.className = "form-message";

    let valid = true;

    if (checkedCollections.length === 0) {
      dropdownBtn.style.borderColor = "#b54242";
      valid = false;
    } else {
      dropdownBtn.style.borderColor = "#ccc";
    }

    if (name.value.trim() === "") {
      name.classList.add("error");
      valid = false;
    }

    if (price.value.trim() === "" || isNaN(price.value) || parseFloat(price.value) < 0) {
      price.classList.add("error");
      valid = false;
    }

    const datePattern = /^\d{2}-\d{2}-\d{4}$/;
    if (!datePattern.test(date.value.trim())) {
      date.classList.add("error");
      valid = false;
    }

    if (!valid) {
      formMessage.textContent = "⚠️ Please fill in all required (*) fields correctly.";
      formMessage.classList.add("error");
      return;
    }

    formMessage.textContent = "✅ Item created successfully!";
    formMessage.classList.add("success");
    form.reset();
    dropdownBtn.textContent = "Select Collections ⮟";
    dropdownContent.querySelectorAll("input[type='checkbox']").forEach(cb => cb.checked = false);
  });
});

