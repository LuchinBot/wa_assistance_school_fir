/**
 * Utilidades adicionales para manejo de tablas
 * Funciones auxiliares reutilizables
 */

/**
 * Formatear fecha
 */
function formatDate(dateString) {
    if (!dateString) return "-";

    const date = new Date(dateString);
    const options = { year: "numeric", month: "long", day: "numeric" };
    return date.toLocaleDateString("es-ES", options);
}

/**
 * Formatear fecha y hora
 */
function formatDateTime(dateString) {
    if (!dateString) return "-";

    const date = new Date(dateString);
    const dateOptions = { year: "numeric", month: "short", day: "numeric" };
    const timeOptions = { hour: "2-digit", minute: "2-digit" };

    return `${date.toLocaleDateString(
        "es-ES",
        dateOptions,
    )} ${date.toLocaleTimeString("es-ES", timeOptions)}`;
}

/**
 * Truncar texto
 */
function truncateText(text, maxLength = 50) {
    if (!text) return "-";
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + "...";
}

/**
 * Formatear número
 */
function formatNumber(number, decimals = 2) {
    if (number === null || number === undefined) return "-";
    return parseFloat(number).toFixed(decimals);
}

/**
 * Formatear moneda
 */
function formatCurrency(amount, currency = "PEN") {
    if (amount === null || amount === undefined) return "-";

    const symbols = {
        PEN: "S/",
        USD: "$",
        EUR: "€",
    };

    const symbol = symbols[currency] || currency;
    return `${symbol} ${parseFloat(amount).toFixed(2)}`;
}

/**
 * Copiar texto al portapapeles
 */
function copyToClipboard(text) {
    const textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.style.position = "fixed";
    textarea.style.opacity = "0";
    document.body.appendChild(textarea);
    textarea.select();

    try {
        document.execCommand("copy");
        showToast("Copiado al portapapeles", "success");
    } catch (err) {
        showToast("Error al copiar", "error");
    }

    document.body.removeChild(textarea);
}

/**
 * Mostrar toast (notificación pequeña)
 */
function showToast(message, type = "info") {
    const bgColor =
        type === "success"
            ? "bg-green-500"
            : type === "error"
              ? "bg-blue-500"
              : type === "warning"
                ? "bg-yellow-500"
                : "bg-blue-500";

    const toast = $(`
        <div class="fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in">
            ${message}
        </div>
    `);

    $("body").append(toast);

    setTimeout(() => {
        toast.fadeOut(300, function () {
            $(this).remove();
        });
    }, 3000);
}

/**
 * Confirmar acción
 */
function confirmAction(message, onConfirm) {
    if (confirm(message)) {
        onConfirm();
    }
}

/**
 * Exportar tabla a CSV
 */
function exportTableToCSV(filename = "export.csv") {
    const rows = [];
    const table = $("#tableBody").closest("table");

    // Headers
    const headers = [];
    table.find("thead th").each(function () {
        const text = $(this).text().trim();
        if (text && text !== "Acciones") {
            headers.push(text);
        }
    });
    rows.push(headers);

    // Datos
    table.find("tbody tr").each(function () {
        const row = [];
        $(this)
            .find("td")
            .not(":last")
            .each(function () {
                row.push($(this).text().trim());
            });
        rows.push(row);
    });

    // Crear CSV
    let csvContent = "";
    rows.forEach((row) => {
        csvContent += row.map((cell) => `"${cell}"`).join(",") + "\n";
    });

    // Descargar
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", filename);
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showToast("Tabla exportada correctamente", "success");
}

/**
 * Imprimir tabla
 */
function printTable() {
    const printWindow = window.open("", "", "height=600,width=800");
    const table = $("#tableBody").closest("table").clone();

    // Remover columna de acciones
    table.find("th:last, td:last").remove();

    printWindow.document.write("<html><head><title>Imprimir</title>");
    printWindow.document.write("<style>");
    printWindow.document.write(
        "table { width: 100%; border-collapse: collapse; }",
    );
    printWindow.document.write(
        "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }",
    );
    printWindow.document.write(
        "th { background-color: #f3f4f6; font-weight: bold; }",
    );
    printWindow.document.write("</style>");
    printWindow.document.write("</head><body>");
    printWindow.document.write("<h1>Reporte</h1>");
    printWindow.document.write(table[0].outerHTML);
    printWindow.document.write("</body></html>");
    printWindow.document.close();
    printWindow.print();
}

/**
 * Resaltar texto de búsqueda
 */
function highlightSearchText(text, keyword) {
    if (!keyword) return text;

    const regex = new RegExp(`(${keyword})`, "gi");
    return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function (...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => (inThrottle = false), limit);
        }
    };
}

/**
 * Validar si es móvil
 */
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent,
    );
}

/**
 * Scroll suave a elemento
 */
function smoothScrollTo(element, offset = 0) {
    $("html, body").animate(
        {
            scrollTop: $(element).offset().top - offset,
        },
        500,
    );
}

/**
 * Cargar más registros (scroll infinito)
 */
function setupInfiniteScroll(loadMoreCallback) {
    let isLoadingMore = false;

    $(window).on(
        "scroll",
        throttle(function () {
            if (isLoadingMore) return;

            const scrollTop = $(window).scrollTop();
            const windowHeight = $(window).height();
            const documentHeight = $(document).height();

            if (scrollTop + windowHeight >= documentHeight - 200) {
                isLoadingMore = true;
                loadMoreCallback(() => {
                    isLoadingMore = false;
                });
            }
        }, 200),
    );
}

/**
 * Obtener parámetros de URL
 */
function getUrlParams() {
    const params = {};
    const queryString = window.location.search.substring(1);
    const queries = queryString.split("&");

    queries.forEach((query) => {
        const [key, value] = query.split("=");
        if (key) {
            params[decodeURIComponent(key)] = decodeURIComponent(value || "");
        }
    });

    return params;
}

/**
 * Actualizar parámetro en URL sin recargar
 */
function updateUrlParam(key, value) {
    const url = new URL(window.location);
    url.searchParams.set(key, value);
    window.history.pushState({}, "", url);
}

/**
 * Generar ID único
 */
function generateUniqueId(prefix = "id") {
    return `${prefix}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
}

/**
 * Sanitizar HTML
 */
function sanitizeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

// Exportar funciones para uso global
window.TableUtils = {
    formatDate,
    formatDateTime,
    truncateText,
    formatNumber,
    formatCurrency,
    copyToClipboard,
    showToast,
    confirmAction,
    exportTableToCSV,
    printTable,
    highlightSearchText,
    debounce,
    throttle,
    isMobile,
    smoothScrollTo,
    setupInfiniteScroll,
    getUrlParams,
    updateUrlParam,
    generateUniqueId,
    sanitizeHtml,
};
