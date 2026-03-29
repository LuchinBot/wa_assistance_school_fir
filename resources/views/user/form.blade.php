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
                    {{ isset($user) ? 'Editar usuario' : 'Nuevo usuario' }}
                </span>
            </div>
            <span class="ml-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                style="{{ isset($user)
                    ? 'background: rgba(245,158,11,0.1); color: rgb(217,119,6); border: 1px solid rgba(245,158,11,0.2);'
                    : 'background: rgba(160,185,0,0.1); color: rgb(120,140,0); border: 1px solid rgba(160,185,0,0.2);' }}">
                {{ isset($user) ? 'Editando' : 'Nuevo' }}
            </span>
        </div>

        {{-- ALERT CONTAINER --}}
        <div id="alertContainer" class="fixed top-16 right-1 md:right-5 z-[100] w-full max-w-sm pointer-events-none"></div>

        {{-- CONTENIDO --}}
        <div class="px-6 lg:px-10 py-8 max-w-3xl mx-auto">
            <form id="mainForm" novalidate autocomplete="off">
                @csrf
                <input type="hidden" id="recordId" value="{{ $user->coduser ?? '' }}">

                <div class="flex flex-col gap-4">

                    {{-- ── SECCIÓN 1: Identidad ── --}}
                    <div class="bg-white rounded-xl"
                        style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">

                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                            {{-- Perfil / Rol --}}
                            <div class="space-y-1.5">
                                <label for="codprofile" class="text-xs font-medium text-slate-500">
                                    Rol / Perfil <span class="text-red-400">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <select id="codprofile" name="codprofile"
                                        class="tom-select flex-1 min-w-0 h-10 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 appearance-none"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                        onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                        onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                        <option value=""></option>
                                        @foreach ($profiles as $profile)
                                            <option value="{{ $profile->codprofile }}"
                                                {{ isset($user) && $user->codprofile == $profile->codprofile ? 'selected' : '' }}>
                                                {{ $profile->name_large }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('role.form') }}?redirect={{ route($extend['controller'] . '.form') }}"
                                        title="Añadir nuevo rol"
                                        class="flex items-center justify-center w-10 h-10 rounded-lg flex-shrink-0 transition-all duration-200 active:scale-95"
                                        style="background: rgba(0,176,202,0.08); border: 1px solid rgba(0,176,202,0.2); color: rgb(0,140,165);"
                                        onmouseover="this.style.background='rgba(0,176,202,0.15)'; this.style.borderColor='rgba(0,176,202,0.4)';"
                                        onmouseout="this.style.background='rgba(0,176,202,0.08)'; this.style.borderColor='rgba(0,176,202,0.2)';">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                    </a>
                                </div>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="codprofile"></span>
                            </div>

                            {{-- Persona --}}
                            <div class="space-y-1.5">
                                <label for="codperson" class="text-xs font-medium text-slate-500">
                                    Persona natural <span class="text-red-400">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <select id="codperson" name="codperson"
                                        class="tom-select flex-1 min-w-0 h-10 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 appearance-none"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                        onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                        onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                        <option value=""></option>
                                        @foreach ($persons as $person)
                                            <option value="{{ $person->codperson }}"
                                                {{ isset($user) && $user->codperson == $person->codperson ? 'selected' : '' }}>
                                                {{ $person->firstname }} {{ $person->lastname_father }}
                                                {{ $person->lastname_mom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('person.form') }}?redirect={{ route($extend['controller'] . '.form') }}"
                                        title="Añadir nueva persona"
                                        class="flex items-center justify-center w-10 h-10 rounded-lg flex-shrink-0 transition-all duration-200 active:scale-95"
                                        style="background: rgba(0,176,202,0.08); border: 1px solid rgba(0,176,202,0.2); color: rgb(0,140,165);"
                                        onmouseover="this.style.background='rgba(0,176,202,0.15)'; this.style.borderColor='rgba(0,176,202,0.4)';"
                                        onmouseout="this.style.background='rgba(0,176,202,0.08)'; this.style.borderColor='rgba(0,176,202,0.2)';">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                    </a>
                                </div>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="codperson"></span>
                            </div>

                        </div>
                    </div>

                    {{-- ── SECCIÓN 2: Credenciales ── --}}
                    <div class="bg-white rounded-xl"
                        style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                        <div class="p-6 grid grid-cols-1 gap-5" style="{{ !isset($user) ? '' : '' }}">

                            {{-- Username — fila completa si es edición, mitad si es creación --}}
                            <div class="{{ !isset($user) ? 'grid grid-cols-1 md:grid-cols-3 gap-5' : '' }}">

                                <div class="space-y-1.5">
                                    <label for="username" class="text-xs font-medium text-slate-500">
                                        Nombre de usuario <span class="text-red-400">*</span>
                                    </label>
                                    <input id="username" name="username" value="{{ $user->username ?? '' }}"
                                        placeholder="Ingrese el usuario"
                                        class="w-full h-10 px-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-300"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                        onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                        onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <span class="error-message hidden text-[11px] font-medium text-red-500"
                                        data-error-for="username"></span>
                                </div>

                                @if (!isset($user))
                                    {{-- Password --}}
                                    <div class="space-y-1.5">
                                        <label for="password" class="text-xs font-medium text-slate-500">
                                            Contraseña <span class="text-red-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="password" name="password" placeholder="••••••••"
                                                autocomplete="new-password" onpaste="return false" oncopy="return false"
                                                oncut="return false"
                                                class="w-full h-10 px-3.5 pr-10 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-300"
                                                style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                                onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                                onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                            <button type="button" data-toggle-password
                                                class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                                                style="color: #94a3b8;" onmouseover="this.style.color='rgb(0,140,165)';"
                                                onmouseout="this.style.color='#94a3b8';">
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </button>
                                        </div>
                                        <span class="error-message hidden text-[11px] font-medium text-red-500"
                                            data-error-for="password"></span>
                                    </div>

                                    {{-- Confirmar password --}}
                                    <div class="space-y-1.5">
                                        <label for="password_confirmation" class="text-xs font-medium text-slate-500">
                                            Confirmar contraseña <span class="text-red-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="password_confirmation"
                                                name="password_confirmation" placeholder="••••••••"
                                                autocomplete="new-password" onpaste="return false" oncopy="return false"
                                                oncut="return false"
                                                class="w-full h-10 px-3.5 pr-10 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-300"
                                                style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                                onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                                onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                            <button type="button" data-toggle-password
                                                class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                                                style="color: #94a3b8;" onmouseover="this.style.color='rgb(0,140,165)';"
                                                onmouseout="this.style.color='#94a3b8';">
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </button>
                                        </div>
                                        <span class="error-message hidden text-[11px] font-medium text-red-500"
                                            data-error-for="password_confirmation"></span>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>

                    {{-- ── FOOTER ── --}}
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 px-1">
                        <p class="text-[11px] text-slate-400">
                            <span class="text-red-400">*</span> Campos obligatorios
                        </p>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <a href="{{ request('redirect') ?? route($extend['controller'] . '.list') }}"
                                class="flex-1 sm:flex-none h-9 px-5 flex items-center justify-center gap-1.5 text-xs font-medium text-slate-600 rounded-lg transition-all bg-slate-200 hover:bg-slate-300 active:scale-95">
                                <span class="material-symbols-outlined text-[15px]">chevron_left</span>
                                Cancelar
                            </a>
                            <button type="submit" id="btnSubmit"
                                class="flex-1 sm:flex-none h-9 px-6 flex items-center justify-center gap-1.5 text-white text-xs font-medium rounded-lg transition-all duration-200 active:scale-95"
                                style="background: rgb(0,176,202); box-shadow: 0 2px 8px rgba(0,176,202,0.3);"
                                onmouseover="this.style.background='rgb(190,214,0)'; this.style.boxShadow='0 2px 8px rgba(190,214,0,0.3)';"
                                onmouseout="this.style.background='rgb(0,176,202)'; this.style.boxShadow='0 2px 8px rgba(0,176,202,0.3)';">
                                <span
                                    class="material-symbols-outlined text-[15px]">{{ isset($user) ? 'save' : 'add' }}</span>
                                <span id="btnSubmitText">{{ isset($user) ? 'Actualizar' : 'Guardar' }}</span>
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        const controller = "{{ $extend['controller'] }}";
        const recordId = "{{ $user->coduser ?? '' }}";
        const totalRecords = {{ $extend['totalRecord'] }};

        document.querySelectorAll('[data-toggle-password]').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.closest('.relative').querySelector('input');
                const icon = this.querySelector('.material-symbols-outlined');
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.textContent = show ? 'visibility_off' : 'visibility';
            });
        });
    </script>
@endsection
