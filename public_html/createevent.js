// =============================
// EVENT FORM HANDLING
// =============================
document.addEventListener("DOMContentLoaded", () => {

  const form = document.getElementById("eventForm");
  const summarySection = document.getElementById("eventSummarySection");
  const summaryContent = document.getElementById("eventSummaryContent");
  const editBtn = document.getElementById("editEvent");
  const finalConfirmBtn = document.getElementById("finalEventConfirm");

  const organizer = "Alex.Mendes147";

  if (!form) return;

  // Only prevent default if showing preview, otherwise let PHP handle it
  form.addEventListener("submit", (e) => {
    // Campos
    const name = document.getElementById("eventName").value.trim();
    const date = document.getElementById("startDate").value.trim();
    const theme = document.getElementById("theme").value.trim();
    const location = document.getElementById("location").value.trim();
    const description = document.getElementById("description").value.trim();
    const tags = document.getElementById("tags").value.trim();
    const imageFile = document.getElementById("coverImage").files[0];

    if (!name || !date || !theme || !location || !description || !tags || !imageFile) {
      e.preventDefault();
      alert("Please fill all required fields.");
      return;
    }

    // Let the form submit normally to PHP - remove e.preventDefault()
    // The form will now POST to the server and create the event
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
  
    // ============================================================
    // COLLECTION DROPDOWN + SEARCH (CREATE EVENT)
    // ============================================================
    const colBtn = document.getElementById("collectionDropdownBtn");
    const colDropdown = document.getElementById("collectionDropdown");
    const colSearch = document.getElementById("collectionSearchInput");
    const colHidden = document.getElementById("collection_id");

    function normalize(str) {
        return str
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");
    }

    if (colBtn && colDropdown && colHidden) {

        colBtn.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();

            colDropdown.style.display =
                    colDropdown.style.display === "block" ? "none" : "block";

            if (colSearch) {
                colSearch.value = "";
                colSearch.focus();
                filterCollections("");
            }
        });

        document.addEventListener("click", (e) => {
            if (!colDropdown.contains(e.target) && e.target !== colBtn) {
                colDropdown.style.display = "none";
            }
        });

        colDropdown.addEventListener("change", () => {
            const checked = colDropdown.querySelector("input[type='radio']:checked");
            if (checked) {
                colHidden.value = checked.value;
                colBtn.textContent = checked.parentElement.textContent.trim();
                colDropdown.style.display = "none";
            }
        });
    }

    function filterCollections(query) {
        const q = normalize(query);
        colDropdown
                .querySelectorAll("label[data-collection-name]")
                .forEach(label => {
                    const name = normalize(label.dataset.collectionName);
                    label.style.display = name.includes(q) ? "flex" : "none";
                });
    }

    if (colSearch) {
        colSearch.addEventListener("input", () => {
            filterCollections(colSearch.value.trim());
        });
    }

});