import { formatPhoneForWhatsApp } from "../../core/utils";
import TableEngine from "../../modules/table/TableEngine";
import registerPageActions from "./actions";
import { initPreviewModal } from "../../core/modal";

export default function () {
    const table = document.getElementById("recordContainer");
    const tableBody = document.getElementById("tableBody");

    if (!table || !tableBody) return;

    const tableEngine = new TableEngine({
        baseUrl: "/person",
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
            const fullName = [
                record.firstname,
                record.lastname_father,
                record.lastname_mom,
            ]
                .filter(Boolean)
                .join(" ");

            const logoPath = record.photo_url ?? "/img/person.jpg";

            const genderMap = {
                1: {
                    label: "Masculino",
                    bg: "rgba(59,130,246,0.10)", // azul
                    color: "rgb(37,99,235)",
                },
                2: {
                    label: "Femenino",
                    bg: "rgba(236,72,153,0.10)", // rosado
                    color: "rgb(190,24,93)",
                },
                3: {
                    label: "Otro",
                    bg: "rgba(107,114,128,0.10)", // gris
                    color: "rgb(75,85,99)",
                },
            };

            const genderKey = record.codgender || "";
            const gender = genderMap[genderKey] || {
                label: "-",
                bg: "rgba(148,163,184,0.10)",
                color: "rgb(100,116,139)",
            };

            return `
         <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.1s;"
            onmouseover="this.style.background='#f8fafc'"
            onmouseout="this.style.background=''">
                    <td class="px-5 py-3 text-xs">
                        <div class="relative w-12 h-12 group/img">
                            <img src="${logoPath}" data-id="${record.codperson}" 
                                class="btn-preview w-full h-full object-cover rounded-md shadow-sm border-2 border-white group-hover/img:scale-110 transition-transform cursor-pointer"
                                alt="Foto">
                        </div>
                    </td>

                    <td class="px-5 py-3 text-xs">
                        ${record.identify_number}
                    </td>

                    <td class="px-5 py-3 text-xs">
                        ${fullName}
                    </td>
                   <td class="px-5 py-3 text-xs">
                        ${
                            record.phone
                                ? `<a href="https://wa.me/${formatPhoneForWhatsApp(record.phone)}" 
                                    target="_blank"
                                    class="text-green-600 hover:underline">
                                    ${record.phone}
                                </a>`
                                : "Sin registrar"
                        }
                    </td>
                   <td class="px-5 py-3 text-xs">
                        <span class="px-2 py-1 rounded-md text-[11px] font-medium"
                            style="background: ${gender.bg}; color: ${gender.color};">
                            ${gender.label}
                        </span>
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex justify-center gap-1.5">
                             <button class="btn-edit w-7 h-7 flex items-center justify-center rounded transition-all"
                            style="color: #64748b;"
                            onmouseover="this.style.background='rgba(0,176,202,0.08)'; this.style.color='rgb(0,140,165)';"
                            onmouseout="this.style.background=''; this.style.color='#64748b';"
                                data-id="${record.codperson}" 
                                title="Editar">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button class="btn-delete w-7 h-7 flex items-center justify-center rounded transition-all"
                                style="color: #94a3b8;"
                                onmouseover="this.style.background='rgba(239,68,68,0.08)'; this.style.color='rgb(220,50,50)';"
                                onmouseout="this.style.background=''; this.style.color='#94a3b8';" 
                                data-id="${record.codperson}" 
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
            const fullName = [record.lastname_father, record.lastname_mom]
                .filter(Boolean)
                .join(" ");

            const logoPath = record.photo_url ?? "/img/person.jpg";

            return `
                <div class="relative overflow-hidden rounded-xl border border-slate-100 shadow-sm mb-3 bg-white"
                    data-id="${record.codperson}">
                    <div class="p-4">

                        <!-- Header: foto + datos -->
                        <div class="flex items-center gap-3">

                            <img src="${logoPath}"
                                class="w-14 h-14 rounded-xl object-cover border-2 border-slate-100 shadow-sm flex-shrink-0 btn-preview cursor-pointer"
                                data-id="${record.codperson}">

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">
                                    ${record.firstname ?? "-"} ${fullName}
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    DNI: ${record.identify_number ?? "-"}
                                </p>
                                <p class="text-xs text-slate-300 mt-0.5">
                                    Cel: ${record.phone ?? "Sin registrar"}
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
                                data-id="${record.codperson}">
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
                                data-id="${record.codperson}">
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

    initPreviewModal();
    registerPageActions("person");
}
