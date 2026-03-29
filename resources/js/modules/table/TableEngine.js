import axios from "axios";
import { showError, showSuccess } from "../../core/alert";

export default class TableEngine {
    constructor(options) {
        this.baseUrl = options.baseUrl;
        this.config = options.config;
        this.actions = options.actions ?? {};
        this.createRow = options.createRow;
        this.extraParams = options.extraParams ?? (() => ({}));
        this.createCard = options.createCard ?? null;

        this.state = {
            currentPage: 1,
            totalPages: 1,
            searchKeyword: null,
            isLoading: false,
            from: 0,
            to: this.config.recordsPerPage,
        };

        this.totalRecords = 0;
        this.tableContainer = document.querySelector("#tableContainer");
        this.tableBody = document.querySelector("#tableBody");
        this.pagination = document.querySelector("#pagination");
        this.loading = document.querySelector("#loadingSpinner");
        this.noResults = document.querySelector("#noResults");
        this.tableWrapper = this.tableBody.closest(".overflow-x-auto");
        this.searchInput = document.querySelector("#searchInput");
        this.totalRecord = document.querySelector("#totalRecord");

        this.cardContainer = document.querySelector("#cardContainer");

        this.init();
        this.bindActions();
    }

    init() {
        this.loadInitialRecords();
        this.registerSearch();
    }

    /* ===============================
        LOAD INITIAL
    =============================== */

    loadInitialRecords() {
        this.loadRecords(0, this.config.recordsPerPage);
    }

    /* ===============================
        LOAD RECORDS
    =============================== */
    async loadRecords(from = this.state.from, to = this.state.to) {
        this.showLoading();
        if (this.state.isLoading) return;

        this.state.isLoading = true;

        this.state.from = from;
        this.state.to = to;

        try {
            const keyword = this.state.searchKeyword ?? "null";
            const extra = new URLSearchParams(this.extraParams()).toString();
            const base =
                typeof this.baseUrl === "function"
                    ? this.baseUrl()
                    : this.baseUrl;

            const url = `${base}/records/${from}/${to}/${keyword}${extra ? "?" + extra : ""}`;

            const { data } = await axios.get(url);

            if (!data.success) return;

            this.totalRecords = data.total;

            this.renderRecords(data.data);
            this.updatePagination();
        } catch (e) {
            console.error("Error cargando registros:", e);
        } finally {
            this.state.isLoading = false;
            this.hideLoading();
        }
    }

    /* ===============================
        RENDER TABLE
    =============================== */

    renderRecords(records) {
        this.tableBody.innerHTML = "";
        this.cardContainer.innerHTML = "";

        this.totalRecord.innerHTML = this.totalRecords;

        if (!records || records.length === 0) {
            this.showNoResults();
            return;
        }

        this.hideNoResults();

        records.forEach((record) => {
            if (this.isMobile()) {
                this.cardContainer.insertAdjacentHTML(
                    "beforeend",
                    this.createCard(record),
                );
            } else {
                this.tableBody.insertAdjacentHTML(
                    "beforeend",
                    this.createRow(record),
                );
            }
        });
    }
    /* ===============================
        PAGINATION
    =============================== */

    updatePagination() {
        const totalPages = Math.ceil(
            this.totalRecords / this.config.recordsPerPage,
        );

        this.state.totalPages = totalPages;

        // limpiar contenedor
        this.pagination.innerHTML = "";

        if (totalPages <= 1) return;

        const currentPage = this.state.currentPage;
        const totalRecords = this.totalRecords;

        /* =========================
       CONTENEDOR PRINCIPAL
    ==========================*/
        const wrapper = document.createElement("div");
        wrapper.className =
            "flex flex-col sm:flex-row sm:items-center justify-center sm:justify-between gap-3";

        /* =========================
       INFO REGISTROS
    ==========================*/
        const info = document.createElement("div");
        info.className = "text-sm text-gray-700 text-center md:text-left";

        const startRecord = (currentPage - 1) * this.config.recordsPerPage + 1;

        const endRecord = Math.min(
            currentPage * this.config.recordsPerPage,
            totalRecords,
        );

        info.innerHTML = `
        Mostrando <span class="font-medium">${startRecord}</span>
        a <span class="font-medium">${endRecord}</span>
        de <span class="font-medium">${totalRecords}</span> registros
    `;

        /* =========================
       BOTONES PAGINACIÓN
    ==========================*/
        const buttons = document.createElement("div");
        buttons.className = "flex gap-2 justify-center md:justify-normal";

        const createBtn = (label, page, disabled = false, active = false) => {
            const btn = document.createElement("button");

            btn.innerHTML = label;

            btn.className = `
            px-3 py-1 rounded transition-all
            ${
                active
                    ? "bg-slate-900 text-white"
                    : "bg-white text-gray-700 hover:bg-gray-100 border border-gray-300"
            }
            ${disabled ? "bg-gray-200 text-gray-400 cursor-not-allowed" : ""}
        `;

            if (!disabled) {
                btn.addEventListener("click", () => this.goToPage(page));
            }

            return btn;
        };

        /* =========================
       PREV BUTTON
    ==========================*/
        buttons.appendChild(createBtn("‹", currentPage - 1, currentPage === 1));

        /* =========================
       RANGO DINÁMICO
    ==========================*/
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            buttons.appendChild(createBtn("1", 1));

            if (startPage > 2) {
                const dots = document.createElement("span");
                dots.textContent = "...";
                dots.className = "px-2";
                buttons.appendChild(dots);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            buttons.appendChild(createBtn(i, i, false, i === currentPage));
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const dots = document.createElement("span");
                dots.textContent = "...";
                dots.className = "px-2";
                buttons.appendChild(dots);
            }

            buttons.appendChild(createBtn(totalPages, totalPages));
        }

        /* =========================
       NEXT BUTTON
    ==========================*/
        buttons.appendChild(
            createBtn("›", currentPage + 1, currentPage === totalPages),
        );

        /* =========================
       APPEND FINAL
    ==========================*/
        wrapper.appendChild(info);
        wrapper.appendChild(buttons);

        this.pagination.appendChild(wrapper);
    }

    goToPage(page) {
        this.state.currentPage = page;

        const from = (page - 1) * this.config.recordsPerPage;
        const to = page * this.config.recordsPerPage;

        this.loadRecords(from, to);
    }

    showLoading() {
        this.loading?.classList.remove("hidden");
        this.tableWrapper?.classList.add("opacity-30", "pointer-events-none");
        this.tableContainer?.classList.add("min-h-[220px]");
        this.noResults?.classList.add("hidden");
    }
    hideLoading() {
        this.loading?.classList.add("hidden");
        this.tableWrapper?.classList.remove(
            "opacity-30",
            "pointer-events-none",
        );
        this.tableContainer?.classList.remove("min-h-[220px]");
    }

    showNoResults() {
        this.hideLoading();

        this.tableWrapper?.classList.add("hidden");
        this.noResults?.classList.remove("hidden");
        this.noResults?.classList.add("flex");

        this.pagination.innerHTML = "";
    }

    hideNoResults() {
        this.noResults?.classList.add("hidden");
        this.noResults?.classList.remove("flex");

        this.tableWrapper?.classList.remove("hidden");
    }

    /* ===============================
        SEARCH
    =============================== */
    registerSearch() {
        if (!this.searchInput) return;

        let timer = null;

        this.searchInput.addEventListener("input", (e) => {
            clearTimeout(timer);

            timer = setTimeout(() => {
                this.search(e.target.value);
            }, 400);
        });
    }
    search(keyword) {
        this.state.searchKeyword = keyword || null;
        this.state.currentPage = 1;

        this.loadRecords(0, this.config.recordsPerPage);
    }

    /*bindActions() {
        this.tableBody.addEventListener("click", (e) => {
            const btn = e.target.closest("[class*='btn-']");
            if (!btn) return;

            const actionClass = [...btn.classList].find((c) =>
                c.startsWith("btn-"),
            );

            if (!actionClass) return;

            const actionName = actionClass.replace("btn-", "");

            const action = this.actions[actionName];
            if (!action) return;

            const id = btn.dataset.id;

            // 👇 ahora pasamos el botón
            this.handleAction(actionName, action, id, btn);
        });
    }*/

    bindActions() {
        // ✅ Escuchar en document para cubrir TANTO tabla como cards mobile
        document.addEventListener("click", (e) => {
            const btn = e.target.closest("[class*='btn-']");
            if (!btn) return;

            const actionClass = [...btn.classList].find((c) =>
                c.startsWith("btn-"),
            );
            if (!actionClass) return;

            const actionName = actionClass.replace("btn-", "");
            const action = this.actions[actionName];
            if (!action) return;

            const id = btn.dataset.id;
            this.handleAction(actionName, action, id, btn);
        });
    }
    async handleAction(name, action, id, btnElement = null) {
        /* ======================
       PREVIEW (SIN FETCH)
    ====================== */
        if (name === "preview") {
            const imageSrc = btnElement?.getAttribute("src");
            if (!imageSrc) return;

            const { openPreviewModal } = await import("../../core/modal");
            openPreviewModal(imageSrc);
            return;
        }

        /* ======================
       EXECUTE ACTION
    ====================== */

        const execute = async () => {
            try {
                const response = await fetch(
                    `${this.baseUrl}${action.url}/${id}`,
                    {
                        method: action.method ?? "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]',
                            ).content,
                            Accept: "application/json",
                        },
                    },
                );

                const data = await response.json();

                if (data.success) {
                    this.reload();
                    showSuccess(data.message);
                } else {
                    showError(data.message || "Error ejecutando la acción");
                }
            } catch (error) {
                console.error("Error en handleAction:", error);
                showError("Error ejecutando la acción");
            }
        };

        if (action.redirect) {
            window.location.href = `${this.baseUrl}${action.url}/${id}`;
            return;
        }

        if (action.modal) {
            const modalModule = await import("../../core/modal");

            const modalFunctionName =
                "open" +
                action.modal.charAt(0).toUpperCase() +
                action.modal.slice(1) +
                "Modal";

            const openModalFn = modalModule[modalFunctionName];

            if (typeof openModalFn === "function") {
                openModalFn(id, execute);
                return;
            }

            console.warn(`Modal ${modalFunctionName} no existe`);
        }

        execute();
    }
    isMobile() {
        return window.innerWidth < 768;
    }

    reload() {
        this.loadRecords();
    }
}
