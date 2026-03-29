import initFormHandler from "../../modules/form/FormEngine";
import initTomSelect from "../../plugins/tomselect";
import initImagePreview from "../../plugins/imagePreview";
import initDocumentSearch from "../../plugins/documentSearch";

export default function initPersonForm() {
    initFormHandler({
        formSelector: "#mainForm",
        submitButtonSelector: "#btnSubmit",
        baseUrl: "/person",
    });

    initTomSelect();

    initImagePreview([
        {
            input: "#photo",
            preview: "#photoPreview",
        },
    ]);

    initDocumentSearch({
        inputSelector: "#identify_number",
        typeSelector: "#codtd_identify",
        btnSelector: "#btnDocument",
        onSuccess: (data) => {
            $("#firstname").val(data.firstname || "");
            $("#lastname_father").val(data.lastname_father || "");
            $("#lastname_mom").val(data.lastname_mom || "");
            $("#birthday").val(data.birthdate || "");
            $("#address").val(data.address || "");
            $("#codgender").val(data.gender || "");
            $("#civil_status").val(data.civil_status || "");
        },
    });
}
