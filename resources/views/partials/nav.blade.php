<nav class="navbar flex justify-between items-center bg-white h-[60px] px-4 md:px-6 sticky top-0 z-[30]"
    style="border-bottom: 1px solid #e8edf2; box-shadow: 0 1px 6px rgba(0,0,0,0.04);">
    {{-- ── IZQUIERDA ── --}}
    <div class="flex items-center gap-3">

        {{-- Toggle sidebar (desktop) --}}
        <button
            class="menubar hidden md:flex items-center justify-center w-8 h-8 rounded-lg transition-all active:scale-95"
            style="color: #94a3b8;" onmouseover="this.style.background='#f4f6f8'; this.style.color='#475569';"
            onmouseout="this.style.background=''; this.style.color='#94a3b8';">
            <span class="material-symbols-outlined text-[21px]">menu_open</span>
        </button>

        {{-- Logout mobile --}}
        <button onclick="document.getElementById('logout-form').submit()"
            class="md:hidden flex items-center justify-center w-8 h-8 rounded-lg transition-all active:scale-95"
            style="color: #94a3b8;"
            onmouseover="this.style.background='rgba(239,68,68,0.06)'; this.style.color='rgb(220,50,50)';"
            onmouseout="this.style.background=''; this.style.color='#94a3b8';">
            <span class="material-symbols-outlined text-[18px]">logout</span>
        </button>

        <div class="h-5 w-px" style="background: #e8edf2;"></div>

        {{-- Breadcrumb assistance-take — visible en móvil y desktop --}}
        @if ($extend['controller'] == 'assistance-take')
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[14px]" style="color: #94a3b8;">
                    {{ $extend['icon'] ?? 'circle' }}
                </span>
                <a href="{{ route('assistance.list') }}" class="text-[13px] font-semibold transition-colors"
                    style="color: #94a3b8;" onmouseover="this.style.color='rgb(0,176,202)'"
                    onmouseout="this.style.color='#94a3b8'">
                    {{ $extend['title'] ?? 'Dashboard' }}
                </a>
                <span class="material-symbols-outlined text-[13px]" style="color: #cbd5e1;">chevron_right</span>
            </div>

            {{-- Breadcrumb normal — solo desktop --}}
        @else
            <div class="hidden md:flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[14px]" style="color: rgb(0,176,202);">
                    {{ $extend['icon'] ?? 'circle' }}
                </span>
                <span class="text-[13px] font-semibold" style="color: #475569;">
                    {{ $extend['title'] ?? 'Dashboard' }}
                </span>
            </div>
        @endif

    </div>

    {{-- ── DERECHA ── --}}
    <div class="flex items-center gap-2">

        {{-- Indicador sesión activa (si aplica) --}}
        @isset($opening)
            @if ($opening)
                <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold"
                    style="background: rgba(34,197,94,0.07); border: 1px solid rgba(34,197,94,0.18); color: rgb(22,163,74);">
                    <span class="relative flex h-1.5 w-1.5 flex-shrink-0">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                    </span>
                    Sesión activa
                </div>
            @endif
        @endisset

        {{-- Usuario dropdown --}}
        <div class="relative">

            {{-- Trigger --}}
            <div class="box-user flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg cursor-pointer transition-all"
                style="border: 1px solid transparent;"
                onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#e8edf2';"
                onmouseout="this.style.background=''; this.style.borderColor='transparent';">

                {{-- Avatar --}}
                <div class="w-7 h-7 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center"
                    style="border: 1.5px solid #e8edf2; background: rgba(0,176,202,0.08);">
                    <img src="{{ $authUser->person->photo_url ?? Vite::asset('resources/images/person.jpg') }}"
                        class="w-full h-full object-cover" alt="{{ $authUser->person->firstname }}"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <span class="material-symbols-outlined text-[15px] hidden"
                        style="color: rgb(0,176,202);">person</span>
                </div>

                {{-- Info --}}
                <div class="text-left hidden sm:block">
                    <p class="text-[12.5px] font-bold text-slate-700 leading-none">
                        {{ $authUser->person->firstname ?? 'Usuario' }}
                        {{ $authUser->person->lastname_father ?? '' }}
                    </p>
                    <p class="text-[10px] font-semibold mt-0.5 leading-none" style="color: rgb(0,155,178);">
                        {{ $authUser->profile->name_large ?? 'Perfil' }}
                    </p>
                </div>

                {{-- Chevron --}}
                <span class="material-symbols-outlined text-[17px] hidden md:block transition-transform duration-200"
                    id="navChevron" style="color: #cbd5e1;">
                    expand_more
                </span>
            </div>

            {{-- Dropdown --}}
            <div class="box-user-collapse absolute top-full right-0 mt-2 w-[210px] hidden z-50 bg-white rounded-xl overflow-hidden"
                style="border: 1px solid #e8edf2; box-shadow: 0 8px 24px rgba(0,0,0,0.08);">

                {{-- Header dropdown --}}
                <div class="px-4 py-3 flex items-center gap-3"
                    style="border-bottom: 1px solid #f1f5f9; background: #fafbfc;">
                    <div class="w-9 h-9 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center"
                        style="border: 1.5px solid #e8edf2; background: rgba(0,176,202,0.08);">
                        <img src="{{ $authUser->person->photo_url ?? Vite::asset('resources/images/person.jpg') }}"
                            class="w-full h-full object-cover" alt="{{ $authUser->person->firstname }}"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <span class="material-symbols-outlined text-[18px] hidden"
                            style="color: rgb(0,176,202);">person</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[12.5px] font-black text-slate-700 truncate leading-none">
                            {{ $authUser->username ?? 'usuario' }}
                        </p>
                        <div class="flex items-center gap-1 mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 flex-shrink-0"></span>
                            <p class="text-[10px] font-semibold" style="color: rgb(0,155,178);">Sesión activa</p>
                        </div>
                    </div>
                </div>

                {{-- Opciones --}}
                <div class="p-1.5 space-y-0.5">
                    <a href="{{ route('user.password') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-all" style="color: #64748b;"
                        onmouseover="this.style.background='#f8fafc'; this.style.color='#1e293b';"
                        onmouseout="this.style.background=''; this.style.color='#64748b';">
                        <span class="material-symbols-outlined text-[17px]" style="color: #cbd5e1;">lock</span>
                        <span class="text-[13px] font-medium">Cambiar contraseña</span>
                    </a>

                    <div class="h-px mx-1" style="background: #f1f5f9;"></div>

                    <button onclick="document.getElementById('logout-form').submit()"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg transition-all"
                        style="color: rgb(220,50,50);" onmouseover="this.style.background='rgba(220,50,50,0.05)';"
                        onmouseout="this.style.background='';">
                        <span class="material-symbols-outlined text-[17px]">logout</span>
                        <span class="text-[13px] font-semibold">Cerrar sesión</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

</nav>
