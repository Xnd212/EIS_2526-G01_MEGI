document.addEventListener('DOMContentLoaded', function() {

    // ======= Dropdown Filtro =======
    const toggle = document.getElementById('filterToggle');
    const menu = document.getElementById('filterMenu');

    if (toggle && menu) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!menu.contains(e.target) && !toggle.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
    }

    // ====== Notificações ======

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

    if (seeMoreLink) {
        seeMoreLink.addEventListener('click', (e) => {
            e.preventDefault();

            popup.classList.toggle('expanded');

            seeMoreLink.textContent =
                popup.classList.contains('expanded') ? "Show less" : "+ See more";
        });
    }

});
