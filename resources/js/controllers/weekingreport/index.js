import TableEngine from "../../modules/table/TableEngine";
import { showError } from "../../core/alert";
export default function () {
    // ── Filtros ──
    const dateFrom = document.getElementById("dateFrom");
    const dateTo = document.getElementById("dateTo");
    const scheduleFilter = document.getElementById("scheduleFilter");
    const gradeFilter = document.getElementById("gradeFilter");
    const sectionFilter = document.getElementById("sectionFilter");
    const sectionFilterWrap = document.getElementById("sectionFilterWrap");
    const periodFilter = document.getElementById("periodFilter");
    const btnToggleFilters = document.getElementById("btn-toggle-filters");
    const filtersRow = document.getElementById("filters-row");
    const btnClearFilters = document.getElementById("btn-clear-filters");
    const dateRangeMsg = document.getElementById("dateRangeMsg");

    if (!document.getElementById("tableBody")) return;

    let tableSummary = null;

    /* =============================================
       VALIDACIÓN DE RANGO — máx 5 días, lun–vie
    ============================================= */

    /**
     * Ajusta una fecha al lunes de su semana (ISO: lunes = día 1).
     */
    function toMonday(date) {
        const d = new Date(date);
        const day = d.getDay(); // 0=dom … 6=sáb
        const diff = day === 0 ? -6 : 1 - day;
        d.setDate(d.getDate() + diff);
        return d;
    }

    /**
     * Devuelve el viernes de la misma semana que `date`.
     */
    function toFriday(date) {
        const mon = toMonday(date);
        const fri = new Date(mon);
        fri.setDate(mon.getDate() + 4);
        return fri;
    }

    /**
     * Formatea un Date a "YYYY-MM-DD" (value de <input type="date">).
     */
    function toInputDate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, "0");
        const d = String(date.getDate()).padStart(2, "0");
        return `${y}-${m}-${d}`;
    }

    /**
     * Cuenta días hábiles (lun–vie) entre dos fechas inclusive.
     */
    function workingDaysBetween(from, to) {
        let count = 0;
        const cur = new Date(from);
        while (cur <= to) {
            const day = cur.getDay();
            if (day >= 1 && day <= 5) count++;
            cur.setDate(cur.getDate() + 1);
        }
        return count;
    }

    /**
     * Valida y corrige el rango de fechas:
     *  - Ambas fechas deben estar en la misma semana lun–vie.
     *  - Si `dateTo` < `dateFrom`, lo iguala a `dateFrom`.
     *  - Si el rango supera 5 días hábiles, ajusta `dateTo` al viernes.
     * Devuelve true si el rango es válido (puede continuar la carga).
     */

    function validateDateRange(changedField) {
        if (!dateFrom?.value || !dateTo?.value) return true;

        const from = new Date(dateFrom.value + "T00:00:00");
        let to = new Date(dateTo.value + "T00:00:00");

        // dateTo no puede ser anterior a dateFrom
        if (to < from) {
            dateTo.value = dateFrom.value;
            to = new Date(from);
        }

        // Ambas deben estar en la misma semana ISO (lun–vie)
        const monFrom = toMonday(from);
        const friFrom = toFriday(from);

        // Si dateFrom cambió, forzar dateTo dentro de esa semana
        if (changedField === "from") {
            // dateFrom → lunes de esa semana
            const clampedFrom = new Date(from);
            if (from.getDay() === 0 || from.getDay() === 6) {
                // fin de semana → mover al lunes siguiente
                const nextMon = toMonday(
                    new Date(from.setDate(from.getDate() + 1)),
                );
                dateFrom.value = toInputDate(nextMon);
                dateTo.value = toInputDate(toFriday(nextMon));
                showRangeMsg(
                    "La fecha fue ajustada al inicio de la semana laboral.",
                );
                return true;
            }
            // dateTo debe estar en la misma semana
            if (to > friFrom) {
                dateTo.value = toInputDate(friFrom);
                showRangeMsg(
                    "El rango máximo es una semana (lunes a viernes).",
                );
            } else {
                clearRangeMsg();
            }
            return true;
        }

        // Si dateTo cambió
        if (changedField === "to") {
            if (to.getDay() === 0 || to.getDay() === 6) {
                // fin de semana → mover al viernes anterior
                const d = new Date(to);
                while (d.getDay() !== 5) d.setDate(d.getDate() - 1);
                dateTo.value = toInputDate(d);
                to = new Date(d);
                showRangeMsg("La fecha fue ajustada al viernes de la semana.");
            }
            // Verificar que no supere la semana de dateFrom
            if (to > friFrom) {
                dateTo.value = toInputDate(friFrom);
                showRangeMsg(
                    "El rango máximo es una semana (lunes a viernes).",
                );
            } else if (workingDaysBetween(from, to) > 5) {
                dateTo.value = toInputDate(friFrom);
                showRangeMsg("El rango máximo es 5 días hábiles.");
            } else {
                clearRangeMsg();
            }
        }

        return true;
    }

    function showRangeMsg(msg) {
        if (!dateRangeMsg) return;
        dateRangeMsg.textContent = msg;
        dateRangeMsg.classList.remove("hidden");
    }

    function clearRangeMsg() {
        if (!dateRangeMsg) return;
        dateRangeMsg.classList.add("hidden");
        dateRangeMsg.textContent = "";
    }

    /* =============================================
       EXTRA PARAMS
    ============================================= */
    function getExtraParams() {
        return {
            date_from: dateFrom?.value ?? "",
            date_to: dateTo?.value ?? "",
            codschedule: scheduleFilter?.value ?? "",
            grade: gradeFilter?.value ?? "",
            grade_schedule: sectionFilter?.value ?? "",
            codperiod: periodFilter?.value ?? "",
        };
    }

    /* =============================================
       FACTORY — RESUMEN
    ============================================= */
    function createSummaryEngine() {
        return new TableEngine({
            baseUrl: () => "/report/weeking/summary",
            config: { recordsPerPage: 15 },
            extraParams: getExtraParams,

            createRow(record) {
                const person = record.student?.person ?? record.person;
                const fullName = [person.lastname_father, person.lastname_mom]
                    .filter(Boolean)
                    .join(" ");

                const grade = record.grade_schedule?.grade;
                const gradeLabel = grade
                    ? `${grade.name_large} <span class="text-slate-400">Sec. ${record.grade_schedule?.section ?? "—"}</span>`
                    : "—";

                const pct = record.percentage ?? 0;
                const barColor =
                    pct >= 80
                        ? "rgb(34,197,94)"
                        : pct >= 60
                          ? "rgb(245,158,11)"
                          : "rgb(239,68,68)";

                return `
                    <tr style="border-bottom:1px solid #f1f5f9;transition:background 0.1s;"
                        onmouseover="this.style.background='#f8fafc'"
                        onmouseout="this.style.background=''">

                        <td class="px-5 py-3">
                            <p class="text-xs font-semibold text-slate-800">
                                ${person.firstname ?? "—"} ${fullName}
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                DNI: ${person.identify_number ?? "—"}
                            </p>
                        </td>

                        <td class="px-5 py-3 text-xs text-slate-600">${gradeLabel}</td>

                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-black"
                                style="background:rgba(16,185,129,0.10);color:rgb(5,150,105);">
                                ${record.present ?? 0}
                            </span>
                        </td>

                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-black"
                                style="background:rgba(245,158,11,0.10);color:rgb(180,115,0);">
                                ${record.late ?? 0}
                            </span>
                        </td>

                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-black"
                                style="background:rgba(234,179,8,0.15);color:rgb(161,136,0);">
                                ${record.justified ?? 0}
                            </span>
                        </td>

                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-black"
                                style="background:rgba(239,68,68,0.10);color:rgb(220,50,50);">
                                ${record.absent ?? 0}
                            </span>
                        </td>

                        <td class="px-5 py-3 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs font-black" style="color:${barColor};">${pct}%</span>
                                <div class="w-14 h-1.5 rounded-full" style="background:#e8edf2;">
                                    <div class="h-full rounded-full transition-all"
                                        style="width:${pct}%;background:${barColor};"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            },

            createCard(record) {
                const person = record.student?.person ?? record.person;
                const fullName = [person.lastname_father, person.lastname_mom]
                    .filter(Boolean)
                    .join(" ");
                const grade = record.grade_schedule?.grade;
                const gradeLabel = grade
                    ? `${grade.name_large} · Sec. ${record.grade_schedule?.section ?? "—"}`
                    : "—";
                const pct = record.percentage ?? 0;
                const barColor =
                    pct >= 80
                        ? "rgb(34,197,94)"
                        : pct >= 60
                          ? "rgb(245,158,11)"
                          : "rgb(239,68,68)";

                const stats = [
                    {
                        label: "Pres.",
                        val: record.present ?? 0,
                        bg: "rgba(16,185,129,0.10)",
                        color: "rgb(5,150,105)",
                    },
                    {
                        label: "Tard.",
                        val: record.late ?? 0,
                        bg: "rgba(245,158,11,0.10)",
                        color: "rgb(180,115,0)",
                    },
                    {
                        label: "Just.",
                        val: record.justified ?? 0,
                        bg: "rgba(234,179,8,0.15)",
                        color: "rgb(161,136,0)",
                    },
                    {
                        label: "Aus.",
                        val: record.absent ?? 0,
                        bg: "rgba(239,68,68,0.10)",
                        color: "rgb(220,50,50)",
                    },
                ];

                return `
                    <div class="relative overflow-hidden rounded-xl border border-slate-100 shadow-sm mb-3 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">
                                    ${person.firstname ?? "—"} ${fullName}
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">DNI: ${person.identify_number ?? "—"}</p>
                                <p class="text-xs text-slate-500 mt-0.5">${gradeLabel}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-sm font-black" style="color:${barColor};">${pct}%</span>
                                <div class="w-16 h-1.5 rounded-full" style="background:#e8edf2;">
                                    <div class="h-full rounded-full"
                                        style="width:${pct}%;background:${barColor};"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-2 mt-3">
                            ${stats
                                .map(
                                    (s) => `
                                <div class="flex flex-col items-center py-2 rounded-lg" style="background:${s.bg};">
                                    <span class="text-base font-black" style="color:${s.color};">${s.val}</span>
                                    <span class="text-[10px] font-semibold mt-0.5" style="color:${s.color};">${s.label}</span>
                                </div>
                            `,
                                )
                                .join("")}
                        </div>
                    </div>
                `;
            },
        });
    }

    /* =============================================
       HELPERS
    ============================================= */
    function reload() {
        if (tableSummary) {
            tableSummary.state.currentPage = 1;
            tableSummary.loadRecords(0, 15);
        }
    }

    async function updateSectionsByGrade() {
        const gradeId = gradeFilter?.value;
        const schedule = scheduleFilter?.value;

        sectionFilter.innerHTML =
            '<option value="">Todas las secciones</option>';

        if (!gradeId) {
            sectionFilterWrap.classList.add("att-hidden");
            sectionFilterWrap.classList.remove("flex");
            return;
        }

        try {
            const params = new URLSearchParams({ grade: gradeId });
            if (schedule) params.set("schedule", schedule);

            const res = await fetch(`/report/weeking/sections?${params}`);
            const data = await res.json();

            if (data.success && data.data.length) {
                sectionFilterWrap.classList.remove("att-hidden");
                sectionFilterWrap.classList.add("flex");
                data.data.forEach((gs) => {
                    const opt = document.createElement("option");
                    opt.value = gs.codgrade_schedule;
                    opt.textContent = `Sección ${gs.section}`;
                    sectionFilter.appendChild(opt);
                });
            } else {
                sectionFilterWrap.classList.add("att-hidden");
                sectionFilterWrap.classList.remove("flex");
            }
        } catch (e) {
            console.error("Error cargando secciones:", e);
        }
    }

    function checkActiveFilters() {
        const hasFilters =
            gradeFilter.value || sectionFilter.value || scheduleFilter.value;
        if (hasFilters) {
            btnClearFilters?.classList.remove("att-hidden");
            btnClearFilters?.classList.add("flex");
        } else {
            btnClearFilters?.classList.add("att-hidden");
            btnClearFilters?.classList.remove("flex");
        }
    }

    function buildExportUrl() {
        const params = new URLSearchParams();
        if (dateFrom?.value) params.set("date_from", dateFrom.value);
        if (dateTo?.value) params.set("date_to", dateTo.value);
        if (scheduleFilter?.value)
            params.set("codschedule", scheduleFilter.value);
        if (gradeFilter?.value) params.set("grade", gradeFilter.value);
        if (sectionFilter?.value)
            params.set("grade_schedule", sectionFilter.value);
        if (periodFilter?.value) params.set("codperiod", periodFilter.value);
        const kw = document.getElementById("searchInput")?.value;
        if (kw) params.set("keyword", kw);
        return `/report/weeking/export/pdf?${params.toString()}`;
    }

    /* =============================================
       FILTROS — EVENTOS
    ============================================= */

    // Fechas con debounce + validación
    let dateDebounce = null;

    dateFrom?.addEventListener("change", () => {
        //validateDateRange("from");
        clearTimeout(dateDebounce);
        dateDebounce = setTimeout(reload, 300);
    });

    dateTo?.addEventListener("change", () => {
        //validateDateRange("to");
        clearTimeout(dateDebounce);
        dateDebounce = setTimeout(reload, 300);
    });

    scheduleFilter?.addEventListener("change", () => {
        updateSectionsByGrade();
        reload();
        checkActiveFilters();
    });

    gradeFilter?.addEventListener("change", () => {
        updateSectionsByGrade();
        reload();
        checkActiveFilters();
    });

    sectionFilter?.addEventListener("change", () => {
        reload();
        checkActiveFilters();
    });

    periodFilter?.addEventListener("change", () => reload());

    // Toggle filtros mobile
    btnToggleFilters?.addEventListener("click", () => {
        filtersRow.classList.toggle("open");
        const isOpen = filtersRow.classList.contains("open");
        btnToggleFilters.style.background = isOpen
            ? "rgba(0,176,202,0.08)"
            : "#f4f6f8";
        btnToggleFilters.style.color = isOpen ? "rgb(0,176,202)" : "#64748b";
        btnToggleFilters.style.borderColor = isOpen
            ? "rgba(0,176,202,0.3)"
            : "#e2e8f0";
    });

    // Limpiar filtros
    btnClearFilters?.addEventListener("click", () => {
        scheduleFilter.selectedIndex = 0;
        gradeFilter.value = "";
        sectionFilter.innerHTML =
            '<option value="">Todas las secciones</option>';
        sectionFilterWrap.classList.add("att-hidden");
        sectionFilterWrap.classList.remove("flex");
        btnClearFilters.classList.add("att-hidden");
        btnClearFilters.classList.remove("flex");
        clearRangeMsg();
        reload();
    });

    // Exportar PDF
    document.getElementById("btn-export-pdf")?.addEventListener("click", () => {
        const missing = [];

        if (!periodFilter?.value) missing.push("Período");
        if (!dateFrom?.value) missing.push("Fecha desde");
        if (!dateTo?.value) missing.push("Fecha hasta");
        if (!scheduleFilter?.value) missing.push("Horario");
        if (!gradeFilter?.value) missing.push("Grado");

        if (missing.length > 0) {
            showError(`Debes seleccionar: ${missing.join(", ")}`);
            return;
        }

        window.location.href = buildExportUrl();
    });

    /* =============================================
       CARGA INICIAL — solo resumen
    ============================================= */
    tableSummary = createSummaryEngine();
}
