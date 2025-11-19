// =============================
// CAMPOS PRINCIPAIS
// =============================
const form = document.getElementById("editProfileForm");
const usernameInput = document.getElementById("username");
const dobInput = document.getElementById("dob");
const emailInput = document.getElementById("email");
const verifyBtn = document.getElementById("verifyEmail");
const emailStatus = document.getElementById("emailStatus");
const countrySelect = document.getElementById("country");
const themeRadios = document.querySelectorAll("input[name='theme']");
const feedbackMsg = document.getElementById("formFeedback");

// === FOTO ===
const photoInput = document.getElementById("profilePhoto");
const photoPreview = document.getElementById("profilePreview");

// === NOTIFICAÇÕES ===
const notifyRadios = document.querySelectorAll("input[name='notify']");
const notificationFields = document.getElementById("notificationFields");
const notifyMethodRadios = document.querySelectorAll("input[name='notifyMethod']");
const emailField = document.getElementById("emailField");
const phoneField = document.getElementById("phoneField");

// =============================
// CARREGAR DADOS GUARDADOS
// =============================
const data = JSON.parse(localStorage.getItem("tralle_userProfile")) || {};

usernameInput.value = data.username || "";
dobInput.value = data.dob || "";
emailInput.value = data.email || "";
countrySelect.value = data.country || "";
emailStatus.textContent = data.emailVerified ? "✅ Email verified" : "";
photoPreview.src = data.photo || "images/userimage.png";

document.addEventListener("DOMContentLoaded", () => {
    const userData = JSON.parse(localStorage.getItem("tralle_userProfile")) || {};

    const appliedTheme = userData.theme === "dark" ? "dark" : "light";
    document.body.setAttribute("data-theme", appliedTheme);

    themeRadios.forEach(radio => {
        radio.checked = (userData.theme ? userData.theme : "light") === radio.value;

        radio.addEventListener("change", () => {
            document.body.setAttribute("data-theme", radio.value);
        });
    });
});

// =============================
// FOTO: PREVIEW
// =============================
photoInput.addEventListener("change", e => {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        alert("File too large! Max size: 2MB");
        photoInput.value = "";
        return;
    }

    const reader = new FileReader();
    reader.onload = event => {
        photoPreview.src = event.target.result;
    };
    reader.readAsDataURL(file);
});

// =============================
// EMAIL: VERIFICAÇÃO SIMULADA
// =============================
verifyBtn.addEventListener("click", () => {
    const email = emailInput.value.trim();

    if (!validateEmail(email)) {
        emailStatus.textContent = "⚠️ Invalid email address.";
        emailStatus.classList.add("error");
        return;
    }

    emailStatus.textContent = "📨 Sending verification email...";
    emailStatus.classList.remove("error");

    setTimeout(() => {
        emailStatus.textContent = "✅ Verification email sent!";
    }, 1500);
});

// =============================
// NOTIFICAÇÕES
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

// =============================
// Custom Multi-select Dropdown
// =============================
const dropdownBtn = document.getElementById("dropdownBtn");
const dropdownContent = document.getElementById("dropdownContent");
const counterEl = document.getElementById("collectionCounter");

dropdownBtn.addEventListener("click", () => {
    dropdownContent.style.display =
        dropdownContent.style.display === "block" ? "none" : "block";
});

document.addEventListener("click", (e) => {
    if (!dropdownContent.contains(e.target) && e.target !== dropdownBtn) {
        dropdownContent.style.display = "none";
    }
});

dropdownContent.addEventListener("change", () => {
    const checked = [
        ...dropdownContent.querySelectorAll("input[type='checkbox']:checked"),
    ].map((c) => c.parentElement.textContent.trim());

    dropdownBtn.textContent =
        checked.length > 0 ? checked.join(", ") : "Select Collections ⮟";
});

// =============================
// LIVE COUNTER FOR COLLECTIONS
// =============================
function updateCollectionCounter() {
    const count = dropdownContent.querySelectorAll("input[type='checkbox']:checked").length;

    if (counterEl) {
        counterEl.textContent = `${count} / 5 selected`;

        if (count > 5) {
            counterEl.classList.add("limit-reached");
        } else {
            counterEl.classList.remove("limit-reached");
        }
    }
}

dropdownContent.addEventListener("change", updateCollectionCounter);
updateCollectionCounter(); // initial run

// =============================
// COLLECTIONS: MAX 5 VALIDATION
// =============================
function validateCollections() {
    const checked = dropdownContent.querySelectorAll("input[type='checkbox']:checked");

    if (checked.length > 5) {
        feedbackMsg.textContent = "⚠️ You can select up to 5 collections only.";
        feedbackMsg.classList.remove("success");
        feedbackMsg.classList.add("error");
        return false;
    }

    return true;
}

// =============================
// VALIDAÇÃO DO FORMULÁRIO
// =============================
function validateForm() {
    let valid = true;

    // limpar mensagem geral
    feedbackMsg.textContent = "";
    feedbackMsg.classList.remove("error", "success");

    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;

    // USERNAME
    if (!usernameRegex.test(usernameInput.value.trim())) {
        valid = false;
        usernameInput.classList.add("error");
    } else {
        usernameInput.classList.remove("error");
    }

    // DOB
    if (!dobInput.value) {
        valid = false;
        dobInput.classList.add("error");
    } else {
        dobInput.classList.remove("error");
    }

    // EMAIL
    if (!validateEmail(emailInput.value.trim())) {
        valid = false;
        emailInput.classList.add("error");
    } else {
        emailInput.classList.remove("error");
    }

    // COUNTRY
    if (!countrySelect.value) {
        valid = false;
        countrySelect.classList.add("error");
    } else {
        countrySelect.classList.remove("error");
    }

    // NOTIFICATIONS (obrigatório escolher sim/não)
    const notifySelected = [...notifyRadios].some(r => r.checked);
    if (!notifySelected) {
        valid = false;
    }

    // Se escolher "yes", método também obrigatório
    if (notifySelected) {
        const notifyYes = [...notifyRadios].find(r => r.checked && r.value === "yes");
        if (notifyYes) {
            const methodSelected = [...notifyMethodRadios].some(r => r.checked);
            if (!methodSelected) {
                valid = false;
            }
        }
    }

    // MAX 5 COLEÇÕES
    if (!validateCollections()) {
        valid = false;
    }

    if (!valid) {
        if (!feedbackMsg.textContent) {
            feedbackMsg.textContent = "⚠️ Please fill in all required (*) fields correctly.";
        }
        feedbackMsg.classList.add("error");
        return false;
    }

    return true;
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// =============================
// SUBMISSÃO DO FORMULÁRIO
// =============================
form.addEventListener("submit", e => {
    e.preventDefault();

    if (!validateForm()) return;

    const selectedMethod = document.querySelector("input[name='notifyMethod']:checked")?.value || "";

    const data = {
        username: usernameInput.value.trim(),
        dob: dobInput.value,
        email: emailInput.value.trim(),
        country: countrySelect.value,
        notificationMethod: selectedMethod,
        theme: [...themeRadios].find(r => r.checked).value,
        emailVerified: emailStatus.textContent.includes("✅"),
        photo: photoPreview.src
    };

    localStorage.setItem("tralle_userProfile", JSON.stringify(data));

    feedbackMsg.textContent = "✅ Profile updated successfully!";
    feedbackMsg.classList.remove("error");
    feedbackMsg.classList.add("success");

    document.body.setAttribute("data-theme", data.theme);

    setTimeout(() => {
        window.location.href = "userpage.php";
    }, 1500);
});
