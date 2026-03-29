@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'SCA') . ' - ' . $extend['title'])

@section('content')
    <div class="container mx-auto px-2 sm:px-4 py-6">
        <div class="mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
            <div class="w-full lg:w-auto">
                <nav class="flex items-center gap-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-2">
                    <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-slate-600">Gestión de {{ $extend['title'] }}</span>
                </nav>
                <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tighter capitalize leading-tight">
                    {{ $extend['title'] }}
                    <span
                        class="text-lg font-medium text-slate-400 ml-2 tabular-nums hidden">({{ $extend['totalRecord'] }})</span>
                </h1>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-2 mb-8 shadow-sm flex flex-col md:flex-row gap-2">
            <div class="flex items-center gap-3 px-4 h-12 bg-slate-50 border border-slate-100 rounded-xl min-w-[140px]">
                <span class="material-symbols-outlined text-slate-400 text-[20px]">event_repeat</span>
                <div class="flex flex-col flex-1">
                    <span
                        class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400 leading-none mb-0.5">Periodo</span>
                    <select id="periodSelect"
                        class="bg-transparent border-none text-xs font-bold text-slate-700 focus:ring-0 cursor-pointer p-0 h-auto w-full appearance-none">
                        @foreach ($periods as $period)
                            <option value="{{ $period->codperiod }}"
                                {{ session('active_period', null) == $period->codperiod ||
                                (!session()->has('active_period') && $period->actually == 'Y')
                                    ? 'selected'
                                    : '' }}>
                                {{ $period->name_year }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="relative flex-1 group">
                <span
                    class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 group-focus-within:text-blue-600 transition-colors">search</span>
                <input type="text" id="searchInput" placeholder="Buscar por nombre, DNI o especialidad..."
                    class="w-full h-12 pl-12 pr-4 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/10 text-sm font-medium transition-all placeholder:text-slate-400 placeholder:font-normal">
            </div>

            <button id="btnClearSearch"
                class="group hidden md:flex items-center justify-center w-12 h-12 bg-slate-50 text-slate-400 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all active:scale-95"
                title="Limpiar búsqueda">
                <span class="material-symbols-outlined transition-transform group-hover:rotate-90">backspace</span>
            </button>
        </div>


        <div id="alertContainer" class="fixed top-20 right-0 left-0 sm:left-auto sm:right-5 z-[100] px-4 sm:px-0 sm:min-w-[380px]"></div>

        {{-- <div
            class="bg-white rounded-md sm:rounded-lg p-3 sm:p-4 mb-8 border border-slate-100 flex flex-col sm:flex-row gap-4 items-center">
            <div class="relative flex-1 w-full">
                <span
                    class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400">search</span>
                <input type="text" id="searchInput" placeholder="Buscar por nombre, DNI..."
                    class="w-full h-[42px] pl-12 pr-4 py-3 bg-slate-50 border-none rounded-md focus:ring-2 focus:ring-red-600/20 text-sm font-medium transition-all">
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button id="btnClearSearch"
                    class="p-3 h-[42px] bg-slate-50 text-slate-600 rounded-md hover:bg-slate-100 transition-all">
                    <span class="material-symbols-outlined">backspace</span>
                </button>
            </div>
        </div> --}}

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

            <div class="overflow-x-auto overflow-y-hidden custom-scrollbar">
                <table id="recordContainer" class="w-full text-left border-collapse min-w-[800px] lg:min-w-full">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-4 py-5 font-black text-[10px] uppercase tracking-[0.2em] text-slate-400 w-20">Foto
                            </th>
                            <th class="px-4 py-5 font-black text-[10px] uppercase tracking-[0.2em] text-slate-400">
                                Persona</th>
                            <th
                                class="px-4 py-5 font-black text-[10px] uppercase tracking-[0.2em] text-slate-400 text-center">
                                Fecha inscripción</th>
                            <th
                                class="px-4 py-5 font-black text-[10px] uppercase tracking-[0.2em] text-slate-400 text-center">
                                Documentos subidos</th>
                            <th
                                class="px-4 py-5 font-black text-[10px] uppercase text-center tracking-[0.2em] text-slate-400 w-24">
                                Estado</th>
                            <th
                                class="px-4 py-5 font-black text-[10px] uppercase tracking-[0.2em] text-slate-400 text-center w-32">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-slate-50">
                    </tbody>
                </table>
            </div>

            <div id="noResults" class="hidden flex-col items-center justify-center py-10 px-6 text-center">
                <span class="material-symbols-outlined text-slate-300 text-5xl mb-2">person_search</span>
                <h3 class="text-sm font-bold text-slate-900">Sin coincidencias</h3>
            </div>

            <div
    id="paginationContainer"
    class="bg-slate-50/50 px-4 sm:px-6 py-4 border-t border-slate-100 w-full overflow-x-auto"
></div>

        </div>
    </div>

    <div id="deleteModal" class="hidden fixed inset-0 z-[100] p-4 flex items-center justify-center">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div
            class="relative bg-white w-full max-w-sm rounded-[2rem] p-6 sm:p-8 shadow-2xl animate-in zoom-in-95 duration-200">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="material-symbols-outlined text-[32px]">warning</span>
            </div>
            <h3 class="text-xl font-black text-slate-900 text-center mb-2 tracking-tight">¿Confirmar eliminación?</h3>
            <p class="text-slate-500 text-center text-sm font-medium mb-8">Esta acción es irreversible. El periodista
                perderá sus accesos y registros asociados.</p>
            <div class="flex flex-col sm:flex-row gap-3">
                <button id="btnCancelDelete"
                    class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-all order-2 sm:order-1">Cancelar</button>
                <button id="btnConfirmDelete"
                    class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-red-200 hover:bg-blue-700 transition-all order-1 sm:order-2">Eliminar</button>
            </div>
        </div>
    </div>

    <div id="modal-image-main"
        class="modal_delete fixed inset-0 z-[110] bg-slate-900/90 backdrop-blur-md hidden overflow-hidden px-4">
        <button class="absolute top-6 right-6 text-white hover:text-blue-500 transition-colors z-[120]"
            onclick="$('#modal-image-main').fadeOut();">
            <span class="material-symbols-outlined text-4xl">close</span>
        </button>
        <div class="modal-body flex justify-center items-center w-full h-full p-4">
            <img src="" alt="Imagen"
                class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl shadow-black/50">
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
    <script src="{{ asset("js/table.js") }}"></script>
@stop
@endsection
