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
  // 1.b MULTISELECT: TAGS (igual visual ao editcollection)
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
  // 2. FORM VALIDATION (simples)
  // ============================================================
  const form = document.getElementById("collectionForm");
  const formMessage = document.getElementById("formMessage");

  if (form) {
    form.addEventListener("submit", (e) => {
      if (!formMessage) return;

      e.preventDefault();

      const name = document.getElementById("collectionName");
      const theme = document.getElementById("collectionTheme");
      const dateInput = document.getElementById("creationDate");

      [name, theme, dateInput].forEach((el) => el && el.classList.remove("error"));
      formMessage.textContent = "";
      formMessage.className = "form-message";

      let valid = true;

      if (!name || name.value.trim() === "") {
        name.classList.add("error");
        valid = false;
      }

      if (!theme || theme.value.trim() === "") {
        theme.classList.add("error");
        valid = false;
      }

      if (dateInput) {
        const dateValue = dateInput.value.trim();
        const datePattern = /^\d{4}-\d{2}-\d{2}$/;

        if (!datePattern.test(dateValue)) {
          dateInput.classList.add("error");
          valid = false;
        }
      } else {
        valid = false;
      }

      if (!valid) {
        formMessage.textContent =
          "⚠️ Please fill in all required (*) fields correctly.";
        formMessage.classList.add("error");
        return;
      }

      form.submit();
    });
  }

  // ============================================================
  // 3. MODAL PARA CRIAR TAGS (visual, sem AJAX)
  // ============================================================
  const openTagModal = document.getElementById("openTagModal");
  const tagModalOverlay = document.getElementById("tagModalOverlay");
  const tagModal = document.getElementById("tagModal");
  const closeTagModal = document.getElementById("closeTagModal");
  const createTagBtn = document.getElementById("createTagBtn");
  const newTagInput = document.getElementById("newTagInput");
  const tagFeedback = document.getElementById("tagFeedback");

  if (openTagModal && tagModalOverlay && tagModal) {
    openTagModal.addEventListener("click", (e) => {
      e.preventDefault();
      tagModalOverlay.classList.remove("hidden");
      tagModal.classList.remove("hidden");
      newTagInput.value = "";
      tagFeedback.textContent = "";
    });
  }

  if (closeTagModal && tagModalOverlay && tagModal) {
    closeTagModal.addEventListener("click", () => {
      tagModalOverlay.classList.add("hidden");
      tagModal.classList.add("hidden");
    });
  }

  if (tagModalOverlay && tagModal) {
    tagModalOverlay.addEventListener("click", () => {
      tagModalOverlay.classList.add("hidden");
      tagModal.classList.add("hidden");
    });
  }

  if (createTagBtn && newTagInput && tagDropdown && tagBtn) {
    createTagBtn.addEventListener("click", () => {
      const value = newTagInput.value.trim();
      if (!value) {
        tagFeedback.textContent = "Please write a tag name.";
        tagFeedback.style.color = "#b54242";
        return;
      }

      // Cria um checkbox localmente (não grava na BD – apenas visual)
      const label = document.createElement("label");
      const input = document.createElement("input");
      input.type = "checkbox";
      input.name = "tags[]";
      // valor -1 para indicar que é novo; se quiseres BD fazemos depois
      input.value = "-1|" + value;

      label.appendChild(input);
      label.appendChild(document.createTextNode(" " + value));
      tagDropdown.appendChild(label);

      input.checked = true;

      const checked = [
        ...tagDropdown.querySelectorAll("input[type='checkbox']:checked"),
      ].map((c) => c.parentElement.textContent.trim());
      tagBtn.textContent =
        checked.length > 0 ? checked.join(", ") : "Select Tags ⮟";

      tagFeedback.textContent = "Tag added (local only).";
      tagFeedback.style.color = "green";
      newTagInput.value = "";
    });
  }

  // ============================================================
  // 4. NOTIFICAÇÕES
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
  // 5. LOGOUT POPUP
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
});

// ============================================================
// 6. CSV IMPORT POPUP
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
  const bulkImportBtn = document.getElementById("bulk-import-btn");
  const csvPopup = document.getElementById("csv-import-popup");
  const closePopupBtn = document.getElementById("close-csv-popup");
  const overlay = document.getElementById("csv-overlay");

  if (bulkImportBtn) {
    bulkImportBtn.addEventListener("click", function (e) {
      e.preventDefault();
      csvPopup.style.display = "block";
      overlay.classList.add("active");
      document.body.style.overflow = "hidden";
    });
  }

  if (closePopupBtn) {
    closePopupBtn.addEventListener("click", function () {
      csvPopup.style.display = "none";
      overlay.classList.remove("active");
      document.body.style.overflow = "";
    });
  }

  if (overlay) {
    overlay.addEventListener("click", function () {
      csvPopup.style.display = "none";
      overlay.classList.remove("active");
      document.body.style.overflow = "";
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && csvPopup.style.display === "block") {
      csvPopup.style.display = "none";
      overlay.classList.remove("active");
      document.body.style.overflow = "";
    }
  });
});
