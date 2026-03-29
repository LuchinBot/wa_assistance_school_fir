import TableEngine from "../../modules/table/TableEngine";
import registerPageActions from "./actions";
import { initPreviewModal } from "../../core/modal";
import initTomSelect from "../../plugins/tomselect";

export default function () {
    const selectedIds = new Set();
    let selectAllMode = false;

    const btnMassiveCarnet = document.getElementById("btnMassiveCarnet");
    const selectAllCheckbox = document.getElementById("selectAll");
    const selectAllMobile = document.getElementById("selectAllMobile");

    const gradeScheduleFilter = document.getElementById("gradeScheduleFilter");
    const btnFilter = document.getElementById("btnFilter");
    const periodFilter = document.getElementById("periodFilter");

    /* ===============================
       TABLE ENGINE
    =============================== */
    const table = document.getElementById("recordContainer");
    const tableBody = document.getElementById("tableBody");

    if (!table || !tableBody) return;

    const tableEngine = new TableEngine({
        baseUrl: "/student",
        search: true,
        pagination: true,
        loading: true,
        extraParams: () => ({
            codgrade_schedule: gradeScheduleFilter?.value ?? "",
            codperiod: periodFilter?.value ?? "",
        }),
        config: {
            recordsPerPage: 5,
        },

        actions: {
            delete: {
                url: "/destroy",
                method: "DELETE",
                modal: "delete",
            },

            edit: {
                url: "/form",
                redirect: true,
            },
        },

        createRow(record) {
            const fullName = [
                record.person?.lastname_father,
                record.person?.lastname_mom,
            ]
                .filter(Boolean)
                .join(" ");

            const logoPath = record.person?.photo_url ?? "/img/person.jpg";
            const checked = selectedIds.has(record.codstudent) ? "checked" : "";

            return `
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td class="px-6 py-4 text-center">
                        <input type="checkbox" class="student-checkbox w-4 h-4" data-id="${record.codstudent}" ${checked}>
                    </td>

                    <td class="px-5 py-3 text-xs">
                        <div class="relative w-12 h-12">
                            <img src="${logoPath}" data-id="${record.codstudent}"
                                class="btn-preview w-full h-full object-cover rounded-md shadow-sm border-2 border-white cursor-pointer">
                        </div>
                    </td>

                    <td class="px-5 py-3 text-xs">
                         <div> ${record.current_enrollment.grade_schedule?.grade?.name_large ?? "-"} - ${record.current_enrollment.grade_schedule?.section ?? "-"}</div>
                        <div class="font-normal text-gray-500">
                             ${record.current_enrollment.grade_schedule?.grade?.level?.name_large ?? "-"}
                        </div>
                    </td>

                      <td class="px-5 py-3 text-xs">
                        ${record.person?.identify_number ?? "-"}
                    </td>


                    <td class="px-5 py-3 text-xs">
                        ${record.person?.firstname ?? "-"} ${fullName}
                    </td>

                      <td class="px-5 py-3 text-xs">
                        ${record.person?.phone ?? "Sin registrar"} 
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex justify-center gap-1.5">
                            <button class="btn-carnet w-7 h-7 flex items-center justify-center rounded transition-all"
                                style="color: #64748b;"
                                onmouseover="this.style.background='rgba(0,176,202,0.08)'; this.style.color='rgb(0,140,165)';"
                                onmouseout="this.style.background=''; this.style.color='#64748b';"
                                data-id="${record.codstudent}" title="Editar">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                    class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                                </svg>
                            </button>
                            <button class="btn-edit w-7 h-7 flex items-center justify-center rounded transition-all"
                                style="color: #64748b;"
                                onmouseover="this.style.background='rgba(0,176,202,0.08)'; this.style.color='rgb(0,140,165)';"
                                onmouseout="this.style.background=''; this.style.color='#64748b';"
                                data-id="${record.codstudent}" title="Editar">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                    class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button class="btn-delete w-7 h-7 flex items-center justify-center rounded transition-all"
                                style="color: #94a3b8;"
                                onmouseover="this.style.background='rgba(239,68,68,0.08)'; this.style.color='rgb(220,50,50)';"
                                onmouseout="this.style.background=''; this.style.color='#94a3b8';"
                                data-id="${record.codstudent}" title="Eliminar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>

                </tr>
            `;
        },

        createCard(record) {
            const fullName = [
                record.person?.lastname_father,
                record.person?.lastname_mom,
            ]
                .filter(Boolean)
                .join(" ");

            const logoPath = record.person?.photo_url ?? "/img/person.jpg";
            const checked = selectedIds.has(record.codstudent) ? "checked" : "";

            return `
                <div class="swipe-card relative overflow-hidden rounded-xl border border-slate-100 shadow-sm mb-3 bg-white" 
                    data-id="${record.codstudent}">

                    <div class="card-content p-4">

                        <!-- Header: checkbox + foto + datos -->
                        <div class="flex items-center gap-3">

                            <input type="checkbox" 
                                class="student-checkbox w-4 h-4 rounded accent-cyan-500 flex-shrink-0" 
                                data-id="${record.codstudent}" ${checked}>

                            <img src="${logoPath}"
                                class="w-14 h-14 rounded-xl object-cover border-2 border-slate-100 shadow-sm flex-shrink-0 btn-preview cursor-pointer"
                                data-id="${record.codstudent}">

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">
                                    ${record.person?.firstname ?? "-"} ${fullName}
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    DNI: ${record.person?.identify_number ?? "-"}
                                </p>
                            </div>

                        </div>

                        <!-- Divider -->
                        <div class="border-t border-slate-100 mt-3 mb-3"></div>

                        <!-- Acciones -->
                        <div class="flex gap-2">

                            <button class="btn-carnet flex-1 flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg text-xs font-semibold transition-all"
                                style="background: rgba(0,176,202,0.10); color: rgb(0,140,165);"
                                onmouseover="this.style.background='rgba(0,176,202,0.20)'"
                                onmouseout="this.style.background='rgba(0,176,202,0.10)'"
                                data-id="${record.codstudent}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                                </svg>
                                Carnet
                            </button>

                            <button class="btn-edit flex-1 flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg text-xs font-semibold transition-all"
                                style="background: rgba(99,102,241,0.10); color: rgb(79,70,229);"
                                onmouseover="this.style.background='rgba(99,102,241,0.20)'"
                                onmouseout="this.style.background='rgba(99,102,241,0.10)'"
                                data-id="${record.codstudent}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                Editar
                            </button>

                            <button class="btn-delete flex items-center justify-center py-2 px-3 rounded-lg transition-all"
                                style="background: rgba(239,68,68,0.08); color: rgb(220,50,50);"
                                onmouseover="this.style.background='rgba(239,68,68,0.18)'"
                                onmouseout="this.style.background='rgba(239,68,68,0.08)'"
                                data-id="${record.codstudent}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>

                        </div>

                    </div>
                </div>
            `;
        },
    });

    /* ===============================
       FUNCIONES CHECKBOX
    =============================== */

    function toggleSelectAll(isChecked) {
        const total = tableEngine.totalRecords;

        if (isChecked) {
            const visible =
                document.querySelectorAll(".student-checkbox").length;

            if (visible < total) {
                showSelectAllNotice(visible, total);
            }

            document.querySelectorAll(".student-checkbox").forEach((cb) => {
                cb.checked = true;
                selectedIds.add(cb.dataset.id);
            });
        } else {
            selectedIds.clear();

            document.querySelectorAll(".student-checkbox").forEach((cb) => {
                cb.checked = false;
            });

            selectAllMode = false;

            document
                .querySelector("#tableContainer .select-all-notice")
                ?.remove();
        }

        updateMassiveButton();
    }

    function showSelectAllNotice(visible, total) {
        document.querySelector("#tableContainer .select-all-notice")?.remove();

        const notice = document.createElement("div");

        notice.className =
            "select-all-notice bg-blue-50 border border-blue-200 text-sm p-3 rounded mb-3"; // ✅ clase identificadora

        notice.innerHTML = `
        Has seleccionado <b>${visible}</b> estudiantes.
        <button id="selectAllResults"
        class="text-blue-600 font-semibold ml-2">
        Seleccionar los ${total} resultados
        </button>
    `;

        const container = document.querySelector("#tableContainer");

        container.prepend(notice);

        document
            .getElementById("selectAllResults")
            .addEventListener("click", () => {
                selectAllMode = true;
                selectedIds.clear();

                notice.innerHTML = `Todos los <b>${total}</b> estudiantes han sido seleccionados.`;

                updateMassiveButton();
            });
    }
    1;

    /* ===============================
       CHECKBOX INDIVIDUAL
    =============================== */

    document.addEventListener("change", function (e) {
        if (e.target.classList.contains("student-checkbox")) {
            const id = e.target.dataset.id;

            if (selectAllMode) {
                return;
            }

            if (e.target.checked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }

            updateMassiveButton();
        }
    });

    /* ===============================
       SELECT ALL DESKTOP
    =============================== */

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener("change", function () {
            toggleSelectAll(this.checked);

            if (selectAllMobile) {
                selectAllMobile.checked = this.checked;
            }
        });
    }

    /* ===============================
       SELECT ALL MOBILE
    =============================== */

    if (selectAllMobile) {
        selectAllMobile.addEventListener("change", function () {
            toggleSelectAll(this.checked);

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = this.checked;
            }
        });
    }

    function updateMassiveButton() {
        if (selectAllMode) {
            btnMassiveCarnet.disabled = false;
            return;
        }

        btnMassiveCarnet.disabled = selectedIds.size === 0;
    }

    /* ===============================
       CARNET INDIVIDUAL
    =============================== */

    document.addEventListener("click", function (e) {
        if (e.target.closest(".btn-carnet")) {
            const id = e.target.closest(".btn-carnet").dataset.id;

            window.open(`/student/carnet/${id}`, "_blank");
        }
    });

    /* ===============================
       CARNET MASIVO
    =============================== */
    btnMassiveCarnet.addEventListener("click", function () {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "/student/carnet-masivo";
        form.target = "_blank";

        const token = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "_token";
        csrfInput.value = token;

        form.appendChild(csrfInput);

        if (selectAllMode) {
            const selectAllInput = document.createElement("input");
            selectAllInput.type = "hidden";
            selectAllInput.name = "select_all";
            selectAllInput.value = 1;

            form.appendChild(selectAllInput);

            // 🔎 BUSQUEDA
            const searchInput = document.querySelector("#searchInput");

            if (searchInput?.value) {
                const search = document.createElement("input");
                search.type = "hidden";
                search.name = "search";
                search.value = searchInput.value;

                form.appendChild(search);
            }

            // 🎓 FILTRO
            if (gradeScheduleFilter?.value) {
                const filter = document.createElement("input");
                filter.type = "hidden";
                filter.name = "codgrade_schedule";
                filter.value = gradeScheduleFilter.value;

                form.appendChild(filter);
            }
        } else {
            const idsInput = document.createElement("input");
            idsInput.type = "hidden";
            idsInput.name = "ids";
            idsInput.value = JSON.stringify(Array.from(selectedIds));

            form.appendChild(idsInput);
        }

        document.body.appendChild(form);
        form.submit();
    });

    btnFilter.addEventListener("click", () => {
        selectAllMode = false;
        selectedIds.clear();

        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        if (selectAllMobile) selectAllMobile.checked = false;

        tableEngine.loadRecords();
    });

    gradeScheduleFilter?.addEventListener("change", () => {
        tableEngine.loadRecords();
    });

    periodFilter?.addEventListener("change", () => {
        tableEngine.loadRecords();
    });

    initPreviewModal();
    registerPageActions("student");

    /* ── EXPORTAR ── */
    function buildExportUrl(base) {
        const params = new URLSearchParams();

        const grade_schedule = gradeScheduleFilter?.value;
        const period = periodFilter?.value;
        const keyword = document.getElementById("searchInput")?.value;

        if (grade_schedule) params.set("grade_schedule", grade_schedule);
        if (period) params.set("period", period);
        if (keyword) params.set("keyword", keyword);

        return `${base}?${params.toString()}`;
    }

    document
        .getElementById("btn-export-excel")
        ?.addEventListener("click", () => {
            window.location.href = buildExportUrl("/student/export/excel");
        });

    document.getElementById("btn-export-pdf")?.addEventListener("click", () => {
        window.location.href = buildExportUrl("/student/export/pdf");
    });
    initTomSelect();
}
