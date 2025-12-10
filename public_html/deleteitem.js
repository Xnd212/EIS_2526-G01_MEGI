// deleteitem.js - Handle item deletion with confirmation

document.addEventListener("DOMContentLoaded", function () {
  console.log("deleteitem.js loaded"); // DEBUG

  const deleteBtn        = document.getElementById("deleteItemBtn");
  const deletePopup      = document.getElementById("delete-popup");
  const cancelDeleteBtn  = document.getElementById("cancel-delete");
  const confirmDeleteBtn = document.getElementById("confirm-delete");

  if (!deleteBtn) {
    console.log("deleteItemBtn not found"); // DEBUG
  }
  if (!deletePopup) {
    console.log("delete-popup not found"); // DEBUG
  }

  // Se não houver elementos (por ex. não és o dono), não faz nada
  if (!deleteBtn || !deletePopup || !cancelDeleteBtn || !confirmDeleteBtn) {
    return;
  }

  let itemIdToDelete = null;

  // Abrir popup
  deleteBtn.addEventListener("click", function (e) {
    e.preventDefault();               // só por segurança
    alert("clicked delete");          // DEBUG -> deves ver isto
    itemIdToDelete = this.getAttribute("data-item-id");
    console.log("Item to delete:", itemIdToDelete); // DEBUG
    deletePopup.style.display = "flex";   // overlay em flex
  });

  // Cancelar
  cancelDeleteBtn.addEventListener("click", function () {
    deletePopup.style.display = "none";
    itemIdToDelete = null;
  });

  // Fechar ao clicar fora da caixinha branca
  deletePopup.addEventListener("click", function (e) {
    if (e.target === deletePopup) {
      deletePopup.style.display = "none";
      itemIdToDelete = null;
    }
  });

  // Confirmar delete
  confirmDeleteBtn.addEventListener("click", function () {
    if (!itemIdToDelete) return;

    confirmDeleteBtn.disabled = true;
    confirmDeleteBtn.textContent = "Deleting...";

    fetch("delete_item.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "item_id=" + encodeURIComponent(itemIdToDelete),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          window.location.href = "myitems.php";
        } else {
          alert("Error deleting item: " + (data.message || "Unknown error"));
          confirmDeleteBtn.disabled = false;
          confirmDeleteBtn.textContent = "Delete";
          deletePopup.style.display = "none";
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred while deleting the item.");
        confirmDeleteBtn.disabled = false;
        confirmDeleteBtn.textContent = "Delete";
        deletePopup.style.display = "none";
      });
  });
});
