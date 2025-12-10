document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('filterToggle');
    const menu = document.getElementById('filterMenu');

    if (toggle && menu) {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target) && !toggle.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
    }
});