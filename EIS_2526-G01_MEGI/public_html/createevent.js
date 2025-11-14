// =============================
// EVENT FORM HANDLING
// =============================
document.addEventListener("DOMContentLoaded", () => {

  const form = document.getElementById("eventForm");
  const summarySection = document.getElementById("eventSummarySection");
  const summaryContent = document.getElementById("eventSummaryContent");
  const editBtn = document.getElementById("editEvent");
  const finalConfirmBtn = document.getElementById("finalEventConfirm");

  const organizer = "Alex.Mendes147";

  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    // Campos
    const name = document.getElementById("eventName").value.trim();
    const date = document.getElementById("startDate").value.trim();
    const theme = document.getElementById("theme").value.trim();
    const location = document.getElementById("location").value.trim();
    const description = document.getElementById("description").value.trim();
    const tags = document.getElementById("tags").value.trim();
    const youtube = document.getElementById("youtube").value.trim();
    const imageFile = document.getElementById("coverImage").files[0];

    if (!name || !date || !theme || !location || !description || !tags || !imageFile) {
      alert("Please fill all required fields.");
      return;
    }

    // Lê imagem
    const reader = new FileReader();
    reader.onload = function (e) {
      const imageURL = e.target.result;

      // ====== RESUMO DO EVENTO ======
      summaryContent.innerHTML = `
        <div class="event-card">
          <div class="event-header">
            <img src="${imageURL}" class="event-cover" alt="Event image">

            <div class="event-info">
              <h3>${name}</h3>
              <p><strong>Creator:</strong> ${organizer}</p>
              <p><strong>Theme:</strong> ${theme}</p>
              <p><strong>Date:</strong> ${date}</p>
              <p><strong>Place:</strong> ${location}</p>
              <p><strong>Description:</strong> ${description}</p>
              <p><strong>Tags:</strong> ${tags}</p>
            </div>
          </div>
        </div>
      `;

      // Mostra resumo, esconde formulário
      form.classList.add("hidden");
      summarySection.classList.remove("hidden");
    };

    reader.readAsDataURL(imageFile);
  });

  // ===== Edit Event =====
  editBtn.addEventListener("click", () => {
    summarySection.classList.add("hidden");
    form.classList.remove("hidden");
  });

  // ===== CONFIRM FINAL — mensagem final =====
  finalConfirmBtn.addEventListener("click", () => {

    summarySection.innerHTML = `
      <h3>🎉 Event Created!</h3>
      <p>Your event has been successfully created. We look forward to seeing you there!</p>

      <div class="summary-actions">
        <a href="homepage.html" class="back-link">← Back to Home Page</a>
        <a href="eventpage.html" class="back-link">← See Event Page</a>
      </div>
    `;
  });

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


