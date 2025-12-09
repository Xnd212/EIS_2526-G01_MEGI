document.addEventListener("DOMContentLoaded", () => {
  // ============================================================
  // 1. MULTISELECT: ITENS EXISTENTES
  // ============================================================
  const itemsBtn = document.getElementById("itemsDropdownBtn");
  const itemsContent = document.getElementById("itemsDropdownContent");

  if (itemsBtn && itemsContent) {
    itemsBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      itemsContent.style.display =
        itemsContent.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", (e) => {
      if (!itemsContent.contains(e.target) && e.target !== itemsBtn) {
        itemsContent.style.display = "none";
      }
    });

    itemsContent.addEventListener("change", () => {
      const checked = [
        ...itemsContent.querySelectorAll("input[type='checkbox']:checked"),
      ].map((c) => c.parentElement.textContent.trim());

      itemsBtn.textContent =
        checked.length > 0
          ? checked.join(", ")
          : "Select from existing items ⮟";
    });
  }

  // ============================================================
  // 1.b MULTISELECT: TAGS
  // ============================================================
  const tagBtn = document.getElementById("dropdownBtn");
  const tagDropdown = document.getElementById("tagDropdown");

  if (tagBtn && tagDropdown) {
    tagBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      tagDropdown.style.display =
        tagDropdown.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", (e) => {
      if (!tagDropdown.contains(e.target) && e.target !== tagBtn) {
        tagDropdown.style.display = "none";
      }
    });

    tagDropdown.addEventListener("change", () => {
      const checked = [
        ...tagDropdown.querySelectorAll("input[type='checkbox']:checked"),
      ].map((c) => c.parentElement.textContent.trim());

      tagBtn.textContent =
        checked.length > 0 ? checked.join(", ") : "Select Tags ⮟";
    });
  }

  // ============================================================
  // 2. MODAL PARA CRIAR TAGS (AJAX)
  // ============================================================
  const openTagModal = document.getElementById("openTagModal");
  const tagModalOverlay = document.getElementById("tagModalOverlay");
  const tagModal = document.getElementById("tagModal");
  const closeTagModal = document.getElementById("closeTagModal");
  const createTagBtn = document.getElementById("createTagBtn");
  const newTagInput = document.getElementById("newTagInput");
  const tagFeedback = document.getElementById("tagFeedback");

  // Abrir modal
  if (openTagModal && tagModalOverlay && tagModal) {
    openTagModal.addEventListener("click", (e) => {
      e.preventDefault();
      tagModalOverlay.classList.remove("hidden");
      tagModal.classList.remove("hidden");
      newTagInput.value = "";
      tagFeedback.textContent = "";
      tagFeedback.style.color = "";
    });
  }

  // Fechar modal (botão)
  if (closeTagModal && tagModalOverlay && tagModal) {
    closeTagModal.addEventListener("click", () => {
      tagModalOverlay.classList.add("hidden");
      tagModal.classList.add("hidden");
    });
  }

  // Fechar modal (clique no overlay)
  if (tagModalOverlay && tagModal) {
    tagModalOverlay.addEventListener("click", () => {
      tagModalOverlay.classList.add("hidden");
      tagModal.classList.add("hidden");
    });
  }

  // Criar tag via create_tag.php
  if (createTagBtn && newTagInput && tagDropdown && tagBtn) {
    createTagBtn.addEventListener("click", async () => {
      const tagName = newTagInput.value.trim();
      tagFeedback.textContent = "";

      if (tagName === "") {
        tagFeedback.textContent = "⚠ Escreva um nome.";
        tagFeedback.style.color = "#b54242";
        return;
      }

      let formData = new FormData();
      formData.append("tagName", tagName);

      try {
        const response = await fetch("create_tag.php", {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (!result.success) {
          tagFeedback.textContent =
            "⚠ " + (result.error || "Erro ao criar tag.");
          tagFeedback.style.color = "#b54242";
          return;
        }

        // Se já existia: apenas marcar a checkbox
        if (result.existing) {
          let existing = tagDropdown.querySelector(
            `input[value='${result.tag_id}']`
          );
          if (existing) {
            existing.checked = true;
          }
          tagFeedback.textContent = "Tag já existia — selecionada ✔";
          tagFeedback.style.color = "#2e7d32";
        } else {
          // Criar nova checkbox no dropdown
          let label = document.createElement("label");
          label.innerHTML = `
            <input type="checkbox" name="tags[]" value="${result.tag_id}" checked>
            ${result.name}
          `;
          tagDropdown.appendChild(label);

          tagFeedback.textContent = "Tag criada ✔";
          tagFeedback.style.color = "#2e7d32";
        }

        // Atualizar texto do botão
        const checked = [
          ...tagDropdown.querySelectorAll("input[type='checkbox']:checked"),
        ].map((c) => c.parentElement.textContent.trim());
        tagBtn.textContent =
          checked.length > 0 ? checked.join(", ") : "Select Tags ⮟";

        newTagInput.value = "";
      } catch (err) {
        tagFeedback.textContent = "⚠ Erro de ligação ao servidor.";
        tagFeedback.style.color = "#b54242";
      }
    });
  }

  // ============================================================
  // 3. NOTIFICAÇÕES
  // ============================================================
  const bellBtn = document.getElementById("notification-btn");
  const notifPopup = document.getElementById("notification-popup");
  const seeMoreLink = document.querySelector(".see-more-link");

  if (bellBtn && notifPopup) {
    bellBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      e.preventDefault();
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
      seeMoreLink.textContent = notifPopup.classList.contains("expanded")
        ? "Show less"
        : "+ See more";
    });
  }

  // ============================================================
  // 4. LOGOUT POPUP
  // ============================================================
  const logoutBtn = document.getElementById("logout-btn");
  const logoutPopup = document.getElementById("logout-popup");
  const cancelLogout = document.getElementById("cancel-logout");
  const confirmLogout = document.getElementById("confirm-logout");

  if (logoutBtn && logoutPopup) {
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();

      if (notifPopup) notifPopup.style.display = "none";

      logoutPopup.classList.add("active");
      logoutPopup.style.display = "block";
    });

    document.addEventListener("click", (e) => {
      if (!logoutPopup.contains(e.target) && !logoutBtn.contains(e.target)) {
        logoutPopup.classList.remove("active");
        logoutPopup.style.display = "none";
      }
    });
  }

  if (cancelLogout && logoutPopup) {
    cancelLogout.addEventListener("click", (e) => {
      e.stopPropagation();
      logoutPopup.classList.remove("active");
      logoutPopup.style.display = "none";
    });
  }

  if (confirmLogout) {
    confirmLogout.addEventListener("click", (e) => {
      e.stopPropagation();
      window.location.href = "logout.php";
    });
  }

  // ============================================================
  // 5. CSV IMPORT POPUP
  // ============================================================
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

    closePopupBtn.addEventListener("click", function () {
      csvPopup.style.display = "none";
      overlay.classList.remove("active");
      document.body.style.overflow = "";
    });

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

  // ============================================================
  // 6. SCROLL PARA MENSAGEM + REDIRECT (SUCESSO)
  // ============================================================
  const successMessage = document.querySelector(".form-message.success");
  const errorMessage   = document.querySelector(".form-message.error");

  if (successMessage) {
    // focar na mensagem de sucesso
    successMessage.scrollIntoView({ behavior: "smooth", block: "center" });

    // redirecionar se o PHP tiver definido o ID
    if (window.NEW_COLLECTION_ID) {
      setTimeout(() => {
        window.location.href = `collectionpage.php?id=${window.NEW_COLLECTION_ID}`;
      }, 2000);
    }
  } else if (errorMessage) {
    // focar na mensagem de erro
    errorMessage.scrollIntoView({ behavior: "smooth", block: "center" });
  }
});
