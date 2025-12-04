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
  
  // ===========================
  // 1. SCROLL PARA A MENSAGEM
  // ===========================
  const msg = document.getElementById("form-message");
  if (msg) {
      msg.scrollIntoView({
          behavior: "smooth",
          block: "center"
      });

      // opcional: efeito visual
      msg.style.transition = "opacity .4s ease";
      msg.style.opacity = "1";
    }
  
  if (window.redirectAfterSuccess === true) {
        setTimeout(() => {
            window.location.href = `collectionpage.php?id=${window.collectionId}`;
        }, 2000);
    }
  
});



