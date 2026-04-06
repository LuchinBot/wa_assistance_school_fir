import TableEngine from "../../modules/table/TableEngine";

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

    // ── Tabs ──
    const tabSummary = document.getElementById("tab-summary");
    const tabDetail = document.getElementById("tab-detail");
    const viewSummary = document.getElementById("view-summary");
    const viewDetail = document.getElementById("view-detail");

    if (!document.getElementById("tableBody")) return;

    let currentTab = "summary";
    let tableSummary = null;
    let tableDetail = null;

    /* =============================================
       EXTRA PARAMS — compartido entre ambos engines
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

            createRow(record, index) {
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
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.1s;"
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
                                style="background: rgba(16,185,129,0.10); color: rgb(5,150,105);">
                                ${record.present ?? 0}
                            </span>
                        </td>

                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-black"
                                style="background: rgba(245,158,11,0.10); color: rgb(180,115,0);">
                                ${record.late ?? 0}
                            </span>
                        </td>

                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-black"
                                style="background: rgba(234,179,8,0.15); color: rgb(161,136,0);">
                                ${record.justified ?? 0}
                            </span>
                        </td>

                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-black"
                                style="background: rgba(239,68,68,0.10); color: rgb(220,50,50);">
                                ${record.absent ?? 0}
                            </span>
                        </td>

                        <td class="px-5 py-3 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs font-black" style="color: ${barColor};">${pct}%</span>
                                <div class="w-14 h-1.5 rounded-full" style="background: #e8edf2;">
                                    <div class="h-full rounded-full transition-all"
                                        style="width: ${pct}%; background: ${barColor};"></div>
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
                                <span class="text-sm font-black" style="color: ${barColor};">${pct}%</span>
                                <div class="w-16 h-1.5 rounded-full" style="background: #e8edf2;">
                                    <div class="h-full rounded-full"
                                        style="width: ${pct}%; background: ${barColor};"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-2 mt-3">
                            ${stats
                                .map(
                                    (s) => `
                                <div class="flex flex-col items-center py-2 rounded-lg" style="background: ${s.bg};">
                                    <span class="text-base font-black" style="color: ${s.color};">${s.val}</span>
                                    <span class="text-[10px] font-semibold mt-0.5" style="color: ${s.color};">${s.label}</span>
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
       FACTORY — DETALLE
    ============================================= */
    function createDetailEngine() {
        return new TableEngine({
            baseUrl: () => "/report/weeking",
            config: { recordsPerPage: 15 },
            extraParams: getExtraParams,

            createRow(record) {
                const person = record.enrollment?.student?.person;
                if (!person) return "";

                const fullName = [person.lastname_father, person.lastname_mom]
                    .filter(Boolean)
                    .join(" ");

                const grade = record.enrollment?.grade_schedule?.grade;
                const gradeLabel = grade
                    ? `${grade.name_large} <span class="text-slate-400">Sec. ${record.enrollment?.grade_schedule?.section ?? "—"}</span>`
                    : "—";

                const sessionDate = record.assistance_session?.date
                    ? new Date(
                          record.assistance_session.date,
                      ).toLocaleDateString("es-PE", {
                          day: "2-digit",
                          month: "2-digit",
                          year: "numeric",
                      })
                    : "—";

                const statusMap = {
                    present: {
                        label: "Presente",
                        cls: "bg-emerald-100 text-emerald-700",
                    },
                    late: {
                        label: "Tardanza",
                        cls: "bg-amber-100 text-amber-700",
                    },
                    absent: {
                        label: "Ausente",
                        cls: "bg-red-100 text-red-700",
                    },
                    justified: {
                        label: "Justificado",
                        cls: "bg-yellow-100 text-yellow-700",
                    },
                };
                const status = statusMap[record.status] ?? statusMap.absent;

                return `
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.1s;"
                        onmouseover="this.style.background='#f8fafc'"
                        onmouseout="this.style.background=''">
                        <td class="px-5 py-3 text-xs">${record.time_entry ?? "—"}</td>
                        <td class="px-5 py-3 text-xs">${person.identify_number ?? "—"}</td>
                        <td class="px-5 py-3 text-xs font-medium text-slate-700">
                            ${person.firstname ?? "—"} ${fullName}
                        </td>
                        <td class="px-5 py-3 text-xs">${gradeLabel}</td>
                        <td class="px-5 py-3 text-xs">${sessionDate}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${status.cls}">
                                ${status.label}
                            </span>
                        </td>
                    </tr>
                `;
            },

            createCard(record) {
                const person = record.enrollment?.student?.person;
                if (!person) return "";
                const fullName = [person.lastname_father, person.lastname_mom]
                    .filter(Boolean)
                    .join(" ");
                const sessionDate = record.assistance_session?.date
                    ? new Date(
                          record.assistance_session.date,
                      ).toLocaleDateString("es-PE", {
                          day: "2-digit",
                          month: "2-digit",
                          year: "numeric",
                      })
                    : "—";
                const statusMap = {
                    present: {
                        label: "Presente",
                        bg: "rgba(16,185,129,0.10)",
                        color: "rgb(5,150,105)",
                    },
                    late: {
                        label: "Tardanza",
                        bg: "rgba(245,158,11,0.10)",
                        color: "rgb(180,115,0)",
                    },
                    justified: {
                        label: "Justificado",
                        bg: "rgba(234,179,8,0.15)",
                        color: "rgb(161,136,0)",
                    },
                    absent: {
                        label: "Ausente",
                        bg: "rgba(239,68,68,0.10)",
                        color: "rgb(220,50,50)",
                    },
                };
                const status = statusMap[record.status] ?? statusMap.absent;

                return `
                    <div class="p-4 bg-white border-b border-slate-100">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    ${person.firstname ?? "—"} ${fullName}
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    DNI: ${person.identify_number ?? "—"} · ${sessionDate}
                                </p>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0"
                                style="background: ${status.bg}; color: ${status.color};">
                                ${status.label}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Hora: ${record.time_entry ?? "—"}</p>
                    </div>
                `;
            },
        });
    }

    /* =============================================
       HELPERS
    ============================================= */
    function reload() {
        if (currentTab === "summary" && tableSummary) {
            tableSummary.state.currentPage = 1;
            tableSummary.loadRecords(0, 15);
        } else if (currentTab === "detail" && tableDetail) {
            tableDetail.state.currentPage = 1;
            tableDetail.loadRecords(0, 15);
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
       TABS
    ============================================= */
    tabSummary?.addEventListener("click", () => {
        if (currentTab === "summary") return;
        currentTab = "summary";

        tabSummary.style.background = "rgb(0,176,202)";
        tabSummary.style.color = "white";
        tabDetail.style.background = "white";
        tabDetail.style.color = "#94a3b8";

        viewSummary.classList.remove("hidden");
        viewDetail.classList.add("hidden");

        if (tableSummary) {
            tableSummary.state.currentPage = 1;
            tableSummary.loadRecords(0, 15);
        }
    });

    tabDetail?.addEventListener("click", () => {
        if (currentTab === "detail") return;
        currentTab = "detail";

        tabDetail.style.background = "rgb(0,176,202)";
        tabDetail.style.color = "white";
        tabSummary.style.background = "white";
        tabSummary.style.color = "#94a3b8";

        viewDetail.classList.remove("hidden");
        viewSummary.classList.add("hidden");

        // Instanciar solo la primera vez
        if (!tableDetail) {
            tableDetail = createDetailEngine();
        } else {
            tableDetail.state.currentPage = 1;
            tableDetail.loadRecords(0, 15);
        }
    });

    /* =============================================
       FILTROS
    ============================================= */

    // Fechas con debounce
    let dateDebounce = null;
    [dateFrom, dateTo].forEach((el) => {
        el?.addEventListener("change", () => {
            clearTimeout(dateDebounce);
            dateDebounce = setTimeout(reload, 300);
        });
    });

    // Horario
    scheduleFilter?.addEventListener("change", () => {
        updateSectionsByGrade();
        reload();
        checkActiveFilters();
    });

    // Grado
    gradeFilter?.addEventListener("change", () => {
        updateSectionsByGrade();
        reload();
        checkActiveFilters();
    });

    // Sección
    sectionFilter?.addEventListener("change", () => {
        reload();
        checkActiveFilters();
    });

    // Período
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
        reload();
    });

    // Exportar PDF
    document.getElementById("btn-export-pdf")?.addEventListener("click", () => {
        window.location.href = buildExportUrl();
    });

    /* =============================================
       CARGA INICIAL — solo resumen
    ============================================= */
    tableSummary = createSummaryEngine();
}
