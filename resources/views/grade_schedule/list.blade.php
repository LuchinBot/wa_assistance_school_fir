@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'Assistance School') . ' - ' . $extend['title'])

@section('content')

    <div class="">


        <div class="px-6 lg:px-10 py-6">

            {{-- ALERT CONTAINER --}}
            <div id="alertContainer" class="fixed top-16 right-1 md:right-5 z-[100] w-full max-w-sm pointer-events-none">
            </div>

            {{-- TÍTULO + ACCIONES --}}
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

                    <div class="flex-shrink-0">
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
            <div class="bg-white px-4 py-3 mb-0 flex items-center gap-3"
                style="border: 1px solid #e8edf2; border-bottom: none; border-radius: 8px 8px 0 0;">

                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md w-64"
                    style="background: #f4f6f8; border: 1px solid #e2e8f0;">
                    <span class="material-symbols-outlined text-[16px] flex-shrink-0 transition-colors" id="searchIcon"
                        style="color: #94a3b8;">search</span>
                    <input type="text" id="searchInput" placeholder="Buscar..."
                        class="flex-1 text-sm text-slate-700 placeholder:text-slate-300 outline-none bg-transparent"
                        onfocus="this.parentElement.style.borderColor='rgba(0,176,202,0.5)'; document.getElementById('searchIcon').style.color='rgb(0,176,202)';"
                        onblur="this.parentElement.style.borderColor='#e2e8f0'; document.getElementById('searchIcon').style.color='#94a3b8';">
                    <button id="btnClearSearch" class="hidden flex-shrink-0 transition-colors" style="color: #94a3b8;"
                        onmouseover="this.style.color='rgb(220,50,50)';" onmouseout="this.style.color='#94a3b8';">
                        <span class="material-symbols-outlined text-[15px]">close</span>
                    </button>
                </div>

                <div class="flex-1"></div>

                <p class="text-xs font-medium hidden sm:block" style="color: #94a3b8;">
                    <span id="totalRecord" class="font-black text-slate-600">{{ $extend['totalRecord'] }}</span> registros
                </p>
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
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Horario</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Grado y sección</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500">Nivel académico</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500 text-center w-28">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white">
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
                    ¿Estás seguro de que deseas eliminar este registro
                    <strong class="text-slate-700">permanentemente</strong>?
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
        var controller = "{{ $extend['controller'] }}";
        var totalRecordsOld = {{ $extend['totalRecord'] }};
        var totalRecords = {{ $extend['totalRecord'] }};
        var countRecords = {{ count($data) }};
        var keyword = null;
        var user_verified = "{{ Auth::user()->verified ?? 0 }}";
    </script>


@endsection
