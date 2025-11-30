
document.addEventListener("DOMContentLoaded", () => {

  /* ============================================================
     POPUP AO PASSAR O RATO NAS TOP COLLECTIONS
  ============================================================ */
  const hoverPopup = document.getElementById("hover-popup");

  const collectionData = {
    "price-card": {
      collection: 'Pokemon Cards',
      user: 'Rafael_Ameida123',
      title: '1st Edition Machamp',
      price: '2000€',
      date: '03/10/2025',
      place: 'Comic Con 2025',
      items: '51',
      image: 'images/1st Edition Machamp.png',
      updated: '27/03/2025'
    },
    "recent-card": {
      collection: "Pokemon Champion's Path",
      user: 'Paul_Perez1697',
      title: 'Charizard V',
      price: '300,25€',
      date: '29/10/2025',
      place: 'Local vintage store',
      items: '14',
      image: 'images/CharizardV.png',
      updated: '31/10/2025'
    },
    "items-card": {
      collection: 'Funko Pop',
      user: 'Ana_SSilva7812',
      title: 'Friends: Joey w/ Pizza',
      price: '20€',
      date: '17/05/2025',
      place: 'Online shop',
      items: '152',
      image: 'images/joeyfunko.png',
      updated: '01/10/2025'
    }
  };

  document.querySelectorAll(".top-collection-block").forEach(block => {
    block.addEventListener("mousemove", e => {
      const id = block.getAttribute("data-id");
      const data = collectionData[id];
      if (!data || !hoverPopup) return;

      hoverPopup.innerHTML = `
        <div class="popup-content-flex">
          <div class="popup-text">
            <h3 class="popup-title">${data.collection}</h3>
            <p class="popup-user">${data.user}</p>
            <p><strong>Price:</strong> ${data.price}</p>
            <p><strong>Acquisition Date:</strong> ${data.date}</p>
            <p><strong>Acquisition Place:</strong> ${data.place}</p>
            <p><strong>Items:</strong> ${data.items}</p>
          </div>
          <div class="popup-image">
            <h4 class="popup-subtitle">${data.title}</h4>
            <img src="${data.image}" alt="${data.title}">
            <p class="popup-date">Last updated: ${data.updated}</p>
          </div>
        </div>
      `;

      hoverPopup.style.left = e.pageX + 20 + "px";
      hoverPopup.style.top = e.pageY + 20 + "px";
      hoverPopup.classList.add("active");
    });

    block.addEventListener("mouseleave", () => {
      if (hoverPopup) hoverPopup.classList.remove("active");
    });
  });


  /* ============================================================
     ELEMENTOS PARTILHADOS
  ============================================================ */
  const bellBtn       = document.querySelector('.icon-btn[aria-label="Notificações"]');
  const notifPopup    = document.getElementById('notification-popup');
  const logoutBtn     = document.getElementById("logout-btn");
  const logoutPopup   = document.getElementById("logout-popup");
  const cancelLogout  = document.getElementById("cancel-logout");
  const confirmLogout = document.getElementById("confirm-logout");
  const seeMoreLink   = notifPopup ? notifPopup.querySelector('.see-more-link') : null;


  /* ============================================================
     DROPDOWN DE NOTIFICAÇÕES
  ============================================================ */
  if (bellBtn && notifPopup) {
    bellBtn.addEventListener("click", e => {
      e.stopPropagation();

      // Fecha popup de logout se estiver aberto
      if (logoutPopup) {
        logoutPopup.classList.remove("active");
      }

      notifPopup.classList.toggle("active");
    });

    // Fecha notificações ao clicar fora
    document.addEventListener("click", e => {
      if (!notifPopup.contains(e.target) && !bellBtn.contains(e.target)) {
        notifPopup.classList.remove("active");
      }
    });
  }

  // Expandir lista de notificações
  if (seeMoreLink && notifPopup) {
    seeMoreLink.addEventListener("click", e => {
      e.preventDefault();
      notifPopup.classList.toggle("expanded");
      seeMoreLink.textContent = notifPopup.classList.contains("expanded")
        ? "Show less"
        : "+ See more";
    });
  }



