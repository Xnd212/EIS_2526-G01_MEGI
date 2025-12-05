document.addEventListener("DOMContentLoaded", () => {

  /* ============================================================
     POPUP AO PASSAR O RATO NAS TOP COLLECTIONS (DINÂMICO)
  ============================================================ */
  const hoverPopup = document.getElementById("hover-popup");

  document.querySelectorAll(".top-collection-block").forEach(block => {
    block.addEventListener("mousemove", e => {
      if (!hoverPopup) return;

      // Lê os data-* do bloco
      const d = block.dataset;

      const collection = d.collection || "";
      const user       = d.user || "";
      const title      = d.title || "";
      const price      = d.price || "";
      const date       = d.date || "";
      const place      = d.place || "";
      const items      = d.items || "";
      const image      = d.image || "";
      const updated    = d.updated || "";

      hoverPopup.innerHTML = `
        <div class="popup-content-flex">
          <div class="popup-text">
            <h3 class="popup-title">${collection}</h3>
            <p class="popup-user">${user}</p>
            ${price   ? `<p><strong>Price:</strong> ${price}</p>` : ""}
            ${date    ? `<p><strong>Acquisition Date:</strong> ${date}</p>` : ""}
            ${place   ? `<p><strong>Acquisition Place:</strong> ${place}</p>` : ""}
            ${items   ? `<p><strong>Items:</strong> ${items}</p>` : ""}
          </div>
          <div class="popup-image">
            ${title ? `<h4 class="popup-subtitle">${title}</h4>` : ""}
            ${image ? `<img src="${image}" alt="${title || collection}">` : ""}
            ${updated ? `<p class="popup-date">Last updated: ${updated}</p>` : ""}
          </div>
        </div>
      `;

      hoverPopup.style.left = e.pageX + 20 + "px";
      hoverPopup.style.top  = e.pageY + 20 + "px";
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

});
