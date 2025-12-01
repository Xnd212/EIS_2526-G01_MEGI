document.addEventListener("DOMContentLoaded", () => {
  // ============================================================
  // 1. CUSTOM MULTI-SELECT DROPDOWN
  // ============================================================
  const dropdownBtn = document.getElementById("dropdownBtn");
  const dropdownContent = document.getElementById("dropdownContent");

  if (dropdownBtn && dropdownContent) {
    dropdownBtn.addEventListener("click", (e) => {
      e.preventDefault(); // Prevent button from submitting form
      e.stopPropagation();
      dropdownContent.style.display =
        dropdownContent.style.display === "block" ? "none" : "block";
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (!dropdownContent.contains(e.target) && e.target !== dropdownBtn) {
        dropdownContent.style.display = "none";
      }
    });

    // Update button text when checkboxes change
    dropdownContent.addEventListener("change", () => {
      const checked = [
        ...dropdownContent.querySelectorAll("input[type='checkbox']:checked"),
      ].map((c) => c.parentElement.textContent.trim());

      dropdownBtn.textContent =
        checked.length > 0
          ? checked.join(", ")
          : "Select from existing items ⮟";
    });
  }

  // ============================================================
  // 2. FORM VALIDATION
  // ============================================================
  const form = document.getElementById("collectionForm");
  const formMessage = document.getElementById("formMessage");

  if (form && formMessage) {
    form.addEventListener("submit", (e) => {
      // Prevent default submission to allow validation
      e.preventDefault();

      const name = document.getElementById("collectionName");
      const theme = document.getElementById("collectionTheme");
      const dateInput = document.getElementById("creationDate");

      // Clear previous errors
      [name, theme, dateInput].forEach((el) => {
        if (el) el.classList.remove("error");
      });
      formMessage.textContent = "";
      formMessage.className = "form-message";

      let valid = true;

      // Name Validation
      if (!name || name.value.trim() === "") {
        if (name) name.classList.add("error");
        valid = false;
      }

      // Theme Validation
      if (!theme || theme.value.trim() === "") {
        if (theme) theme.classList.add("error");
        valid = false;
      }

      // Date Validation
      if (dateInput) {
        const dateValue = dateInput.value.trim();
        const datePattern = /^\d{4}-\d{2}-\d{2}$/; // matches "YYYY-MM-DD"

        if (!datePattern.test(dateValue)) {
          dateInput.classList.add("error");
          valid = false;
        } else {
          // Check if date is in the future
          const selectedDate = new Date(dateValue);
          const currentDate = new Date();
          currentDate.setHours(0, 0, 0, 0); // Reset time
          selectedDate.setHours(0, 0, 0, 0);

          if (selectedDate > currentDate) {
            dateInput.classList.add("error");
            valid = false;
          } else {
            dateInput.classList.remove("error");
          }
        }
      } else {
        valid = false;
      }

      // --- FINAL DECISION ---
      if (!valid) {
        formMessage.textContent = "⚠️ Please fill in all required (*) fields correctly.";
        formMessage.classList.add("error");
        return; // Stop here, do not send to PHP
      }

      // IF VALID: SUBMIT THE FORM TO PHP
      formMessage.textContent = "Processing...";
      form.submit(); // This bypasses the listener and sends data to the server
    });
  }

  // ============================================================
  // 3. NOTIFICATIONS
  // ============================================================
  const bellBtn = document.getElementById("notification-btn");
  const notifPopup = document.getElementById("notification-popup");
  const seeMoreLink = document.querySelector(".see-more-link");

  if (bellBtn && notifPopup) {
    bellBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      e.preventDefault();
      // Toggle display
      notifPopup.style.display =
        notifPopup.style.display === "block" ? "none" : "block";
    });

    // Close when clicking outside
    document.addEventListener("click", (e) => {
      if (!notifPopup.contains(e.target) && !bellBtn.contains(e.target)) {
        notifPopup.style.display = "none";
      }
    });
  }

  // Expand/Collapse notifications
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
  // 4. LOGOUT POPUP (Fixed ReferenceError)
  // ============================================================
  const logoutBtn = document.getElementById("logout-btn");
  const logoutPopup = document.getElementById("logout-popup");
  const cancelLogout = document.getElementById("cancel-logout");
  const confirmLogout = document.getElementById("confirm-logout");

  if (logoutBtn && logoutPopup) {
    // Open Popup
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();

      // Close notifications if open
      if (notifPopup) notifPopup.style.display = "none";

      logoutPopup.classList.add("active");
      logoutPopup.style.display = "block"; // Force display
    });

    // Close when clicking outside
    document.addEventListener("click", (e) => {
      if (!logoutPopup.contains(e.target) && !logoutBtn.contains(e.target)) {
        logoutPopup.classList.remove("active");
        logoutPopup.style.display = "none";
      }
    });
  }

  // Cancel Button
  if (cancelLogout && logoutPopup) {
    cancelLogout.addEventListener("click", (e) => {
      e.stopPropagation();
      logoutPopup.classList.remove("active");
      logoutPopup.style.display = "none";
    });
  }

  // Confirm Button (Redirect to logout PHP)
  if (confirmLogout) {
    confirmLogout.addEventListener("click", (e) => {
      e.stopPropagation();
      window.location.href = "logout.php";
    });
  }
});