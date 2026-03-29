@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'Assistance School') . ' - ' . $extend['title'])

@section('content')
    <div class="min-h-screen" style="background: #f4f6f8;">

        {{-- TOPBAR --}}
        <div class="bg-white px-6 lg:px-10 py-4 flex items-center gap-4" style="border-bottom: 1px solid #e8edf2;">

            <a href="{{ route($extend['controller'] . '.list') }}"
                class="group flex items-center justify-center w-8 h-8 rounded-lg flex-shrink-0 transition-all duration-200 active:scale-95 bg-slate-100 hover:bg-slate-200">
                <span
                    class="material-symbols-outlined text-[17px] text-slate-500 group-hover:-translate-x-0.5 transition-transform">
                    arrow_back
                </span>
            </a>

            <div class="w-px h-5 bg-slate-200 flex-shrink-0"></div>

            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route($extend['controller'] . '.list') }}"
                    class="font-medium text-slate-400 hover:text-slate-600 transition-colors">
                    {{ $extend['title'] }}
                </a>
                <span class="material-symbols-outlined text-[14px] text-slate-300">chevron_right</span>
                <span class="font-semibold text-slate-700">
                    {{ isset($modules) ? 'Editar registro' : 'Nuevo registro' }}
                </span>
            </div>

            <span class="ml-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                style="{{ isset($modules)
                    ? 'background: rgba(245,158,11,0.1); color: rgb(217,119,6); border: 1px solid rgba(245,158,11,0.2);'
                    : 'background: rgba(160,185,0,0.1); color: rgb(120,140,0); border: 1px solid rgba(160,185,0,0.2);' }}">
                {{ isset($modules) ? 'Editando' : 'Nuevo' }}
            </span>
        </div>

        {{-- ALERT CONTAINER --}}
        <div id="alertContainer" class="fixed top-16 right-1 md:right-5 z-[100] w-full max-w-sm pointer-events-none"></div>

        {{-- CONTENIDO --}}
        <div class="px-6 lg:px-10 py-6 max-w-3xl mx-auto">

            <form id="mainForm" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" id="recordId" value="{{ $modules->codmodule ?? '' }}">

                <div class="bg-white rounded-lg" style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">

                    {{-- CAMPOS --}}
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div class="space-y-1.5">
                                <label for="codschedule" class="flex items-center gap-1 text-xs text-slate-500">
                                    Módulo Padre <span class="text-red-400">*</span>
                                </label>
                                <select id="codmodule_parent" name="codmodule_parent"
                                    class="tom-select flex-1 min-w-0 h-10 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 appearance-none"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <option value="">Seleccione...</option>
            
                                    @foreach ($modules_father as $mf)
                                        <option value="{{ $mf->codmodule }}"
                                            {{ isset($modules) && $modules->codmodule_parent == $mf->codmodule ? 'selected' : '' }}>
                                            {{ $mf->name_large }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="codmodule_parent"></span>
                            </div>

                            <!-- Nombre largo -->
                            <div class="space-y-1.5">
                                <label for="name_large" class="flex items-center gap-1 text-xs text-slate-500">
                                    Nombre <span class="text-blue-500">*</span>
                                </label>
                                <input type="text" id="name_large" name="name_large"
                                    value="{{ $modules->name_large ?? '' }}" placeholder="Administración"
                                    class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-30"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"></select>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="name_large"></span>
                            </div>

                            <!-- Abreviatura -->
                            <div class="space-y-1.5">
                                <label for="name_short" class="flex items-center gap-1 text-xs text-slate-500">
                                    Abreviatura <span class="text-blue-500">*</span>
                                </label>
                                <input type="text" id="name_short" name="name_short"
                                    value="{{ $modules->name_short ?? '' }}" placeholder="adm"
                                    class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-30"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"></select>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="name_short"></span>
                            </div>

                            <!-- Order -->
                            <div class="space-y-1.5">
                                <label for="order" class="flex items-center gap-1 text-xs text-slate-500">
                                    Orden <span class="text-blue-500">*</span>
                                </label>
                                <input type="text" id="order" name="order" value="{{ $modules->order ?? '' }}"
                                    placeholder="1"
                                    class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-30"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"></select>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="order"></span>
                            </div>

                            <!-- Ruta -->
                            <div class="space-y-1.5">
                                <label for="route" class="flex items-center gap-1 text-xs text-slate-500">
                                    Ruta
                                </label>
                                <input type="text" id="route" name="route" value="{{ $modules->route ?? '' }}"
                                    placeholder="role.list"
                                    class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-30"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"></select>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="route"></span>
                            </div>

                            <!-- Icono -->
                            <div class="space-y-1.5">
                                <label for="icon" class="flex items-center gap-1 text-xs text-slate-500">
                                    Icono <span class="text-blue-500">*</span> | <a
                                        class="text-blue-500 font-medium underline" target="_blank"
                                        href="https://fonts.google.com/icons?selected=Material+Symbols+Outlined:settings:FILL@0;wght@400;GRAD@0;opsz@24&icon.size=24&icon.color=%23e8eaed">Referencia</a>
                                </label>
                                <input type="text" id="icon" name="icon" value="{{ $modules->icon ?? '' }}"
                                    placeholder="settings"
                                    class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-30"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"></select>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="icon"></span>
                            </div>
                        </div>
                        {{-- FOOTER --}}
                        <div class="px-6 py-4 flex flex-col-reverse sm:flex-row items-center justify-between gap-3"
                            style="border-top: 1px solid #f1f5f9; background: #fafbfc;">

                            <p class="text-[11px] text-slate-400">
                                <span class="text-red-400">*</span> Campos obligatorios
                            </p>

                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <a href="{{ request('redirect') ?? route($extend['controller'] . '.list') }}"
                                    class="flex-1 sm:flex-none h-9 px-5 flex items-center justify-center gap-1.5 text-xs font-normal text-slate-600 rounded-lg transition-all bg-slate-200 hover:bg-slate-300 active:scale-95">
                                    <span class="material-symbols-outlined text-[15px]">chevron_left</span>
                                    Cancelar
                                </a>
                                <button type="submit" id="btnSubmit"
                                    class="flex-1 sm:flex-none h-9 px-6 flex items-center justify-center gap-1.5 text-white text-xs font-normal rounded-lg transition-all duration-200 active:scale-95"
                                    style="background: rgb(0,176,202); box-shadow: 0 2px 8px rgba(0,176,202,0.3);"
                                    onmouseover="this.style.background='rgb(190,214,0)'; this.style.boxShadow='0 2px 8px rgba(190,214,0,0.3)'; this.style.color='white';"
                                    onmouseout="this.style.background='rgb(0,176,202)'; this.style.boxShadow='0 2px 8px rgba(0,176,202,0.3)'; this.style.color='white';">
                                    <span id="btnSubmitText">
                                        {{ isset($modules) ? 'Actualizar' : 'Guardar' }}
                                    </span>
                                </button>
                            </div>
                        </div>

                    </div>
            </form>
        </div>
    </div>

    <script>
        const controller = "{{ $extend['controller'] }}";
        const recordId = "{{ $modules->codmodule ?? '' }}";
        const totalRecordsOld = {{ $extend['totalRecord'] }};
        const totalRecords = {{ $extend['totalRecord'] }};
    </script>
@endsection
