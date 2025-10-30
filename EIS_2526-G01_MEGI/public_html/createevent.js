// =============================
// Notification Popup
// =============================
document.addEventListener("DOMContentLoaded", () => {
  const bellBtn = document.querySelector('.icon-btn[aria-label="Notificações"]');
  const popup = document.getElementById('notification-popup');

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

  // =============================
  // EVENT FORM HANDLING
  // =============================
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("eventForm");
  const formMessage = document.getElementById("formMessage");
  const content = document.querySelector(".content");

  const organizer = "Alex.Mendes147";

  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    // Campos
    const name = document.getElementById("eventName");
    const date = document.getElementById("startDate");
    const theme = document.getElementById("theme");
    const location = document.getElementById("location");
    const description = document.getElementById("description");
    const tags = document.getElementById("tags");
    const youtube = document.getElementById("youtube");
    const image = document.getElementById("coverImage");

    const fields = [name, date, theme, location, description, tags, image];
    fields.forEach((el) => el.classList.remove("error"));
    formMessage.textContent = "";
    formMessage.className = "form-message";

    let valid = true;

    // Validação
    fields.forEach((field) => {
      if (!field.value.trim() && field.type !== "file") {
        field.classList.add("error");
        valid = false;
      }
    });

    const selectedDate = new Date(date.value);
    const currentDate = new Date();
    currentDate.setHours(0, 0, 0, 0);
    selectedDate.setHours(0, 0, 0, 0);

    if (selectedDate < currentDate) {
      date.classList.add("error");
      valid = false;
    }

    if (!image.files[0]) {
      image.classList.add("error");
      valid = false;
    }

    if (!valid) {
      formMessage.textContent = "⚠️ Please fill in all required (*) fields correctly.";
      formMessage.classList.add("error");
      return;
    }

    // Ler imagem e gerar sumário
    const reader = new FileReader();
    reader.onload = function (e) {
      const imageUrl = e.target.result;

      const summaryBox = document.createElement("div");
      summaryBox.className = "event-card";

      // Construir embed YouTube (opcional)
      let youtubeEmbed = "";
      if (youtube.value.includes("youtube.com") || youtube.value.includes("youtu.be")) {
        let videoId = "";
        if (youtube.value.includes("embed/")) {
          videoId = youtube.value.split("embed/")[1];
        } else if (youtube.value.includes("watch?v=")) {
          videoId = youtube.value.split("watch?v=")[1];
        } else if (youtube.value.includes("youtu.be/")) {
          videoId = youtube.value.split("youtu.be/")[1];
        }
        videoId = videoId.split("&")[0];
        youtubeEmbed = `<div class="youtube-embed"><iframe src="https://www.youtube.com/embed/${videoId}" allowfullscreen></iframe></div>`;
      }

      // Sumário visual
      summaryBox.innerHTML = `
        <div class="event-header">
          <img src="${imageUrl}" alt="${name.value}" class="event-cover" />
          <div class="event-info">
            <h3>${name.value}</h3>
            <p><strong>Creator:</strong> ${organizer}</p>
            <p><strong>Theme:</strong> ${theme.value}</p>
            <p><strong>Date:</strong> ${date.value}</p>
            <p><strong>Place:</strong> ${location.value}</p>
            <p><strong>Description:</strong> ${description.value}</p>
            <p class="event-tags"><strong>Tags:</strong> ${tags.value}</p>
          </div>
        </div>
        ${youtubeEmbed}
        <div class="form-actions" style="justify-content: center; gap: 1rem; margin-top: 1rem;">
          <button id="editBtn" class="btn-primary" type="button">Edit</button>
          <button id="confirmBtn" class="btn-primary" type="button">Confirm and Create</button>
        </div>
      `;

      // Apagar sumários anteriores
      document.querySelectorAll(".event-card").forEach((el) => el.remove());
      formMessage.textContent = "";
      content.appendChild(summaryBox);

      // Ocultar o formulário
      form.style.display = "none";

      // Botão Editar → mostra formulário de novo
      document.getElementById("editBtn").addEventListener("click", () => {
        summaryBox.remove();
        form.style.display = "block";
      });

      // Botão Confirmar → mostra cartão final
      document.getElementById("confirmBtn").addEventListener("click", () => {
        form.reset();
        summaryBox.querySelector(".form-actions").remove(); // remove botões
        formMessage.textContent = "✅ Event created successfully!";
        formMessage.classList.add("success");
        form.style.display = "block";
      });
    };

    reader.readAsDataURL(image.files[0]);
  });
});

});




