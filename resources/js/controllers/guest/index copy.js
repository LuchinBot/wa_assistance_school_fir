/**
 * resources/js/controllers/guest/index.js
 * Panel público de asistencias — dark SaaS edition
 */

import Chart from "chart.js/auto";

// ── Paleta ─────────────────────────────────────────────────────────────────
const TEAL = "#2dd4bf";
const GREEN = "#34d399";
const RED = "#f87171";
const AMBER = "#fbbf24";
const MUTED = "#6b8f9a";
const LIME = "#a3e635";

// ── Chart defaults ─────────────────────────────────────────────────────────
Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.font.weight = "600";
Chart.defaults.color = MUTED;
Chart.defaults.plugins.legend.labels.boxWidth = 10;
Chart.defaults.plugins.legend.labels.padding = 14;
Chart.defaults.plugins.legend.labels.font = { size: 11 };

// ── Instancias de charts (para destruir antes de redibujar) ────────────────
let chartDonut = null;
let chartTrend = null;
let chartGrades = null;

// ── Estado de la tabla ─────────────────────────────────────────────────────
let allRecords = [];
let filtered = [];
let currentPage = 1;
const PAGE_SIZE = 12;

const el = (id) => document.getElementById(id);

// ══════════════════════════════════════════════════════════════════════════
// ENTRY POINT
// ══════════════════════════════════════════════════════════════════════════
export default function init() {
    const data = window.guestData;
    if (!data) {
        console.error("[guest/index] guestData no encontrado");
        return;
    }

    // Charts con datos PHP
    renderDonut(data.today ?? {});
    renderTrend(data.trend ?? []);
    renderGrades(data.byGrade ?? []);

    // Tabla vía AJAX
    loadRecords();

    // ── Event listeners tabla ──────────────────────────────────────────────
    el("tableSearch")?.addEventListener("input", debounce(applyFilters, 260));
    el("statusFilter")?.addEventListener("change", applyFilters);

    // ── Recarga sesiones cuando cambia periodo/grado/sección ──────────────
    el("fPeriod")?.addEventListener("change", reloadSessions);
    el("fGrade")?.addEventListener("change", reloadSessions);
    el("fSection")?.addEventListener("change", reloadSessions);

    // ── Buscador de alumno ─────────────────────────────────────────────────
    el("btnStudentSearch")?.addEventListener("click", searchStudent);
    el("studentSearch")?.addEventListener("keydown", (e) => {
        if (e.key === "Enter") searchStudent();
    });
}

// ══════════════════════════════════════════════════════════════════════════
// SESIONES DINÁMICAS
// ══════════════════════════════════════════════════════════════════════════
function reloadSessions() {
    const period = el("fPeriod")?.value ?? "";
    const grade = el("fGrade")?.value ?? "";
    const section = el("fSection")?.value ?? "";
    const sel = el("fSession");
    if (!sel) return;

    sel.innerHTML = '<option value="">Cargando...</option>';
    sel.disabled = true;

    const params = new URLSearchParams();
    if (period) params.set("period", period);
    if (grade) params.set("grade", grade);
    if (section) params.set("section", section);

    fetch(`/guest/api/sessions?${params}`)
        .then((r) => r.json())
        .then(({ sessions = [] }) => {
            sel.innerHTML = '<option value="">Todas las sesiones</option>';
            sessions.forEach((s) => {
                const o = document.createElement("option");
                o.value = s.codassistance_session;
                o.textContent = `${s.date} · ${s.turn}`;
                sel.appendChild(o);
            });
            sel.disabled = false;
        })
        .catch(() => {
            sel.innerHTML = '<option value="">Error al cargar</option>';
            sel.disabled = false;
        });
}

// ══════════════════════════════════════════════════════════════════════════
// TABLA DE REGISTROS
// ══════════════════════════════════════════════════════════════════════════
function loadRecords() {
    setTableLoading(true);

    const params = new URLSearchParams(window.location.search);

    fetch(`/guest/api/records?${params}`)
        .then((r) => r.json())
        .then(({ records = [] }) => {
            allRecords = records;
            filtered = [...records];
            currentPage = 1;
            renderTable();
        })
        .catch(() => {
            allRecords = [];
            filtered = [];
            renderTable();
        });
}

function setTableLoading(on) {
    const tbody = el("tableBody");
    if (!tbody) return;
    if (on) {
        tbody.innerHTML = `<tr><td colspan="7">
            <div class="empty-state"><div class="spinner"></div></div>
        </td></tr>`;
    }
}

function applyFilters() {
    const q = (el("tableSearch")?.value ?? "").toLowerCase().trim();
    const status = (el("statusFilter")?.value ?? "").toLowerCase();

    filtered = allRecords.filter((r) => {
        const matchQ =
            !q ||
            [r.dni, r.fullname, r.grade, r.section]
                .join(" ")
                .toLowerCase()
                .includes(q);
        const matchS = !status || r.status === status;
        return matchQ && matchS;
    });

    currentPage = 1;
    renderTable();
}

function renderTable() {
    const tbody = el("tableBody");
    if (!tbody) return;

    const total = filtered.length;
    const pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
    const start = (currentPage - 1) * PAGE_SIZE;
    const slice = filtered.slice(start, start + PAGE_SIZE);

    if (!slice.length) {
        tbody.innerHTML = `<tr><td colspan="7">
            <div class="empty-state">
                <span class="material-symbols-outlined">search_off</span>
                <p>No se encontraron registros</p>
            </div>
        </td></tr>`;
        setText("paginationInfo", "0 registros");
        const pb = el("paginationBtns");
        if (pb) pb.innerHTML = "";
        return;
    }

    tbody.innerHTML = slice
        .map(
            (r) => `
        <tr>
            <td><span class="td-mono">${esc(r.dni)}</span></td>
            <td style="font-weight:600;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                title="${esc(r.fullname)}">${esc(r.fullname)}</td>
            <td style="font-weight:700">${esc(r.grade)}</td>
            <td class="td-muted">${esc(r.section)}</td>
            <td class="td-muted" style="white-space:nowrap">${esc(r.date)}</td>
            <td><span class="td-mono" style="color:${TEAL}">${esc(r.time_entry ?? "—")}</span></td>
            <td>${statusPill(r.status)}</td>
        </tr>
    `,
        )
        .join("");

    setText(
        "paginationInfo",
        `${start + 1}–${Math.min(start + PAGE_SIZE, total)} de ${total} registros`,
    );
    renderPagination(pages);
}

function renderPagination(pages) {
    const pb = el("paginationBtns");
    if (!pb) return;

    const btn = (html, page, disabled = false, active = false) => {
        const cls = active ? "pg-btn active" : disabled ? "pg-btn" : "pg-btn";
        const click =
            !disabled && !active
                ? `onclick="window._guestGoPage(${page})"`
                : "";
        return `<button class="${cls}" ${disabled ? "disabled" : ""} ${click}>${html}</button>`;
    };

    let html = btn(
        '<span class="material-symbols-outlined">chevron_left</span>',
        currentPage - 1,
        currentPage === 1,
    );

    for (let i = 1; i <= pages; i++) {
        if (
            pages > 7 &&
            Math.abs(i - currentPage) > 2 &&
            i !== 1 &&
            i !== pages
        ) {
            if (i === currentPage - 3 || i === currentPage + 3)
                html += btn("…", i, true);
            continue;
        }
        html += btn(i, i, false, i === currentPage);
    }

    html += btn(
        '<span class="material-symbols-outlined">chevron_right</span>',
        currentPage + 1,
        currentPage === pages,
    );

    pb.innerHTML = html;
}

window._guestGoPage = function (p) {
    const pages = Math.ceil(filtered.length / PAGE_SIZE);
    if (p < 1 || p > pages) return;
    currentPage = p;
    renderTable();
};

// ══════════════════════════════════════════════════════════════════════════
// BUSCADOR DE ALUMNO
// ══════════════════════════════════════════════════════════════════════════
function searchStudent() {
    const dni = (el("studentSearch")?.value ?? "").trim();
    const result = el("studentResult");
    if (!result) return;

    if (dni.length < 3) {
        result.innerHTML = `<div class="empty-state" style="padding:24px 0">
            <span class="material-symbols-outlined">info</span>
            <p>Ingresa al menos 3 caracteres del DNI</p>
        </div>`;
        return;
    }

    result.innerHTML = `<div class="empty-state" style="padding:20px 0">
        <div class="spinner"></div>
    </div>`;

    const period = el("fPeriod")?.value ?? "";
    const params = new URLSearchParams({ dni });
    if (period) params.set("period", period);

    fetch(`/guest/api/student?${params}`)
        .then((r) => r.json())
        .then(renderStudentResult)
        .catch(() => {
            result.innerHTML = `<div class="empty-state" style="padding:24px 0">
                <span class="material-symbols-outlined">error</span>
                <p>Error al buscar. Intenta de nuevo.</p>
            </div>`;
        });
}

function renderStudentResult({ student, enrollment, assistances = [], stats }) {
    const result = el("studentResult");
    if (!result) return;

    if (!student) {
        result.innerHTML = `<div class="empty-state" style="padding:24px 0">
            <span class="material-symbols-outlined">person_off</span>
            <p>No se encontró ningún alumno con ese DNI</p>
        </div>`;
        return;
    }

    const initials = (student.fullname ?? "?")
        .split(" ")
        .slice(0, 2)
        .map((w) => w[0] ?? "")
        .join("")
        .toUpperCase();

    const enrollInfo = enrollment
        ? `${enrollment.grade} ${enrollment.section} · ${enrollment.turn} · ${enrollment.period}`
        : "Sin matrícula en este periodo";

    const rateColor = !stats
        ? MUTED
        : stats.rate >= 90
          ? GREEN
          : stats.rate >= 75
            ? TEAL
            : stats.rate >= 60
              ? AMBER
              : RED;

    const miniStats = stats
        ? `
        <div class="mini-stats">
            <div class="mini-stat">
                <div class="mini-stat-val" style="color:${TEAL}">${stats.total}</div>
                <div class="mini-stat-lbl">Total</div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-val" style="color:${GREEN}">${stats.present}</div>
                <div class="mini-stat-lbl">Presentes</div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-val" style="color:${RED}">${stats.absent}</div>
                <div class="mini-stat-lbl">Ausentes</div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-val" style="color:${rateColor}">${stats.rate}%</div>
                <div class="mini-stat-lbl">Asistencia</div>
            </div>
        </div>`
        : "";

    const rows = assistances.length
        ? assistances
              .map(
                  (a) => `
            <div class="assist-row">
                <span class="assist-date">${esc(a.date)}</span>
                <span class="assist-time">${esc(a.time_entry ?? "—")}</span>
                ${statusPill(a.status)}
                <span class="assist-obs">${esc(a.observation ?? "")}</span>
            </div>`,
              )
              .join("")
        : `<div class="empty-state" style="padding:12px 0">
            <span class="material-symbols-outlined">event_busy</span>
            <p>Sin registros de asistencia</p>
           </div>`;

    result.innerHTML = `
        <div class="student-profile">
            <div class="student-avatar">${initials}</div>
            <div>
                <div class="student-name">${esc(student.fullname)}</div>
                <div class="student-meta">DNI: ${esc(student.dni)} · ${esc(enrollInfo)}</div>
            </div>
        </div>
        ${miniStats}
        <div style="font-size:11px;font-weight:700;color:var(--muted);
                    text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px">
            Historial de asistencias
        </div>
        <div class="assist-list">${rows}</div>
    `;
}

// ══════════════════════════════════════════════════════════════════════════
// CHARTS
// ══════════════════════════════════════════════════════════════════════════
function renderDonut(today = {}) {
    const ctx = el("chartDonut")?.getContext("2d");
    if (!ctx) return;
    chartDonut?.destroy();

    chartDonut = new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: ["Presente", "Ausente", "Tardanza"],
            datasets: [
                {
                    data: [
                        today.present ?? 0,
                        today.absent ?? 0,
                        today.late ?? 0,
                    ],
                    backgroundColor: [GREEN, RED, AMBER],
                    borderWidth: 2,
                    borderColor: "#0d1a22",
                    hoverOffset: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "70%",
            plugins: {
                legend: { position: "bottom" },
                tooltip: {
                    callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.raw}` },
                },
            },
        },
    });
}

function renderTrend(trend = []) {
    const ctx = el("chartTrend")?.getContext("2d");
    if (!ctx) return;
    chartTrend?.destroy();

    chartTrend = new Chart(ctx, {
        type: "line",
        data: {
            labels: trend.map((t) => t.week ?? ""),
            datasets: [
                lineDs(
                    "Presentes",
                    trend.map((t) => t.present ?? 0),
                    GREEN,
                    "rgba(52,211,153,.08)",
                ),
                lineDs(
                    "Ausentes",
                    trend.map((t) => t.absent ?? 0),
                    RED,
                    "rgba(248,113,113,.06)",
                ),
                lineDs(
                    "Tardanzas",
                    trend.map((t) => t.late ?? 0),
                    AMBER,
                    "rgba(251,191,36,.06)",
                    true,
                ),
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            scales: {
                x: {
                    grid: { display: false, color: "rgba(255,255,255,.04)" },
                    ticks: { font: { size: 11 } },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: "rgba(255,255,255,.04)" },
                    ticks: { font: { size: 11 } },
                },
            },
            plugins: { legend: { position: "top" } },
        },
    });
}

function lineDs(label, data, color, bg, dashed = false) {
    return {
        label,
        data,
        borderColor: color,
        backgroundColor: bg,
        fill: true,
        tension: 0.4,
        pointRadius: 3,
        pointBackgroundColor: color,
        borderWidth: 2.5,
        ...(dashed ? { borderDash: [4, 3] } : {}),
    };
}

function renderGrades(byGrade = []) {
    const ctx = el("chartGrades")?.getContext("2d");
    if (!ctx) return;
    chartGrades?.destroy();

    const rates = byGrade.map((g) => g.rate ?? 0);
    const colors = rates.map((v) =>
        v >= 90 ? GREEN : v >= 75 ? TEAL : v >= 60 ? AMBER : RED,
    );

    chartGrades = new Chart(ctx, {
        type: "bar",
        data: {
            labels: byGrade.map((g) => `${g.grade} ${g.section}`),
            datasets: [
                {
                    label: "% Asistencia",
                    data: rates,
                    backgroundColor: colors,
                    borderRadius: 5,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: (ctx) => ` ${ctx.raw.toFixed(1)}%` },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: {
                    min: 0,
                    max: 100,
                    grid: { color: "rgba(255,255,255,.04)" },
                    ticks: { callback: (v) => v + "%", font: { size: 11 } },
                },
            },
        },
    });
}

// ══════════════════════════════════════════════════════════════════════════
// STATUS PILL
// ══════════════════════════════════════════════════════════════════════════
function statusPill(status) {
    const map = {
        present: [
            `background:rgba(52,211,153,.12);color:${GREEN};border:1px solid rgba(52,211,153,.2)`,
            "check_circle",
            "Presente",
        ],
        absent: [
            `background:rgba(248,113,113,.12);color:${RED};border:1px solid rgba(248,113,113,.2)`,
            "cancel",
            "Ausente",
        ],
        late: [
            `background:rgba(251,191,36,.12);color:${AMBER};border:1px solid rgba(251,191,36,.2)`,
            "schedule",
            "Tardanza",
        ],
    };
    const key = (status ?? "").toLowerCase();
    const info = map[key] ?? [
        `background:rgba(107,143,160,.1);color:${MUTED}`,
        "help",
        status ?? "—",
    ];

    return `<span class="status-pill" style="${info[0]}">
        <span class="material-symbols-outlined">${info[1]}</span>
        ${esc(info[2])}
    </span>`;
}

// ══════════════════════════════════════════════════════════════════════════
// UTILS
// ══════════════════════════════════════════════════════════════════════════
function esc(v) {
    return String(v ?? "—")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
}
function setText(id, t) {
    const e = el(id);
    if (e) e.textContent = t;
}
function debounce(fn, ms) {
    let t;
    return (...a) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...a), ms);
    };
}
