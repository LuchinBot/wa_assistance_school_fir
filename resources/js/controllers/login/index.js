import axios from "axios";
import { showSuccess, showError } from "../../core/alert";

/**
 * UI Utilities — Login Page
 */
const UI = {
    resetErrors() {
        $("input").removeClass("border-red-600 ring-4 ring-red-600/10");
        $("[id^='alert-']").fadeOut(300);
    },

    showInputError(field, message) {
        $(`#${field}`)
            .addClass("border-red-600 ring-4 ring-red-600/10")
            .attr("aria-invalid", "true");

        $(`#alert-${field}`)
            .stop(true)
            .fadeIn(300)
            .text(message)
            .attr("role", "alert");
    },

    setButtonLoading($btn, isLoading, originalHTML) {
        const spinnerHTML = `
            <div class="w-4 h-4 rounded-full border-2 border-white/30 border-t-white animate-spin"></div>
            Verificando...
        `;
        $btn.prop("disabled", isLoading).html(
            isLoading ? spinnerHTML : originalHTML,
        );
    },
};

/**
 * Toggle password visibility
 */
function initTogglePassword() {
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");
    const eyeIcon = document.getElementById("eyeIcon");

    togglePassword?.addEventListener("click", () => {
        const isVisible = passwordInput.type === "text";
        passwordInput.type = isVisible ? "password" : "text";
        eyeIcon.textContent = isVisible ? "visibility" : "visibility_off";
    });
}

/**
 * Validación del formulario de login
 */
function validateLoginForm() {
    let isValid = true;

    const fields = [
        { id: "username", message: "El nombre de usuario es obligatorio" },
        { id: "password", message: "La contraseña es obligatoria" },
    ];

    fields.forEach(({ id, message }) => {
        if (!$(`#${id}`).val().trim()) {
            UI.showInputError(id, message);
            isValid = false;
        }
    });

    return isValid;
}

/**
 * Init login
 */
export default function initLogin() {
    initTogglePassword();

    // Limpiar errores al escribir
    $("input").on("input", function () {
        const id = $(this).attr("id");
        $(this)
            .removeClass("border-red-600 ring-4 ring-red-600/10")
            .removeAttr("aria-invalid");
        $(`#alert-${id}`).fadeOut(300);
    });

    $("#btnSubmit").on("click", async function (e) {
        e.preventDefault(); // ✅ ya lo tienes
        e.stopPropagation(); // 👈 agrega esto también desde el inicio
        e.stopImmediatePropagation(); // 👈 y esto
        UI.resetErrors();

        if (!validateLoginForm()) return;

        const $btn = $(this);
        const originalHTML = $btn.html();

        UI.setButtonLoading($btn, true, originalHTML);
        try {
            const { data } = await axios.post(
                route("login.post"),
                new FormData($(".form")[0]),
            );

            if (data.code === 200) {
                showSuccess("Bienvenido, redirigiendo...");
                setTimeout(() => {
                    window.location.href = route(data.redirect); // 👈 route()
                }, 800);
                return;
            }

            if (data.code === 423) {
                showError(data.msg ?? "Cuenta bloqueada temporalmente.");
                return; // 👈 sin redirección, solo mostrar el error
            }

            if (data.code === 403) {
                showError(data.msg ?? "Usuario inactivo.");
                return;
            }

            if (data.code === 401) {
                showError(data.msg ?? "Credenciales incorrectas.");
                return;
            }

            showError(data.msg ?? "Error inesperado.");
        } catch (err) {
            // solo errores de red reales
            showError("Error de conexión. Intenta de nuevo.");
        } finally {
            UI.setButtonLoading($btn, false, originalHTML);
        }
    });
}
