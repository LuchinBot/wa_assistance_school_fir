@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'SCA') . ' - ' . $extend['title'])

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="mb-7 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 mb-2">
                    <a href="{{ route('home') }}"
                        class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400 hover:text-blue-600 transition-colors">
                        Dashboard
                    </a>
                    <span class="material-symbols-outlined text-[12px] text-slate-300">chevron_right</span>
                    <span class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">
                        {{ $extend['title'] }}
                    </span>
                </nav>

                <h1 class="text-2xl sm:text-3xl font-black text-[#0c1527] leading-none">
                    {{ $extend['title'] }}
                </h1>
                <p class="text-[12px] text-slate-400 mt-1.5 [font-family:'DM_Sans',sans-serif]">
                    <span class="font-semibold text-slate-600">{{ $extend['totalRecord'] }}</span> registros en total
                </p>
            </div>

            <a href="{{ route($extend['controller'] . '.form') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#0c1527] hover:bg-blue-700 text-white text-[12px] font-bold rounded-xl shadow-[0_4px_20px_rgba(12,21,39,0.25)] hover:shadow-[0_4px_24px_rgba(29,78,216,0.4)] transition-all duration-200 active:scale-95 w-full sm:w-auto">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                Nuevo Registro
            </a>
        </div>
        <div id="alertContainer" class="fixed top-20 right-0 left-0 sm:left-auto sm:right-5 z-[100] px-4 sm:px-0 sm:min-w-[380px]"></div>

        <div class="bg-white rounded-2xl p-2 mb-8 shadow-sm flex flex-col md:flex-row gap-2">
            <div class="relative flex-1 group">
                <span
                    class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 group-focus-within:text-blue-600 transition-colors">search</span>
                <input type="text" id="searchInput" placeholder="Buscar por nombre, abreviatura o fecha de fundación..."
                    class="w-full h-12 pl-12 pr-4 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/10 text-sm font-medium transition-all placeholder:text-slate-400 placeholder:font-normal">
            </div>

            <button id="btnClearSearch"
                class="group hidden md:flex items-center justify-center w-12 h-12 bg-slate-50 text-slate-400 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all active:scale-95"
                title="Limpiar búsqueda">
                <span class="material-symbols-outlined transition-transform group-hover:rotate-90">backspace</span>
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_2px_16px_rgba(0,0,0,0.06)] overflow-hidden relative">

        {{-- Spinner --}}
        <div id="loadingSpinner"
             class="hidden absolute inset-0 z-20 backdrop-blur-sm bg-white/80 flex flex-col justify-center items-center gap-3">
            <div class="relative w-10 h-10 flex items-center justify-center">
                <div class="absolute inset-0 animate-spin rounded-full border-[3px] border-slate-100 border-t-blue-600"></div>
                <span class="material-symbols-outlined text-[14px] text-slate-400 animate-pulse">sync</span>
            </div>
            <p class="text-[9px] font-black uppercase tracking-[0.22em] text-slate-400">Sincronizando...</p>
        </div>

        {{-- Tabla scroll horizontal en mobile --}}
        <div class="overflow-x-auto custom-scrollbar">
            <table id="recordContainer" class="w-full text-left border-collapse min-w-[640px]">
                <thead>
                    <tr class="bg-[#0c1527]/[0.03] border-b border-slate-100">
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                Parámetro</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                Valor</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                Descripción</th>
                            <th class="px-5 text-center py-3.5 text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="bg-white divide-y divide-gray-200 overflow-hidden hover:overflow-hidden">
                        <!-- Los registros se cargarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>

            {{-- Sin resultados --}}
        <div id="noResults" class="hidden flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mb-4 mx-auto">
                <span class="material-symbols-outlined text-slate-300 text-[32px]">person_search</span>
            </div>
            <p class="text-[13px] font-bold text-slate-700">Sin coincidencias</p>
            <p class="text-[12px] text-slate-400 mt-1 [font-family:'DM_Sans',sans-serif]">Intenta con otro término de búsqueda</p>
        </div>

        {{-- Paginación --}}
        <div id="paginationContainer"
             class="bg-slate-50/60 px-4 sm:px-6 py-3.5 border-t border-slate-100 overflow-x-auto">
        </div>
    </div>


        <div id="deleteModal"
        class="opacity-0 pointer-events-none fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center px-4 transition-all duration-300">
        <div
            class="modal-content scale-95 opacity-0 w-full max-w-sm bg-white rounded-2xl border border-slate-100 shadow-[0_24px_60px_rgba(0,0,0,0.2)] overflow-hidden transition-all duration-300 delay-75">
            {{-- Header oscuro --}}
            <div class="bg-[#0c1527] px-6 py-5 flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-xl bg-red-500/15 border border-red-500/20 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-red-400 text-[20px]">warning</span>
                </div>
                <div>
                    <p class="text-[13px] font-black text-white">¿Eliminar registro?</p>
                    <p class="text-[11px] text-blue-300/50 mt-0.5 font-sans">Esta acción no se puede deshacer</p>
                </div>
            </div>

            <div class="px-6 py-5">
                <p class="text-[13px] text-slate-500 font-sans">¿Estás seguro de que deseas eliminar este registro
                    permanentemente?</p>
            </div>

            <div class="px-6 pb-5 flex gap-2.5">
                <button onclick="closeDeleteModal()"
                    class="flex-1 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-[12px] font-bold uppercase tracking-[0.12em] transition-all active:scale-95">
                    Cancelar
                </button>
                <button id="btnConfirmDelete"
                    class="flex-1 h-10 rounded-xl bg-red-500 hover:bg-red-600 text-white text-[12px] font-bold uppercase tracking-[0.12em] shadow-[0_4px_16px_rgba(239,68,68,0.3)] transition-all active:scale-95">
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <script>
    window.App = {
        controller: "{{ $extend['controller'] }}",
        totalRecordsOld: {{ $extend['totalRecord'] }},
        totalRecords: {{ $extend['totalRecord'] }},
        countRecords: {{ count($data) }},
        keyword: null,
        user_verified: "{{ Auth::user()->verified ?? 0 }}"
    };
</script>

@section('script')
    <script src="{{ asset("js/$extend[controller]/index.js") }}"></script>
    <script src="{{ asset("js/$extend[controller]/table.js") }}"></script>
    <script src="{{ asset('js/table.js') }}"></script>
@stop

@endsection
