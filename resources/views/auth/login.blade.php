@extends('layouts.auth')

@section('title', 'Iniciar sesión | Assistance School')

@section('content')
    <div class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
        {{-- ALERT CONTAINER --}}
        <div id="alertContainer" class="fixed top-16 right-1 md:right-5 z-[100] w-full max-w-sm pointer-events-none"></div>

        {{-- Fondo dots --}}
        <div class="fixed inset-0 pointer-events-none"
            style="background-image: radial-gradient(rgba(58,188,212,0.12) 1.5px, transparent 1.5px); background-size: 28px 28px;">
        </div>

        {{-- Blob cyan --}}
        <div class="fixed -top-48 -left-48 w-[600px] h-[600px] rounded-full pointer-events-none"
            style="background: radial-gradient(circle, rgba(58,188,212,0.15) 0%, transparent 70%);"></div>

        {{-- Blob green --}}
        <div class="fixed -bottom-32 -right-32 w-[500px] h-[500px] rounded-full pointer-events-none"
            style="background: radial-gradient(circle, rgba(141,198,63,0.13) 0%, transparent 70%);"></div>

        {{-- CARD --}}
        <div class="relative z-10 w-full max-w-[400px]" style="animation: cardIn 0.6s cubic-bezier(0.34,1.56,0.64,1) both;">

            <style>
                @keyframes cardIn {
                    from {
                        opacity: 0;
                        transform: translateY(28px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            </style>

            <div class="rounded-2xl overflow-hidden bg-white"
                style="box-shadow: 0 0 0 1px rgba(58,188,212,0.15), 0 8px 32px rgba(58,188,212,0.12), 0 32px 64px rgba(0,60,80,0.07);">

                {{-- BANNER TOP --}}
                <div class="relative flex flex-col items-center px-8 pt-8 pb-0 overflow-hidden"
                    style="background: linear-gradient(135deg, #2a9ab0 0%, #3abcd4 60%, #5ecee0 100%);">

                    {{-- Círculos decorativos --}}
                    <div class="absolute -top-14 -right-10 w-48 h-48 rounded-full opacity-10" style="background: white;">
                    </div>
                    <div class="absolute -bottom-8 -left-6 w-32 h-32 rounded-full"
                        style="background: rgba(141,198,63,0.2);"></div>

                    {{-- Logo --}}
                    <img src="{{ asset('img/logotipo.png') }}" alt="Assistance School"
                        class="relative z-10 w-52 drop-shadow-lg"
                        style="animation: logoPop 0.7s cubic-bezier(0.34,1.56,0.64,1) 0.15s both;">

                    <style>
                        @keyframes logoPop {
                            from {
                                opacity: 0;
                                transform: scale(0.75);
                            }

                            to {
                                opacity: 1;
                                transform: scale(1);
                            }
                        }
                    </style>

                    <p class="relative z-10 text-[10px] font-bold tracking-[0.2em] uppercase mt-2 mb-4"
                        style="color: rgba(255,255,255,0.7);">
                        Assistance Control System
                    </p>

                    {{-- Wave --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 28" preserveAspectRatio="none"
                        class="w-[calc(100%+64px)] -mx-8 block" style="margin-bottom: -1px;">
                        <path d="M0,14 C80,28 160,0 200,14 C240,28 320,0 400,14 L400,28 L0,28 Z" fill="white" />
                    </svg>
                </div>

                {{-- BODY --}}
                <div class="px-8 pt-5 pb-8">

                    <h2 class="text-lg font-black text-slate-800 leading-none">¡Bienvenido de vuelta! 👋</h2>
                    <p class="text-xs text-slate-400 font-semibold mt-1 mb-6">Ingresa tus credenciales para continuar</p>

                    {{-- Error Laravel --}}
                    @if ($errors->any() || session('error'))
                        <div class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold mb-4"
                            style="background: rgba(220,50,50,0.06); border: 1px solid rgba(220,50,50,0.18); color: rgb(185,28,28);">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">error</span>
                            {{ $errors->first() ?? session('error') }}
                        </div>
                    @endif
                    <form class="space-y-4 form">
                        @csrf
                        {{-- Usuario --}}
                        <div class="mb-4">
                            <label for="username"
                                class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1.5">
                                Usuario
                            </label>
                            <div class="relative">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[17px] pointer-events-none transition-colors"
                                    id="iconUser" style="color: #94a3b8;">person</span>
                                <input type="text" name="username" id="username" value="{{ old('username') }}"
                                    placeholder="Ingresa tu usuario" autocomplete="username"
                                    class="w-full h-11 pl-10 pr-4 text-sm font-semibold text-slate-700 rounded-xl outline-none transition-all duration-200 placeholder:text-slate-300"
                                    style="background: #f8fdff; border: 2px solid #d4eaf3;"
                                    onfocus="this.style.borderColor='rgba(58,188,212,0.6)'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(58,188,212,0.1)'; document.getElementById('iconUser').style.color='rgb(58,188,212)';"
                                    onblur="this.style.borderColor='#d4eaf3'; this.style.background='#f8fdff'; this.style.boxShadow='none'; document.getElementById('iconUser').style.color='#94a3b8';">
                            </div>
                            @error('username')
                                <span class="text-[11px] font-semibold text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Contraseña --}}
                        <div class="mb-5">
                            <label for="password"
                                class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1.5">
                                Contraseña
                            </label>
                            <div class="relative">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[17px] pointer-events-none transition-colors"
                                    id="iconPass" style="color: #94a3b8;">lock</span>
                                <input type="password" name="password" id="password" placeholder="••••••••"
                                    autocomplete="current-password"
                                    class="w-full h-11 pl-10 pr-10 text-sm font-semibold text-slate-700 rounded-xl outline-none transition-all duration-200 placeholder:text-slate-300"
                                    style="background: #f8fdff; border: 2px solid #d4eaf3;"
                                    onfocus="this.style.borderColor='rgba(58,188,212,0.6)'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(58,188,212,0.1)'; document.getElementById('iconPass').style.color='rgb(58,188,212)';"
                                    onblur="this.style.borderColor='#d4eaf3'; this.style.background='#f8fdff'; this.style.boxShadow='none'; document.getElementById('iconPass').style.color='#94a3b8';">
                                <button type="button" id="togglePassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                                    style="color: #94a3b8;" onmouseover="this.style.color='rgb(58,188,212)'"
                                    onmouseout="this.style.color='#94a3b8'">
                                    <span class="material-symbols-outlined text-[18px]" id="eyeIcon">visibility</span>
                                </button>
                            </div>
                            @error('password')
                                <span class="text-[11px] font-semibold text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <button type="button" id="btnSubmit"
                            class="w-full h-12 rounded-xl text-white text-sm font-black uppercase tracking-widest transition-all duration-200 active:scale-[0.98] flex items-center justify-center gap-2 hover:!bg-[rgb(190,214,0)]"
                            style="background: linear-gradient(135deg, #3abcd4 0%, #2a9ab0 100%); box-shadow: 0 4px 16px rgba(58,188,212,0.4);"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(58,188,212,0.45)';"
                            onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 16px rgba(58,188,212,0.4)';">
                            <span class="material-symbols-outlined text-[17px]">login</span>
                            Ingresar
                        </button>

                        {{-- Para invitado (le lleva a una vista donde puede hacer consultas) --}}
                        {{-- Para invitado (le lleva a una vista donde puede hacer consultas) --}}
                        <a href="{{ route('guest.index') }}" id="btnGuest"
                            class="w-full h-12 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-200 active:scale-[0.98] flex items-center justify-center gap-2 no-underline"
                            style="border: 2px solid #3abcd4; color: #3abcd4; background: transparent; box-shadow: 0 2px 8px rgba(58,188,212,0.15);"
                            onmouseover="this.style.background='rgba(58,188,212,0.08)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(58,188,212,0.25)';"
                            onmouseout="this.style.background='transparent'; this.style.transform=''; this.style.boxShadow='0 2px 8px rgba(58,188,212,0.15)';">
                            <span class="material-symbols-outlined text-[17px]">person_outline</span>
                            Soy invitado
                        </a>

                    </form>
                </div>

                {{-- FOOTER CARD --}}
                <div class="px-8 py-3 text-center" style="border-top: 1px solid #f0f7fb;">
                    <p class="text-[10.5px] font-normal" style="color: #b0cdd9;">
                        <span style="color: rgb(58,188,212);" class="font-normal">Centro en Tecnologías de
                            Información</span>
                        · © {{ date('Y') }} · Copyright
                    </p>
                </div>

            </div>
        </div>
    </div>
@endsection
