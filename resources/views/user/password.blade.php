@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'Assistance School') . ' - ' . $extend['title'])

@section('content')
    <div class="">

        {{-- TOPBAR --}}
        <div class="bg-white px-6 lg:px-10 py-4 flex items-center gap-4" style="border-bottom: 1px solid #e8edf2;">

            <a href="{{ route('home') }}"
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
                    Dashboard
                </a>
                <span class="material-symbols-outlined text-[14px] text-slate-300">chevron_right</span>
                <span class="font-semibold text-slate-700">Contraseña</span>
            </div>

            <span class="ml-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                style="background: rgba(0,176,202,0.08); color: rgb(0,140,165); border: 1px solid rgba(0,176,202,0.2);">
                Seguridad
            </span>
        </div>

        {{-- ALERT CONTAINER --}}
        <div id="alertContainer" class="fixed top-16 right-1 md:right-5 z-[100] w-full max-w-sm pointer-events-none"></div>

        {{-- CONTENIDO --}}
        <div class="px-6 lg:px-10 py-6 max-w-2xl mx-auto">
            @if (Auth::user()->must_change_password)
                <div class="mb-4 rounded-lg px-4 py-3 flex items-start gap-3"
                    style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);">

                    <span class="material-symbols-outlined text-[20px]" style="color: rgb(220,38,38);">
                        warning
                    </span>

                    <div>
                        <p class="text-sm font-semibold text-red-600">
                            Debe actualizar su contraseña
                        </p>
                        <p class="text-xs text-red-500 mt-0.5">
                            Por seguridad, es obligatorio cambiar su contraseña antes de continuar.
                        </p>
                    </div>
                </div>
            @endif

            <form id="mainForm" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" id="recordId" value="{{ $user->coduser ?? '' }}">
                <input type="hidden" id="prefix" value="change_password">

                <div class="space-y-4">

                    {{-- CARD CUENTA --}}
                    <div class="bg-white rounded-lg overflow-hidden"
                        style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">

                        {{-- Info usuario --}}
                        <div class="px-6 pt-5 pb-2">
                            <div class="flex items-center justify-between px-4 py-3 rounded-lg"
                                style="background: #f8fafc; border: 1px solid #e8edf2;">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                        style="background: rgba(0,176,202,0.08);">
                                        <span class="material-symbols-outlined text-[17px]" style="color: rgb(0,140,165);">
                                            manage_accounts
                                        </span>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider leading-none mb-0.5">
                                            Usuario</p>
                                        <p class="text-sm font-bold text-slate-700">{{ $user->username ?? '—' }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                                    style="background: rgba(34,197,94,0.08); color: rgb(22,163,74); border: 1px solid rgba(34,197,94,0.2);">
                                    Activo
                                </span>
                            </div>
                        </div>

                        {{-- Campos contraseña --}}
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-5">

                            {{-- Contraseña actual --}}
                            <div class="space-y-1.5">
                                <label for="actually_password" class="flex items-center gap-1 text-xs text-slate-500">
                                    Contraseña actual <span class="text-red-400">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="actually_password" name="actually_password"
                                        placeholder="••••••••"
                                        class="w-full h-10 px-3.5 pr-10 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-300"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                        onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                        onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <button type="button" onclick="togglePassword('actually_password', this)"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                                        style="color: #94a3b8;" onmouseover="this.style.color='rgb(0,140,165)';"
                                        onmouseout="this.style.color='#94a3b8';">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </button>
                                </div>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="actually_password"></span>
                            </div>

                            {{-- Nueva contraseña --}}
                            <div class="space-y-1.5">
                                <label for="password" class="flex items-center gap-1 text-xs text-slate-500">
                                    Nueva contraseña <span class="text-red-400">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" placeholder="••••••••"
                                        class="w-full h-10 px-3.5 pr-10 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-300"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                        onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                        onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <button type="button" onclick="togglePassword('password', this)"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                                        style="color: #94a3b8;" onmouseover="this.style.color='rgb(0,140,165)';"
                                        onmouseout="this.style.color='#94a3b8';">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </button>
                                </div>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="password"></span>
                            </div>

                            {{-- Repetir contraseña --}}
                            <div class="space-y-1.5">
                                <label for="password_confirmation" class="flex items-center gap-1 text-xs text-slate-500">
                                    Repetir contraseña <span class="text-red-400">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        placeholder="••••••••"
                                        class="w-full h-10 px-3.5 pr-10 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-300"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                        onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                        onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <button type="button" onclick="togglePassword('password_confirmation', this)"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                                        style="color: #94a3b8;" onmouseover="this.style.color='rgb(0,140,165)';"
                                        onmouseout="this.style.color='#94a3b8';">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </button>
                                </div>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="password_confirmation"></span>
                            </div>

                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="bg-white rounded-lg px-6 py-4 flex flex-col-reverse sm:flex-row items-center justify-between gap-3"
                        style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">

                        <p class="text-[11px] text-slate-400">
                            <span class="text-red-400">*</span> Campos obligatorios
                        </p>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <a href="{{ route('home') }}"
                                class="flex-1 sm:flex-none h-9 px-5 flex items-center justify-center gap-1.5 text-xs font-normal text-slate-600 rounded-lg transition-all bg-slate-200 hover:bg-slate-300 active:scale-95">
                                <span class="material-symbols-outlined text-[15px]">chevron_left</span>
                                Cancelar
                            </a>
                            <button type="submit" id="btnSubmit"
                                class="flex-1 sm:flex-none h-9 px-6 flex items-center justify-center gap-1.5 text-white text-xs font-normal rounded-lg transition-all duration-200 active:scale-95"
                                style="background: rgb(0,176,202); box-shadow: 0 2px 8px rgba(0,176,202,0.3);"
                                onmouseover="this.style.background='rgb(190,214,0)'; this.style.boxShadow='0 2px 8px rgba(190,214,0,0.3)'; this.style.color='white';"
                                onmouseout="this.style.background='rgb(0,176,202)'; this.style.boxShadow='0 2px 8px rgba(0,176,202,0.3)'; this.style.color='white';">
                                <span class="material-symbols-outlined text-[15px]">lock_reset</span>
                                <span id="btnSubmitText">Actualizar</span>
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>


    <script>
        var controller = "{{ $extend['controller'] }}";
    </script>
    <script>
        function togglePassword(inputId, button) {

            const input = document.getElementById(inputId);
            const icon = button.querySelector(".material-symbols-outlined");

            if (!input) return;

            if (input.type === "password") {
                input.type = "text";
                icon.textContent = "visibility_off";
            } else {
                input.type = "password";
                icon.textContent = "visibility";
            }

        }
    </script>

@endsection
