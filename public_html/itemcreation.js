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

/*
// ============================================
  // 2. FORM VALIDATION (SAFER VERSION)
  // ============================================
  const form = document.getElementById("itemForm");
  const formMessage = document.getElementById("formMessage");

  if (form) {
    form.addEventListener("submit", (e) => {
      // NOTE: We do NOT preventDefault here. We assume it's valid until proven otherwise.

      // Elements
      const collection = document.getElementById("collection_id"); 
      const name = document.getElementById("itemName");
      const price = document.getElementById("itemPrice");
      const type = document.getElementById("itemType");
      const date = document.getElementById("acc_date");
      
      // Reset Errors (remove red borders)
      [collection, name, price, type, date].forEach(el => {
          if(el) el.classList.remove("error");
      });
      
      if(formMessage) {
        formMessage.textContent = "";
        formMessage.className = "form-message";
      }

      let valid = true;

      // --- CHECK FIELDS ---

      // Check Collection
      if (!collection || collection.value === "") { 
          if(collection) collection.classList.add("error"); 
          valid = false; 
      }

      // Check Name
      if (!name || !name.value.trim()) { 
          if(name) name.classList.add("error"); 
          valid = false; 
      }

      // Check Price
      if (!price || !price.value.trim() || parseFloat(price.value) < 0) { 
          if(price) price.classList.add("error"); 
          valid = false; 
      }

      // Check Type
      if (!type || !type.value.trim()) { 
          if(type) type.classList.add("error"); 
          valid = false; 
      }

      // Check Date (Empty)
      if (!date || !date.value.trim()) { 
          if(date) date.classList.add("error"); 
          valid = false; 
      }

      // Check Date (Future) - SAFER LOGIC
      if (date && date.value) {
          const selectedDate = new Date(date.value);
          // Check if date is valid before running setHours
          if (!isNaN(selectedDate.getTime())) { 
             const currentDate = new Date();
             currentDate.setHours(0,0,0,0);
             selectedDate.setHours(0,0,0,0);
             
             if (selectedDate > currentDate) {
                 date.classList.add("error");
                 valid = false;
                 if(formMessage) formMessage.textContent = "Date cannot be in the future.";
             }
          }
      }

      // --- FINAL DECISION ---
      if (!valid) {
        // ONLY NOW do we stop the form from sending
        e.preventDefault(); 
        
        if(formMessage && formMessage.textContent === "") {
            formMessage.textContent = "⚠️ Please fill in all required (*) fields.";
        }
        if(formMessage) formMessage.classList.add("error");
        
        // Console log for debugging
        console.log("Form submission blocked due to validation errors.");
      } 
      
      // If valid is true, we do nothing. 
      // The browser continues to PHP naturally.
    });
  }

*/
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

// CSV Import Popup Functionality
document.addEventListener('DOMContentLoaded', function() {
    const bulkImportBtn = document.getElementById('bulk-import-btn');
    const csvPopup = document.getElementById('csv-import-popup');
    const closePopupBtn = document.getElementById('close-csv-popup');
    const overlay = document.getElementById('csv-overlay');

    if (bulkImportBtn) {
        bulkImportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            csvPopup.style.display = 'block';
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        });
    }

    if (closePopupBtn) {
        closePopupBtn.addEventListener('click', function() {
            csvPopup.style.display = 'none';
            overlay.classList.remove('active');
            document.body.style.overflow = ''; // Restore scrolling
        });
    }

    // Close on overlay click
    if (overlay) {
        overlay.addEventListener('click', function() {
            csvPopup.style.display = 'none';
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && csvPopup.style.display === 'block') {
            csvPopup.style.display = 'none';
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

