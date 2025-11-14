const btn = document.querySelector(".edit-btn");

btn.addEventListener("click", () => {
  if (btn.dataset.state !== "added") {
    btn.innerHTML = "✔️ Friend Added";
    btn.style.backgroundColor = "#4CAF50";
    btn.style.color = "white";
    btn.dataset.state = "added";
  } else {
    btn.innerHTML = "👥 Add Friend";
    btn.style.backgroundColor = "#ffffff";
    btn.style.color = "#000000";
    btn.dataset.state = "default";
  }
});
