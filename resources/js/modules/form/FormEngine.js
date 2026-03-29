import http from "../../core/https.js";
import { showSuccess, showError } from "../../core/alert";
export default function initFormHandler(options) {
    const {
        formSelector,
        submitButtonSelector,
        baseUrl,
        redirectUrl = null,
        onSuccess = null,
        onError = null,
    } = options;

    const $form = $(formSelector);
    const $button = $(submitButtonSelector);

    if (!$form.length) return;

    $form.on("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const recordId = $form.find("[id='recordId']").val();
        const prefix = $form.find("[id='prefix']").val() ?? "store";

        const url = recordId
            ? `${baseUrl}/${prefix}/${recordId}`
            : `${baseUrl}/${prefix}`;

        try {
            setLoading(true);

            const { data } = await http.post(url, formData);

            if (data.success) {
                showSuccess(data.message || "Guardado correctamente");

                if (onSuccess) {
                    onSuccess(data);
                } else {
                    setTimeout(() => {
                        window.location.href =
                            data.redirect || redirectUrl || `${baseUrl}/list`;
                    }, 1200);
                }
            } else {
                showError(data.message || "Error al guardar");
            }
        } catch (error) {
            if (error.response?.status === 422) {
                handleValidationErrors(error.response.data.errors);
            }

            if (onError) onError(error);
        } finally {
            setLoading(false);
        }
    });

    function setLoading(state) {
        $button.prop("disabled", state);
        $button.text(state ? "Guardando..." : "Guardar");
    }

    function handleValidationErrors(errors) {
        $(".error-message").addClass("hidden").text("");

        Object.keys(errors).forEach((field) => {
            const span = $(`.error-message[data-error-for="${field}"]`);
            if (span.length) {
                span.text(errors[field][0]).removeClass("hidden");
            }
        });

        showError("Corrige los errores del formulario");
    }
}
