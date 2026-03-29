import initFormHandler from "../../modules/form/FormEngine";
import initTomSelect from "../../plugins/tomselect";
export default function initPersonForm() {
    initFormHandler({
        formSelector: "#mainForm",
        submitButtonSelector: "#btnSubmit",
        baseUrl: "/schedule",
    });

    initTomSelect(".tom-select");
}
