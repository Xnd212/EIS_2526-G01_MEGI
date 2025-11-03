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
});

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".status-text").forEach((el) => {
    const status = el.textContent.trim().toLowerCase();

    if (status === "going") {
      el.classList.add("going");
    } else if (status === "interested") {
      el.classList.add("interested");
    }
  });
});