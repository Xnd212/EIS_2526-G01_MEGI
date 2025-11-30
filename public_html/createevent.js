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

  // Only prevent default if showing preview, otherwise let PHP handle it
  form.addEventListener("submit", (e) => {
    // Campos
    const name = document.getElementById("eventName").value.trim();
    const date = document.getElementById("startDate").value.trim();
    const theme = document.getElementById("theme").value.trim();
    const location = document.getElementById("location").value.trim();
    const description = document.getElementById("description").value.trim();
    const tags = document.getElementById("tags").value.trim();
    const imageFile = document.getElementById("coverImage").files[0];

    if (!name || !date || !theme || !location || !description || !tags || !imageFile) {
      e.preventDefault();
      alert("Please fill all required fields.");
      return;
    }

    // Let the form submit normally to PHP - remove e.preventDefault()
    // The form will now POST to the server and create the event
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