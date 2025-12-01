document.addEventListener("DOMContentLoaded", () => {
    console.log("Event Page Script Loaded"); 

    // ============================================
    // 1. GLOBAL VARIABLES
    // ============================================
    const bellBtn = document.getElementById("notification-btn");
    const notifPopup = document.getElementById("notification-popup");
    const logoutBtn = document.getElementById("logout-btn");
    const logoutPopup = document.getElementById("logout-popup");
    const cancelLogout = document.getElementById("cancel-logout");
    const confirmLogout = document.getElementById("confirm-logout");

    // Event Page Specific Elements
    const leaveBtn = document.getElementById("leave-event-btn");
    const leavePopup = document.getElementById("leave-popup");
    const cancelLeave = document.getElementById("cancel-leave");
    const confirmLeave = document.getElementById("confirm-leave");

    let leaveOverlay = null;

    // ============================================
    // 2. NOTIFICATIONS & LOGOUT LOGIC
    // ============================================
  
    // Bell Logic
    if (bellBtn && notifPopup) {
      bellBtn.addEventListener("click", (e) => {
        e.preventDefault(); e.stopPropagation();
        if(logoutPopup) logoutPopup.style.display = "none";
        if(leaveOverlay) leaveOverlay.style.display = "none";
        
        notifPopup.style.display = (notifPopup.style.display === "block") ? "none" : "block";
      });
    }
  
    // Door Logic
    if (logoutBtn && logoutPopup) {
      logoutBtn.addEventListener("click", (e) => {
        e.preventDefault(); e.stopPropagation();
        if(notifPopup) notifPopup.style.display = "none";
        if(leaveOverlay) leaveOverlay.style.display = "none";

        logoutPopup.classList.add("active");
        logoutPopup.style.display = "block";
      });
    }
  
    // Logout Actions
    if (cancelLogout) {
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
    // 3. LEAVE EVENT POPUP LOGIC (MODAL OVERLAY)
    // ============================================
    
    function getOverlay() {
        if (!leaveOverlay) {
            leaveOverlay = document.createElement("div");
            leaveOverlay.id = "leave-modal-overlay";
            document.body.appendChild(leaveOverlay);

            // Full screen overlay styles
            leaveOverlay.style.position = "fixed";
            leaveOverlay.style.top = "0";
            leaveOverlay.style.left = "0";
            leaveOverlay.style.width = "100%";
            leaveOverlay.style.height = "100%";
            leaveOverlay.style.backgroundColor = "rgba(0, 0, 0, 0.5)"; 
            leaveOverlay.style.zIndex = "10000";
            leaveOverlay.style.display = "none";
            leaveOverlay.style.justifyContent = "center";
            leaveOverlay.style.alignItems = "center";

            if (leavePopup) {
                leaveOverlay.appendChild(leavePopup);
                leavePopup.style.position = "relative";
                leavePopup.style.top = "auto";
                leavePopup.style.left = "auto";
                leavePopup.style.right = "auto";
                leavePopup.style.bottom = "auto";
                leavePopup.style.margin = "0";
                leavePopup.style.display = "block"; 
                leavePopup.style.backgroundColor = "white";
                leavePopup.style.zIndex = "10001";
            }
        }
        return leaveOverlay;
    }

    // Open Leave Popup
    if (leaveBtn && leavePopup) {
        leaveBtn.addEventListener("click", (e) => {
            e.preventDefault(); 
            e.stopPropagation();

            if(notifPopup) notifPopup.style.display = "none";
            if(logoutPopup) logoutPopup.style.display = "none";

            const overlay = getOverlay();
            overlay.style.display = "flex"; 
        });
    }

    // Cancel Leave
    if (cancelLeave) {
        cancelLeave.addEventListener("click", (e) => {
            e.preventDefault();
            if (leaveOverlay) leaveOverlay.style.display = "none";
        });
    }

    // Confirm Leave (FIXED: CACHE BUSTING)
    if (confirmLeave && leaveBtn) {
        confirmLeave.addEventListener("click", (e) => {
            e.preventDefault();
            
            const eventId = leaveBtn.getAttribute("data-id");
            console.log("Processing Leave Event via AJAX for ID:", eventId);

            if(eventId) {
                // Add timestamp to prevent browser caching (?t=...)
                const url = "sign_up_event.php?id=" + eventId + "&action=leave&t=" + new Date().getTime();

                fetch(url, {
                    method: 'GET',
                    cache: 'no-store' // Ensure we don't use cached response
                })
                .then(response => {
                    if (response.ok) {
                        console.log("AJAX Success. Reloading page...");
                        window.location.reload(); 
                    } else {
                        alert("Server error: " + response.status);
                    }
                })
                .catch(error => {
                    console.error("Error leaving event:", error);
                    alert("There was an error connecting to the server.");
                });
            } else {
                console.error("Error: No Event ID found on button");
            }
        });
    }

    // ============================================
    // 4. GLOBAL CLOSE (Click Outside)
    // ============================================
    document.addEventListener("click", (e) => {
        if (notifPopup && !notifPopup.contains(e.target) && bellBtn && !bellBtn.contains(e.target)) {
            notifPopup.style.display = "none";
        }
        if (logoutPopup && !logoutPopup.contains(e.target) && logoutBtn && !logoutBtn.contains(e.target)) {
            logoutPopup.style.display = "none";
            logoutPopup.classList.remove("active");
        }
        if (leaveOverlay && e.target === leaveOverlay) {
            leaveOverlay.style.display = "none";
        }
    });

});