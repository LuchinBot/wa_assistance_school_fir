import http from "../core/https";
import { showError, showSuccess } from "../core/alert";

export default function initDocumentSearch({
    inputSelector,
    typeSelector,
    btnSelector,
    onSuccess = () => {},
}) {
    const $btn = $(btnSelector);
    const $number = $(inputSelector);
    const $type = $(typeSelector);

    if (!$btn.length || !$number.length || !$type.length) return;

    $btn.on("click", async function (e) {
        e.preventDefault();

        const td = $type.find("option:selected").data("td");
        const number = $number.val().trim();

        if (!number) {
            showError("Ingrese un número de documento");
            return;
        }

        try {
            setLoading(true);
            const { data } = await http.get("/actions/person", {
                params: { td, identify_number: number },
            });
            if (!data.success) {
                showError(data.message || "No se encontró información");
                return;
            }

            // Llenar los campos vía callback
            onSuccess(data.data);

            showSuccess("Documento encontrado");
        } catch (error) {
            showError("Error consultando documento");
        } finally {
            setLoading(false);
        }
    });

    // búsqueda automática cuando DNI tenga 8 dígitos
    $number.on("keyup", function () {
        const value = $(this).val();
        if (value.length === 8) $btn.click();
    });

    function setLoading(state) {
        $btn.prop("disabled", state);
        $btn.text(state ? "Consultando..." : "Buscar");
    }
}
