document.addEventListener("DOMContentLoaded", () => {

    // ============================
    // MULTISELECT DROPDOWN
    // ============================
    const dropdownBtn = document.getElementById("dropdownBtn");
    const dropdown = document.getElementById("tagDropdown");

    dropdownBtn.addEventListener("click", () => {
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", (e) => {
        if (!dropdown.contains(e.target) && e.target !== dropdownBtn) {
            dropdown.style.display = "none";
        }
    });

    // ============================
    // MODAL PARA CRIAR TAGS
    // ============================
    const modal = document.getElementById("tagModal");
    const overlay = document.getElementById("tagModalOverlay");
    const openModal = document.getElementById("openTagModal");
    const closeModal = document.getElementById("closeTagModal");

    openModal.addEventListener("click", () => {
        modal.classList.remove("hidden");
        overlay.classList.remove("hidden");
        document.getElementById("newTagInput").value = "";
        document.getElementById("tagFeedback").textContent = "";
    });

    closeModal.addEventListener("click", () => {
        modal.classList.add("hidden");
        overlay.classList.add("hidden");
    });

    overlay.addEventListener("click", () => {
        modal.classList.add("hidden");
        overlay.classList.add("hidden");
    });

    // ============================
    // CRIAR TAG — AJAX
    // ============================
    const createBtn = document.getElementById("createTagBtn");
    const newTagInput = document.getElementById("newTagInput");
    const tagFeedback = document.getElementById("tagFeedback");

    createBtn.addEventListener("click", async () => {

        const tagName = newTagInput.value.trim();
        tagFeedback.textContent = "";

        if (tagName === "") {
            tagFeedback.textContent = "⚠ Escreva um nome.";
            tagFeedback.style.color = "#b54242";
            return;
        }

        let formData = new FormData();
        formData.append("tagName", tagName);

        const response = await fetch("create_tag.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (!result.success) {
            tagFeedback.textContent = "⚠ " + result.error;
            tagFeedback.style.color = "#b54242";
            return;
        }

        // Se já existia, marcar
        if (result.existing) {
            let existing = dropdown.querySelector(`input[value='${result.tag_id}']`);
            if (existing) {
                existing.checked = true;
            }
            tagFeedback.textContent = "Tag já existia — selecionada ✔";
            tagFeedback.style.color = "#2e7d32";
            return;
        }

        // Criar nova checkbox no dropdown
        let label = document.createElement("label");
        label.innerHTML = `
            <input type="checkbox" name="tags[]" value="${result.tag_id}" checked>
            ${result.name}
        `;
        dropdown.appendChild(label);

        tagFeedback.textContent = "Tag criada ✔";
        tagFeedback.style.color = "#2e7d32";

        newTagInput.value = "";
    });

    // ============================
    // REDIRECT APÓS SUCESSO
    // ============================
    const msg = document.getElementById("form-message");

    if (window.redirectAfterSuccess === true) {
        setTimeout(() => {
            window.location.href = `collectionpage.php?id=${window.collectionId}`;
        }, 2000);
    }
    
    const msg_anchor = document.getElementById("form-message");

    if (msg_anchor) {
        const anchor = document.getElementById("msg-anchor");

        if (anchor) {
            anchor.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });
        }
    }
});
