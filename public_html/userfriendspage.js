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
  
    /* ============================================================
     POPUP DE LOGOUT
  ============================================================ */
  if (logoutBtn && logoutPopup) {
    // Abrir/fechar popup ao clicar no ícone 🚪
    logoutBtn.addEventListener("click", e => {
      e.preventDefault();
      e.stopPropagation();

      // Fecha popup de notificações, se estiver aberto
      if (notifPopup) {
        notifPopup.classList.remove("active");
      }

      logoutPopup.classList.toggle("active");
    });

    // Fechar ao clicar fora
    document.addEventListener("click", e => {
      if (!logoutPopup.contains(e.target) && !logoutBtn.contains(e.target)) {
        logoutPopup.classList.remove("active");
      }
    });
  }

  // Botão "Cancel"
  if (cancelLogout && logoutPopup) {
    cancelLogout.addEventListener("click", e => {
      e.stopPropagation();
      logoutPopup.classList.remove("active");
    });
  }

  // Botão "Log out" 
  if (confirmLogout && logoutPopup) {
    confirmLogout.addEventListener("click", e => {
      e.stopPropagation();
      window.location.href = "logout.php"; 
    });
  }
});



