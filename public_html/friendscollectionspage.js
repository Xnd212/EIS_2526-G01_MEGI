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


// =============================
// Filtros 
// =============================

document.addEventListener('DOMContentLoaded', function () {
  const filterToggle = document.getElementById('filterToggle');
  const filterMenu = document.getElementById('filterMenu');
  const grid = document.getElementById('collectionGrid');

  if (!filterToggle || !filterMenu || !grid) return;

  const cards = Array.from(grid.querySelectorAll('.collection-card'));

  // abrir/fechar dropdown
  filterToggle.addEventListener('click', function (e) {
    e.stopPropagation();
    const isOpen = filterMenu.classList.toggle('show');
    filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  // fechar ao clicar fora
  document.addEventListener('click', function (e) {
    if (!filterMenu.contains(e.target) && !filterToggle.contains(e.target)) {
      filterMenu.classList.remove('show');
      filterToggle.setAttribute('aria-expanded', 'false');
    }
  });

  function sortCards(compareFn) {
    const sorted = [...cards].sort(compareFn);
    grid.innerHTML = '';
    sorted.forEach(card => grid.appendChild(card));
  }

  // clique nas opções do menu
  filterMenu.addEventListener('click', function (e) {
    const btn = e.target.closest('button[data-sort]');
    if (!btn) return;

    const sortType = btn.dataset.sort;

    switch (sortType) {
      case 'alpha-asc':
        sortCards((a, b) =>
          a.dataset.name.localeCompare(b.dataset.name)
        );
        break;

      case 'alpha-desc':
        sortCards((a, b) =>
          b.dataset.name.localeCompare(a.dataset.name)
        );
        break;

      case 'price-asc':
        sortCards((a, b) =>
          (parseFloat(a.dataset.price) || 0) - (parseFloat(b.dataset.price) || 0)
        );
        break;

      case 'price-desc':
        sortCards((a, b) =>
          (parseFloat(b.dataset.price) || 0) - (parseFloat(a.dataset.price) || 0)
        );
        break;

    }

    // fecha SEMPRE o menu depois de escolher
    filterMenu.classList.remove('show');
    filterToggle.setAttribute('aria-expanded', 'false');
  });
});
