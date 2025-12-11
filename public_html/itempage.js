document.addEventListener("DOMContentLoaded", () => {
  // =============================
  // Notificações 
  // =============================
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

    // =============================
// Popup de DELETE do Item
// =============================
    const deleteBtn = document.getElementById("deleteItemBtn");
    const deletePopup = document.getElementById("delete-item-popup");
    const cancelDeleteBtn = document.getElementById("cancel-delete");
    const confirmDeleteBtn = document.getElementById("confirm-delete");

    let itemIdToDelete = null; // inicializado vazio

    if (deleteBtn && deletePopup && cancelDeleteBtn && confirmDeleteBtn) {

        // abrir popup e DEFINIR o ID aqui!
        deleteBtn.addEventListener("click", (e) => {
            e.preventDefault();
            itemIdToDelete = deleteBtn.getAttribute("data-item-id");
            deletePopup.style.display = "flex";
        });

        cancelDeleteBtn.addEventListener("click", () => {
            deletePopup.style.display = "none";
        });

        deletePopup.addEventListener("click", (e) => {
            if (e.target === deletePopup) {
                deletePopup.style.display = "none";
            }
        });

        confirmDeleteBtn.addEventListener("click", () => {
            if (!itemIdToDelete) {
                alert("Invalid item ID.");
                return;
            }

            const formData = new FormData();
            formData.append("item_id", itemIdToDelete);

            fetch("deleteitem.php", {
                method: "POST",
                body: formData,
            })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            window.location.href = "myitems.php";
                        } else {
                            alert("Error deleting item: " + (data.message || ""));
                        }
                    })
                    .catch((err) => {
                        console.error(err);
                        alert("Error deleting item.");
                    });
        });
    }
});
