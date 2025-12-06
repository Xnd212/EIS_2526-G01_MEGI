document.addEventListener("DOMContentLoaded", () => {
  /* ============================================================
     POPUP AO PASSAR O RATO NAS TOP COLLECTIONS (DINÂMICO)
  ============================================================ */
  const hoverPopup = document.getElementById("hover-popup");

  document.querySelectorAll(".top-collection-block").forEach((block) => {
    block.addEventListener("mousemove", (e) => {
      if (!hoverPopup) return;

      const d = block.dataset; // atalho

      const collectionName       = d.collectionName        || "";
      const collectionUser       = d.collectionUser        || "";
      const collectionValue      = d.collectionValue       || "";
      const collectionItems      = d.collectionItems       || "";
      const collectionLastupdate = d.collectionLastupdated || "";

      const itemTitle = d.itemTitle  || "";
      const itemPrice = d.itemPrice  || collectionValue;
      const itemDate  = d.itemDate   || "";
      const itemPlace = d.itemPlace  || "";
      const itemImage = d.itemImage  || "images/default_item.png";

      hoverPopup.innerHTML = `
        <div class="popup-content-flex">
          <div class="popup-text">
            <h3 class="popup-title">${collectionName}</h3>
            <p class="popup-user">${collectionUser}</p>
            <p><strong>Price:</strong> ${itemPrice}</p>
            <p><strong>Acquisition Date:</strong> ${itemDate}</p>
            <p><strong>Acquisition Place:</strong> ${itemPlace}</p>
            <p><strong>Items:</strong> ${collectionItems}</p>
          </div>
          <div class="popup-image">
            <h4 class="popup-subtitle">${itemTitle}</h4>
            <img src="${itemImage}" alt="${itemTitle}">
            <p class="popup-date">Last updated: ${collectionLastupdate}</p>
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
     ELEMENTOS PARTILHADOS (NOTIFICAÇÕES + LOGOUT)
  ============================================================ */
  const bellBtn       = document.querySelector('.icon-btn[aria-label="Notificações"]');
  const notifPopup    = document.getElementById("notification-popup");
  const logoutBtn     = document.getElementById("logout-btn");
  const logoutPopup   = document.getElementById("logout-popup");
  const cancelLogout  = document.getElementById("cancel-logout");
  const confirmLogout = document.getElementById("confirm-logout");
  const seeMoreLink   = notifPopup ? notifPopup.querySelector(".see-more-link") : null;

  /* =================== DROPDOWN DE NOTIFICAÇÕES =================== */
  if (bellBtn && notifPopup) {
    bellBtn.addEventListener("click", (e) => {
      e.stopPropagation();

      // Fecha popup de logout se estiver aberto
      if (logoutPopup) {
        logoutPopup.classList.remove("active");
      }

      notifPopup.classList.toggle("active");
    });

    document.addEventListener("click", (e) => {
      if (!notifPopup.contains(e.target) && !bellBtn.contains(e.target)) {
        notifPopup.classList.remove("active");
      }
    });
  }

  if (seeMoreLink && notifPopup) {
    seeMoreLink.addEventListener("click", (e) => {
      e.preventDefault();
      notifPopup.classList.toggle("expanded");
      seeMoreLink.textContent = notifPopup.classList.contains("expanded")
        ? "Show less"
        : "+ See more";
    });
  }

  
  
  const topList = document.getElementById("top-collectors-list");

  if (topList) {
    const currentUserId = topList.dataset.currentUserId;

    topList.querySelectorAll("li").forEach(li => {
      li.style.cursor = "pointer";

      li.addEventListener("click", () => {
        const userId = li.dataset.userId;
        if (!userId) return;

        if (userId === currentUserId) {
          // se clicar no próprio → vai para a userpage normal
          window.location.href = "userpage.php";
        } else {
          // se for outro → vai para a friendpage
          window.location.href = "friendpage.php?user_id=" + userId;
        }
      });
    });
  }
});
