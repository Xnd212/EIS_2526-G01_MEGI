document.addEventListener("DOMContentLoaded", () => {
    console.log("Sign Up Event Script Loaded");

    // ============================================
    // 1. NAVBAR LOGIC (Notifications & Logout)
    // ============================================
    const bellBtn = document.getElementById("notification-btn");
    const notifPopup = document.getElementById("notification-popup");
    const logoutBtn = document.getElementById("logout-btn");
    const logoutPopup = document.getElementById("logout-popup");
    const cancelLogout = document.getElementById("cancel-logout");
    const confirmLogout = document.getElementById("confirm-logout");

    // Helper to close all popups
    function closePopups() {
        if (notifPopup) notifPopup.style.display = "none";
        if (logoutPopup) {
            logoutPopup.style.display = "none";
            logoutPopup.classList.remove("active");
        }
    }

    if (bellBtn && notifPopup) {
        bellBtn.addEventListener("click", (e) => {
            e.preventDefault(); e.stopPropagation();
            const isVisible = notifPopup.style.display === 'flex' || notifPopup.style.display === 'block';
            closePopups(); // Close others
            notifPopup.style.display = isVisible ? 'none' : 'block';
        });
    }

    if (logoutBtn && logoutPopup) {
        logoutBtn.addEventListener("click", (e) => {
            e.preventDefault(); e.stopPropagation();
            const isVisible = logoutPopup.classList.contains("active");
            closePopups(); // Close others
            if (!isVisible) {
                logoutPopup.classList.add("active");
                logoutPopup.style.display = "block";
            }
        });
    }

    if (cancelLogout) {
        cancelLogout.addEventListener("click", closePopups);
    }
    
    if (confirmLogout) {
        confirmLogout.addEventListener("click", () => {
             window.location.href = "logout.php";
        });
    }
    
    // Close on click outside
    document.addEventListener("click", (e) => {
        if (notifPopup && !notifPopup.contains(e.target) && e.target !== bellBtn) {
            notifPopup.style.display = 'none';
        }
        if (logoutPopup && !logoutPopup.contains(e.target) && e.target !== logoutBtn) {
            logoutPopup.style.display = 'none';
            logoutPopup.classList.remove("active");
        }
    });

    // ============================================
    // 2. FORM NOTIFICATIONS TOGGLE
    // ============================================
    const notifyRadios = document.querySelectorAll('input[name="notify"]');
    const notificationFields = document.getElementById('notificationFields');
    
    notifyRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            if (e.target.value === 'yes') {
                notificationFields.style.display = 'block';
                notificationFields.classList.remove('hidden');
            } else {
                notificationFields.style.display = 'none';
                notificationFields.classList.add('hidden');
            }
        });
    });

    const methodRadios = document.querySelectorAll('input[name="notifyMethod"]');
    const emailField = document.getElementById('emailField');
    const phoneField = document.getElementById('phoneField');

    methodRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            if (e.target.value === 'email') {
                emailField.style.display = 'block';
                emailField.classList.remove('hidden');
                phoneField.style.display = 'none';
                phoneField.classList.add('hidden');
            } else {
                emailField.style.display = 'none';
                emailField.classList.add('hidden');
                phoneField.style.display = 'block';
                phoneField.classList.remove('hidden');
            }
        });
    });

    // ============================================
    // 3. FORM SUBMISSION & SUMMARY
    // ============================================
    const form = document.getElementById('eventSignUpForm');
    const summarySection = document.getElementById('summarySection');
    const summaryContent = document.getElementById('summaryContent');
    const formMessage = document.getElementById('formMessage');
    const editBtn = document.getElementById('editRegistration');
    const finalConfirmBtn = document.getElementById('finalConfirm');

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if(formMessage) formMessage.textContent = "";
            
            // Client Validation
            const collection = document.getElementById('collection').value;
            const terms = document.getElementById('terms').checked;
            const notify = document.querySelector('input[name="notify"]:checked').value;

            if (!collection) {
                alert("Please select a collection option.");
                return;
            }
            if (!terms) {
                alert("You must accept the terms and conditions.");
                return;
            }
            if (notify === 'yes') {
                const method = document.querySelector('input[name="notifyMethod"]:checked');
                if(!method) { alert("Please select a notification method."); return; }
                if(method.value === 'email' && !document.getElementById('notifyEmail').value.trim()) {
                    alert("Please enter your email."); return;
                }
                if(method.value === 'phone' && !document.getElementById('notifyPhone').value.trim()) {
                    alert("Please enter your phone number."); return;
                }
            }

            // Generate Summary
            const colSelect = document.getElementById('collection');
            const colName = colSelect.options[colSelect.selectedIndex].text;
            const participants = document.getElementById('participants').value;
            const comments = document.getElementById('comments').value || "None";

            let html = `<strong>Collection:</strong> ${colName}<br>`;
            html += `<strong>Participants:</strong> ${participants}<br>`;
            html += `<strong>Notifications:</strong> ${notify.toUpperCase()}<br>`;
            if (notify === 'yes') {
                const method = document.querySelector('input[name="notifyMethod"]:checked').value;
                const contact = method === 'email' ? document.getElementById('notifyEmail').value : document.getElementById('notifyPhone').value;
                html += `<strong>Contact (${method}):</strong> ${contact}<br>`;
            }
            html += `<strong>Comments:</strong> ${comments}`;

            summaryContent.innerHTML = html;

            // Show Summary
            form.style.display = 'none';
            form.classList.add('hidden');
            summarySection.style.display = 'block';
            summarySection.classList.remove('hidden');
        });
    }

    // Go Back (Edit)
    if (editBtn) {
        editBtn.addEventListener('click', () => {
            summarySection.style.display = 'none';
            summarySection.classList.add('hidden');
            form.style.display = 'block';
            form.classList.remove('hidden');
        });
    }

    // Final Submission (Server)
    if (finalConfirmBtn) {
        finalConfirmBtn.addEventListener('click', () => {
            const eventId = form.getAttribute('data-event-id');
            let collectionId = document.getElementById('collection').value;
            
            // "Not bringing a collection" (value 0)
            if (collectionId === "0") collectionId = 0; 

            // AJAX POST
            fetch('sign_up_event.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    event_id: eventId,
                    collection_id: collectionId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Show success message and button to go to event page
                    summarySection.innerHTML = `
                        <h3>🎉 Registration Completed!</h3>
                        <p>Thank you for signing up.</p>
                        <div class="summary-actions">
                            <a href="${data.redirect}" class="btn-primary" style="text-decoration:none; padding:10px 20px; display:inline-block; margin-top:10px; background:#b54242; color:white; border-radius:10px;">Back to Event Page</a>
                        </div>
                    `;
                } else {
                    alert("Error: " + data.message);
                    // On error, let them try again
                    summarySection.style.display = 'none';
                    form.style.display = 'block';
                }
            })
            .catch(err => {
                console.error(err);
                alert("Connection error.");
            });
        });
    }
});