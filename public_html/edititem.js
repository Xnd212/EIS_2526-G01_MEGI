document.addEventListener("DOMContentLoaded", () => {

  function normalize(str) {
    return str
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");
  }

  // ============================================================
  // COLLECTION DROPDOWN (CHECKBOXES) + SEARCH + LABEL UPDATE
  // ============================================================
  const colBtn = document.getElementById("collectionDropdownBtn");
  const colDropdown = document.getElementById("collectionDropdown");
  const colSearch = document.getElementById("collectionSearchInput");

  if (colBtn && colDropdown) {

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

    colDropdown.addEventListener("change", updateCollectionButtonText);
    updateCollectionButtonText();
  }

  function updateCollectionButtonText() {
    const checked = [
      ...colDropdown.querySelectorAll("input[type='checkbox']:checked")
    ].map(cb => cb.parentElement.textContent.trim());

    colBtn.textContent =
      checked.length > 0 ? checked.join(", ") : "Select Collections ⮟";
  }

  function filterCollections(query) {
    const q = normalize(query);
    colDropdown.querySelectorAll("label[data-collection-name]").forEach(label => {
      const name = normalize(label.dataset.collectionName);
      label.style.display = name.includes(q) ? "flex" : "none";
    });
  }

  if (colSearch) {
    colSearch.addEventListener("input", () => {
      filterCollections(colSearch.value.trim());
    });
  }

  // ============================================================
  // TYPE DROPDOWN + SEARCH
  // ============================================================
  const typeBtn = document.getElementById("typeDropdownBtn");
  const typeDropdown = document.getElementById("typeDropdown");
  const hiddenType = document.getElementById("itemType");
  const typeSearchInput = document.getElementById("typeSearchInput");

  if (typeBtn && typeDropdown && hiddenType) {

    typeBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      typeDropdown.style.display =
        typeDropdown.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", (e) => {
      if (!typeDropdown.contains(e.target) && e.target !== typeBtn) {
        typeDropdown.style.display = "none";
      }
    });

    typeDropdown.addEventListener("change", () => {
      const checked = typeDropdown.querySelector("input[type='radio']:checked");
      if (checked) {
        hiddenType.value = checked.value;
        typeBtn.textContent = checked.value;
      }
    });
  }

  function filterTypes(query) {
    const q = normalize(query);
    typeDropdown.querySelectorAll("label[data-type-name]").forEach(label => {
      const name = normalize(label.dataset.typeName);
      label.style.display = name.includes(q) ? "flex" : "none";
    });
  }

  if (typeSearchInput) {
    typeSearchInput.addEventListener("input", () => {
      filterTypes(typeSearchInput.value.trim());
    });
  }

  // ============================================================
  // CREATE TYPE MODAL (AJAX)
  // ============================================================
  const openTypeModal = document.getElementById("openTypeModal");
  const typeModalOverlay = document.getElementById("typeModalOverlay");
  const typeModal = document.getElementById("typeModal");
  const closeTypeModal = document.getElementById("closeTypeModal");
  const createTypeBtn = document.getElementById("createTypeBtn");
  const newTypeInput = document.getElementById("newTypeInput");
  const typeFeedback = document.getElementById("typeFeedback");

  if (openTypeModal) {
    openTypeModal.addEventListener("click", (e) => {
      e.preventDefault();
      typeModalOverlay.classList.remove("hidden");
      typeModal.classList.remove("hidden");
      newTypeInput.value = "";
      typeFeedback.textContent = "";
      typeFeedback.style.color = "";
    });
  }

  if (closeTypeModal) {
    closeTypeModal.addEventListener("click", closeTypeModalFn);
  }

  if (typeModalOverlay) {
    typeModalOverlay.addEventListener("click", closeTypeModalFn);
  }

  function closeTypeModalFn() {
    typeModalOverlay.classList.add("hidden");
    typeModal.classList.add("hidden");
  }

  if (createTypeBtn) {
    createTypeBtn.addEventListener("click", async () => {
      const typeName = newTypeInput.value.trim();
      typeFeedback.textContent = "";

      if (typeName === "") {
        typeFeedback.textContent = "⚠ Please write a name.";
        typeFeedback.style.color = "#b54242";
        return;
      }

      const formData = new FormData();
      formData.append("typeName", typeName);

      try {
        const res = await fetch("create_type.php", {
          method: "POST",
          body: formData
        });
        const data = await res.json();

        if (!data.success) {
          typeFeedback.textContent = "⚠ " + (data.error || "Error creating type.");
          typeFeedback.style.color = "#b54242";
          return;
        }

        let existing = typeDropdown.querySelector(
          `input[value="${CSS.escape(data.name)}"]`
        );

        if (!existing) {
          const label = document.createElement("label");
          label.dataset.typeName = data.name.toLowerCase();
          label.innerHTML = `
            <input type="radio" name="typeRadio" value="${data.name}" checked>
            ${data.name}
          `;
          typeDropdown.appendChild(label);
          existing = label.querySelector("input");
        }

        existing.checked = true;
        hiddenType.value = data.name;
        typeBtn.textContent = data.name;

        typeFeedback.textContent = data.existing
          ? "Type already existed — selected ✔"
          : "Type created ✔";
        typeFeedback.style.color = "#2e7d32";

        newTypeInput.value = "";

      } catch (err) {
        console.error(err);
        typeFeedback.textContent = "⚠ Connection error.";
        typeFeedback.style.color = "#b54242";
      }
    });
  }

  // ============================================================
  // IMPORTANCE SLIDER <-> NUMBER
  // ============================================================
  const slider = document.getElementById("importanceSlider");
  const number = document.getElementById("itemImportance");

  if (slider && number) {

    slider.addEventListener("input", () => {
      number.value = slider.value;
    });

    number.addEventListener("input", () => {
      let val = parseInt(number.value, 10);
      if (isNaN(val)) val = 1;
      if (val < 1) val = 1;
      if (val > 10) val = 10;
      number.value = val;
      slider.value = val;
    });
  }

  // ============================================================
  // SCROLL TO MESSAGE + REDIRECT
  // ============================================================
  const msg = document.getElementById("form-message");

  if (msg) {
    msg.scrollIntoView({ behavior: "smooth", block: "center" });
  }

  if (window.redirectAfterSuccess === true && window.itemId) {
    setTimeout(() => {
      window.location.href = `itempage.php?id=${window.itemId}`;
    }, 2000);
  }

});
