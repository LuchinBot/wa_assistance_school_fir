/**
 * Sistema de gestión de formularios CRUD
 * Compatible con cualquier módulo
 */

// Configuración
const CONFIG = {
    baseUrl: `/${controller}`,
    baseUrlActions: `/actions`,
    maxFileSize: 2 * 1024 * 1024, // 2MB
    allowedImageTypes: ["image/jpeg", "image/jpg", "image/png"],
};

// Estado del formulario
const STATE = {
    isSubmitting: false,
    hasChanges: false,
    originalData: {},
};

// Variables de elementos DOM
let mainForm,
    btnSubmit,
    btnSubmitText,
    alertContainer,
    photoInput,
    photoPreview;
let departmentSelect, provinceSelect, districtSelect, codubigeoInput;
let btnDocument;

/**
 * Inicialización
 */
$(document).ready(function () {
    initializeElements();
    setupEventListeners();
    captureOriginalData();
    setupUnsavedChangesWarning();
});

/**
 * Inicializar referencias a elementos DOM
 */
function initializeElements() {
    mainForm = $("#mainForm");
    btnSubmit = $("#btnSubmit");
    btnSubmitText = $("#btnSubmitText");
    alertContainer = $("#alertContainer");
    photoInput = $("#photo");
    photoPreview = $("#photoPreview");

    // Elementos de ubigeo
    departmentSelect = $("#coddepartment");
    provinceSelect = $("#codprovince");
    districtSelect = $("#coddistrict");
    codubigeoInput = $("#codubigeo");

    // Consultas extra
    btnDocument = $("#btnDocument");
}

/**
 * Configurar event listeners
 */
function setupEventListeners() {
    // Submit del formulario
    mainForm.on("submit", function (e) {
        e.preventDefault();
        handleSubmit();
    });

    // Detectar cambios en el formulario
    mainForm.find("input, select, textarea").on("change input", function () {
        STATE.hasChanges = true;
    });

    // Validación en tiempo real
    mainForm
        .find("input[required], select[required], textarea[required]")
        .on("blur", function () {
            validateField($(this));
        });
}

/**
 * Capturar datos originales del formulario
 */
function captureOriginalData() {
    mainForm.find("input, select, textarea").each(function () {
        const field = $(this);
        STATE.originalData[field.attr("name")] = field.val();
    });
}

/**
 * Configurar advertencia de cambios no guardados
 */
function setupUnsavedChangesWarning() {
    $(window).on("beforeunload", function (e) {
        if (STATE.hasChanges && !STATE.isSubmitting) {
            e.preventDefault();
            return "Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?";
        }
    });
}

/**
 * Manejar envío del formulario
 */
function handleSubmit() {
    if (STATE.isSubmitting) return;

    // Preparar datos
    const formData = new FormData(mainForm[0]);
    const recordId = $("#recordId").val();
    const url = `${CONFIG.baseUrl}/change_password/${recordId}`;

    // Mostrar loading
    setSubmitButton(true);
    STATE.isSubmitting = true;
    console.log(formData);
    // Enviar datos
    $.ajax({
        url: url,
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.success) {
                STATE.hasChanges = false;
                showSuccess(
                    response.message || "Registro guardado correctamente",
                );

                // Redirigir después de 1.5 segundos
                setTimeout(() => {
                    window.location.href = response.redirect || CONFIG.baseUrl;
                }, 1500);
            } else {
                showError(response.message || "Error al guardar el registro");
            }
        },
        error: function (xhr) {
            handleAjaxError(xhr);
        },
        complete: function () {
            STATE.isSubmitting = false;
            setSubmitButton(false);
        },
    });
}

/**
 * Mostrar/ocultar loading en botón submit
 */
function setSubmitButton(loading) {
    if (loading) {
        btnSubmit.prop("disabled", true);
        btnSubmitText.text("Guardando...");
        btnSubmit.prepend(`
            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `);
    } else {
        btnSubmit.prop("disabled", false);
        btnSubmit.find("svg.animate-spin").remove();
        const recordId = $("#recordId").val();
        btnSubmitText.text(recordId ? "Actualizar" : "Guardar");
    }
}

const AlertService = {
    container: $("#alertContainer"),

    // Configuración de tipos (Colores, Iconos, Animaciones)
    types: {
        success: {
            class: "bg-emerald-50 border-emerald-400 text-emerald-800",
            icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            animate: "animate-slide-in",
        },
        error: {
            class: "bg-rose-50 border-rose-400 text-rose-800",
            icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            animate: "animate-shake",
        },
        waiting: {
            class: "bg-amber-50 border-amber-400 text-amber-800",
            icon: '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>',
            animate: "",
        },
    },

    show(type, message, duration = 4000) {
        const config = this.types[type];
        const isWaiting = type === "waiting";

        const html = `
            <div class="alert-item ${config.class} ${config.animate} border-l-4 p-4 mb-4 rounded-xl shadow-xl flex items-center transition-all duration-300 transform" role="alert">
                <svg class="w-6 h-6 mr-3 flex-shrink-0 ${isWaiting ? "animate-spin" : ""}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${config.icon}
                </svg>
                <div class="flex-1 font-semibold text-sm md:text-base">${message}</div>
                ${
                    !isWaiting
                        ? `
                    <button class="close-alert ml-auto pl-3 hover:opacity-70 transition-opacity">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>`
                        : ""
                }
            </div>
        `;

        const $alert = $(html);
        this.container.append($alert); // .append en lugar de .html permite múltiples alertas si lo deseas

        // Auto-scroll suave
        window.scrollTo({ top: 0, behavior: "smooth" });

        // Evento cerrar
        $alert.find(".close-alert").on("click", () => this.hide($alert));

        // Auto-ocultar (excepto si es 'waiting')
        if (!isWaiting && duration > 0) {
            setTimeout(() => this.hide($alert), duration);
        }
    },

    hide($el) {
        $el.addClass("opacity-0 translate-y-[-20px]");
        setTimeout(() => $el.remove(), 300);
    },
};

// Aliases para mantener compatibilidad con tu código actual
const showSuccess = (msg) => AlertService.show("success", msg);
const showError = (msg) => AlertService.show("error", msg);
const showWaiting = (msg) => AlertService.show("waiting", msg);
const hideError = () =>
    AlertService.hide(AlertService.container.find(".alert-item"));

function handleAjaxError(xhr) {
    if (xhr.status === 422) {
        const errors = xhr.responseJSON.errors;

        // Oculta todos los errores primero
        $(".error-message").addClass("hidden").text("");

        $.each(errors, function (field, messages) {
            const message = messages[0];

            const errorSpan = $(`.error-message[data-error-for="${field}"]`);

            if (errorSpan.length) {
                errorSpan
                    .text(message)
                    .removeClass("hidden")
                    .addClass("animate-pulse");

                // ⏱️ Ocultar automáticamente
                setTimeout(() => {
                    errorSpan.addClass("hidden").text("");
                }, 4000); // 4 segundos
            }
        });

        showError("Por favor, corrige los errores.");

        // Ocultar el mensaje general también
        setTimeout(() => {
            hideError();
        }, 4000);
    } else {
        let message = "Ha ocurrido un error inesperado";

        if (xhr.responseJSON?.message) message = xhr.responseJSON.message;
        else if (xhr.status === 404) message = "Recurso no encontrado";
        else if (xhr.status === 500) message = "Error del servidor";
        else if (xhr.status === 403)
            message = "No tienes permisos para realizar esta acción";

        showError(message);

        setTimeout(() => {
            hideError();
        }, 4000);
    }
}

/**
 * Scroll al inicio de la página
 */
function scrollToTop() {
    $("html, body").animate(
        {
            scrollTop: alertContainer.offset().top - 100,
        },
        300,
    );
}

/**
 * Resetear formulario
 */
function resetForm() {
    mainForm[0].reset();
    clearErrors();
    photoPreview.html(`
        <div class="w-full h-full flex items-center justify-center">
            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
    `);
    STATE.hasChanges = false;
}

/**
 * Scroll al inicio de la página
 */
function scrollToTop() {
    $("html, body").animate(
        {
            scrollTop: alertContainer.offset().top - 100,
        },
        300,
    );
}

// VER CONTRASEÑA
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const svg = button.querySelector("svg");

    if (input.type === "password") {
        input.type = "text";
        // Icono de "Ojo tachado" (Ver ocultar)
        svg.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
        `;
    } else {
        input.type = "password";
        // Icono de "Ojo normal"
        svg.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;
    }
}
