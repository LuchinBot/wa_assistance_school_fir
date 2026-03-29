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
    initializeUbigeo();
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
    firmaInput = $("#firma");
    photoPreview = $("#photoPreview");
    firmaPreview = $("#firmaPreview");

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

    // Preview de foto
    photoInput.on("change", function () {
        handlePhotoPreview(this);
    });

    // Preview de foto
    firmaInput.on("change", function () {
        handleFirmaPreview(this);
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

    // Selects en cascada para ubigeo
    departmentSelect.on("change", function () {
        const coddepartment = $(this).val();
        loadProvinces(coddepartment);
    });

    provinceSelect.on("change", function () {
        const coddepartment = departmentSelect.val();
        const codprovince = $(this).val();
        loadDistricts(coddepartment, codprovince);
    });

    districtSelect.on("change", function () {
        const selectedOption = $(this).find(":selected");
        const ubigeo = selectedOption.data("ubigeo");
        codubigeoInput.val(ubigeo || "");
    });

    btnDocument.on("click", function (e) {
        e.preventDefault();
        const url = `${CONFIG.baseUrlActions}/person`;
        const td = $("#codtd_identify option:selected").data("td");
        const identifyNumber = $("#identify_number").val().trim();
        if (!identifyNumber) {
            showError("Por favor, ingresa un documento.");
            return;
        }
        // Mostrar un mensaje de carga
        hideError();
        showWaiting("Consultando documento...");

        $.ajax({
            url: url,
            method: "GET",
            data: { td: td, identify_number: identifyNumber },
            success: function (response) {
                if (response.success) {
                    hideError();
                    showSuccess(
                        response.message ||
                            "Documento consultado correctamente",
                    );

                    // Rellenar campos del formulario
                    $("#firstname").val(response.data.firstname || "");
                    $("#lastname_father").val(
                        response.data.lastname_father || "",
                    );
                    $("#lastname_mom").val(response.data.lastname_mom || "");
                    $("#birthday").val(response.data.birthdate || "");
                    $("#address").val(response.data.address || "");
                    $("#codgender").val(response.data.gender || "");
                    $("#civil_status").val(response.data.civil_status || "");

                    switch (response.data.civil_status) {
                        case "SOLTERO":
                            $("#codcivil_status").val(1);
                            break;
                        case "CASADO":
                            $("#codcivil_status").val(2);
                            break;
                        case "DIVORCIADO":
                            $("#codcivil_status").val(3);
                            break;
                        case "VIUDO":
                            $("#codcivil_status").val(4);
                            break;
                        default:
                            $("#codcivil_status").val(1);
                    }

                    // Para el ubigeo debo consultar cual es el ccpp (response.data.ubigeo_dir) del registro de la tabla ubigeo
                } else {
                    hideError();
                    showError(
                        response.message || "Error al consultar el registro",
                    );
                }
            },
            error: function (xhr) {
                handleAjaxError(xhr);
            },
        });
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
    const url = recordId
        ? `${CONFIG.baseUrl}/store/${recordId}`
        : `${CONFIG.baseUrl}/store`;

    // Mostrar loading
    setSubmitButton(true);
    STATE.isSubmitting = true;

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
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.href = `${CONFIG.baseUrl}/list`;
                    }
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
 * Inicializar ubigeo para modo edición
 */
function initializeUbigeo() {
    // Si hay un departamento seleccionado, habilitar provincia
    if (departmentSelect.val()) {
        provinceSelect.prop("disabled", false);
    }

    // Si hay una provincia seleccionada, habilitar distrito
    if (provinceSelect.val()) {
        districtSelect.prop("disabled", false);
    }
}

function loadProvinces(coddepartment) {
    provinceSelect.prop("disabled", true);
    districtSelect.prop("disabled", true);

    provinceSelect.html('<option value="">Cargando...</option>');
    districtSelect.html('<option value="">Seleccione distrito</option>');
    codubigeoInput.val("");

    $.ajax({
        url: `/person/provinces/${coddepartment}`,
        method: "GET",
        success: function (response) {
            provinceSelect.prop("disabled", false);
            provinceSelect.html(
                '<option value="">Seleccione provincia</option>',
            );

            response.data.forEach((item) => {
                provinceSelect.append(`
                        <option value="${item.codprovince}">${item.name}</option>
                    `);
            });
        },
    });
}

/**
 * Cargar distritos por departamento y provincia
 */
function loadDistricts(coddepartment, codprovince) {
    districtSelect.prop("disabled", true);
    districtSelect.html('<option value="">Cargando...</option>');
    codubigeoInput.val("");

    $.ajax({
        url: `/person/districts/${coddepartment}/${codprovince}`,
        method: "GET",
        success: function (response) {
            districtSelect.prop("disabled", false);
            districtSelect.html(
                '<option value="">Seleccione distrito</option>',
            );

            response.data.forEach((item) => {
                districtSelect.append(`
                        <option value="${item.coddistrict}" data-ubigeo="${item.codubigeo}">
                            ${item.name}
                        </option>
                    `);
            });
        },
    });
}
