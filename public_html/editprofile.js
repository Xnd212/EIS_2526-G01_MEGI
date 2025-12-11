/* ============================================================
   FOTO DE PERFIL — PREVIEW IMEDIATO
============================================================ */
const photoInput = document.getElementById("profilePhoto");
const photoPreview = document.getElementById("profilePreview");

if (photoInput) {
    photoInput.addEventListener("change", e => {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert("File too large! Max size: 2MB");
            photoInput.value = "";
            return;
        }

        const reader = new FileReader();
        reader.onload = event => {
            photoPreview.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
}

/* ============================================================
   DROPDOWN DAS COLEÇÕES FAVORITAS
============================================================ */
const dropdownBtn = document.getElementById("dropdownBtn");
const dropdownContent = document.getElementById("dropdownContent");
const counterEl = document.getElementById("collectionCounter");

if (dropdownBtn && dropdownContent) {

    // Abrir / Fechar dropdown
    dropdownBtn.addEventListener("click", () => {
        dropdownContent.style.display =
            dropdownContent.style.display === "block" ? "none" : "block";
    });

    // Fechar ao clicar fora
    document.addEventListener("click", (e) => {
        if (!dropdownContent.contains(e.target) && e.target !== dropdownBtn) {
            dropdownContent.style.display = "none";
        }
    });

    // Atualizar texto do botão + contador
    dropdownContent.addEventListener("change", () => {
        const checked = [...dropdownContent.querySelectorAll("input[type='checkbox']:checked")];

        // Atualizar texto do botão
        if (checked.length > 0) {
            dropdownBtn.textContent = checked
                .map(c => c.dataset.name)
                .join(", ");
        } else {
            dropdownBtn.textContent = "Select collections ⮟";
        }

        // Atualizar contador
        counterEl.textContent = `${checked.length} / 5 selected`;

        // Aplicar limite
        if (checked.length > 5) {
            alert("You can select up to 5 favourite collections.");
            checked[checked.length - 1].checked = false;
            counterEl.textContent = `${checked.length - 1} / 5 selected`;
        }
    });
}
