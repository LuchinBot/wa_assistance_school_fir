/**
 * Sistema de gestión de lista con búsqueda y paginación
 * Compatible con cualquier módulo CRUD
 */

// Configuración global
const CONFIG = {
    recordsPerPage: 5,
    searchDelay: 500, // milisegundos
    baseUrl: `/${controller}`,
};

// Estado de la aplicación
const STATE = {
    currentPage: 1,
    totalPages: 1,
    isLoading: false,
    searchKeyword: null,
    deleteId: null,
};

// Variables de elementos DOM
let searchInput,
    btnClearSearch,
    tableBody,
    loadingSpinner,
    noResults,
    paginationContainer,
    totalRecordsSpan,
    deleteModal,
    btnConfirmDelete,
    btnCancelDelete;

// Timer para búsqueda con delay
let searchTimer = null;

/**
 * Inicialización al cargar el documento
 */
$(document).ready(function () {
    initializeElements();
    loadInitialRecords();
    setupEventListeners();
});

/**
 * Inicializar referencias a elementos DOM
 */
function initializeElements() {
    searchInput = $("#searchInput");
    btnClearSearch = $("#btnClearSearch");
    tableBody = $("#tableBody");
    loadingSpinner = $("#loadingSpinner");
    noResults = $("#noResults");
    paginationContainer = $("#paginationContainer");
    totalRecordsSpan = $("#totalRecords");
    deleteModal = $("#deleteModal");
    btnConfirmDelete = $("#btnConfirmDelete");
    btnCancelDelete = $("#btnCancelDelete");
}

/**
 * Configurar event listeners
 */
function setupEventListeners() {
    // Búsqueda en tiempo real
    searchInput.on("input", function () {
        const keyword = $(this).val().trim();

        // Limpiar timer anterior
        clearTimeout(searchTimer);

        // Mostrar u ocultar botón de limpiar
        if (keyword) {
            btnClearSearch.removeClass("hidden");
        } else {
            btnClearSearch.addClass("hidden");
        }

        // Ejecutar búsqueda con delay
        searchTimer = setTimeout(() => {
            performSearch(keyword);
        }, CONFIG.searchDelay);
    });

    // Limpiar búsqueda
    btnClearSearch.on("click", function () {
        searchInput.val("");
        btnClearSearch.addClass("hidden");
        STATE.searchKeyword = null;
        STATE.currentPage = 1;
        loadRecords(0, CONFIG.recordsPerPage);
    });

    // Confirmar eliminación
    btnConfirmDelete.on("click", function () {
        if (STATE.deleteId) {
            deleteRecord(STATE.deleteId);
        }
    });

    // Cancelar eliminación
    btnCancelDelete.on("click", function () {
        closeDeleteModal();
    });

    // Cerrar modal al hacer click fuera
    deleteModal.on("click", function (e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    // Event delegation para botones de acciones
    $(document).on("click", ".btn-edit", function () {
        const id = $(this).data("id");
        window.location.href = `${CONFIG.baseUrl}/form/${id}`;
    });

    $(document).on("click", ".btn-delete", function () {
        const id = $(this).data("id");
        openDeleteModal(id);
    });

    $(document).on("click", ".btn-view", function () {
        const id = $(this).data("id");
        viewRecord(id);
    });
}

/**
 * Crear fila de tabla
 */
function createTableRow(record) {
    return `
        <tr class="hover:bg-gray-50 transition-colors duration-150">
            <td class="px-6 text-left py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                ${record.param || "-"}
            </td>
            <td class="px-6 text-center py-4 whitespace-nowrap text-sm text-gray-900">
                ${record.value || "-"}
            </td>
            <td class="px-6 text-left py-4 whitespace-nowrap text-sm text-gray-900">
                ${record.description || "-"}
            </td>
            <td class="px-5 py-3 text-xs">
                <div class="flex justify-center gap-2">
                    <button class="btn-edit p-2 text-blue-500 bg-blue-50 hover:bg-blue-100 border rounded-xl transition-all" 
                            data-id="${record.codparam}" 
                            title="Editar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button class="btn-delete w-7 h-7 flex items-center justify-center rounded transition-all"
                            style="color: #94a3b8;"
                            onmouseover="this.style.background='rgba(239,68,68,0.08)'; this.style.color='rgb(220,50,50)';"
                            onmouseout="this.style.background=''; this.style.color='#94a3b8';" 
                            data-id="${record.codparam}" 
                            title="Eliminar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `;
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
