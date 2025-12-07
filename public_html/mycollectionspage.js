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
// Filtros (UI Only - Dropdown Toggle)
// =============================

        document.addEventListener('DOMContentLoaded', function () {
        const filterToggle = document.getElementById('filterToggle');
                const filterMenu = document.getElementById('filterMenu');
                // Safety check: if button or menu don't exist, stop.
                if (!filterToggle || !filterMenu) {
        console.warn("Filter button or menu not found in HTML");
                return;
        }

        // 1. Toggle Menu on Click
        filterToggle.addEventListener('click', function (e) {
        e.stopPropagation(); // Stop the click from closing the menu immediately

                // Toggle the CSS class 'show' defined in your CSS
                const isOpen = filterMenu.classList.toggle('show');
                // Accessibility attribute
                filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
                // 2. Close Menu when clicking outside
                document.addEventListener('click', function (e) {
                // If click is NOT inside the menu AND NOT on the button...
                if (!filterMenu.contains(e.target) && !filterToggle.contains(e.target)) {
                filterMenu.classList.remove('show');
                        filterToggle.setAttribute('aria-expanded', 'false');
                }
                });
                });
        // fecha SEMPRE o menu depois de escolher
        filterMenu.classList.remove('show');
        filterToggle.setAttribute('aria-expanded', 'false');

        

