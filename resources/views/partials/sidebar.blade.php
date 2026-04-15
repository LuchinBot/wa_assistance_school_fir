<aside
    class="sidebar-panel fixed top-2 left-2 bottom-2  w-[300px] rounded-xl border border-gray-200 z-50 transition-transform duration-300 ease-in-out flex flex-col"
    style="background: #f9fafb; box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;">

    {{-- ── BRAND ── --}}
    <div class="flex items-center justify-between px-4 h-[62px] flex-shrink-0 border-b border-gray-200">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <div
                class="w-8 h-8 p-1 rounded-md overflow-hidden flex-shrink-0 bg-blue-50 border border-blue-200 flex items-center justify-center">
                <img src="img/logo.svg" alt="">
            </div>
            <span class="text-[14px] font-bold text-black">Assistance School</span>
        </a>
        <div class="flex items-center gap-1">
            <button
                class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:bg-gray-200 hover:text-gray-600 transition-all">
                <span class="material-symbols-outlined text-[18px]">unfold_more</span>
            </button>
            <button
                class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:bg-gray-200 hover:text-gray-600 transition-all">
                <span class="material-symbols-outlined text-[18px] icon-medium">notifications</span>
            </button>
        </div>
    </div>

    {{-- ── MENÚ ── --}}
    <div class="flex-1 overflow-y-auto py-3 px-2 custom-scrollbar">

        {{-- ── HOME ── --}}
        <p class="px-3 pb-1.5 text-xs font-normal text-gray-400 capitalize">
            Home
        </p>

        @php $dashActive = ($extend['controller'] ?? '') == 'home'; @endphp
        <a href="{{ route('home') }}"
            class="flex items-center gap-2 px-2 py-2 rounded-lg mb-0.5 transition-all duration-100
                   {{ $dashActive ? 'bg-gray-200/80 text-gray-900' : 'text-gray-600 hover:bg-gray-200/50 hover:text-gray-900' }}">
            <span class="material-symbols-outlined text-[19px] flex-shrink-0">grid_view</span>
            <span class="text-[13.5px] font-medium">Dashboard</span>
        </a>

        {{-- ── MÓDULOS ── --}}
        @php
            $parentModules = collect($authPermission ?? [])->filter(function ($pp) {
                return $pp &&
                    $pp->permission &&
                    $pp->permission->module &&
                    $pp->permission->module->codmodule_parent == null;
            });
        @endphp

        @if ($parentModules->count() > 0)
            <p class="px-3 pt-4 pb-1.5 text-xs font-normal text-slate-500 capitalize">
                Módulos
            </p>

            @foreach ($parentModules as $profilePermission)
                @php
                    $parentModule = $profilePermission->permission->module;
                    $authorizedChildren = $parentModule->children ?? collect();
                    $hasActiveChild = $authorizedChildren->contains(function ($child) {
                        $base = implode('.', array_slice(explode('.', $child->route), 0, -1));
                        return request()->routeIs($child->route) || request()->routeIs($base . '.*');
                    });
                @endphp

                @if ($authorizedChildren->count() > 0)
                    {{-- Padre toggle --}}
                    <a href="#mod{{ $parentModule->codmodule }}"
                        class="toggle-section flex items-center gap-2 px-2 py-2 rounded-lg mb-0.5 transition-all duration-100 cursor-pointer
                               {{ $hasActiveChild
                                   ? 'bg-gray-200/80 text-gray-900 active-menu'
                                   : 'text-gray-800 hover:bg-gray-200/30 hover:text-gray-900' }}">
                        <span
                            class="material-symbols-outlined text-[19px] flex-shrink-0">{{ $parentModule->icon }}</span>
                        <span
                            class="text-[13.5px] font-normal flex-1">{{ $parentModule->name_large ?? $parentModule->name_short }}</span>
                        <span
                            class="material-symbols-outlined text-[16px] text-gray-400 flex-shrink-0 transition-transform duration-200 {{ $hasActiveChild ? 'rotate-0' : '-rotate-90' }}">
                            expand_more
                        </span>
                    </a>

                    {{-- Hijos --}}
                    <div id="mod{{ $parentModule->codmodule }}"
                        class="content-section overflow-hidden {{ $hasActiveChild ? 'block' : 'hidden' }} mb-0.5"
                        style="padding-left: 28px;">

                        @foreach ($authorizedChildren as $child)
                            @php
                                $base = implode('.', array_slice(explode('.', $child->route), 0, -1));
                                $childActive = request()->routeIs($child->route) || request()->routeIs($base . '.*');
                            @endphp
                            <a href="{{ route($child->route) }}"
                                class="flex items-center gap-2 px-2 py-[7px] rounded-lg mb-0.5 transition-all duration-100
                                       {{ $childActive ? 'bg-gray-200/50 text-gray-900' : 'text-gray-500 hover:bg-gray-200/50 hover:text-gray-900' }}">
                                <span
                                    class="w-1 h-1 rounded-full flex-shrink-0 {{ $childActive ? 'bg-blue-500' : 'bg-gray-300' }}"></span>
                                <span
                                    class="text-[13px] font-normal">{{ $child->name_large ?? $child->name_short }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endforeach
        @endif

    </div>

    {{-- ── FOOTER ── --}}
    <div class="flex-shrink-0 border-t border-gray-200 px-2 py-2 relative">

        {{-- Dropdown hacia arriba --}}
        <div id="userDropdownSidebar"
            class="hidden absolute bottom-full left-2 right-2 mb-1 bg-white rounded-xl border border-gray-200 overflow-hidden"
            style="box-shadow: 0 -4px 24px rgba(0,0,0,0.08);">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <div class="min-w-0">
                    <p class="text-[13.5px] font-bold leading-none text-gray-800">
                        {{ $authUser->person->firstname ?? 'Mi Cuenta' }}
                        {{ $authUser->person->lastname_father ?? '' }}
                    </p>
                    <p class="text-[11.5px] text-gray-400 mt-1 leading-none truncate">
                        {{ $authUser->email ?? ('@' . $authUser->username ?? '') }}
                    </p>
                </div>
                {{-- Placeholder icono tema (decorativo) --}}
                <button
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-400 hover:bg-gray-200 transition-all flex-shrink-0">
                    <span class="material-symbols-outlined text-[17px]">wb_sunny</span>
                </button>
            </div>

            {{-- Opciones --}}
            <div class="py-1">
                <a href="{{ route('user.password') }}"
                    class="flex items-center px-4 py-2.5 text-[13.5px] text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all">
                    Cambiar contraseña
                </a>
            </div>

            {{-- Log out --}}
            <div class="border-t border-gray-100 py-1">
                <button onclick="document.getElementById('logout-form').submit()"
                    class="w-full flex items-center px-4 py-2.5 text-[13.5px] text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all">
                    Cerrar sesión
                </button>
            </div>

        </div>

        {{-- Trigger --}}
        <button onclick="toggleSidebarUserDropdown()"
            class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg hover:bg-gray-100 transition-all cursor-pointer group">
            @php
                $sex = strtolower($authUser->person->codgender ?? 'm');
                $avatarStyle = $sex === 'f' ? 'lorelei' : 'notionists';
            @endphp

            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border border-gray-200">
                <img src="https://api.dicebear.com/7.x/{{ $avatarStyle }}/svg?seed={{ $authUser->coduser }}&backgroundColor=b6e3f4,c0aede,ffd5dc"
                    class="w-full h-full object-cover" alt="{{ $authUser->person->firstname }}">
            </div>

            <div class="flex-1 min-w-0 text-left">
                <p class="text-sm font-semibold leading-none text-black truncate">
                    {{ $authUser->person->firstname ?? 'Account' }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5 leading-none truncate">
                    {{ $authUser->profile->name_large ?? '-' }}
                </p>
            </div>

            <span class="material-symbols-outlined text-[16px] text-gray-400 flex-shrink-0">unfold_more</span>
        </button>

        <form id="logout-form" action="{{ route('logout') }}" method="GET" class="hidden">@csrf</form>
        {{-- Version --}}
        <p class="text-center text-[10.5px] text-gray-400 mb-1.5">Version v1.0.0</p>
    </div>

    <script>
        function toggleSidebarUserDropdown() {
            const dropdown = document.getElementById('userDropdownSidebar');
            dropdown.classList.toggle('hidden');
        }

        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdownSidebar');
            const trigger = dropdown?.previousElementSibling?.querySelector('button[onclick]') ??
                document.querySelector('button[onclick="toggleSidebarUserDropdown()"]');
            if (!dropdown) return;
            if (!dropdown.contains(e.target) && !trigger?.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>


</aside>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    .toggle-section .material-symbols-outlined:last-child {
        transition: transform 0.2s cubic-bezier(.4, 0, .2, 1);
    }

    .toggle-section.active-menu .material-symbols-outlined:last-child {
        transform: rotate(0deg) !important;
    }
</style>
