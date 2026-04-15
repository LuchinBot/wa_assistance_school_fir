@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'Assistance School') . ' - ' . $extend['title'])
@section('navbar_breadcrumb')
    <div class="flex items-center justify-between gap-4 flex-1">

        <div class="flex items-center gap-3">
            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route($extend['controller'] . '.list') }}"
                    class="font-medium text-slate-400 hover:text-slate-600 transition-colors">
                    {{ $extend['title'] }}
                </a>
                <span class="material-symbols-outlined text-md text-slate-300">chevron_right</span>
                <span class="font-semibold text-slate-700">
                    {{ isset($user) ? 'Editar ' . $extend['title_form'] : 'Nuevo ' . $extend['title_form'] }}
                </span>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="px-2 md:px-7 py-0">
        {{-- ALERT CONTAINER --}}
        <div id="alertContainer" class="fixed top-20 right-1 md:right-5 z-[100] w-full max-w-sm pointer-events-none"></div>

        {{-- CONTENIDO --}}
        <div class="px-6 lg:px-10 py-6">
            <form id="mainForm" class="space-y-4" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" id="recordId" value="{{ $person->codperson ?? '' }}">
                <input type="hidden" name="redirect" value="{{ $redirect }}">
                {{-- <input type="number" id="codgender" name="codgender" value="{{ $person->codgender ?? '' }}"> --}}

                {{-- CARD: INFORMACIÓN DE IDENTIDAD --}}
                <div class="bg-white rounded-lg overflow-hidden"
                    style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">

                    <div class="px-6 py-3" style="border-bottom: 1px solid #f1f5f9; background: #fafbfc;">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em]" style="color: #94a3b8;">
                            Información de Identidad
                        </p>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                            {{-- Foto --}}
                            <div class="md:col-span-3 flex flex-col items-center gap-4 pb-6 md:pb-0 md:pr-6 border-b md:border-b-0 md:border-r"
                                style="border-color: #f1f5f9;">

                                <div id="photoPreview"
                                    class="w-36 h-36 overflow-hidden rounded-xl flex items-center justify-center"
                                    style="background: #f8fafc; border: 2px dashed #e2e8f0;">
                                    @if (isset($person) && $person->photo_url)
                                        <img src="{{ $person->photo_url }}" alt="Foto"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div class="flex flex-col items-center justify-center p-4 text-center">
                                            <span class="material-symbols-outlined text-[40px] mb-1"
                                                style="color: #cbd5e1;">account_circle</span>
                                            <p class="text-[9px] font-bold uppercase tracking-tight"
                                                style="color: #94a3b8;">Sin Foto</p>
                                        </div>
                                    @endif
                                </div>

                                <label for="photo"
                                    class="w-full cursor-pointer h-9 px-4 flex items-center justify-center gap-1.5 text-white text-xs font-medium rounded-lg transition-all duration-200 active:scale-95"
                                    style="background: rgb(0,176,202); box-shadow: 0 2px 8px rgba(0,176,202,0.3);"
                                    onmouseover="this.style.background='rgb(190,214,0)'; this.style.boxShadow='0 2px 8px rgba(190,214,0,0.3)';"
                                    onmouseout="this.style.background='rgb(0,176,202)'; this.style.boxShadow='0 2px 8px rgba(0,176,202,0.3)';">
                                    <span class="material-symbols-outlined text-[15px]">photo_camera</span>
                                    Subir Foto
                                    <input type="file" id="photo" name="photo" accept="image/*" class="hidden">
                                </label>
                                <p class="text-[9px] font-medium uppercase tracking-wide" style="color: #94a3b8;">JPG, PNG ·
                                    Máx. 2MB</p>
                            </div>

                            {{-- Campos identidad --}}
                            <div class="md:col-span-9 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                    {{-- Tipo de documento --}}
                                    <div class="space-y-1.5">
                                        <label for="codtd_identify" class="flex items-center gap-1 text-xs text-slate-500">
                                            Tipo de Documento <span class="text-red-400">*</span>
                                        </label>
                                        <select id="codtd_identify" name="codtd_identify"
                                            class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 cursor-pointer"
                                            style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                            onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                            <option value="">Seleccione...</option>
                                            @foreach ($documentTypes as $docType)
                                                <option data-td="{{ $docType->name_short }}"
                                                    value="{{ $docType->codtd_identify }}"
                                                    {{ isset($person) && $person->codtd_identify == $docType->codtd_identify ? 'selected' : '' }}>
                                                    {{ $docType->name_short }} - {{ $docType->name_large }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="error-message hidden text-[11px] font-medium text-red-500"
                                            data-error-for="codtd_identify"></span>
                                    </div>

                                    {{-- Número de documento --}}
                                    <div class="space-y-1.5">
                                        <label for="identify_number" class="flex items-center gap-1 text-xs text-slate-500">
                                            Número de Documento <span class="text-red-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" id="identify_number" name="identify_number"
                                                value="{{ $person->identify_number ?? '' }}" placeholder="12345678"
                                                class="w-full h-10 px-3.5 pr-12 text-sm text-slate-700 rounded-md outline-none transition-all duration-200"
                                                style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                                onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                            <button type="button" id="btnDocument"
                                                {{ isset($person) ? 'disabled' : '' }}
                                                class="absolute right-1 top-1 h-8 px-3 flex items-center justify-center rounded-md text-white text-xs transition-all active:scale-95 disabled:opacity-40"
                                                style="background: rgb(0,176,202);"
                                                onmouseover="if(!this.disabled){this.style.background='rgb(0,140,165)';}"
                                                onmouseout="if(!this.disabled){this.style.background='rgb(0,176,202)';}">
                                                <span class="material-symbols-outlined text-[16px]">search</span>
                                            </button>
                                        </div>
                                        <span class="error-message hidden text-[11px] font-medium text-red-500"
                                            data-error-for="identify_number"></span>
                                    </div>

                                    {{-- Nombres --}}
                                    <div class="space-y-1.5">
                                        <label for="firstname" class="flex items-center gap-1 text-xs text-slate-500">
                                            Nombres <span class="text-red-400">*</span>
                                        </label>
                                        <input type="text" id="firstname" name="firstname"
                                            value="{{ $person->firstname ?? '' }}" placeholder="Luis Daniel"
                                            class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200"
                                            style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                            onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                        <span class="error-message hidden text-[11px] font-medium text-red-500"
                                            data-error-for="firstname"></span>
                                    </div>

                                    {{-- Apellido Paterno --}}
                                    <div class="space-y-1.5">
                                        <label for="lastname_father"
                                            class="flex items-center gap-1 text-xs text-slate-500">
                                            Apellido Paterno <span class="text-red-400">*</span>
                                        </label>
                                        <input type="text" id="lastname_father" name="lastname_father"
                                            value="{{ $person->lastname_father ?? '' }}" placeholder="Linares"
                                            class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200"
                                            style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                            onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                        <span class="error-message hidden text-[11px] font-medium text-red-500"
                                            data-error-for="lastname_father"></span>
                                    </div>

                                    {{-- Apellido Materno --}}
                                    <div class="space-y-1.5">
                                        <label for="lastname_mom" class="flex items-center gap-1 text-xs text-slate-500">
                                            Apellido Materno <span class="text-red-400">*</span>
                                        </label>
                                        <input type="text" id="lastname_mom" name="lastname_mom"
                                            value="{{ $person->lastname_mom ?? '' }}" placeholder="Casternoque"
                                            class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200"
                                            style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                            onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                        <span class="error-message hidden text-[11px] font-medium text-red-500"
                                            data-error-for="lastname_mom"></span>
                                    </div>

                                    {{-- Fecha de Nacimiento --}}
                                    <div class="space-y-1.5">
                                        <label for="birthday" class="flex items-center gap-1 text-xs text-slate-500">
                                            Fecha de Nacimiento <span class="text-red-400">*</span>
                                        </label>
                                        <input type="date" id="birthday" name="birthday"
                                            value="{{ $person->birthday ?? '' }}"
                                            class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200"
                                            style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                            onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                        <span class="error-message hidden text-[11px] font-medium text-red-500"
                                            data-error-for="birthday"></span>
                                    </div>

                                    {{-- Género --}}
                                    <div class="space-y-1.5">
                                        <label for="codgender" class="flex items-center gap-1 text-xs text-slate-500">
                                            Género <span class="text-red-400">*</span>
                                        </label>
                                        <select id="codgender" name="codgender"
                                            class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 cursor-pointer"
                                            style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                            onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                            <option value="">Seleccione...</option>
                                            <option value="1"
                                                {{ isset($person) && $person->codgender == 1 ? 'selected' : '' }}>Masculino
                                            </option>
                                            <option value="2"
                                                {{ isset($person) && $person->codgender == 2 ? 'selected' : '' }}>Femenino
                                            </option>
                                        </select>
                                        <span class="error-message hidden text-[11px] font-medium text-red-500"
                                            data-error-for="codgender"></span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD: DATOS DE CONTACTO --}}
                <div class="bg-white rounded-lg overflow-hidden"
                    style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">

                    <div class="px-6 py-3" style="border-bottom: 1px solid #f1f5f9; background: #fafbfc;">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em]" style="color: #94a3b8;">
                            Datos de Contacto
                        </p>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                            {{-- Email --}}
                            <div class="space-y-1.5">
                                <label for="email" class="flex items-center gap-1 text-xs text-slate-500">
                                    Correo electrónico <span class="text-red-400">*</span>
                                </label>
                                <input type="email" id="email" name="email" value="{{ $person->email ?? '' }}"
                                    placeholder="correo@web.com"
                                    class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="email"></span>
                            </div>

                            {{-- Celular --}}
                            <div class="space-y-1.5">
                                <label for="phone" class="flex items-center gap-1 text-xs text-slate-500">
                                    Celular <span class="text-red-400">*</span>
                                </label>
                                <input type="tel" id="phone" name="phone" value="{{ $person->phone ?? '' }}"
                                    placeholder="930226745"
                                    class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="phone"></span>
                            </div>

                            {{-- Dirección --}}
                            <div class="space-y-1.5 sm:col-span-2 lg:col-span-1">
                                <label for="address" class="flex items-center gap-1 text-xs text-slate-500">
                                    Dirección <span class="text-red-400">*</span>
                                </label>
                                <input type="text" id="address" name="address"
                                    value="{{ $person->address ?? '' }}" placeholder="Jr. Los Pinos 345"
                                    class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="address"></span>
                            </div>

                            {{-- Distrito --}}
                            <div class="space-y-1.5">
                                <label for="district" class="flex items-center gap-1 text-xs text-slate-500">
                                    Distrito <span class="text-red-400">*</span>
                                </label>
                                <input type="text" id="district" name="district"
                                    value="{{ $person->district ?? '' }}" placeholder="Tarapoto"
                                    class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="district"></span>
                            </div>

                            {{-- Provincia --}}
                            <div class="space-y-1.5">
                                <label for="province" class="flex items-center gap-1 text-xs text-slate-500">
                                    Provincia <span class="text-red-400">*</span>
                                </label>
                                <input type="text" id="province" name="province"
                                    value="{{ $person->province ?? '' }}" placeholder="San Martín"
                                    class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="province"></span>
                            </div>

                            {{-- Departamento --}}
                            <div class="space-y-1.5">
                                <label for="department" class="flex items-center gap-1 text-xs text-slate-500">
                                    Departamento <span class="text-red-400">*</span>
                                </label>
                                <input type="text" id="department" name="department"
                                    value="{{ $person->department ?? '' }}" placeholder="San Martín"
                                    class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="department"></span>
                            </div>

                        </div>
                    </div>

                    {{-- Card footer --}}
                    <div class="px-6 py-4 flex flex-col-reverse sm:flex-row items-center justify-between gap-3"
                        style="border-top: 1px solid #f1f5f9; background: #fafbfc;">

                        <p class="text-[11px] text-slate-400">
                            <span class="text-red-400">*</span> Campos obligatorios
                        </p>

                        <div class="flex items-center gap-2 w-full sm:w-auto">

                            {{-- Cancelar --}}
                            <a href="{{ request('redirect') ?? route($extend['controller'] . '.list') }}"
                                class="flex-1 sm:flex-none h-9 px-5 flex items-center justify-center gap-1.5 text-xs font-medium text-slate-600 rounded-lg transition-all bg-slate-100 hover:bg-slate-200 active:scale-95">
                                <span class="material-symbols-outlined text-[15px]">chevron_left</span>
                                Cancelar
                            </a>

                            {{-- Guardar --}}
                            <button type="submit" id="btnSubmit"
                                class="flex-1 sm:flex-none h-9 px-6 flex items-center justify-center gap-1.5 text-white text-xs font-medium rounded-lg transition-all duration-200 active:scale-95"
                                style="background: rgb(0,176,202); box-shadow: 0 2px 8px rgba(0,176,202,0.3);"
                                onmouseover="this.style.background='rgb(190,214,0)'; this.style.boxShadow='0 2px 8px rgba(190,214,0,0.3)'; this.style.color='white';"
                                onmouseout="this.style.background='rgb(0,176,202)'; this.style.boxShadow='0 2px 8px rgba(0,176,202,0.3)'; this.style.color='white';">
                                <span class="material-symbols-outlined text-[15px]">save</span>
                                <span
                                    id="btnSubmitText">{{ isset($person) ? 'Actualizar Registro' : 'Guardar Persona' }}</span>
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        var controller = "{{ $extend['controller'] }}";
        var totalRecordsOld = {{ $extend['totalRecord'] }};
        var totalRecords = {{ $extend['totalRecord'] }};
        var recordId = "{{ $person->codperson ?? '' }}";
    </script>

@endsection
