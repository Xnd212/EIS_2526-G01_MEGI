document.addEventListener("DOMContentLoaded", () => {
  const logoutBtn    = document.getElementById("logout-btn");
  const logoutPopup  = document.getElementById("logout-popup");
  const cancelLogout = document.getElementById("cancel-logout");
  const confirmLogout = document.getElementById("confirm-logout");

  if (!logoutBtn || !logoutPopup) {
    // Nesta página não há botão ou popup de logout → não faz nada
    return;
  }

  // Abrir / fechar popup ao clicar no ícone 🚪
  logoutBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    logoutPopup.classList.toggle("active");
  });

  // Fechar ao clicar fora
  document.addEventListener("click", (e) => {
    if (!logoutPopup.contains(e.target) && !logoutBtn.contains(e.target)) {
      logoutPopup.classList.remove("active");
    }
  });

  // Botão Cancel → só fecha
  if (cancelLogout) {
    cancelLogout.addEventListener("click", (e) => {
      e.stopPropagation();
      logoutPopup.classList.remove("active");
    });
  }

  // Botão Log out → vai para logout.php
  if (confirmLogout) {
    confirmLogout.addEventListener("click", (e) => {
      e.stopPropagation();
      window.location.href = "logout.php";
    });
  }
});
