// =============================
// Notificações 
// =============================

document.addEventListener("DOMContentLoaded", () => {
  const bellBtn = document.querySelector('.icon-btn[aria-label="Notificações"]');
  const popup = document.getElementById('notification-popup');
  const seeMoreLink = document.querySelector('.see-more-link');

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

  // Expandir / Encolher notificações
  if (seeMoreLink) {
    seeMoreLink.addEventListener('click', (e) => {
      e.preventDefault();

      popup.classList.toggle('expanded');

      if (popup.classList.contains('expanded')) {
        seeMoreLink.textContent = "Show less";
      } else {
        seeMoreLink.textContent = "+ See more";
      }
    });
  }
});


    
  // === Importance Slider and Number Sync ===
  const slider = document.getElementById("itemImportanceSlider");
  const number = document.getElementById("itemImportanceNumber");

  if (slider && number) {
    // When slider changes, update number
    slider.addEventListener("input", () => {
      number.value = slider.value;
    });

    // When number changes, update slider (with bounds check)
    number.addEventListener("input", () => {
      let val = parseInt(number.value, 10);
      if (isNaN(val)) return;
      if (val < 1) val = 1;
      if (val > 10) val = 10;
      slider.value = val;
      number.value = val;
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
      checked.length > 0 ? checked.join(", ") : "Select Collections ⮟";
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
    const type = document.getElementById("itemType");
    const importance = document.getElementById("itemImportanceNumber");
    const date = document.getElementById("acquisitionDate");

    [name, price, type, importance, date].forEach((el) =>
      el.classList.remove("error")
    );
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

    if (
      price.value.trim() === "" ||
      isNaN(price.value) ||
      parseFloat(price.value) < 0
    ) {
      price.classList.add("error");
      valid = false;
    }

    if (type.value.trim() === "") {
      type.classList.add("error");
      valid = false;
    }

    if (importance.value.trim() === "") {
      importance.classList.add("error");
      valid = false;
    }

    const today = new Date().toISOString().split("T")[0];
    date.max = today;

    const dateValue = date.value.trim();
    if (!dateValue) {
      date.classList.add("error");
      valid = false;
    } else {
      const selected = new Date(dateValue);
      const now = new Date();
      selected.setHours(0, 0, 0, 0);
      now.setHours(0, 0, 0, 0);
      if (selected > now) {
        date.classList.add("error");
        valid = false;
      }
    }

    if (!valid) {
      formMessage.textContent =
        "⚠️ Please fill in all required (*) fields correctly.";
      formMessage.classList.add("error");
      return;
    }

    formMessage.textContent = "✅ Item created successfully!";
    formMessage.classList.add("success");
    form.reset();
    dropdownBtn.textContent = "Select Collections ⮟";
    dropdownContent
      .querySelectorAll("input[type='checkbox']")
      .forEach((cb) => (cb.checked = false));
  });

