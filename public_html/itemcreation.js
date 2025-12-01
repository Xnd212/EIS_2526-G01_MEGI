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
      // Bounds check
      let val = parseInt(numberInput.value, 10);
      if (isNaN(val)) val = 5;
      if (val < 1) val = 1;
      if (val > 10) val = 10;
      slider.value = val;
    });
  }

  // ============================================
  // 2. FORM VALIDATION
  // ============================================
  const form = document.getElementById("itemForm");
  const formMessage = document.getElementById("formMessage");

  if (form) {
    form.addEventListener("submit", (e) => {
      // 1. Stop submission initially to check errors
      e.preventDefault();

      // Elements
      // NOTE: This looks for the <select> ID "collection_id", not checkboxes!
      const collection = document.getElementById("collection_id"); 
      const name = document.getElementById("itemName");
      const price = document.getElementById("itemPrice");
      const type = document.getElementById("itemType");
      const date = document.getElementById("acc_date");
      
      // Reset Errors (remove red borders)
      [collection, name, price, type, date].forEach(el => {
          if(el) el.classList.remove("error");
      });
      
      formMessage.textContent = "";
      formMessage.className = "form-message";

      let valid = true;

      // --- CHECK FIELDS ---

      // Check Collection (Select Box)
      if (!collection || collection.value === "") { 
          if(collection) collection.classList.add("error"); 
          valid = false; 
      }

      // Check Name
      if (!name.value.trim()) { 
          name.classList.add("error"); 
          valid = false; 
      }

      // Check Price
      if (!price.value.trim() || parseFloat(price.value) < 0) { 
          price.classList.add("error"); 
          valid = false; 
      }

      // Check Type
      if (!type.value.trim()) { 
          type.classList.add("error"); 
          valid = false; 
      }

      // Check Date
      if (!date.value.trim()) { 
          date.classList.add("error"); 
          valid = false; 
      }

      // Date Future Check
      if (date.value) {
          const selectedDate = new Date(date.value);
          const currentDate = new Date();
          currentDate.setHours(0,0,0,0);
          selectedDate.setHours(0,0,0,0);
          
          if (selectedDate > currentDate) {
              date.classList.add("error");
              valid = false;
          }
      }

      // --- FINAL DECISION ---
      if (!valid) {
        formMessage.textContent = "⚠️ Please fill in all required (*) fields.";
        formMessage.classList.add("error");
        return; // Stop here. Do not send to PHP.
      }

      // IF VALID: Send to PHP!
      formMessage.textContent = "Processing...";
      form.submit(); // <--- THIS IS THE MISSING LINE IN YOUR OLD CODE
    });
  }

  // ============================================
  // 3. NOTIFICATIONS & LOGOUT (Bell/Door)
  // ============================================
  const bellBtn = document.getElementById("notification-btn");
  const notifPopup = document.getElementById("notification-popup");
  const logoutBtn = document.getElementById("logout-btn");
  const logoutPopup = document.getElementById("logout-popup");
  const cancelLogout = document.getElementById("cancel-logout");
  const confirmLogout = document.getElementById("confirm-logout");

  // Bell Logic
  if (bellBtn && notifPopup) {
    bellBtn.addEventListener("click", (e) => {
      e.preventDefault(); e.stopPropagation();
      if(logoutPopup) logoutPopup.style.display = "none"; // Close door if open
      
      notifPopup.style.display = (notifPopup.style.display === "block") ? "none" : "block";
    });
  }

  // Door Logic
  if (logoutBtn && logoutPopup) {
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault(); e.stopPropagation();
      if(notifPopup) notifPopup.style.display = "none"; // Close bell if open
      
      logoutPopup.classList.add("active");
      logoutPopup.style.display = "block";
    });
  }

  // Close Popups on Outside Click
  document.addEventListener("click", (e) => {
    // Close Notification
    if (notifPopup && !notifPopup.contains(e.target) && bellBtn && !bellBtn.contains(e.target)) {
        notifPopup.style.display = "none";
    }
    // Close Logout
    if (logoutPopup && !logoutPopup.contains(e.target) && logoutBtn && !logoutBtn.contains(e.target)) {
        logoutPopup.style.display = "none";
        logoutPopup.classList.remove("active");
    }
  });

  // Logout Cancel
  if (cancelLogout) {
      cancelLogout.addEventListener("click", () => {
          logoutPopup.style.display = "none";
          logoutPopup.classList.remove("active");
      });
  }
  
  // Logout Confirm
  if (confirmLogout) {
      confirmLogout.addEventListener("click", () => {
          window.location.href = "logout.php";
      });
  }
});