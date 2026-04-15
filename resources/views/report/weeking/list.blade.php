@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'SCA') . ' - ' . $extend['title'])

@section('content')

<div class="">
    <div class="px-6 lg:px-10 py-6">

        {{-- ALERT CONTAINER --}}
        <div id="alertContainer" class="fixed top-16 right-1 md:right-5 z-[100] w-full max-w-sm pointer-events-none"></div>

        {{-- FILA 1: TÍTULO + ACCIONES --}}
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <h1 class="text-xl font-bold text-slate-800 leading-none">
                        {{ $extend['title'] }}
                    </h1>
                    <p class="text-xs text-gray-500 mt-0.5 font-normal">
                        Consulta el resumen de asistencia semanal por estudiante (lunes a viernes).
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 flex-shrink-0">
                    <button id="btn-export-pdf"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-white text-sm rounded-lg transition-all duration-200 active:scale-95 w-full sm:w-auto"
                        style="background:rgb(220,50,50);box-shadow:0 2px 8px rgba(220,50,50,0.3);"
                        onmouseover="this.style.background='rgb(200,30,30)';"
                        onmouseout="this.style.background='rgb(220,50,50)';">
                        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                        Exportar PDF
                    </button>
                </div>
            </div>
        </div>

        {{-- TOOLBAR --}}
        <div class="bg-white mb-0" style="border:1px solid #e8edf2;border-bottom:none;border-radius:8px 8px 0 0;">

            {{-- FILA 1: Búsqueda + contador --}}
            <div class="px-4 py-2.5 flex items-center gap-2 flex-wrap" style="border-bottom:1px solid #f1f5f9;">

                {{-- Buscador --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md flex-1 min-w-[160px] max-w-xs"
                    style="background:#f4f6f8;border:1px solid #e2e8f0;">
                    <span class="material-symbols-outlined text-[16px] flex-shrink-0 transition-colors" id="searchIcon"
                        style="color:#94a3b8;">search</span>
                    <input type="text" id="searchInput" placeholder="Buscar estudiante..."
                        class="flex-1 text-sm text-slate-700 placeholder:text-slate-300 outline-none bg-transparent min-w-0"
                        onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)';document.getElementById('searchIcon').style.color='rgb(0,176,202)';"
                        onblur="this.parentElement.style.borderColor='#e2e8f0';document.getElementById('searchIcon').style.color='#94a3b8';">
                    <button id="btnClearSearch" class="hidden flex-shrink-0" style="color:#94a3b8;"
                        onmouseover="this.style.color='rgb(220,50,50)';" onmouseout="this.style.color='#94a3b8';">
                        <span class="material-symbols-outlined text-[15px]">close</span>
                    </button>
                </div>

                <div class="flex-1 hidden sm:block"></div>

                {{-- Contador --}}
                <p class="text-xs font-medium hidden sm:block flex-shrink-0" style="color:#94a3b8;">
                    <span class="font-black text-slate-600" id="totalRecord">0</span> estudiantes
                    <span id="totalDaysLabel" class="ml-1 text-[10px]"></span>
                </p>

                {{-- Botón toggle filtros (mobile) --}}
                <button id="btn-toggle-filters"
                    class="flex items-center gap-1 h-8 px-2.5 rounded-lg text-xs font-semibold transition-all flex-shrink-0 sm:hidden"
                    style="background:#f4f6f8;color:#64748b;border:1px solid #e2e8f0;">
                    <span class="material-symbols-outlined text-[15px]">tune</span>
                    Filtros
                </button>
            </div>

            {{-- FILA 2: Filtros --}}
            <div id="filters-row" class="px-4 py-2 flex flex-wrap items-center gap-2">

                <span class="text-[10px] font-bold uppercase tracking-widest hidden sm:block flex-shrink-0 text-slate-500">
                    Filtros:
                </span>

                {{-- Período --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md flex-shrink-0"
                    style="background:#f4f6f8;border:1px solid #e2e8f0;min-width:180px;">
                    <span class="material-symbols-outlined text-[14px]" style="color:#94a3b8;">calendar_month</span>
                    <select id="periodFilter" class="flex-1 text-xs text-slate-700 outline-none bg-transparent cursor-pointer">
                        <option value="">Todos los períodos</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period->codperiod }}" {{ $period->is_active == 'Y' ? 'selected' : '' }}>
                                {{ $period->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Fecha desde --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md flex-shrink-0"
                    style="background:#f4f6f8;border:1px solid #e2e8f0;">
                    <span class="material-symbols-outlined text-[14px]" style="color:#94a3b8;">date_range</span>
                    <input type="date" id="dateFrom" value="{{ $defaultFrom }}"
                        class="text-xs text-slate-700 outline-none bg-transparent cursor-pointer"
                        onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)';"
                        onblur="this.parentElement.style.borderColor='#e2e8f0';">
                </div>

                {{-- Separador --}}
                <span class="text-xs text-slate-400 flex-shrink-0">→</span>

                {{-- Fecha hasta --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md flex-shrink-0"
                    style="background:#f4f6f8;border:1px solid #e2e8f0;">
                    <span class="material-symbols-outlined text-[14px]" style="color:#94a3b8;">event</span>
                    <input type="date" id="dateTo" value="{{ $defaultTo }}"
                        class="text-xs text-slate-700 outline-none bg-transparent cursor-pointer"
                        onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)';"
                        onblur="this.parentElement.style.borderColor='#e2e8f0';">
                </div>

                {{-- Horario --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md flex-shrink-0"
                    style="background:#f4f6f8;border:1px solid #e2e8f0;min-width:190px;">
                    <span class="material-symbols-outlined text-[14px]" style="color:#94a3b8;">schedule</span>
                    <select id="scheduleFilter" class="flex-1 text-xs text-slate-700 outline-none bg-transparent cursor-pointer"
                        onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)';"
                        onblur="this.parentElement.style.borderColor='#e2e8f0';">
                        <option value="">Todos los horarios</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->codschedule }}">
                                {{ $schedule->turn }} · {{ $schedule->time_start }} – {{ $schedule->time_end }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Grado --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md flex-shrink-0"
                    style="background:#f4f6f8;border:1px solid #e2e8f0;min-width:160px;">
                    <span class="material-symbols-outlined text-[14px]" style="color:#94a3b8;">school</span>
                    <select id="gradeFilter" class="flex-1 text-xs text-slate-700 outline-none bg-transparent cursor-pointer"
                        onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)';"
                        onblur="this.parentElement.style.borderColor='#e2e8f0';">
                        <option value="">Todos los grados</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->codgrade }}">{{ $grade->name_large }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sección --}}
                <div id="sectionFilterWrap" class="att-hidden items-center gap-2 px-3 py-1.5 rounded-md flex-shrink-0"
                    style="background:#f4f6f8;border:1px solid #e2e8f0;min-width:120px;">
                    <span class="material-symbols-outlined text-[14px]" style="color:#94a3b8;">tab</span>
                    <select id="sectionFilter" class="flex-1 text-xs text-slate-700 outline-none bg-transparent cursor-pointer"
                        onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)';"
                        onblur="this.parentElement.style.borderColor='#e2e8f0';">
                        <option value="">Todas las secciones</option>
                    </select>
                </div>

                {{-- Limpiar filtros --}}
                <button id="btn-clear-filters"
                    class="att-hidden h-7 px-2.5 rounded-md text-[11px] font-semibold transition-all flex-shrink-0 flex items-center gap-1"
                    style="color:#94a3b8;border:1px solid #e2e8f0;"
                    onmouseover="this.style.color='rgb(220,50,50)';this.style.borderColor='rgba(220,50,50,0.3)';"
                    onmouseout="this.style.color='#94a3b8';this.style.borderColor='#e2e8f0';">
                    <span class="material-symbols-outlined text-[13px]">filter_list_off</span>
                    Limpiar
                </button>

            </div>

            {{-- Aviso de rango de fecha --}}
            <div id="dateRangeMsg"
                class="hidden mx-4 mb-2 px-3 py-1.5 rounded-md text-[11px] font-semibold text-amber-700 flex items-center gap-1.5"
                style="background:rgba(245,158,11,0.10);border:1px solid rgba(245,158,11,0.3);">
                <span class="material-symbols-outlined text-[14px]">info</span>
                <span id="dateRangeMsgText"></span>
            </div>

        </div>

        {{-- TABLA —— solo RESUMEN --}}
        <div id="tableContainer" class="bg-white overflow-hidden relative"
            style="border:1px solid #e8edf2;border-radius:0 0 8px 8px;">

            {{-- SPINNER --}}
            <div id="loadingSpinner"
                class="hidden absolute inset-0 z-20 flex flex-col items-center justify-center gap-3"
                style="background:rgba(255,255,255,0.95);backdrop-filter:blur(2px);">
                <div class="relative w-8 h-8">
                    <div class="absolute inset-0 rounded-full border-2" style="border-color:rgba(0,176,202,0.1);"></div>
                    <div class="absolute inset-0 rounded-full border-2 border-transparent animate-spin"
                        style="border-top-color:rgb(0,176,202);"></div>
                    <div class="absolute inset-1 rounded-full border-2 border-transparent animate-spin"
                        style="border-top-color:rgb(190,214,0);animation-direction:reverse;animation-duration:0.6s;">
                    </div>
                </div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] animate-pulse"
                    style="color:rgba(0,176,202,0.6);">Cargando...</p>
            </div>

            {{-- TABLA DESKTOP --}}
            <div id="view-summary" class="overflow-x-auto custom-scrollbar">
                <table id="recordContainer" class="hidden md:table w-full text-left min-w-[700px]">
                    <thead>
                        <tr style="border-bottom:1px solid #e8edf2;background:#f8fafc;">
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500">Estudiante</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500">Grado / Sección</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 text-center">
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span> Presentes
                                </span>
                            </th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 text-center">
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span> Tardanzas
                                </span>
                            </th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 text-center">
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span> Justif.
                                </span>
                            </th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 text-center">
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span> Ausentes
                                </span>
                            </th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 text-center">% Asist.</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="bg-white divide-y" style="divide-color:#f1f5f9;"></tbody>
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
                <p class="text-xs text-slate-400">Ajusta los filtros o el rango de fechas</p>
            </div>

            {{-- PAGINACIÓN --}}
            <div id="pagination" class="px-5 py-3 bg-slate-50/50" style="border-top:1px solid #e8edf2;"></div>
        </div>

    </div>
</div>

<style>
    @media (max-width: 640px) {
        #filters-row { display: none; }
        #filters-row.open { display: flex; }
    }
    .att-hidden { display: none !important; }
</style>

<script>
    var totalRecords = 0;
    var keyword = null;
</script>

@endsection