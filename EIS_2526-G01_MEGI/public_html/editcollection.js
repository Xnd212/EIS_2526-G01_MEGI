// =============================
// Form Validation
// =============================
const form = document.getElementById("collectionForm");
const formMessage = document.getElementById("formMessage");

form.addEventListener("submit", (e) => {
  e.preventDefault();

  const collector = document.getElementById("collectorName");
  const theme = document.getElementById("collectionTheme");
  const startDate = document.getElementById("startDate");

  [collector, theme, startDate].forEach((el) => el.classList.remove("error"));
  formMessage.textContent = "";
  formMessage.className = "form-message";

  let valid = true;

  if (collector.value.trim() === "") {
    collector.classList.add("error");
    valid = false;
  }

  if (theme.value.trim() === "") {
    theme.classList.add("error");
    valid = false;
  }

  const today = new Date().toISOString().split("T")[0];
  startDate.max = today;

  if (startDate.value === "" || startDate.value > today) {
    startDate.classList.add("error");
    valid = false;
  }

  if (!valid) {
    formMessage.textContent = "⚠️ Please fill in all required (*) fields correctly.";
    formMessage.classList.add("error");
    return;
  }

  formMessage.textContent = "✅ Collection updated successfully!";
  formMessage.classList.add("success");
});


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


