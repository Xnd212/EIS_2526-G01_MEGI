document.addEventListener("DOMContentLoaded", () => {
  const popup = document.getElementById("hover-popup"); 

  
// Para cada card dos Items mostra o conteúdo do popup ao mover o rato sobre o card
  document.querySelectorAll(".item-card").forEach(block => {
    block.addEventListener("mousemove", e => {
      const id = block.getAttribute("data-id");
      const data = itemData[id];
      if (!data) return;

      // Gera o conteúdo do popup com base nos dados da coleção
      popup.innerHTML = `
  <div class="popup-content-flex">
            
    <!-- Texto à esquerda -->
    <div class="popup-text">
      <h3 class="popup-title">${data.item}</h3>
      <p><strong>Price:</strong> ${data.price}</p>
      <p><strong>Importance:</strong> ${data.importance}</p>
      <p><strong>Item Type:</strong> ${data.item_type}</p>
      <p><strong>Acquisition Date:</strong> ${data.date}</p>
      <p><strong>Acquisition Place:</strong> ${data.place}</p>
    </div>

    <!-- Imagem + nome do produto + data -->
    <div class="popup-image">
      <img src="${data.image}" alt="${data.item}">
    </div>
  </div>
`;


      // Posiciona o popup próximo ao cursor do rato
      popup.style.left = e.pageX + 20 + "px";
      popup.style.top = e.pageY - 225 + "px";
      popup.classList.add("active");
    });
      // Esconde o popup quando o rato sai do card
      block.addEventListener("mouseleave", () => {
      popup.classList.remove("active");
    });
  });
});

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

document.addEventListener('DOMContentLoaded', () => {
        const deleteBtn = document.getElementById('deleteCollectionBtn');
        const popup = document.getElementById('delete-collection-popup');
        const cancelBtn = document.getElementById('cancel-col-delete');
        const confirmBtn = document.getElementById('confirm-col-delete');
        const messageP = document.getElementById('delete-message');

        let collectionIdToDelete = null;

        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                collectionIdToDelete = this.getAttribute('data-col-id');
                
                // 1. Check for Exclusive Items via AJAX
                fetch(`delete_collection.php?action=check&id=${collectionIdToDelete}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exclusive_count > 0) {
                            messageP.innerHTML = `Warning: This collection contains <strong>${data.exclusive_count} item(s)</strong> that do not belong to any other collection.<br><br>If you delete this collection, <strong>those items will be permanently deleted</strong> as well.<br><br>Do you want to proceed?`;
                        } else {
                            messageP.innerText = "Are you sure you want to delete this collection? The items will remain in your inventory (if they belong to other collections) otherwise will be deleted as well.";
                        }
                        popup.style.display = 'flex';
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        alert("Error checking collection details.");
                    });
            });
        }

        cancelBtn.addEventListener('click', () => {
            popup.style.display = 'none';
        });

        confirmBtn.addEventListener('click', () => {
            if (!collectionIdToDelete) return;

            // 2. Perform Deletion
            const formData = new FormData();
            formData.append('collection_id', collectionIdToDelete);

            fetch('delete_collection.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "mycollectionspage.php";
                } else {
                    alert("Error deleting: " + data.message);
                }
            })
            .catch(err => console.error(err));
        });
    });