import "./bootstrap";

import ui from "./core/ui";
import ajax from "./core/ajax";
import { setupDeleteModal } from "./core/modal";

ui();
ajax();
setupDeleteModal();
/* ===============================
    CARGA DINÁMICA DE MÓDULOS
=============================== */

const pages = import.meta.glob("./controllers/**/index.js");
const tables = import.meta.glob("./controllers/**/table.js");
const form = import.meta.glob("./controllers/**/form.js");

async function loadModule(modules, path) {
    const mod = modules[path];
    if (!mod) return;

    try {
        const { default: init } = await mod();
        init?.();
    } catch (e) {
        console.error(`Error cargando módulo [${path}]:`, e);
    }
}
(async () => {
    const controller = document.body.dataset.controller;
    const view = document.body.dataset.view;

    console.log("Controller:", controller);
    console.log("View:", view);

    if (!controller || !view) return;

    if (view === "list") {
        await loadModule(pages, `./controllers/${controller}/index.js`);
        await loadModule(tables, `./controllers/${controller}/table.js`);
    }

    if (view === "form") {
        await loadModule(form, `./controllers/${controller}/form.js`);
    }

    if (view === "index") {
        await loadModule(pages, `./controllers/${controller}/index.js`);
    }
})();

/* ===============================
    TOGGLE SECTIONS (SIDEBAR/MENU)
=============================== */

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".toggle-section").forEach((toggle) => {
        toggle.addEventListener("click", function (e) {
            e.preventDefault();

            const targetId = this.getAttribute("href");
            const content = document.querySelector(targetId);

            if (!content) return;

            // Cerrar otros abiertos
            document.querySelectorAll(".content-section").forEach((sec) => {
                if (sec !== content) sec.classList.add("hidden");
            });

            document.querySelectorAll(".toggle-section").forEach((el) => {
                el.classList.remove("active-menu");
            });

            // Toggle del actual
            if (content.classList.contains("hidden")) {
                content.classList.remove("hidden");
                this.classList.add("active-menu");
            } else {
                content.classList.add("hidden");
                this.classList.remove("active-menu");
            }
        });
    });
});
