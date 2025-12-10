document.addEventListener("DOMContentLoaded", () => {
  // =================================================
  // 1. POPUP DE HOVER DOS ITENS
  // =================================================
  const hoverPopup = document.getElementById("hover-popup");

  if (hoverPopup) {
    document.querySelectorAll(".item-card").forEach((block) => {
      const id = block.getAttribute("data-id");

      block.addEventListener("mousemove", (e) => {
        if (!window.itemData) return;
        const data = itemData[id];
        if (!data) return;

        hoverPopup.innerHTML = `
          <div class="popup-content-flex">
            <div class="popup-text">
              <h3 class="popup-title">${data.item}</h3>
              <p><strong>Price:</strong> ${data.price}</p>
              <p><strong>Importance:</strong> ${data.importance}</p>
              <p><strong>Item Type:</strong> ${data.item_type}</p>
              <p><strong>Acquisition Date:</strong> ${data.date}</p>
              <p><strong>Acquisition Place:</strong> ${data.place}</p>
            </div>
            <div class="popup-image">
              <img src="${data.image}" alt="${data.item}">
            </div>
          </div>
        `;

        hoverPopup.style.left = e.pageX + 20 + "px";
        hoverPopup.style.top = e.pageY - 225 + "px";
        hoverPopup.classList.add("active");
      });

      block.addEventListener("mouseleave", () => {
        hoverPopup.classList.remove("active");
      });
    });
  }

  // =================================================
  // 2. NOTIFICAÇÕES
  // =================================================
  const bellBtn = document.querySelector('.icon-btn[aria-label="Notificações"]');
  const notifPopup = document.getElementById("notification-popup");
  const seeMoreLink = document.querySelector(".see-more-link");

  if (bellBtn && notifPopup) {
    bellBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      notifPopup.style.display =
        notifPopup.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", (e) => {
      if (!notifPopup.contains(e.target) && !bellBtn.contains(e.target)) {
        notifPopup.style.display = "none";
      }
    });
  }

  if (seeMoreLink && notifPopup) {
    seeMoreLink.addEventListener("click", (e) => {
      e.preventDefault();

      notifPopup.classList.toggle("expanded");

      if (notifPopup.classList.contains("expanded")) {
        seeMoreLink.textContent = "Show less";
      } else {
        seeMoreLink.textContent = "+ See more";
      }
    });
  }

  // =================================================
  // 3. POPUP DE DELETE DA COLEÇÃO
  // =================================================
  const deleteBtn = document.getElementById("deleteCollectionBtn");
  const deletePopup = document.getElementById("delete-collection-popup");
  const cancelDeleteBtn = document.getElementById("cancel-col-delete");
  const confirmDeleteBtn = document.getElementById("confirm-col-delete");
  const messageP = document.getElementById("delete-message");

  let collectionIdToDelete = null;

  if (deleteBtn && deletePopup && cancelDeleteBtn && confirmDeleteBtn) {
    deleteBtn.addEventListener("click", (e) => {
      e.preventDefault();
      collectionIdToDelete = deleteBtn.getAttribute("data-col-id");

      fetch(`delete_collection.php?action=check&id=${collectionIdToDelete}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.exclusive_count > 0) {
            messageP.innerHTML = `
              Warning: This collection contains <strong>${data.exclusive_count} item(s)</strong> 
              that do not belong to any other collection.<br><br>
              If you delete this collection, <strong>those items will be permanently deleted</strong> as well.<br><br>
              Do you want to proceed?
            `;
          } else {
            messageP.innerText =
              "Are you sure you want to delete this collection? The items will remain in your inventory (if they belong to other collections) otherwise will be deleted as well.";
          }

          deletePopup.style.display = "flex";
        })
        .catch((err) => {
          console.error("Error:", err);
          alert("Error checking collection details.");
        });
    });

    cancelDeleteBtn.addEventListener("click", () => {
      deletePopup.style.display = "none";
    });

    // Fechar ao clicar fora da caixa
    deletePopup.addEventListener("click", (e) => {
      if (e.target === deletePopup) {
        deletePopup.style.display = "none";
      }
    });

    confirmDeleteBtn.addEventListener("click", () => {
      if (!collectionIdToDelete) return;

      const formData = new FormData();
      formData.append("collection_id", collectionIdToDelete);

      fetch("delete_collection.php?action=delete", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            window.location.href = "mycollectionspage.php";
          } else {
            alert("Error deleting: " + data.message);
          }
        })
        .catch((err) => console.error(err));
    });
  }
});
