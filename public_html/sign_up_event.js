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
});

  
  
const userCollections = ["Pokémon Cards", "Rare Coins", "Panini Stickers", "Comics"];

const collectionSelect = document.getElementById("collection");
userCollections.forEach(col => {
  const option = document.createElement("option");
  option.value = col;
  option.textContent = col;
  collectionSelect.appendChild(option);
});


const form = document.getElementById("eventSignUpForm");
const summarySection = document.getElementById("summarySection");
const summaryContent = document.getElementById("summaryContent");
const editButton = document.getElementById("editRegistration");
const finalConfirmButton = document.getElementById("finalConfirm");

// notificação
const notifyRadios = document.querySelectorAll("input[name='notify']");
const notificationFields = document.getElementById("notificationFields");
const notifyMethodRadios = document.querySelectorAll("input[name='notifyMethod']");
const emailField = document.getElementById("emailField");
const phoneField = document.getElementById("phoneField");

// pagamento
const paymentRadios = document.querySelectorAll("input[name='payment']");
const paymentFields = document.getElementById("paymentFields");
const transferField = document.getElementById("transferField");
const mbwayField = document.getElementById("mbwayField");
const presentialField = document.getElementById("presentialField");


// =============================
// NOTIFICATION LOGIC
// =============================
notifyRadios.forEach(radio => {
  radio.addEventListener("change", () => {
    if (radio.value === "yes") {
      notificationFields.classList.remove("hidden");
    } else {
      notificationFields.classList.add("hidden");
      emailField.classList.add("hidden");
      phoneField.classList.add("hidden");
      notifyMethodRadios.forEach(r => (r.checked = false));
    }
  });
});

notifyMethodRadios.forEach(method => {
  method.addEventListener("change", () => {
    emailField.classList.add("hidden");
    phoneField.classList.add("hidden");
    if (method.value === "email") emailField.classList.remove("hidden");
    if (method.value === "phone") phoneField.classList.remove("hidden");
  });
});



// FORM SUBMISSION
form.addEventListener("submit", e => {
  e.preventDefault();

  // Reset mensagens de erro
  const inputs = form.querySelectorAll("input, select, textarea");
  inputs.forEach(input => input.classList.remove("error"));

  let valid = true;
  const name = document.getElementById("userName").value.trim();
  const collection = collectionSelect.value;
  const participants = document.getElementById("participants").value;
  const notify = [...notifyRadios].find(r => r.checked)?.value || "no";
  const comments = document.getElementById("comments").value.trim();
  const terms = document.getElementById("terms").checked;

 
  // Validações
  if (collection === "") {
    collectionSelect.classList.add("error");
    valid = false;
  }

  if (!terms) {
    alert("You must accept the terms and conditions.");
    valid = false;
  }


  let notifyText = "No notifications";
  if (notify === "yes") {
    const method = [...notifyMethodRadios].find(r => r.checked)?.value;
    if (!method) {
      alert("Please select a notification method (email or phone).");
      valid = false;
    } else if (method === "email") {
      const email = document.getElementById("notifyEmail").value.trim();
      if (!email) {
        document.getElementById("notifyEmail").classList.add("error");
        valid = false;
      } else {
        notifyText = `Email → ${email}`;
      }
    } else if (method === "phone") {
      const phone = document.getElementById("notifyPhone").value.trim();
      if (!phone) {
        document.getElementById("notifyPhone").classList.add("error");
        valid = false;
      } else {
        notifyText = `Phone → ${phone}`;
      }
    }
  }

    if (!valid) {
    formMessage.textContent = "⚠️ Please fill in all required (*) fields correctly.";
    formMessage.classList.add("error");
    return;
  }
  
  formMessage.textContent = "";
  formMessage.className = "form-message";

  // Summary
  const summaryHTML = `
    <p><strong>User:</strong> ${name}</p>
    <p><strong>Collection:</strong> ${collection}</p>
    <p><strong>Participants:</strong> ${participants}</p>
    <p><strong>Notification:</strong> ${notifyText}</p>
    ${comments ? `<p><strong>Comments:</strong> ${comments}</p>` : ""}
  `;

  summaryContent.innerHTML = summaryHTML;
  form.classList.add("hidden");
  summarySection.classList.remove("hidden");
});

finalConfirmButton.addEventListener("click", () => {
  summarySection.innerHTML = `
    <h3>🎉 Registration Completed!</h3>
    <p>Thank you for signing up for this event. We look forward to seeing you there!</p>
    <div class="summary-actions">
      <a href="events.php" class="back-link" id="goToEventPage">← Back to Event Page</a>
    </div>
  `;
});
  
  
// FINAL ACTIONS
editButton.addEventListener("click", () => {
  summarySection.classList.add("hidden");
  form.classList.remove("hidden");
});

finalConfirmButton.addEventListener("click", () => {
  summarySection.innerHTML = `
    <h3>🎉 Registration completed!</h3>
    <p>Thank you for signing up for this event. We look forward to seeing you there!</p>
    <div class="summary-actions">
      <a href="homepage.php" class="back-link" id="goToEventPage">← Back to Home Page</a>
    </div>
  `;
});
   
