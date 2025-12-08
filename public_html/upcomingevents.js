// =============================
// NOTIFICAÇÕES 
// =============================

document.addEventListener("DOMContentLoaded", () => {

  // -------------- POPUP DE NOTIFICAÇÕES --------------
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

  // -------------- EXPANDIR / ENCNCOLHER NOTIFICAÇÕES --------------
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

  // -------------- STATUS COLOR (GOING / INTERESTED) --------------
  document.querySelectorAll(".status-text").forEach((el) => {
    const status = el.textContent.trim().toLowerCase();

    if (status === "going") {
      el.classList.add("going");
    } else if (status === "interested") {
      el.classList.add("interested");
    }
  });

  // =============================
  // SORT DROPDOWN (Upcoming Events)
  // =============================
  const filterToggle = document.getElementById("filterToggle");
  const filterMenu = document.getElementById("filterMenu");

  if (filterToggle && filterMenu) {

    // Abrir/Fechar menu
    filterToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      filterMenu.classList.toggle("show");
    });

    // Clicar fora fecha o menu
    document.addEventListener("click", (e) => {
      if (!filterMenu.contains(e.target) && !filterToggle.contains(e.target)) {
        filterMenu.classList.remove("show");
      }
    });
  }

});
