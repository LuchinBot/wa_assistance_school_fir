import TableEngine from "../../modules/table/TableEngine";
import {
    initPreviewModal,
    setupDeleteModal,
    setupResetModal,
} from "../../core/modal";

export default function () {
    const table = document.getElementById("recordContainer");
    const tableBody = document.getElementById("tableBody");

    if (!table || !tableBody) return;

    const tableEngine = new TableEngine({
        baseUrl: "/grade_schedule",
        search: true,
        pagination: true,
        loading: true,

        config: {
            recordsPerPage: 10,
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
            const formatTime = (time) => (time ? time.slice(0, 5) : "-");
            return `
         <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.1s;"
            onmouseover="this.style.background='#f8fafc'"
            onmouseout="this.style.background=''">

                    <td class="px-5 py-3 text-xs">
                        <div class="font-medium text-slate-700">${record.schedule.turn}</div>

                        <div class="flex items-center gap-2 mt-1 text-[11px]">

                            <!-- Inicio -->
                            <span class="flex items-center gap-1 px-2 py-0.5 rounded-md"
                                style="background: rgba(34,197,94,0.10); color: rgb(22,163,74);">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                </svg>
                                ${formatTime(record.schedule.time_start)}
                            </span>

                            <!-- Separador -->
                            <span class="text-slate-300">—</span>

                            <!-- Fin -->
                            <span class="flex items-center gap-1 px-2 py-0.5 rounded-md"
                                style="background: rgba(239,68,68,0.10); color: rgb(220,38,38);">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12h12" />
                                </svg>
                                ${formatTime(record.schedule.time_end)}
                            </span>

                        </div>
                    </td>

                    <td class="px-5 py-3 text-xs">
                        <div>${record.grade.name_large ?? "-"} </div>
                        <div class="font-normal text-gray-500">
                            ${record.grade.name_short ?? "-"} - ${record.section ?? "-"}
                        </div>
                    </td>
                    <td class="px-5 py-3 text-xs">
                        ${record.grade.level.name_large ?? "-"} 
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center gap-1.5">
                             <button class="btn-edit w-7 h-7 flex items-center justify-center rounded transition-all"
                            style="color: #64748b;"
                            onmouseover="this.style.background='rgba(0,176,202,0.08)'; this.style.color='rgb(0,140,165)';"
                            onmouseout="this.style.background=''; this.style.color='#64748b';"
                                data-id="${record.codgrade_schedule}" 
                                title="Editar">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button class="btn-delete w-7 h-7 flex items-center justify-center rounded transition-all"
                                style="color: #94a3b8;"
                                onmouseover="this.style.background='rgba(239,68,68,0.08)'; this.style.color='rgb(220,50,50)';"
                                onmouseout="this.style.background=''; this.style.color='#94a3b8';" 
                                data-id="${record.codgrade_schedule}" 
                                title="Eliminar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            </div>
                    </td>

                </tr>
            `;
        },

        createCard(record) {
            return `
                <div class="relative overflow-hidden rounded-xl border border-slate-100 shadow-sm mb-3 bg-white"
                    data-id="${record.codgrade_schedule}">
                    <div class="p-4">

                        <!-- Info -->
                        <div class="flex items-start justify-between gap-3">

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">
                                    ${record.grade.name_large ?? "-"}
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    ${record.grade.level.name_large ?? "-"}
                                </p>
                            </div>

                            <!-- Sección badge -->
                            <span class="flex-shrink-0 text-xs font-bold px-3 py-1 rounded-full"
                                style="background: rgba(0,176,202,0.10); color: rgb(0,140,165);">
                                ${record.grade.name_short ?? "-"} - ${record.section ?? "-"}
                            </span>

                        </div>

                        <!-- Horario -->
                        <div class="flex items-center gap-2 mt-3 px-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #94a3b8;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span class="text-xs" style="color: #64748b;">
                                <span class="font-semibold text-slate-700">${record.schedule.turn}</span>
                                &nbsp;·&nbsp; ${record.schedule.time_start ?? "-"} – ${record.schedule.time_end ?? "-"}
                            </span>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-slate-100 mt-3 mb-3"></div>

                        <!-- Acciones -->
                        <div class="flex gap-2">

                            <button class="btn-edit flex-1 flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg text-xs font-semibold transition-all"
                                style="background: rgba(99,102,241,0.10); color: rgb(79,70,229);"
                                ontouchstart="this.style.background='rgba(99,102,241,0.20)'"
                                ontouchend="this.style.background='rgba(99,102,241,0.10)'"
                                data-id="${record.codgrade_schedule}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                Editar
                            </button>

                            <button class="btn-delete flex items-center justify-center py-2 px-3 rounded-lg transition-all"
                                style="background: rgba(239,68,68,0.08); color: rgb(220,50,50);"
                                ontouchstart="this.style.background='rgba(239,68,68,0.18)'"
                                ontouchend="this.style.background='rgba(239,68,68,0.08)'"
                                data-id="${record.codgrade_schedule}">
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

    /* ---------- PAGE ACTIONS ---------- */
    setupDeleteModal();
    initPreviewModal();
}
