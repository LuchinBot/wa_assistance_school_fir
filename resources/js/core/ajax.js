import $ from "jquery";

export function submitForm(url, form, button, successCallback) {
    const formData = new FormData(form);
    const $button = $(button);
    const originalText = $button.html();

    $.ajax({
        url,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: successCallback,
        error: function (xhr) {
            $button.prop("disabled", false).html(originalText);

            if (xhr.status === 422) {
                console.log(xhr.responseJSON.errors);
            } else {
                console.error("Unexpected error");
            }
        },
    });
}

export default function initAjax() {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
    });

    console.log("AJAX initialized");
}
