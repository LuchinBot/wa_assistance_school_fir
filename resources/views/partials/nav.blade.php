<nav
    class="navbar flex justify-between items-center bg-white h-[60px] mx-0 md:mx-6 mt-2.5 px-4 md:px-3 top-0 z-30  rounded-xl border border-gray-100 ">

    {{-- ── IZQUIERDA: Toggle + Título página ── --}}
    <div class="flex items-center gap-3">

        {{-- Toggle sidebar — desktop --}}
        <button
            class="menubar hidden md:flex items-center justify-center w-8 h-8 rounded-lg text-gray-800 hover:bg-gray-100 hover:text-gray-600 transition-all active:scale-95">
            <span class="material-symbols-outlined text-[22px]">dock_to_right</span>
        </button>

        {{-- Separador — desktop --}}
        <div class="hidden md:block h-4 w-px bg-gray-200"></div>

        {{-- Título de la página actual --}}
        @hasSection('navbar_breadcrumb')
            @yield('navbar_breadcrumb')
        @else
            <span class="text-sm font-normal text-gray-500">
                {{ $extend['title'] ?? 'Dashboard' }}
            </span>
        @endif {{-- ← esto, NO @endsection --}}

    </div>

    {{-- ── DERECHA: Server time + Badge sesión ── --}}
    <div class="flex items-center gap-2">

        {{-- Badge sesión activa — mobile --}}
        @isset($opening)
            @if ($opening)
                <div
                    class="flex md:hidden items-center gap-1.5 px-2 py-1 rounded-full text-sm font-semibold bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <span class="relative flex h-[6px] w-[6px] flex-shrink-0">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex rounded-full h-[6px] w-[6px] bg-emerald-500"></span>
                    </span>
                    Activa
                </div>
            @endif
        @endisset

        {{-- Server time — desktop --}}
        <div
            class="hidden md:flex items-center gap-0 rounded-xl overflow-hidden border bg-gray-50 border-gray-200 text-[12px] font-medium text-gray-500">
            <div class="flex items-center gap-1.5 px-3 py-1.5 border-r border-gray-200 text-gray-800 ">
                <span class="material-symbols-outlined text-sm text-gray-400">schedule</span>
                <span>Servidor:</span>
                <span id="navServerTime" class="tabular-nums font-semibold">--:--:--</span>
            </div>

            <div class="px-3 py-1.5 text-gray-800">
                {{ config('app.timezone', 'UTC') }}
            </div>

        </div>

        {{-- Usuario dropdown — solo mobile (desktop está en sidebar footer) --}}
        <div class="relative md:hidden">
            @php
                $sex = strtolower($authUser->person->codgender ?? 'm');
                $avatarStyle = $sex === 'f' ? 'lorelei' : 'notionists';
            @endphp
            <div
                class="box-user flex items-center gap-1.5 px-1.5 py-1.5 rounded-lg cursor-pointer
                        border border-transparent hover:border-gray-200 hover:bg-gray-50 transition-all duration-150">
                <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border border-gray-200">
                    <img src="https://api.dicebear.com/7.x/{{ $avatarStyle }}/svg?seed={{ $authUser->coduser }}&backgroundColor=b6e3f4,c0aede,ffd5dc"
                        class="w-full h-full object-cover" alt="{{ $authUser->person->firstname }}">
                </div>
            </div>

            {{-- Dropdown mobile --}}
            <div class="box-user-collapse absolute top-full right-0 mt-1.5 w-max hidden z-50
                        bg-white rounded-xl border border-gray-100 overflow-hidden"
                style="box-shadow: 0 4px 24px rgba(0,0,0,0.08);">

                <div class="px-4 py-3 flex items-center gap-3 bg-gray-50 border-b border-gray-100">
                    <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 border border-gray-200">
                        <img src="https://api.dicebear.com/7.x/{{ $avatarStyle }}/svg?seed={{ $authUser->coduser }}&backgroundColor=b6e3f4,c0aede,ffd5dc"
                            class="w-full h-full object-cover" alt="{{ $authUser->person->firstname }}">
                    </div>
                    <div class="min-w-0">
                        <p class="text-md font-bold truncate leading-none text-black">
                            {{ $authUser->person->firstname . ' ' . $authUser->person->lastname_father ?? 'usuario' }}
                        </p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ $authUser->profile->name_large ?? 'Perfil' }}
                        </p>
                    </div>
                </div>

                <div class="p-1.5 space-y-0.5">
                    {{--  <a href="{{ route('user.password') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-700 transition-all">
                        <span class="material-symbols-outlined text-2xl text-gray-600">lock</span>
                        <span class="text-md font-normal">Cambiar contraseña</span>
                    </a>
                    <div class="h-px mx-1 bg-gray-100"></div>
                    <button onclick="document.getElementById('logout-form').submit()"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-red-900 hover:bg-red-50 transition-all">
                        <span class="material-symbols-outlined text-2xl">logout</span>
                        <span class="text-md font-semibold">Cerrar sesión</span>
                    </button> --}}
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    {{-- Reloj en tiempo real --}}
        (function() {
            const el = document.getElementById('navServerTime');
            if (!el) return;

            function tick() {
                const now = new Date();
                el.textContent = now.toLocaleTimeString('es-PE', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
            }
            tick();
            setInterval(tick, 1000);
        })();
</script>
