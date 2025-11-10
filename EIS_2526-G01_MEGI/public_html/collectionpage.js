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

// Define os dados associados a cada card de coleção
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
