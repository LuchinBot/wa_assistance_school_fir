@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'SCA') . ' - ' . $extend['title'])

@section('content')
    <div class="container mx-auto px-2 sm:px-4 py-6">
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

        <div id="alertContainer"
            class="fixed top-20 right-0 left-0 sm:left-auto sm:right-5 z-[100] px-4 sm:px-0 sm:min-w-[380px]"></div>

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

        <div class="bg-white rounded-lg shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">

            <div id="loadingSpinner"
                class="hidden absolute inset-0 z-20 backdrop-blur-sm bg-white/70 flex flex-col justify-center items-center py-10">
                <div class="relative flex items-center justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-slate-100 border-t-red-600"></div>
                    <span class="material-symbols-outlined absolute text-slate-400 text-sm animate-pulse">sync</span>
                </div>
                <p class="mt-2 text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 animate-pulse">
                    Sincronizando...</p>
            </div>
            <div class="overflow-x-auto overflow-y-hidden custom-scrollbar flex-1">
                <table id="recordContainer" class="w-full text-left border-collapse min-w-[800px] lg:min-w-full">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                Año</th>
                            <th
                                class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                Actual</th>
                            <th
                                class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                Inscripciones Abiertas</th>
                            <th
                                class="px-6 py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="bg-white divide-y divide-slate-50">
                    </tbody>
                </table>
            </div>

            <div id="noResults" class="hidden flex-col items-center justify-center py-10 px-6 text-center">
                <span class="material-symbols-outlined text-slate-300 text-5xl mb-2">person_search</span>
                <h3 class="text-sm font-bold text-slate-900">Sin coincidencias</h3>
            </div>

            <div id="paginationContainer"
                class="bg-slate-50/50 px-4 sm:px-6 py-4 border-t border-slate-100 w-full overflow-x-auto"></div>

        </div>
    </div>

    <div id="deleteModal" class="hidden fixed inset-0 z-[100] p-4 flex items-center justify-center">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white w-full max-w-sm rounded-[2rem] p-8 shadow-2xl animate-in zoom-in-95 duration-200">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="material-symbols-outlined text-[32px]">warning</span>
            </div>
            <h3 class="text-xl font-black text-slate-900 text-center mb-2 tracking-tight">¿Eliminar registro?</h3>
            <p class="text-slate-500 text-center text-sm font-medium mb-8">Esta acción no se puede deshacer. ¿Estás seguro?
            </p>
            <div class="flex gap-3">
                <button id="btnCancelDelete"
                    class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-all">Cancelar</button>
                <button id="btnConfirmDelete"
                    class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-red-200 hover:bg-blue-700 transition-all">Eliminar</button>
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
