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
                        <p class="text-sm text-gray-500 mt-0.5 font-normal">
                            Gestiona los registros de {{ strtolower($extend['title']) }}.
                        </p>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="flex flex-col sm:flex-row gap-2 flex-shrink-0">
                        @if (in_array(optional(Auth::user()->profile)->name_short, ['auxiliary', 'suadmin', 'executive']))

                            @if ($opening)
                                {{-- Indicador sesión activa --}}
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg w-full sm:w-auto"
                                    style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.2);">
                                    <span class="relative flex h-2 w-2 shrink-0">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                    </span>
                                    <div class="leading-tight">
                                        <p class="text-sm font-black text-green-700">Horario activo <span class="font-normal hidden"> (desde {{ \Carbon\Carbon::parse($opening->time_opening)->format('h:i A') }})</span></p>
                                        <p class="text-xs font-medium" style="color: rgba(21,128,61,0.7);">
                                            {{ $opening->schedule->turn ?? 'Sin turno' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Cerrar sesión --}}
                                <button id="btn-closing"
                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-white text-sm rounded-lg transition-all duration-200 active:scale-95 w-full sm:w-auto"
                                    style="background: rgb(220,50,50); box-shadow: 0 2px 8px rgba(220,50,50,0.3);"
                                    onmouseover="this.style.background='rgb(200,30,30)';"
                                    onmouseout="this.style.background='rgb(220,50,50)';">
                                    <span class="material-symbols-outlined text-[16px]">lock</span>
                                    Finalizar asistencia
                                </button>

                                {{-- Tomar asistencia --}}
                                <a href="{{ route($extend['controller'] . '.take') }}"
                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-white text-sm rounded-lg transition-all duration-200 active:scale-95 w-full sm:w-auto"
                                    style="background: rgb(0,176,202); box-shadow: 0 2px 8px rgba(0,176,202,0.3);"
                                    onmouseover="this.style.background='rgb(190,214,0)'; this.style.boxShadow='0 2px 8px rgba(190,214,0,0.3)'; this.style.color='white';"
                                    onmouseout="this.style.background='rgb(0,176,202)'; this.style.boxShadow='0 2px 8px rgba(0,176,202,0.3)'; this.style.color='white';">
                                    <span class="material-symbols-outlined text-[16px]">qr_code_scanner</span>
                                    Tomar asistencia
                                </a>
                            @else
                                {{-- Aperturar --}}
                                <button id="btn-opening"
                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-white text-sm rounded-lg transition-all duration-200 active:scale-95 w-full sm:w-auto"
                                    style="background: rgb(34,197,94); box-shadow: 0 2px 8px rgba(34,197,94,0.3);"
                                    onmouseover="this.style.background='rgb(22,163,74)';"
                                    onmouseout="this.style.background='rgb(34,197,94)';">
                                    <span class="material-symbols-outlined text-[16px]">add_circle</span>
                                    Aperturar asistencia
                                </button>
                            @endif

                        @endif

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
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>

                    {{-- Tab presentes / ausentes --}}
                    <div class="flex items-center rounded-lg overflow-hidden flex-shrink-0"
                        style="border: 1px solid #e2e8f0;">
                        <button id="tab-present" class="h-8 px-3 text-xs font-bold transition-all flex items-center gap-1"
                            style="background: rgb(0,176,202); color: white;">
                            <span class="material-symbols-outlined text-sm">how_to_reg</span>
                            <span class="hidden sm:inline">Presentes</span>
                        </button>
                        <button id="tab-absent" class="h-8 px-3 text-xs font-bold transition-all flex items-center gap-1"
                            style="background: white; color: #94a3b8;">
                            <span class="material-symbols-outlined text-sm">person_off</span>
                            <span class="hidden sm:inline">Ausentes</span>
                        </button>
                    </div>

                    <div class="flex-1 hidden sm:block"></div>

                    {{-- Contador --}}
                    <p class="text-xs font-medium flex-shrink-0" style="color: #94a3b8;">
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
                            <span class="material-symbols-outlined text-sm">table_view</span>
                            <span class="hidden md:inline">Excel</span>
                        </button>
                        <button id="btn-export-pdf"
                            class="flex items-center gap-1 h-8 px-2.5 rounded-lg text-xs font-semibold transition-all active:scale-95"
                            style="background: rgba(239,68,68,0.08); color: rgb(220,50,50); border: 1px solid rgba(239,68,68,0.2);"
                            onmouseover="this.style.background='rgba(239,68,68,0.15)';"
                            onmouseout="this.style.background='rgba(239,68,68,0.08)';" title="Exportar PDF">
                            <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
                            <span class="hidden md:inline">PDF</span>
                        </button>
                    </div>

                    {{-- Botón toggle filtros (mobile) --}}
                    <button id="btn-toggle-filters"
                        class="flex items-center gap-1 h-8 px-2.5 rounded-lg text-sm font-semibold transition-all flex-shrink-0 sm:hidden"
                        style="background: #f4f6f8; color: #64748b; border: 1px solid #e2e8f0;">
                        <span class="material-symbols-outlined text-sm">tune</span>
                        Filtros
                    </button>
                </div>

                {{-- FILA 2: Filtros --}}
                <div id="filters-row" class="px-4 py-2 flex flex-wrap items-center gap-2">

                    {{-- Label filtros (desktop) --}}
                    <span
                        class="text-[10px] font-bold uppercase tracking-widest hidden sm:block flex-shrink-0 text-slate-500">Filtros:</span>


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

                    {{-- Filtro sesión --}}
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-md flex-shrink-0"
                        style="background: #f4f6f8; border: 1px solid #e2e8f0; min-width: 190px;">
                        <span class="material-symbols-outlined text-[14px] flex-shrink-0"
                            style="color: #94a3b8;">event_note</span>
                        <select id="sessionFilter"
                            class="flex-1 text-xs text-slate-700 outline-none bg-transparent cursor-pointer min-w-0"
                            onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)';"
                            onblur="this.parentElement.style.borderColor='#e2e8f0';">
                            <option value="">Todas las sesiones</option>
                            @foreach ($sessions as $session)
                                <option value="{{ $session->codassistance_session }}"
                                    data-schedule="{{ $session->codschedule }}" {{ $loop->first ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}
                                    · {{ $session->schedule->turn ?? 'Sin turno' }}
                                    @if (!$session->time_ending)
                                        🟢
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro grado --}}
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-md flex-shrink-0"
                        style="background: #f4f6f8; border: 1px solid #e2e8f0; min-width: 160px;">
                        <span class="material-symbols-outlined text-[14px] flex-shrink-0"
                            style="color: #94a3b8;">school</span>
                        <select id="gradeFilter"
                            class="flex-1 text-xs text-slate-700 outline-none bg-transparent cursor-pointer min-w-0"
                            onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)';"
                            onblur="this.parentElement.style.borderColor='#e2e8f0';">
                            <option value="">Todos los grados</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade->codgrade }}">{{ $grade->name_large }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro sección --}}
                    <div id="sectionFilterWrap" class="att-hidden items-center gap-2 px-3 py-1.5 rounded-md flex-shrink-0"
                        style="background: #f4f6f8; border: 1px solid #e2e8f0; min-width: 120px;">
                        <span class="material-symbols-outlined text-[14px] flex-shrink-0"
                            style="color: #94a3b8;">tab</span>
                        <select id="sectionFilter"
                            class="flex-1 text-xs text-slate-700 outline-none bg-transparent cursor-pointer min-w-0"
                            onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)';"
                            onblur="this.parentElement.style.borderColor='#e2e8f0';">
                            <option value="">Todas las secciones</option>
                        </select>
                    </div>

                    {{-- Limpiar filtros --}}
                    <button id="btn-clear-filters"
                        class="att-hidden h-7 px-2.5 rounded-md text-[11px] font-semibold transition-all flex-shrink-0 flex items-center gap-1"
                        style="color: #94a3b8; border: 1px solid #e2e8f0;"
                        onmouseover="this.style.color='rgb(220,50,50)'; this.style.borderColor='rgba(220,50,50,0.3)';"
                        onmouseout="this.style.color='#94a3b8'; this.style.borderColor='#e2e8f0';">
                        <span class="material-symbols-outlined text-[13px]">filter_list_off</span>
                        Limpiar
                    </button>

                </div>
            </div>
            {{-- TABLA --}}
            <div id="tableContainer" class="bg-white overflow-hidden relative"
                style="border: 1px solid #e8edf2; border-radius: 0 0 8px 8px;">

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
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Foto</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Hora de ingreso</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Documento</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Nombre completo</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Celular</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500 text-center">Observación</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white divide-y" style="divide-color: #f1f5f9;">
                        </tbody>
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


    {{-- MODAL APERTURAR ASISTENCIA --}}
    <div id="openingModal"
        class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center px-4 transition-all duration-300"
        style="background: rgba(0,20,40,0.4); backdrop-filter: blur(4px);">
        <div class="modal-content scale-95 opacity-0 w-full max-w-md bg-white rounded-xl overflow-hidden transition-all duration-300"
            style="border: 1px solid #e8edf2; box-shadow: 0 20px 40px rgba(0,0,0,0.12);">

            <div class="px-5 py-4 flex items-center gap-3" style="border-bottom: 1px solid #f1f5f9;">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                    style="background: rgba(34,197,94,0.1);">
                    <span class="material-symbols-outlined text-[17px] text-green-500">event_available</span>
                </div>
                <div>
                    <p class="text-md font-black text-slate-800 leading-none">Aperturar asistencia</p>
                    <p class="text-xs text-slate-400 mt-0.5">Crear nueva toma de asistencia</p>
                </div>
            </div>

            <form id="formOpening">
                <div class="p-5 space-y-4">

                    {{-- Fecha informativa --}}
                    <div class="flex items-center gap-3 px-4 py-3 rounded-lg"
                        style="background: #edeff1; border: 1px solid #e8edf2;">
                        <span class="material-symbols-outlined text-[17px]" style="color: #94a3b8;">calendar_today</span>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest" style="color: #94a3b8;">Fecha de
                                apertura</p>
                            <p id="opening_date_label" class="text-[13px] font-bold text-slate-700"></p>
                        </div>
                    </div>

                    {{-- Schedule --}}
                    <div>
                        <label class="text-sm font-bold text-slate-500 block mb-1">Horarios disponibles</label>
                        <select name="codschedule" id="codschedule"
                            class="w-full h-10 rounded-lg px-3 text-sm outline-none transition-colors"
                            style="border: 1px solid #e2e8f0; color: #334155;"
                            onfocus="this.style.borderColor='rgba(0,176,202,0.5)';"
                            onblur="this.style.borderColor='#e2e8f0';">
                            @foreach ($schedules as $schedule)
                                <option value="{{ $schedule->codschedule }}">
                                    {{ $schedule->turn . ' - ' . $schedule->time_start . ' a ' . $schedule->time_end ?? 'Horario ' . $schedule->codschedule }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="px-5 pb-5 flex gap-2">
                    <button type="button" id="closeOpeningModal"
                        class="flex-1 h-9 rounded-lg text-sm font-bold text-slate-600 transition-all active:scale-95 bg-slate-100 hover:bg-slate-200">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 h-9 rounded-lg text-white text-sm font-bold transition-all active:scale-95"
                        style="background: rgb(34,197,94);" onmouseover="this.style.background='rgb(22,163,74)';"
                        onmouseout="this.style.background='rgb(34,197,94)';">
                        Aperturar
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- MODAL CERRAR SESIÓN --}}
    <div id="closingModal"
        class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center px-4 transition-all duration-300"
        style="background: rgba(0,20,40,0.4); backdrop-filter: blur(4px);">
        <div class="modal-content scale-95 opacity-0 w-full max-w-sm bg-white rounded-xl overflow-hidden transition-all duration-300 delay-75"
            style="border: 1px solid #e8edf2; box-shadow: 0 20px 40px rgba(0,0,0,0.12);">

            <div class="px-5 py-4 flex items-center gap-3" style="border-bottom: 1px solid #f1f5f9;">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 bg-red-50">
                    <span class="material-symbols-outlined text-[17px] text-red-500">lock</span>
                </div>
                <div>
                    <p class="text-md font-black text-slate-800 leading-none">¿Finalizar asistencia?</p>
                    <p class="text-xs text-slate-400 mt-0.5">Esta acción no se puede deshacer</p>
                </div>
            </div>

            <div class="px-5 pb-4 space-y-1">
                <p class="text-sm text-slate-500 hidden">Se registrará la hora de finalización de la asistencia:</p>
                @if ($opening)
                    <div class="flex items-center gap-3 px-4 py-3 rounded-lg"
                        style="background: #f8fafc; border: 1px solid #e8edf2;">
                        <span class="material-symbols-outlined text-[17px]" style="color: #94a3b8;">schedule</span>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest" style="color: #94a3b8;">Asistencia
                                aperturada desde</p>
                            <p class="text-sm font-bold text-slate-700 truncate">
                                {{ \Carbon\Carbon::parse($opening->time_opening)->format('h:i A') }}
                                · {{ $opening->schedule->turn ?? 'Sin turno' }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="px-5 pb-5 flex gap-2">
                <button id="closeClosingModal"
                    class="flex-1 h-9 rounded-lg text-xs font-bold text-slate-600 transition-all active:scale-95 bg-slate-100 hover:bg-slate-200">
                    Cancelar
                </button>
                <button id="btnConfirmClosing"
                    class="flex-1 h-9 rounded-lg text-white text-xs font-bold transition-all active:scale-95"
                    style="background: rgb(220,50,50);" onmouseover="this.style.background='rgb(200,30,30)';"
                    onmouseout="this.style.background='rgb(220,50,50)';">
                    Cerrar sesión
                </button>
            </div>
        </div>
    </div>
    <style>
        /* Mobile: filtros colapsables */
        @media (max-width: 640px) {
            #filters-row {
                display: none;
            }

            #filters-row.open {
                display: flex;
            }
        }
    </style>

    <script>
        var totalRecordsOld = {{ $extend['totalRecord'] }};
        var totalRecords = {{ $extend['totalRecord'] }};
        var countRecords = {{ count($data) }};
        var keyword = null;
        var user_verified = "{{ Auth::user()->verified ?? 0 }}";
    </script>

@endsection
