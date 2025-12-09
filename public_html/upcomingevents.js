document.addEventListener("DOMContentLoaded", () => {

    // SORT DROPDOWN
    const filterToggle = document.getElementById("filterToggle");
    const filterMenu = document.getElementById("filterMenu");

    if (filterToggle && filterMenu) {
        filterToggle.addEventListener("click", (e) => {
            e.stopPropagation();
            filterMenu.classList.toggle("show");
        });

        document.addEventListener("click", (e) => {
            if (!filterMenu.contains(e.target) && !filterToggle.contains(e.target)) {
                filterMenu.classList.remove("show");
            }
        });
    }

    // NOTIFICAÇÕES
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
