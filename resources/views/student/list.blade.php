@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'SCA') . ' - ' . $extend['title'])

@section('content')

    <div class="">
        <div class="px-6 lg:px-10 py-6">

            {{-- ALERT CONTAINER --}}
            <div id="alertContainer" class="fixed top-16 right-1 md:right-5 z-[100] w-full max-w-sm pointer-events-none">
            </div>

            {{-- FILA 1: TÍTULO + ACCIONES --}}
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                    <div class="space-y-1.5">
                        <h1 class="text-xl font-bold text-slate-800 leading-none">
                            {{ $extend['title'] }}
                        </h1>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">
                            Gestiona los registros de {{ strtolower($extend['title']) }}.
                        </p>
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button id="btnMassiveCarnet" disabled
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm rounded-lg transition-all duration-200 active:scale-95 disabled:opacity-40 disabled:grayscale"
                            style="background: rgba(16,185,129,0.20); color: rgb(5,150,105); border: 1px solid rgba(16,185,129,0.35);"
                            onmouseover="if(!this.disabled){this.style.background='rgb(16,185,129)'; this.style.color='white';}"
                            onmouseout="if(!this.disabled){this.style.background='rgba(16,185,129,0.20)'; this.style.color='rgb(5,150,105)';}">

                            <span class="material-symbols-outlined text-[16px]">contact_emergency</span>
                            <span class="whitespace-nowrap">Carnet Masivo</span>
                        </button>

                        <a href="{{ route($extend['controller'] . '.form') }}"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-white text-sm rounded-lg transition-all duration-200 active:scale-95"
                            style="background: rgb(0,176,202); box-shadow: 0 2px 8px rgba(0,176,202,0.3);"
                            onmouseover="this.style.background='rgb(190,214,0)'; this.style.boxShadow='0 2px 8px rgba(190,214,0,0.3)'; this.style.color='white';"
                            onmouseout="this.style.background='rgb(0,176,202)'; this.style.boxShadow='0 2px 8px rgba(0,176,202,0.3)'; this.style.color='white';">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            Nuevo Registro
                        </a>
                    </div>

                </div>
            </div>

            {{-- TOOLBAR --}}
            <div class="bg-white mb-0" style="border: 1px solid #e8edf2; border-bottom: none; border-radius: 8px 8px 0 0;">

                {{-- FILA 1: Búsqueda + Tabs + Export --}}
                <div class="px-4 py-2.5 flex items-center gap-2 flex-wrap" style="border-bottom: 1px solid #f1f5f9;">

                    {{-- Buscador --}}
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-md flex-1 min-w-[160px] max-w-xs"
                        style="background: #f4f6f8; border: 1px solid #e2e8f0;">
                        <span class="material-symbols-outlined text-[16px] flex-shrink-0 transition-colors" id="searchIcon"
                            style="color: #94a3b8;">search</span>
                        <input type="text" id="searchInput" placeholder="Buscar..."
                            class="flex-1 text-sm text-slate-700 placeholder:text-slate-300 outline-none bg-transparent min-w-0"
                            onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)'; document.getElementById('searchIcon').style.color='rgb(0,176,202)';"
                            onblur="this.parentElement.style.borderColor='#e2e8f0'; document.getElementById('searchIcon').style.color='#94a3b8';">
                        <button id="btnClearSearch" class="hidden flex-shrink-0" style="color: #94a3b8;"
                            onmouseover="this.style.color='rgb(220,50,50)';" onmouseout="this.style.color='#94a3b8';">
                            <span class="material-symbols-outlined text-[15px]">close</span>
                        </button>
                    </div>

                    <div class="flex-1 hidden sm:block"></div>

                    {{-- Contador --}}
                    <p class="text-xs font-medium hidden sm:block flex-shrink-0" style="color: #94a3b8;">
                        <span class="font-black text-slate-600" id="totalRecord">{{ $extend['totalRecord'] }}</span>
                        registros
                    </p>


                    {{-- Exportar --}}
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button id="btn-export-excel"
                            class="flex items-center gap-1 h-8 px-2.5 rounded-lg text-xs font-semibold transition-all active:scale-95"
                            style="background: rgba(34,197,94,0.08); color: rgb(22,163,74); border: 1px solid rgba(34,197,94,0.2);"
                            onmouseover="this.style.background='rgba(34,197,94,0.15)';"
                            onmouseout="this.style.background='rgba(34,197,94,0.08)';" title="Exportar Excel">
                            <span class="material-symbols-outlined text-[15px]">table_view</span>
                            <span class="hidden md:inline">Excel</span>
                        </button>
                        <button id="btn-export-pdf"
                            class="flex items-center gap-1 h-8 px-2.5 rounded-lg text-xs font-semibold transition-all active:scale-95"
                            style="background: rgba(239,68,68,0.08); color: rgb(220,50,50); border: 1px solid rgba(239,68,68,0.2);"
                            onmouseover="this.style.background='rgba(239,68,68,0.15)';"
                            onmouseout="this.style.background='rgba(239,68,68,0.08)';" title="Exportar PDF">
                            <span class="material-symbols-outlined text-[15px]">picture_as_pdf</span>
                            <span class="hidden md:inline">PDF</span>
                        </button>
                    </div>
                </div>

                {{-- FILTROS ESTUDIANTES --}}
                <div id="filters-row" class="px-4 py-2 flex flex-wrap items-center gap-2">


                    {{-- Label filtros --}}
                    <span
                        class="text-[10px] font-bold uppercase tracking-widest hidden sm:block flex-shrink-0 text-slate-500">
                        Filtros:
                    </span>

                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-md flex-shrink-0"
                        style="background:#f4f6f8;border:1px solid #e2e8f0;min-width:180px;">

                        <span class="material-symbols-outlined text-[14px]" style="color:#94a3b8;">
                            calendar_month
                        </span>

                        <select id="periodFilter"
                            class="flex-1 text-xs text-slate-700 outline-none bg-transparent cursor-pointer">

                            @foreach ($periods as $period)
                                <option value="{{ $period->codperiod }}" {{ $period->is_active == 'Y' ? 'selected' : '' }}>
                                    {{ $period->name }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                    {{-- Filtro grado/sección/turno --}}
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-md flex-shrink-0"
                        style="background:#f4f6f8;border:1px solid #e2e8f0;min-width:220px;">

                        <span class="material-symbols-outlined text-[14px] flex-shrink-0" style="color:#94a3b8;">
                            school
                        </span>

                        <select id="gradeScheduleFilter"
                            class="flex-1 text-xs text-slate-700 outline-none bg-transparent cursor-pointer min-w-0"
                            onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)';"
                            onblur="this.parentElement.style.borderColor='#e2e8f0';">

                            <option value="">Todos los grados y sección</option>

                            @foreach ($grade_schedules as $gs)
                                <option value="{{ $gs->codgrade_schedule }}">
                                    {{ $gs->grade->name_short }} · {{ $gs->section }} · {{ $gs->schedule->turn }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                    {{-- Botón filtrar --}}
                    <button id="btnFilter"
                        class="h-7 hidden px-3 rounded-md text-[11px] font-semibold transition-all flex-shrink-0 flex items-center gap-1"
                        style="color:white;background:#0f172a;" onmouseover="this.style.background='#1e293b';"
                        onmouseout="this.style.background='#0f172a';">

                        <span class="material-symbols-outlined text-[13px]">
                            filter_alt
                        </span>

                        Filtrar
                    </button>
                </div>
            </div>


            <div id="tableContainer" class="bg-white overflow-hidden relative"
                style="border: 1px solid #e8edf2; border-radius: 0 0 8px 8px;">
                <div id="mobileSelectionBar"
                    class="md:hidden flex items-center gap-3 px-4 py-3 bg-slate-50 border-b border-slate-200">

                    <input type="checkbox" id="selectAllMobile" class="w-4 h-4 rounded border-slate-300 cursor-pointer"
                        style="accent-color: rgb(0,176,202);">

                    <span class="text-sm font-medium text-slate-600">
                        Seleccionar todos
                    </span>

                </div>

                {{-- SPINNER --}}
                <div id="loadingSpinner"
                    class="hidden absolute inset-0 z-20 flex flex-col items-center justify-center gap-3"
                    style="background: rgba(255,255,255,0.95); backdrop-filter: blur(2px);">
                    <div class="relative w-8 h-8">
                        <div class="absolute inset-0 rounded-full border-2" style="border-color: rgba(0,176,202,0.1);">
                        </div>
                        <div class="absolute inset-0 rounded-full border-2 border-transparent animate-spin"
                            style="border-top-color: rgb(0,176,202);"></div>
                        <div class="absolute inset-1 rounded-full border-2 border-transparent animate-spin"
                            style="border-top-color: rgb(190,214,0); animation-direction: reverse; animation-duration: 0.6s;">
                        </div>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] animate-pulse"
                        style="color: rgba(0,176,202,0.6);">Sincronizando...</p>
                </div>

                {{-- TABLA DESKTOP --}}
                <div class="overflow-x-auto custom-scrollbar">
                    <table id="recordContainer" class="hidden md:table w-full text-left min-w-[640px]">

                        <thead>
                            <tr style="border-bottom: 1px solid #e8edf2; background: #f8fafc;">
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500 text-center w-14">

                                    <input type="checkbox" id="selectAll"
                                        class="w-4 h-4 rounded border-slate-300 cursor-pointer"
                                        style="accent-color: rgb(0,176,202);">

                                </th>

                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Foto</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Grado y sección</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Documento</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Nombre completo</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Celular</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500 text-center w-28">Acciones</th>
                            </tr>
                        </thead>

                        <tbody id="tableBody" class="bg-white divide-y"></tbody>

                    </table>

                </div>

                {{-- CARDS MOBILE --}}
                <div id="cardContainer" class="md:hidden divide-y bg-white"></div>

                {{-- SIN RESULTADOS --}}
                <div id="noResults" class="hidden flex-col items-center justify-center py-16 px-6 text-center">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3 bg-slate-100">
                        <span class="material-symbols-outlined text-[20px] text-slate-400">person_search</span>
                    </div>
                    <p class="text-sm font-black text-slate-600 mb-0.5">Sin coincidencias</p>
                    <p class="text-xs text-slate-400">Intenta con otro término de búsqueda</p>
                </div>

                {{-- PAGINACIÓN --}}
                <div id="pagination" class="px-5 py-3 bg-slate-50/50" style="border-top: 1px solid #e8edf2;"></div>

            </div>
        </div>
    </div>


    {{-- MODAL PREVIEW IMAGEN --}}
    <div id="previewModal" class="fixed inset-0 z-[110] hidden overflow-hidden"
        style="background: rgba(15,25,50,0.9); backdrop-filter: blur(8px);">
        <span
            class="material-symbols-outlined absolute top-5 right-5 text-4xl text-white cursor-pointer z-[120] transition-colors"
            id="closePreviewModal" onmouseover="this.style.color='rgb(0,176,202)'"
            onmouseout="this.style.color='white'">close</span>
        <div class="flex justify-center items-center w-full h-full p-4">
            <div class="bg-white p-2 rounded-xl shadow-2xl overflow-hidden" style="max-width: 90%; max-height: 90%;">
                <img src="" alt="Imagen" class="max-w-full max-h-[80vh] object-contain">
            </div>
        </div>
    </div>

    {{-- MODAL ELIMINAR --}}
    <div id="deleteModal"
        class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center px-4 transition-all duration-300"
        style="background: rgba(0,20,40,0.4); backdrop-filter: blur(4px);">
        <div class="modal-content scale-95 opacity-0 w-full max-w-[340px] bg-white rounded-xl overflow-hidden transition-all duration-300"
            style="border: 1px solid #e8edf2; box-shadow: 0 20px 40px rgba(0,0,0,0.12);">

            <div class="px-5 py-4 flex items-center gap-3" style="border-bottom: 1px solid #f1f5f9;">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 bg-red-50">
                    <span class="material-symbols-outlined text-[17px] text-red-500">warning</span>
                </div>
                <div>
                    <p class="text-sm font-black text-slate-800 leading-none">¿Eliminar registro?</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Esta acción no se puede deshacer</p>
                </div>
            </div>

            <div class="px-5 py-4">
                <p class="text-sm text-slate-500 leading-relaxed">
                    ¿Estás seguro de que deseas eliminar este registro <strong
                        class="text-slate-700">permanentemente</strong>?
                </p>
            </div>

            <div class="px-5 pb-4 flex gap-2">
                <button id="btnCancelDelete"
                    class="flex-1 h-9 rounded-lg text-xs font-bold text-slate-600 transition-all active:scale-95 bg-slate-100 hover:bg-slate-200">
                    Cancelar
                </button>
                <button id="btnConfirmDelete"
                    class="flex-1 h-9 rounded-lg text-white text-xs font-bold transition-all active:scale-95"
                    style="background: rgb(220,50,50);" onmouseover="this.style.background='rgb(200,30,30)';"
                    onmouseout="this.style.background='rgb(220,50,50)';">
                    Eliminar
                </button>
            </div>
        </div>
    </div>


    <script>
        var totalRecordsOld = {{ $extend['totalRecord'] }};
        var totalRecords = {{ $extend['totalRecord'] }};
        var countRecords = {{ count($data) }};
        var keyword = null;
        var user_verified = "{{ Auth::user()->verified ?? 0 }}";
    </script>

@endsection
