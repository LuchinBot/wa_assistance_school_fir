import TomSelect from "tom-select";

/**
 * Inicializa TomSelect de forma segura con mensajes en español.
 * @param {string|HTMLElement} target - Selector CSS o elemento del DOM.
 */
export default function initTomSelect(target = ".tom-select") {
    const elements =
        typeof target === "string"
            ? document.querySelectorAll(target)
            : [target];

    elements.forEach((el) => {
        if (!(el instanceof HTMLElement) || el.tomselect) return;

        try {
            new TomSelect(el, {
                create: false,
                allowEmptyOption: true,
                placeholder:
                    el.dataset.placeholder ||
                    el.getAttribute("placeholder") ||
                    "Seleccione...",
                copyClassesToDropdown: false,

                // Configuración de mensajes en español
                render: {
                    no_results: function (data, escape) {
                        return `<div class="no-results">No se encontraron resultados para "${escape(data.input)}"</div>`;
                    },
                    option_create: function (data, escape) {
                        return `<div class="create">Añadir <strong>${escape(data.input)}</strong>...</div>`;
                    },
                },

                onInitialize() {
                    this.wrapper.classList.add("ts-ready");
                },
            });
        } catch (error) {
            console.error("Error inicializando TomSelect:", error, el);
        }
    });
}
