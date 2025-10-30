
// event.js

document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("event-details");

  const eventData = {
    comiccon: {
      title: "Comic Con Portugal",
      creator: "Alex.Mendes147",
      theme: "Card",
      date: "03/10/2025",
      place: "EXPONOR – Porto",
      
      description: "The biggest pop culture event in Portugal.",
      tags: ["Pokemon", "Cards", "Anime", "TCG"],
      image: "images/comiccon.png",
      video: "https://www.youtube.com/embed/6mw8rvBWbYE?si=ZelwmH-iaCxQ7H4C",
      collections: [
        { name: "Pokemon Cards", user: "Rafael_Ameida123", logo: "images/pokémon_logo.png" },
        { name: "Pokemon Champion's Path", user: "Andre_SS123", logo: "images/championspath.png" }
      ]
    },
    amadoraBD: {
      title: "Amadora BD",
      creator: "Bruno.Costa76",
      theme: "Comics",
      date: "23/10/2025",
      place: "R. Carvalho Araújo 105, Damaia, 2720-087 Amadora",
  
      description: "National comic book festival featuring artists and publishers.",
      tags: ["Comics", "Drawing", "Exhibitions"],
      image: "images/amadoraBD.png",
      video: "https://www.youtube.com/embed/0uRyDYmtcXc?si=GUY9qmrNhgIwOmTj",
      collections: [
          { name: "Superhero comics", user: "David.Ramos_2222", logo: "images/bdsuperherois.png" }
      ]
    },
    iberanime: {
      title: "Iberanime Porto",
      creator: "Sara.Takahashi92",
      theme: "Anime & Cosplay",
      date: "02/11/2025",
      place: "EXPONOR – Porto",
      
      description: "The ultimate anime convention in Portugal.",
      tags: ["Anime", "Cosplay", "Gaming"],
      image: "images/iberanime.png",
      video: "https://www.youtube.com/embed/32r5ZUGvmu0?si=ITB522nLQ9Y2VsSG",
      collections: [{ name: "Pokemon Champion's Path", user: "Andre_SS123", logo: "images/championspath.png" },
                    { name: "Funko Pop", user: "Rafa_Silva147", logo: "images/funko3.png" }]
    },
    lisbon: {
      title: "Lisbon Games Week",
      creator: "João.Gamer998",
      theme: "Gaming",
      date: "20/11/2025",
      place: "Rua do Bojador, 1998-010 Lisboa, Portugal",
      
      description: "Portugal’s biggest video game expo.",
      tags: ["Games", "Esports", "Tech"],
      image: "images/lisbon.png",
      video: "https://www.youtube.com/embed/9izfyvxz5mY?si=eYeEsVpSPE-5BSWH",
      collections: [{ name: "Funko Pop", user: "Rafa_Silva147", logo: "images/Funko.png" }]
    },
    cardmadness: {
      title: "Cardmadness 2026",
      creator: "Inês.Moura02",
      theme: "TCG",
      date: "15/01/2026",
      place: "Siegburger Str. 15, 40591 Düsseldorf, Germany",
      
      description: "Europe’s largest trading card convention.",
      tags: ["Cards", "Trading", "Exclusives"],
      image: "images/cardmadness.png",
      video: "https://www.youtube.com/embed/EZ01xr-_Gug?si=8ZXLiEmLEelusHu2",
      collections: [{ name: "Pokemon Cards", user: "Rafael_Ameida123", logo: "images/pokémon_logo.png" },
        { name: "Pokemon Champion's Path", user: "Andre_SS123", logo: "images/championspath.png" }]
    }
  };

  const urlParams = new URLSearchParams(window.location.search);
  const eventKey = urlParams.get("event");
  const event = eventData[eventKey];

  if (!event) {
    container.innerHTML = "<p>Evento não encontrado.</p>";
    return;
  }


  // HTML gerado
container.innerHTML = `
  <h2>${event.title}</h2>

  <!-- DETALHES + TEASER -->
  <div class="event-teaser-wrapper">
    <div class="event-details-content">
      <img src="${event.image}" alt="${event.title}" style="height: 200px;">
      <div class="event-info">
        <p><strong>Creator:</strong> ${event.creator}</p>
        <p><strong>Theme:</strong> ${event.theme}</p>
        <p><strong>Date:</strong> ${event.date}</p>
        <p><strong>Place:</strong> ${event.place}</p>
        <p><strong>Description:</strong> ${event.description}</p>
        <p><strong>Tags:</strong> ${event.tags.join(", ")}</p>
      </div>
    </div>

    ${event.video ? `
      <div class="video-thumbnail">
        <a href="${event.video.replace('/embed/', '/watch?v=')}" target="_blank">
          <img src="https://img.youtube.com/vi/${event.video.split('/embed/')[1].split('?')[0]}/hqdefault.jpg" alt="Video Teaser">
          <div class="play-button">▶</div>
        </a>
      </div>
    ` : ""}
  </div>

<!-- COLEÇÕES -->
    ${event.collections.length > 0 ? `
      <h3>Collections others are bringing:</h3>
      <div class="collections-brought">
        ${event.collections.map(col => `
          <div class="collection-bring">
            <img src="${col.logo}" alt="${col.name}" >
            <p class="collection-name"><strong>${col.name}</strong></p>
            <p class="collection-user">${col.user}</p>
          </div>
        `).join("")}
      </div>
` : ""}


  <!-- MAPA -->
  <h3 class="map-title"> Where to find us:</h3>
  <div class="map-container">
    <iframe
      src="https://www.google.com/maps?q=${encodeURIComponent(event.place.trim())}&output=embed"
      allowfullscreen
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade">
    </iframe>
  </div>

  <!-- BOTÃO DE INSCRIÇÃO -->
  <div class="register-section">
    <div class="register-row">
      <p class="register-text">🎟️ Want to join? Sign up now!</p>
      <a href="sign_up_event.html" class="register-button">Sign up</a>
    </div>
  </div>
`;

});


// =============================
// Notificações 
// =============================

document.addEventListener("DOMContentLoaded", () => {
  const bellBtn = document.querySelector('.icon-btn[aria-label="Notificações"]');
  const popup = document.getElementById('notification-popup');

  if (bellBtn && popup) {
    bellBtn.addEventListener('click', (e) => {
      e.stopPropagation(); // Não fecha imediatamente se clicado
      popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', (e) => {
      if (!popup.contains(e.target) && !bellBtn.contains(e.target)) {
        popup.style.display = 'none';
      }
    });
  }
});