/**
 * resources/js/controllers/guest/index.js
 * Panel público — wizard de pasos (rol → DNI → panel alumno)
 *
 * Este archivo solo inicializa Chart.js con defaults globales y exporta
 * la función init(). Toda la lógica de pasos vive en el blade para poder
 * usar sessionStorage directamente sin complicar el bundling.
 */

import Chart from "chart.js/auto";

// ── Chart.js defaults globales ─────────────────────────────────────────────
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.font.weight = "600";
Chart.defaults.color = "#94a3b8";
Chart.defaults.plugins.legend.labels.boxWidth = 10;
Chart.defaults.plugins.legend.labels.padding = 14;
Chart.defaults.plugins.legend.labels.font = { size: 11, weight: "700" };

// Exponemos Chart globalmente para que el blade pueda usarlo sin importarlo
window.Chart = Chart;

/**
 * Punto de entrada del controlador Stimulus / manual.
 * No hace mucho aquí porque la lógica de steps vive en el blade,
 * pero es necesario para que Vite procese Chart.js.
 */
export default function init() {
    // Chart.js ya está disponible en window.Chart gracias a la línea de arriba.
    // El blade se encarga del resto al recibir DOMContentLoaded.
    console.debug("[guest/index] Chart.js listo");
}
