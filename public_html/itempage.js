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



document.addEventListener("DOMContentLoaded", () => {
  const editBtn = document.getElementById("editBtn");

  if (editBtn) {
    editBtn.addEventListener("click", () => {
      const itemData = {
        price: document.getElementById("itemPrice").textContent,
        itemType: document.getElementById("itemType").textContent,
        importance: document.getElementById("itemImportance").textContent,
        acquisitionDate: document.getElementById("itemAcquisitionDate").textContent,
        acquisitionPlace: document.getElementById("itemAcquisitionPlace").textContent,
        description: document.getElementById("itemDescription").textContent
      };

      localStorage.setItem("editItemData", JSON.stringify(itemData));
      window.location.href = "edititem.php";
    });
  }
});

