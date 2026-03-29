import TableEngine from "../../modules/table/TableEngine";
import registerPageActions from "./actions";
import { initPreviewModal } from "../../core/modal";

export default function () {
    const sessionFilter = document.getElementById("sessionFilter");
    const gradeFilter = document.getElementById("gradeFilter");
    const sectionFilter = document.getElementById("sectionFilter");
    const sectionFilterWrap = document.getElementById("sectionFilterWrap");
    const tabPresent = document.getElementById("tab-present");
    const tabAbsent = document.getElementById("tab-absent");

    const btnToggleFilters = document.getElementById("btn-toggle-filters");
    const filtersRow = document.getElementById("filters-row");
    const btnClearFilters = document.getElementById("btn-clear-filters");
    const periodFilter = document.getElementById("periodFilter");

    let currentTab = "present"; // 'present' | 'absent'

    const table = document.getElementById("recordContainer");
    const tableBody = document.getElementById("tableBody");

    if (!table || !tableBody) return;

    const tableEngine = new TableEngine({
        baseUrl: () =>
            currentTab === "present" ? "/assistance" : "/assistance/absents",
        search: true,
        pagination: true,
        loading: true,

        config: {
            recordsPerPage: 5,
        },

        extraParams: () => ({
            session: sessionFilter?.value ?? "",
            grade: gradeFilter?.value ?? "",
            grade_schedule: sectionFilter?.value ?? "",
            codperiod: periodFilter?.value ?? "",
        }),

        actions: {
            delete: {
                url: "/destroy",
                method: "DELETE",
                modal: "delete",
            },

            preview: {
                url: "",
                method: "",
                modal: "preview",
            },
        },
        createRow(record) {
            // detectar si viene de ausentes
            const student =
                currentTab === "absent"
                    ? (record.student ?? record)
                    : record.enrollment.student;

            const person = student.person;

            const fullName = [person.lastname_father, person.lastname_mom]
                .filter(Boolean)
                .join(" ");

            const logoPath = person?.photo_url ?? "/img/person.jpg";

            const statusMap = {
                present: {
                    label: "Presente",
                    cls: "bg-emerald-100 text-emerald-700",
                },
                late: { label: "Tardanza", cls: "bg-amber-100 text-amber-700" },
                absent: { label: "Ausente", cls: "bg-red-100 text-red-700" },
                justified: {
                    label: "Justificado",
                    cls: "bg-yellow-100 text-yellow-700",
                },
            };

            const statusKey =
                currentTab === "absent" ? "absent" : record.status;

            const status = statusMap[statusKey] ?? statusMap.absent;

            const timeEntry =
                currentTab === "absent" ? "—" : (record.time_entry ?? "-");

            const obs =
                currentTab === "absent"
                    ? ""
                    : record.observation
                      ? `<span class="block text-xs text-slate-400 mt-0.5">${record.observation}</span>`
                      : "";

            return `
         <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.1s;"
            onmouseover="this.style.background='#f8fafc'"
            onmouseout="this.style.background=''">

                    <td class="px-5 py-3 text-xs">
                        <div class="relative w-12 h-12 group/img">
                            <img src="${logoPath}" data-id="${record.codassistance}" 
                                class="btn-preview w-full h-full object-cover rounded-md shadow-sm border-2 border-white group-hover/img:scale-110 transition-transform cursor-pointer"
                                alt="Foto">
                        </div>
                    </td>

                    <td class="px-5 py-3 text-xs">
                        ${timeEntry}
                    </td>

                    <td class="px-5 py-3 text-xs">
                        ${person.identify_number ?? "-"}
                    </td>

                    <td class="px-5 py-3 text-xs">
                        ${person.firstname ?? "-"} ${fullName}
                    </td>
                       <td class="px-5 py-3 text-xs">
                        ${person.phone ?? "Sin registrar"}
                    </td>

                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${status.cls}">
                            ${status.label}
                        </span>
                    </td>
                </tr>
            `;
        },

        createCard(record) {
            const student =
                currentTab === "absent"
                    ? (record.student ?? record)
                    : record.enrollment.student;

            const person = student.person;

            const fullName = [person.lastname_father, person.lastname_mom]
                .filter(Boolean)
                .join(" ");

            const logoPath = person?.photo_url ?? "/img/person.jpg";

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
                absent: {
                    label: "Ausente",
                    bg: "rgba(239,68,68,0.10)",
                    color: "rgb(220,50,50)",
                },
                justified: {
                    label: "Justificado",
                    bg: "rgba(234,179,8,0.15)",
                    color: "rgb(180,115,0)",
                },
            };

            const statusKey =
                currentTab === "absent" ? "absent" : record.status;

            const status = statusMap[statusKey] ?? statusMap.absent;

            const timeEntry =
                currentTab === "absent" ? "—" : (record.time_entry ?? "-");
            const obs =
                currentTab !== "absent" && record.observation
                    ? `<p class="text-xs mt-1" style="color: #94a3b8;">${record.observation}</p>`
                    : "";

            // Solo asistencia (no ausentes) tiene acciones delete/reset
            const actions =
                currentTab !== "absent"
                    ? `
                        <div class="border-t border-slate-100 mt-3 mb-3"></div>
                        <div class="flex gap-2">
                            <button class="btn-reset flex-1 flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg text-xs font-semibold transition-all"
                                style="background: rgba(245,158,11,0.10); color: rgb(180,115,0);"
                                ontouchstart="this.style.background='rgba(245,158,11,0.20)'"
                                ontouchend="this.style.background='rgba(245,158,11,0.10)'"
                                data-id="${record.codassistance}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                Resetear
                            </button>
                            <button class="btn-delete flex items-center justify-center py-2 px-3 rounded-lg transition-all"
                                style="background: rgba(239,68,68,0.08); color: rgb(220,50,50);"
                                ontouchstart="this.style.background='rgba(239,68,68,0.18)'"
                                ontouchend="this.style.background='rgba(239,68,68,0.08)'"
                                data-id="${record.codassistance}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        `
                    : "";

            return `
                    <div class="relative overflow-hidden rounded-xl border border-slate-100 shadow-sm mb-3 bg-white"
                        data-id="${record.codassistance}">
                        <div class="p-4">

                            <!-- Header: foto + datos + badge -->
                            <div class="flex items-center gap-3">

                                <img src="${logoPath}"
                                    class="w-14 h-14 rounded-xl object-cover border-2 border-slate-100 shadow-sm flex-shrink-0 btn-preview cursor-pointer"
                                    data-id="${record.codassistance}">

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 truncate">
                                        ${person.firstname ?? "-"} ${fullName}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        Cel: ${person.phone ?? "Sin registrar"}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        DNI: ${person.identify_number ?? "-"}
                                    </p>
                                </div>

                                <!-- Badge estado -->
                                <span class="flex-shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full"
                                    style="background: ${status.bg}; color: ${status.color};">
                                    ${status.label}
                                </span>

                            </div>

                            <!-- Hora entrada -->
                            <div class="flex items-center gap-2 mt-3 px-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #94a3b8;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <span class="text-xs" style="color: #64748b;">
                                    Entrada: <span class="font-semibold text-slate-700">${timeEntry}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                `;
        },
    });

    /* ── SESIÓN → actualiza grados disponibles ── */
    sessionFilter?.addEventListener("change", () => {
        updateGradesBySession();
        reload();
    });

    /* ── SECCIÓN ── */
    sectionFilter?.addEventListener("change", () => reload());

    /* ── TABS PRESENTE / AUSENTE ── */
    tabPresent?.addEventListener("click", () => {
        currentTab = "present";
        tabPresent.style.background = "rgb(0,176,202)";
        tabPresent.style.color = "white";
        tabAbsent.style.background = "white";
        tabAbsent.style.color = "#94a3b8";
        reload();
    });

    tabAbsent?.addEventListener("click", () => {
        currentTab = "absent";

        tabAbsent.style.background = "rgb(0,176,202)";
        tabAbsent.style.color = "white";
        tabPresent.style.background = "white";
        tabPresent.style.color = "#94a3b8";

        reload();
    });

    /* ── HELPER: cargar grados del horario de la sesión seleccionada ── */
    async function updateGradesBySession() {
        const selectedOption =
            sessionFilter?.options[sessionFilter.selectedIndex];
        const codschedule = selectedOption?.dataset?.schedule;

        // Reset grado y sección
        gradeFilter.innerHTML = '<option value="">Todos los grados</option>';
        sectionFilter.innerHTML =
            '<option value="">Todas las secciones</option>';
        sectionFilterWrap.classList.add("att-hidden");

        if (!codschedule) return;

        try {
            const res = await fetch(
                `/grade_schedule/by-schedule/${codschedule}`,
            );
            const data = await res.json();

            if (data.success && data.data.length) {
                data.data.forEach((grade) => {
                    const opt = document.createElement("option");
                    opt.value = grade.codgrade;
                    opt.textContent = grade.name_large;
                    gradeFilter.appendChild(opt);
                });
            }
        } catch (e) {
            console.error("Error cargando grados:", e);
        }
    }

    /* ── HELPER: cargar secciones del grado+horario seleccionado ── */
    async function updateSectionsByGrade() {
        const gradeId = gradeFilter?.value;
        const selectedOpt = sessionFilter?.options[sessionFilter.selectedIndex];
        const codschedule = selectedOpt?.dataset?.schedule;

        sectionFilter.innerHTML =
            '<option value="">Todas las secciones</option>';

        if (!gradeId || !codschedule) {
            sectionFilterWrap.classList.add("att-hidden");
            return;
        }

        try {
            const res = await fetch(
                `/grade_schedule/sections?grade=${gradeId}&schedule=${codschedule}`,
            );
            const data = await res.json();

            if (data.success && data.data.length) {
                sectionFilterWrap.classList.remove("att-hidden");
                data.data.forEach((gs) => {
                    const opt = document.createElement("option");
                    opt.value = gs.codgrade_schedule;
                    opt.textContent = `Sección ${gs.section}`;
                    sectionFilter.appendChild(opt);
                });
            } else {
                sectionFilterWrap.classList.add("att-hidden");
            }
        } catch (e) {
            console.error("Error cargando secciones:", e);
        }
    }

    /* ── HELPER: cargar ausentes ── */
    // async function loadAbsents() {
    //     const sessionId = sessionFilter?.value;
    //     const gradeId = gradeFilter?.value;
    //     const sectionId = sectionFilter?.value;

    //     if (!sessionId) {
    //         alert("Selecciona una sesión para ver ausentes");
    //         tabPresent.click();
    //         return;
    //     }

    //     engine.showLoading();

    //     try {
    //         const params = new URLSearchParams({ session: sessionId });
    //         if (gradeId) params.set("grade", gradeId);
    //         if (sectionId) params.set("grade_schedule", sectionId);

    //         const res = await fetch(`/assistance/absents?${params}`);
    //         const data = await res.json();

    //         document.getElementById("totalRecord").textContent =
    //             data.total ?? 0;
    //         document.getElementById("tableBody").innerHTML = "";

    //         if (!data.data?.length) {
    //             engine.showNoResults();
    //             return;
    //         }

    //         engine.hideNoResults();
    //         data.data.forEach((student) => {
    //             document
    //                 .getElementById("tableBody")
    //                 .insertAdjacentHTML("beforeend", renderAbsentRow(student));
    //         });
    //     } catch (e) {
    //         console.error("Error cargando ausentes:", e);
    //     } finally {
    //         engine.hideLoading();
    //     }
    // }

    // function renderAbsentRow(student) {
    //     const fullName = [
    //         student.person?.lastname_father,
    //         student.person?.lastname_mom,
    //     ]
    //         .filter(Boolean)
    //         .join(" ");

    //     const photo = student.person?.photo_url ?? "/img/person.jpg";

    //     return `
    //         <tr style="border-bottom:1px solid #f1f5f9; transition:background .1s;"
    //             onmouseover="this.style.background='#fef9f0'"
    //             onmouseout="this.style.background=''">
    //             <td class="px-5 py-3">
    //                 <img src="${photo}" class="w-10 h-10 rounded-md object-cover border-2 border-white shadow-sm">
    //             </td>
    //             <td class="px-5 py-3 text-xs text-slate-400">—</td>
    //             <td class="px-5 py-3 text-xs">${student.person?.identify_number ?? "—"}</td>
    //             <td class="px-5 py-3 text-xs font-medium text-slate-700">
    //                 ${student.person?.firstname ?? ""} ${fullName}
    //             </td>
    //             <td class="px-5 py-3 text-center">
    //                 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600">
    //                     Ausente
    //                 </span>
    //             </td>
    //             <td class="px-5 py-3 text-center text-xs text-slate-300">—</td>
    //         </tr>
    //     `;
    // }

    function reload() {
        tableEngine.state.currentPage = 1;
        tableEngine.loadRecords(0, tableEngine.config.recordsPerPage);
    }

    initPreviewModal();
    registerPageActions("assistance");

    // Al cargar, inicializa grados de la sesión por defecto
    if (sessionFilter?.value) updateGradesBySession();

    /* ── EXPORTAR ── */
    function buildExportUrl(base) {
        const params = new URLSearchParams();
        const period = periodFilter?.value;
        const session = sessionFilter?.value;
        const grade = gradeFilter?.value;
        const section = sectionFilter?.value;
        const keyword = document.getElementById("searchInput")?.value;

        if (session) params.set("session", session);
        if (period) params.set("period", period);
        if (grade) params.set("grade", grade);
        if (section) params.set("grade_schedule", section);
        if (keyword) params.set("keyword", keyword);
        params.set("tab", currentTab); // para saber si exportar ausentes

        console.log(params.toString());
        return `${base}?${params.toString()}`;
    }

    document
        .getElementById("btn-export-excel")
        ?.addEventListener("click", () => {
            window.location.href = buildExportUrl("/assistance/export/excel");
        });

    document.getElementById("btn-export-pdf")?.addEventListener("click", () => {
        window.location.href = buildExportUrl("/assistance/export/pdf");
    });

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
        gradeFilter.innerHTML = '<option value="">Todos los grados</option>';
        sectionFilter.innerHTML =
            '<option value="">Todas las secciones</option>';
        sectionFilterWrap.classList.add("att-hidden");
        sectionFilterWrap.classList.remove("flex");
        btnClearFilters.classList.add("att-hidden");
        sessionFilter.selectedIndex = 0;
        reload();
    });

    // Mostrar botón limpiar cuando hay filtros activos
    function checkActiveFilters() {
        const hasFilters = gradeFilter.value || sectionFilter.value;
        if (hasFilters) {
            btnClearFilters.classList.remove("att-hidden");
            btnClearFilters.classList.add("flex");
        } else {
            btnClearFilters.classList.add("att-hidden");
            btnClearFilters.classList.remove("flex");
        }
    }

    // Llama checkActiveFilters en cada cambio de filtro
    gradeFilter?.addEventListener("change", () => {
        updateSectionsByGrade();
        reload();
        checkActiveFilters();
    });
    sectionFilter?.addEventListener("change", () => {
        reload();
        checkActiveFilters();
    });
    periodFilter?.addEventListener("change", () => {
        tableEngine.loadRecords();
    });
}
