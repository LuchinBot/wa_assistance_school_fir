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
        baseUrl: "/rolpermission",
        search: true,
        pagination: true,
        loading: true,

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
            return `
         <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.1s;"
            onmouseover="this.style.background='#f8fafc'"
            onmouseout="this.style.background=''">
                    <td class="px-5 py-3 text-xs">
                        ${record.profile.name_large ?? "-"} 
                    </td>
                      <td class="px-5 py-3 text-xs">
                        ${record.permission.name ?? "-"} 
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center gap-1.5">
                             <button class="btn-edit w-7 h-7 flex items-center justify-center rounded transition-all"
                            style="color: #64748b;"
                            onmouseover="this.style.background='rgba(0,176,202,0.08)'; this.style.color='rgb(0,140,165)';"
                            onmouseout="this.style.background=''; this.style.color='#64748b';"
                                data-id="${record.codprofile_permission}" 
                                title="Editar">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button class="btn-delete w-7 h-7 flex items-center justify-center rounded transition-all"
                                style="color: #94a3b8;"
                                onmouseover="this.style.background='rgba(239,68,68,0.08)'; this.style.color='rgb(220,50,50)';"
                                onmouseout="this.style.background=''; this.style.color='#94a3b8';" 
                                data-id="${record.codprofile_permission}" 
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
                    data-id="${record.codprofile_permission}">
                    <div class="p-4">

                        <!-- Info -->
                        <div class="flex items-center gap-3">

                            <!-- Ícono rol -->
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                style="background: rgba(99,102,241,0.10);">
                                <svg class="w-5 h-5" style="color: rgb(79,70,229);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">
                                    ${record.profile?.name_large ?? "-"}
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5 truncate">
                                    ${record.permission?.name ?? "-"}
                                </p>
                            </div>

                        </div>

                        <!-- Divider -->
                        <div class="border-t border-slate-100 mt-3 mb-3"></div>

                        <!-- Acciones -->
                        <div class="flex gap-2">

                            <button class="btn-edit flex-1 flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg text-xs font-semibold transition-all"
                                style="background: rgba(99,102,241,0.10); color: rgb(79,70,229);"
                                ontouchstart="this.style.background='rgba(99,102,241,0.20)'"
                                ontouchend="this.style.background='rgba(99,102,241,0.10)'"
                                data-id="${record.codprofile_permission}">
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
                                data-id="${record.codprofile_permission}">
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
