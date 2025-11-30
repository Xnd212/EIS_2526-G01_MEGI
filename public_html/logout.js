document.addEventListener("DOMContentLoaded", () => {
  const logoutBtn    = document.getElementById("logout-btn");
  const logoutPopup  = document.getElementById("logout-popup");
  const cancelLogout = document.getElementById("cancel-logout");
  const confirmLogout = document.getElementById("confirm-logout");
  
  if (!logoutBtn || !logoutPopup) {
    return;
  }
  
  let isTogglingLogout = false;
  
  // Abrir / fechar popup ao clicar no ícone 🚪
  logoutBtn.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    isTogglingLogout = true;
    
    // Close notification popup if open
    const notifPopup = document.getElementById('notification-popup');
    if (notifPopup) {
      notifPopup.classList.remove("active");
    }
    
    logoutPopup.classList.toggle("active");
    
    setTimeout(() => {
      isTogglingLogout = false;
    }, 100);
  });
  
  // Fechar ao clicar fora
  document.addEventListener("click", (e) => {
    if (isTogglingLogout) return;
    
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