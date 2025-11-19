// === APLICA O TEMA GUARDADO EM TODAS AS PÁGINAS ===
document.addEventListener("DOMContentLoaded", () => {
  const userData = JSON.parse(localStorage.getItem("tralle_userProfile")) || {};

  // ✅ Se o utilizador escolheu um tema, aplica-o
  if (userData.theme === "dark") {
    document.body.setAttribute("data-theme", "dark");
  } else {
    // ✅ Caso contrário, mantém sempre o tema claro
    document.body.setAttribute("data-theme", "light");
  }
});


