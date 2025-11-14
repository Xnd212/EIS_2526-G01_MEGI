// Aguarda até que todo o conteúdo da página esteja carregado
document.addEventListener("DOMContentLoaded", () => {
  const popup = document.getElementById("hover-popup"); 

// Define os dados associados a cada item
  const itemData = {
    "charizard": {
      item: "Champion's Path Charizard V (PSA 10)",
      price: '950€',
      importance: "High",
      item_type: "Card",
      date: '29/10/2025',
      place: 'Comic Con 2025',
      image: 'images/CharizardV.png'
    },
    "machamp": {
      item: "1st Edition Machamp",
      price: '2000€',
      importance: "High",
      item_type: "Card",
      date: '03/10/2025',
      place: 'Comic Con 2025',
      image: 'images/1st Edition Machamp.png'
    },
    "charizard_1st": {
      item: "1st Edition Charizard",
      price: '651,17€',
      importance: "High",
      item_type: "Card",
      date: '03/10/2025',
      place: 'Comic Con 2025',
      image: 'images/3.png'
    }
  };
  
// Para cada card dos Items mostra o conteúdo do popup ao mover o rato sobre o card
  document.querySelectorAll(".item-card").forEach(block => {
    block.addEventListener("mousemove", e => {
      const id = block.getAttribute("data-id");
      const data = itemData[id];
      if (!data) return;

      // Gera o conteúdo do popup com base nos dados da coleção
      popup.innerHTML = `
  <div class="popup-content-flex">
            
    <!-- Texto à esquerda -->
    <div class="popup-text">
      <h3 class="popup-title">${data.item}</h3>
      <p><strong>Price:</strong> ${data.price}</p>
      <p><strong>Importance:</strong> ${data.importance}</p>
      <p><strong>Item Type:</strong> ${data.item_type}</p>
      <p><strong>Acquisition Date:</strong> ${data.date}</p>
      <p><strong>Acquisition Place:</strong> ${data.place}</p>
    </div>

    <!-- Imagem + nome do produto + data -->
    <div class="popup-image">
      <img src="${data.image}" alt="${data.item}">
    </div>
  </div>
`;


      // Posiciona o popup próximo ao cursor do rato
      popup.style.left = e.pageX + 20 + "px";
      popup.style.top = e.pageY - 225 + "px";
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
