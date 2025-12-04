document.addEventListener("DOMContentLoaded", () => {
  // =========================
  // DROPDOWN DE COLEÇÕES
  // =========================
  const dropdownBtn = document.getElementById("dropdownBtn");
  const dropdownContent = document.getElementById("dropdownContent");

  if (dropdownBtn && dropdownContent) {
    dropdownBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      dropdownContent.style.display =
        dropdownContent.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", (e) => {
      if (!dropdownContent.contains(e.target) && e.target !== dropdownBtn) {
        dropdownContent.style.display = "none";
      }
    });

    // Atualizar texto do botão conforme checkboxes
    const updateButtonText = () => {
      const checked = [
        ...dropdownContent.querySelectorAll("input[type='checkbox']:checked"),
      ].map((c) => c.parentElement.textContent.trim());

      dropdownBtn.textContent =
        checked.length > 0 ? checked.join(", ") : "Select Collections ⮟";
    };

    dropdownContent.addEventListener("change", updateButtonText);
    // Atualizar já ao carregar a página (para coleções já associadas)
    updateButtonText();
  }

  // =========================
  // NOTIFICAÇÕES
  // =========================
  const bellBtn = document.querySelector('.icon-btn[aria-label="Notificações"]');
  const popup = document.getElementById("notification-popup");
  const seeMoreLink = document.querySelector(".see-more-link");

  if (bellBtn && popup) {
    bellBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      popup.style.display = popup.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", (e) => {
      if (!popup.contains(e.target) && !bellBtn.contains(e.target)) {
        popup.style.display = "none";
      }
    });
  }

  if (seeMoreLink && popup) {
    seeMoreLink.addEventListener("click", (e) => {
      e.preventDefault();
      popup.classList.toggle("expanded");
      seeMoreLink.textContent = popup.classList.contains("expanded")
        ? "Show less"
        : "+ See more";
    });
  }

  // =========================
  // SCROLL PARA A MENSAGEM
  // =========================
  const msg = document.getElementById("form-message");
  if (msg) {
    msg.scrollIntoView({
      behavior: "smooth",
      block: "center",
    });
    msg.style.transition = "opacity .4s ease";
    msg.style.opacity = "1";
  }

  // =========================
  // REDIRECT APÓS SUCESSO
  // =========================
  if (window.redirectAfterSuccess === true && window.itemId) {
    setTimeout(() => {
      window.location.href = `itempage.php?id=${window.itemId}`;
    }, 2000);
  }
});
