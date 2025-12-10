document.addEventListener("DOMContentLoaded", () => {

  // ============================================
  // 1. IMPORTANCE SLIDER SYNCHRONIZATION
  // ============================================
  const slider = document.getElementById("itemImportanceSlider");
  const numberInput = document.getElementById("itemImportanceNumber");

  if (slider && numberInput) {
    slider.addEventListener("input", () => {
      numberInput.value = slider.value;
    });

    numberInput.addEventListener("input", () => {
      let val = parseInt(numberInput.value, 10);
      if (isNaN(val)) val = 5;
      if (val < 1) val = 1;
      if (val > 10) val = 10;
      slider.value = val;
      numberInput.value = val;
    });
  }

  // ============================================
  // 2. TYPE DROPDOWN
  // ============================================
  const typeBtn      = document.getElementById("typeDropdownBtn");
  const typeDropdown = document.getElementById("typeDropdown");
  const hiddenType   = document.getElementById("itemType");

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
        const labelText = checked.parentElement.textContent.trim();
        hiddenType.value = checked.value;
        typeBtn.textContent = labelText;
      }
    });
  }

  // ============================================
  // 3. MODAL PARA CRIAR TYPE (AJAX)
  // ============================================
  const openTypeModal   = document.getElementById("openTypeModal");
  const typeModalOverlay= document.getElementById("typeModalOverlay");
  const typeModal       = document.getElementById("typeModal");
  const closeTypeModal  = document.getElementById("closeTypeModal");
  const createTypeBtn   = document.getElementById("createTypeBtn");
  const newTypeInput    = document.getElementById("newTypeInput");
  const typeFeedback    = document.getElementById("typeFeedback");

  if (openTypeModal && typeModalOverlay && typeModal) {
    openTypeModal.addEventListener("click", (e) => {
      e.preventDefault();
      typeModalOverlay.classList.remove("hidden");
      typeModal.classList.remove("hidden");
      if (newTypeInput) newTypeInput.value = "";
      if (typeFeedback) {
        typeFeedback.textContent = "";
        typeFeedback.style.color = "";
      }
    });
  }

  if (closeTypeModal && typeModalOverlay && typeModal) {
    closeTypeModal.addEventListener("click", () => {
      typeModalOverlay.classList.add("hidden");
      typeModal.classList.add("hidden");
    });
  }

  if (typeModalOverlay && typeModal) {
    typeModalOverlay.addEventListener("click", () => {
      typeModalOverlay.classList.add("hidden");
      typeModal.classList.add("hidden");
    });
  }

  if (createTypeBtn && newTypeInput && typeDropdown && typeBtn && hiddenType) {
    createTypeBtn.addEventListener("click", async () => {
      const typeName = newTypeInput.value.trim();
      typeFeedback.textContent = "";

      if (typeName === "") {
        typeFeedback.textContent = "⚠ Please write a name.";
        typeFeedback.style.color = "#b54242";
        return;
      }

      let formData = new FormData();
      formData.append("typeName", typeName);

      try {
        const response = await fetch("create_type.php", {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (!result.success) {
          typeFeedback.textContent = "⚠ " + (result.error || "Error creating type.");
          typeFeedback.style.color = "#b54242";
          return;
        }

        // Se já existia: apenas selecionar
        if (result.existing) {
          let existing = typeDropdown.querySelector(
            `input[value='${result.name.replace(/'/g, "\\'")}']`
          );

          if (!existing) {
            // Pode não existir ainda no dropdown → criar
            let label = document.createElement("label");
            label.innerHTML = `
              <input type="radio" name="typeRadio" value="${result.name}">
              ${result.name}
            `;
            typeDropdown.appendChild(label);
            existing = label.querySelector("input");
          }

          existing.checked = true;
          hiddenType.value = result.name;
          typeBtn.textContent = result.name;

          typeFeedback.textContent = "Type already existed — selected ✔";
          typeFeedback.style.color = "#2e7d32";
        } else {
          // Criar nova radio no dropdown
          let label = document.createElement("label");
          label.innerHTML = `
            <input type="radio" name="typeRadio" value="${result.name}" checked>
            ${result.name}
          `;
          typeDropdown.appendChild(label);

          hiddenType.value = result.name;
          typeBtn.textContent = result.name;

          typeFeedback.textContent = "Type created ✔";
          typeFeedback.style.color = "#2e7d32";
        }

        newTypeInput.value = "";
      } catch (err) {
        console.error(err);
        typeFeedback.textContent = "⚠ Connection error.";
        typeFeedback.style.color = "#b54242";
      }
    });
  }

  // ============================================
  // 4. NOTIFICATIONS & LOGOUT
  // ============================================
  const bellBtn = document.getElementById("notification-btn");
  const notifPopup = document.getElementById("notification-popup");
  const logoutBtn = document.getElementById("logout-btn");
  const logoutPopup = document.getElementById("logout-popup");
  const cancelLogout = document.getElementById("cancel-logout");
  const confirmLogout = document.getElementById("confirm-logout");

  if (bellBtn && notifPopup) {
    bellBtn.addEventListener("click", (e) => {
      e.preventDefault(); e.stopPropagation();
      if (logoutPopup) logoutPopup.style.display = "none";
      notifPopup.style.display =
        notifPopup.style.display === "block" ? "none" : "block";
    });
  }

  if (logoutBtn && logoutPopup) {
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault(); e.stopPropagation();
      if (notifPopup) notifPopup.style.display = "none";
      logoutPopup.classList.add("active");
      logoutPopup.style.display = "block";
    });
  }

  document.addEventListener("click", (e) => {
    if (notifPopup && !notifPopup.contains(e.target) && bellBtn && !bellBtn.contains(e.target)) {
      notifPopup.style.display = "none";
    }
    if (logoutPopup && !logoutPopup.contains(e.target) && logoutBtn && !logoutBtn.contains(e.target)) {
      logoutPopup.style.display = "none";
      logoutPopup.classList.remove("active");
    }
  });

  if (cancelLogout && logoutPopup) {
    cancelLogout.addEventListener("click", () => {
      logoutPopup.style.display = "none";
      logoutPopup.classList.remove("active");
    });
  }

  if (confirmLogout) {
    confirmLogout.addEventListener("click", () => {
      window.location.href = "logout.php";
    });
  }

  // ============================================
  // 5. CSV IMPORT POPUP
  // ============================================
  const bulkImportBtn = document.getElementById("bulk-import-btn");
  const csvPopup = document.getElementById("csv-import-popup");
  const closePopupBtn = document.getElementById("close-csv-popup");
  const overlay = document.getElementById("csv-overlay");

  if (bulkImportBtn && csvPopup && overlay) {
    bulkImportBtn.addEventListener("click", function (e) {
      e.preventDefault();
      csvPopup.style.display = "block";
      overlay.classList.add("active");
      document.body.style.overflow = "hidden";
    });

    if (closePopupBtn) {
      closePopupBtn.addEventListener("click", function () {
        csvPopup.style.display = "none";
        overlay.classList.remove("active");
        document.body.style.overflow = "";
      });
    }

    overlay.addEventListener("click", function () {
      csvPopup.style.display = "none";
      overlay.classList.remove("active");
      document.body.style.overflow = "";
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && csvPopup.style.display === "block") {
        csvPopup.style.display = "none";
        overlay.classList.remove("active");
        document.body.style.overflow = "";
      }
    });
  }

  // ============================================
  // 6. SCROLL PARA MENSAGEM + REDIRECT (SUCESSO)
  // ============================================
  const successMessage = document.querySelector(".form-message.success");
  const errorMessage   = document.querySelector(".form-message.error");

  if (successMessage) {
    successMessage.scrollIntoView({ behavior: "smooth", block: "center" });

    if (window.NEW_ITEM_ID) {
      setTimeout(() => {
        window.location.href = `itempage.php?id=${window.NEW_ITEM_ID}`;
      }, 2000);
    }
  } else if (errorMessage) {
    errorMessage.scrollIntoView({ behavior: "smooth", block: "center" });
  }
});
