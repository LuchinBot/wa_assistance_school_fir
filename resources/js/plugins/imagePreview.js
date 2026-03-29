import { showError, showSuccess } from "../core/alert";
export default function initImagePreview(configs = []) {
    configs.forEach((config) => {
        const input = document.querySelector(config.input);
        const preview = document.querySelector(config.preview);

        if (!input || !preview) return;

        input.addEventListener("change", function () {
            const file = this.files[0];
            if (!file) return;

            const allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
            const maxSize = 2 * 1024 * 1024;

            if (!allowedTypes.includes(file.type)) {
                showError("Solo se permiten imágenes JPG o PNG");
                input.value = "";
                return;
            }

            if (file.size > maxSize) {
                showError("La imagen no debe superar los 2MB");
                input.value = "";
                return;
            }

            const reader = new FileReader();

            reader.onload = (e) => {
                preview.innerHTML = `
                    <img src="${e.target.result}" 
                         class="w-full h-full object-cover">
                `;
            };

            reader.readAsDataURL(file);
        });
    });
}
