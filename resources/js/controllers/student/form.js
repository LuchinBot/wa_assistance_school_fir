import initFormHandler from "../../modules/form/FormEngine";
import initTomSelect from "../../plugins/tomselect";

export default function initStudentForm() {
    initFormHandler({
        formSelector: "#mainForm",
        submitButtonSelector: "#btnSubmit",
        baseUrl: "/student",
    });

    initTomSelect();
}
