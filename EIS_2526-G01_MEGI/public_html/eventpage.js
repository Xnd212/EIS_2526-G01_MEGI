// Notification Popup Handling

window.addEventListener("DOMContentLoaded", () => {
  const bellBtn = document.querySelector('.icon-btn[aria-label="Notificacoes"]');
  const popup = document.getElementById("notification-popup");

  if (bellBtn && popup) {
    bellBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      popup.style.display = popup.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", (e) => {
      if (!popup.contains(e.target) && !bellBtn.contains(e.target)) {
        popup.style.display = "none";
      }
    });
  }
});
