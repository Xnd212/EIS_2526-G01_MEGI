const btn = document.querySelector(".edit-btn");

if (btn) {
  btn.addEventListener("click", () => {
    if (btn.dataset.state === "default") {
      // Vai seguir → muda para Member of your bubble
      btn.textContent = "✔ Member of your bubble";
      btn.dataset.state = "added";
      btn.classList.add("active");
    } else if (btn.dataset.state === "added") {
      // Vai deixar de seguir → muda para Add to bubble
      btn.textContent = "👥 Add to bubble";
      btn.dataset.state = "default";
      btn.classList.remove("active");
    }
    // O link continua a ir para add_friend.php / remove_friend.php normalmente
  });
}
