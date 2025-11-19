/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/ClientSide/javascript.js to edit this template
 */

// Aguarda até que todo o conteúdo da página esteja carregado
document.addEventListener("DOMContentLoaded", () => {
  const popup = document.getElementById("hover-popup"); 


// Define os dados associados a cada card de coleção
  const collectionData = {
    "price-card": {
      collection: 'Pokemon Cards',
      user: 'Rafael_Ameida123',
      title: '1st Edition Machamp',
      price: '2000€',
      date: '03/10/2025',
      place: 'Comic Con 2025',
      items: '51',
      image: 'images/1st Edition Machamp.png',
      updated: '27/03/2025'
    },
    "recent-card": {
      collection: "Pokemon Champion's Path",
      user: 'Paul_Perez1697',  
      title: 'Charizard V',
      price: '300,25€',
      date: '29/10/2025',
      place: 'Local vintage store',
      items: '14',
      image: 'images/CharizardV.png',
      updated: '31/10/2025'
    },
    "items-card": {
      collection: 'Funko Pop',
      user: 'Ana_SSilva7812',
      title: 'Friends: Joey w/ Pizza',
      price: '20€',
      date: '17/05/2025',
      place: 'Online shop',
      items: '152',
      image: 'images/joeyfunko.png',
      updated: '01/10/2025'
    }
  };


// Para cada card da Top Collections mostra e atualiza o conteúdo do popup ao mover o rato sobre o card
  document.querySelectorAll(".top-collection-block").forEach(block => {
    block.addEventListener("mousemove", e => {
      const id = block.getAttribute("data-id");
      const data = collectionData[id];
      if (!data) return;

      // Gera o conteúdo do popup com base nos dados da coleção
      popup.innerHTML = `
  <div class="popup-content-flex">
            
    <!-- Texto à esquerda -->
    <div class="popup-text">
      <h3 class="popup-title">${data.collection}</h3>
      <p class="popup-user">${data.user}</p>

      <p><strong>Price:</strong> ${data.price}</p>
      <p><strong>Acquisition Date:</strong> ${data.date}</p>
      <p><strong>Acquisition Place:</strong> ${data.place}</p>
      <p><strong>Items:</strong> ${data.items}</p>
    </div>

    <!-- Imagem + nome do produto + data -->
    <div class="popup-image">
      <h4 class="popup-subtitle">${data.title}</h4>
      <img src="${data.image}" alt="${data.title}">
      <p class="popup-date">Last updated: ${data.updated}</p>
    </div>
  </div>
`;


      // Posiciona o popup próximo ao cursor do rato
      popup.style.left = e.pageX + 20 + "px";
      popup.style.top = e.pageY + 20 + "px";
      popup.classList.add("active");
    });
      // Esconde o popup quando o rato sai do card
      block.addEventListener("mouseleave", () => {
      popup.classList.remove("active");
    });
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
