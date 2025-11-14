
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

  // Aplica o tema guardado; se não existir, usa sempre "light"
  const appliedTheme = userData.theme === "dark" ? "dark" : "light";
  document.body.setAttribute("data-theme", appliedTheme);

  // Atualiza o estado dos rádios e só altera visualmente quando o user interage
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
  // VALIDAÇÃO DO FORMULÁRIO
  // =============================
  function validateForm() {
    let valid = true;
    feedbackMsg.textContent = "";

    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;

    if (!usernameRegex.test(usernameInput.value.trim())) {
      valid = false;
      usernameInput.classList.add("error");
    } else usernameInput.classList.remove("error");

    if (!dobInput.value) {
      valid = false;
      dobInput.classList.add("error");
    } else dobInput.classList.remove("error");

    if (!validateEmail(emailInput.value.trim())) {
      valid = false;
      emailInput.classList.add("error");
    } else emailInput.classList.remove("error");

    if (!countrySelect.value) {
      valid = false;
      countrySelect.classList.add("error");
    } else countrySelect.classList.remove("error");

    if (!valid) {
      feedbackMsg.textContent = "⚠️ Please fill in all required (*) fields correctly.";
      feedbackMsg.classList.add("error");
      return false;
    }

    feedbackMsg.textContent = "";
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

    // Guarda os dados atualizados
    localStorage.setItem("tralle_userProfile", JSON.stringify(data));

    // Feedback visual
    feedbackMsg.textContent = "✅ Profile updated successfully!";
    feedbackMsg.classList.remove("error");
    feedbackMsg.classList.add("success");

    // Aplica imediatamente o tema escolhido
    document.body.setAttribute("data-theme", data.theme);

    // Redireciona para o perfil
    setTimeout(() => {
      window.location.href = "userpage.html";
    }, 1500);
  });


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

