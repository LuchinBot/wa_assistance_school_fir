{{-- ============================================================
    SIDEBAR LATERAL (lg en adelante)
    ============================================================ --}}
<aside
    class="sidebar-panel fixed top-0 left-0 h-full w-[240px] z-50 transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0"
    style="background: #ffffff; border-right: 1px solid #e8edf2;">

    <div class="sidebar-content h-full flex flex-col">

        {{-- ── BRAND ── --}}
        <div class="flex flex-col items-center px-5 pt-5 pb-4 flex-shrink-0" style="border-bottom: 1px solid #f1f5f9;">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 w-full">
                <img src="{{ asset('img/logotipo.png') }}" alt="Assistance School" class="w-36 object-contain">
            </a>
        </div>

        {{-- ── MENÚ ── --}}
        <div class="flex-1 overflow-y-auto py-3 custom-scrollbar">

            {{-- Dashboard --}}
            @php $dashActive = ($extend['controller'] ?? '') == 'home'; @endphp
            <a href="{{ route('home') }}"
                class="flex items-center gap-2.5 mx-2 px-3 py-2 rounded-lg transition-all duration-150"
                style="{{ $dashActive
                    ? 'background: rgba(0,176,202,0.08); color: rgb(0,140,165); border: 1px solid rgba(0,176,202,0.15);'
                    : 'color: #94a3b8; border: 1px solid transparent;' }}"
                onmouseover="{{ !$dashActive ? "this.style.background='#f8fafc'; this.style.color='#475569'; this.style.borderColor='#e8edf2';" : '' }}"
                onmouseout="{{ !$dashActive ? "this.style.background=''; this.style.color='#94a3b8'; this.style.borderColor='transparent';" : '' }}">
                <span class="material-symbols-outlined text-[17px] flex-shrink-0"
                    style="{{ $dashActive ? 'color: rgb(0,176,202);' : '' }}">grid_view</span>
                <span class="text-[13px] font-normal flex-1">Dashboard</span>
                @if ($dashActive)
                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: rgb(0,176,202);"></span>
                @endif
            </a>

            {{-- Dashboard --}}
            @if ($authUser->codprofile == 1)
                @php $dashActive = request()->routeIs('student.import.form'); @endphp

                <a href="{{ route('student.import.form') }}"
                    class="flex items-center gap-2.5 mx-2 px-3 py-2 rounded-lg transition-all duration-150"
                    style="{{ $dashActive
                        ? 'background: rgba(0,176,202,0.08); color: rgb(0,140,165); border: 1px solid rgba(0,176,202,0.15);'
                        : 'color: #94a3b8; border: 1px solid transparent;' }}">
                    <span class="material-symbols-outlined text-[17px] flex-shrink-0"
                        style="{{ $dashActive ? 'color: rgb(0,176,202);' : '' }}">upload</span>
                    <span class="text-[13px] font-normal flex-1">Student Import</span>
                    @if ($dashActive)
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: rgb(0,176,202);"></span>
                    @endif
                </a>
            @endif
            {{-- Label sección --}}
            <p class="px-4 pt-4 pb-1.5 text-[9px] font-normal uppercase" style="color: #cbd5e1;">Módulos principales
            </p>

            {{-- Módulos dinámicos --}}
            @php
                $parentModules = collect($authPermission ?? [])->filter(function ($profilePermission) {
                    return $profilePermission &&
                        $profilePermission->permission &&
                        $profilePermission->permission->module &&
                        $profilePermission->permission->module->codmodule_parent == null;
                });
            @endphp

            @foreach ($parentModules as $profilePermission)
                @php
                    $parentModule = $profilePermission->permission->module;
                    $authorizedChildren = $parentModule->children ?? collect();
                    $hasActiveChild = $authorizedChildren->contains(fn($child) => request()->routeIs($child->route));
                @endphp

                @if ($authorizedChildren->count() > 0)
                    <div class="mb-0.5 mx-2">

                        {{-- Padre --}}
                        <a href="#mod{{ $parentModule->codmodule }}"
                            class="toggle-section flex items-center gap-2.5 px-3 py-2 rounded-lg transition-all duration-150 {{ $hasActiveChild ? 'active-menu' : '' }}"
                            style="{{ $hasActiveChild
                                ? 'background: rgba(0,176,202,0.06); color: #1e293b; border: 1px solid rgba(0,176,202,0.12);'
                                : 'color: #94a3b8; border: 1px solid transparent;' }}"
                            onmouseover="{{ !$hasActiveChild ? "this.style.background='#f8fafc'; this.style.color='#475569'; this.style.borderColor='#e8edf2';" : '' }}"
                            onmouseout="{{ !$hasActiveChild ? "this.style.background=''; this.style.color='#94a3b8'; this.style.borderColor='transparent';" : '' }}">

                            <span class="material-symbols-outlined text-[17px] flex-shrink-0"
                                style="{{ $hasActiveChild ? 'color: rgb(0,176,202);' : '' }}">
                                {{ $parentModule->icon }}
                            </span>
                            <span class="text-[13px] font-normal flex-1">
                                {{ $parentModule->name_large ?? $parentModule->name_short }}
                            </span>
                            <span
                                class="material-symbols-outlined text-[15px] transition-transform duration-200 {{ $hasActiveChild ? '' : '-rotate-90' }}"
                                style="color: #cbd5e1; flex-shrink: 0;">
                                expand_more
                            </span>
                        </a>

                        {{-- Hijos --}}
                        <div id="mod{{ $parentModule->codmodule }}"
                            class="content-section mt-0.5 overflow-hidden transition-all duration-200 {{ $hasActiveChild ? 'block' : 'hidden' }}">
                            @foreach ($authorizedChildren as $child)
                                @php $childActive = request()->routeIs($child->route); @endphp
                                <a href="{{ route($child->route) }}"
                                    class="flex items-center gap-2.5 ml-5 mr-0 px-3 py-1.5 rounded-lg transition-all duration-150"
                                    style="{{ $childActive
                                        ? 'background: rgba(141,198,63,0.08); color: rgb(90,130,20); border: 1px solid rgba(141,198,63,0.2);'
                                        : 'color: #94a3b8; border: 1px solid transparent;' }}"
                                    onmouseover="{{ !$childActive ? "this.style.background='#f8fafc'; this.style.color='#475569'; this.style.borderColor='#e8edf2';" : '' }}"
                                    onmouseout="{{ !$childActive ? "this.style.background=''; this.style.color='#94a3b8'; this.style.borderColor='transparent';" : '' }}">
                                    <span class="w-1 h-1 rounded-full flex-shrink-0"
                                        style="{{ $childActive ? 'background: rgb(141,198,63);' : 'background: #e2e8f0;' }}">
                                    </span>
                                    <span class="text-[12.5px] font-normal">
                                        {{ $child->name_large ?? $child->name_short }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

        </div>

        {{-- ── FOOTER USUARIO ── --}}
        <div class="flex-shrink-0 p-3" style="border-top: 1px solid #f1f5f9;">
            <div class="flex items-center gap-2.5 px-2 py-2 rounded-xl"
                style="background: #f8fafc; border: 1px solid #e8edf2;">

                {{-- Avatar --}}
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                    style="background: rgba(0,176,202,0.1); border: 1px solid rgba(0,176,202,0.2);">
                    <span class="material-symbols-outlined text-[16px]" style="color: rgb(0,176,202);">person</span>
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-[12px] font-bold text-slate-700 truncate leading-none">
                        {{ $authUser->person->firstname ?? 'Usuario' }}
                    </p>
                    <p class="text-[10px] mt-0.5 leading-none truncate" style="color: #94a3b8;">
                        {{ $authUser->username ?? '' }}
                    </p>
                </div>

                {{-- Acciones --}}
                <a href="{{ route('user.password') }}"
                    class="w-7 h-7 flex items-center justify-center rounded-lg transition-all flex-shrink-0"
                    style="color: #cbd5e1;"
                    onmouseover="this.style.background='rgba(0,176,202,0.08)'; this.style.color='rgb(0,176,202)';"
                    onmouseout="this.style.background=''; this.style.color='#cbd5e1';" title="Cambiar contraseña">
                    <span class="material-symbols-outlined text-[16px]">lock</span>
                </a>
                <button onclick="document.getElementById('logout-form').submit()"
                    class="w-7 h-7 flex items-center justify-center rounded-lg transition-all flex-shrink-0"
                    style="color: #cbd5e1;"
                    onmouseover="this.style.background='rgba(239,68,68,0.08)'; this.style.color='rgb(220,50,50)';"
                    onmouseout="this.style.background=''; this.style.color='#cbd5e1';" title="Cerrar sesión">
                    <span class="material-symbols-outlined text-[16px]">logout</span>
                </button>
                <form id="logout-form" action="{{ route('logout') }}" method="GET" class="hidden">@csrf</form>
            </div>
        </div>

    </div>
</aside>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(0, 176, 202, 0.15);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 176, 202, 0.35);
    }

    .toggle-section .material-symbols-outlined:last-child {
        transition: transform 0.2s cubic-bezier(.4, 0, .2, 1);
    }

    .toggle-section.active-menu .material-symbols-outlined:last-child {
        transform: rotate(0deg) !important;
    }
</style>


{{-- ============================================================
    NAVEGACIÓN INFERIOR MOBILE
    ============================================================ --}}
<div class="mobile-bottom-nav md:hidden">
    <div class="fixed bottom-0 left-0 right-0 backdrop-blur-md border-t z-40"
        style="background: rgba(255,255,255,0.96); border-color: #e8edf2; box-shadow: 0 -4px 20px rgba(0,0,0,0.06);">
        <div class="flex items-center justify-around h-16 px-6">

            {{-- Perfil --}}
            <button onclick="toggleUserMenu()"
                class="flex items-center justify-center w-10 h-10 rounded-xl transition-all" style="color: #94a3b8;"
                onmouseover="this.style.background='#f4f6f8'; this.style.color='#475569';"
                onmouseout="this.style.background=''; this.style.color='#94a3b8';">
                <span class="material-symbols-outlined text-[30px]">account_circle</span>
            </button>

            {{-- Home --}}
            <a href="{{ route('home') }}" class="relative flex items-center justify-center">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl -mt-6 transition-all"
                    style="background: linear-gradient(135deg, rgb(0,176,202) 0%, rgb(0,140,165) 100%); box-shadow: 0 4px 16px rgba(0,176,202,0.35);">
                    <span class="material-symbols-outlined text-white text-[30]">home</span>
                </div>
            </a>

            {{-- Módulos --}}
            <button onclick="toggleModulesMenu()"
                class="flex items-center justify-center w-10 h-10 rounded-xl transition-all" style="color: #94a3b8;"
                onmouseover="this.style.background='#f4f6f8'; this.style.color='#475569';"
                onmouseout="this.style.background=''; this.style.color='#94a3b8';">
                <span class="material-symbols-outlined text-[30px]">apps</span>
            </button>

        </div>
    </div>
</div>


{{-- ============================================================
    MODAL MÓDULOS (bottom sheet mobile)
    ============================================================ --}}
<div id="modulesModal" class="fixed inset-0 backdrop-blur-sm z-50 hidden" style="background: rgba(15,25,50,0.4);"
    onclick="toggleModulesMenu()">
    <div class="absolute bottom-0 left-0 right-0 rounded-t-2xl max-h-[85vh] flex flex-col"
        style="background: #ffffff; border-top: 1px solid #e8edf2; box-shadow: 0 -8px 40px rgba(0,0,0,0.1); transform: translateY(100%); transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);"
        onclick="event.stopPropagation()">

        <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-slate-200"></div>
        </div>

        <div class="flex items-center justify-between px-5 py-3" style="border-bottom: 1px solid #f1f5f9;">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]" style="color: rgb(0,176,202);">apps</span>
                <h2 class="text-[14px] font-black text-slate-700 uppercase tracking-wide">Módulos</h2>
            </div>
            <button onclick="toggleModulesMenu()"
                class="w-8 h-8 flex items-center justify-center rounded-lg transition-all"
                style="background: #f4f6f8; color: #94a3b8;" onmouseover="this.style.background='#e8edf2';"
                onmouseout="this.style.background='#f4f6f8';">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <div class="overflow-y-auto flex-1 p-4 space-y-2 custom-scrollbar">
            @php
                $parentModules = collect($authPermission ?? [])->filter(function ($profilePermission) {
                    return $profilePermission &&
                        $profilePermission->permission &&
                        $profilePermission->permission->module &&
                        $profilePermission->permission->module->codmodule_parent == null;
                });
            @endphp

            @if ($parentModules->count() > 0)
                @foreach ($parentModules as $profilePermission)
                    @php
                        $parentModule = $profilePermission->permission->module;
                        $authorizedChildren = $parentModule->children ?? collect();
                        $hasActiveChild = $authorizedChildren->contains(
                            fn($child) => request()->routeIs($child->route),
                        );
                    @endphp

                    @if ($authorizedChildren->count() > 0)
                        <div class="rounded-xl overflow-hidden"
                            style="border: 1px solid #e8edf2; background: #fafbfc;">

                            <button onclick="toggleModule({{ $loop->index }})"
                                class="w-full flex items-center gap-3 px-4 py-3 transition-all text-left"
                                onmouseover="this.style.background='#f4f6f8';" onmouseout="this.style.background='';">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                                    style="background: rgba(0,176,202,0.08); border: 1px solid rgba(0,176,202,0.15);">
                                    <span class="material-symbols-outlined text-[18px]"
                                        style="color: rgb(0,176,202);">{{ $parentModule->icon }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] font-semibold text-slate-700">
                                        {{ $parentModule->name_large ?? $parentModule->name_short }}
                                    </p>
                                    <p class="text-[11px] text-slate-400">
                                        {{ $authorizedChildren->count() }} submódulos
                                    </p>
                                </div>
                                <span
                                    class="material-symbols-outlined text-[16px] transition-transform duration-200 module-arrow-{{ $loop->index }}"
                                    style="color: #cbd5e1; flex-shrink: 0;">
                                    expand_more
                                </span>
                            </button>

                            <div id="moduleContent{{ $loop->index }}" class="module-children hidden"
                                style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; border-top: 1px solid #f1f5f9;">
                                <div class="p-3 space-y-0.5">
                                    @foreach ($authorizedChildren as $child)
                                        @php $childActive = request()->routeIs($child->route); @endphp
                                        <a href="{{ route($child->route) }}"
                                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all"
                                            style="{{ $childActive
                                                ? 'background: rgba(141,198,63,0.08); color: rgb(90,130,20); border: 1px solid rgba(141,198,63,0.2);'
                                                : 'color: #94a3b8; border: 1px solid transparent;' }}"
                                            onmouseover="{{ !$childActive ? "this.style.background='#f4f6f8'; this.style.color='#475569';" : '' }}"
                                            onmouseout="{{ !$childActive ? "this.style.background=''; this.style.color='#94a3b8';" : '' }}"
                                            onclick="toggleModulesMenu()">
                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                                style="{{ $childActive ? 'background: rgb(141,198,63);' : 'background: #e2e8f0;' }}">
                                            </span>
                                            <p class="text-[13px] font-medium">
                                                {{ $child->name_large ?? $child->name_short }}
                                            </p>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="text-center py-16">
                    <span class="material-symbols-outlined text-[48px] block mb-3 text-slate-200">apps</span>
                    <p class="text-[13px] text-slate-400">No hay módulos disponibles</p>
                </div>
            @endif
        </div>
    </div>
</div>


{{-- ============================================================
    MODAL USUARIO (bottom sheet mobile)
    ============================================================ --}}
<div id="userModal" class="fixed inset-0 backdrop-blur-sm z-50 hidden" style="background: rgba(15,25,50,0.4);"
    onclick="toggleUserMenu()">
    <div class="absolute bottom-0 left-0 right-0 rounded-t-2xl"
        style="background: #ffffff; border-top: 1px solid #e8edf2; box-shadow: 0 -8px 40px rgba(0,0,0,0.1); transform: translateY(100%); transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);"
        onclick="event.stopPropagation()">

        <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-slate-200"></div>
        </div>

        <div class="flex items-center gap-4 px-5 py-4" style="border-bottom: 1px solid #f1f5f9;">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                style="background: rgba(0,176,202,0.08); border: 1px solid rgba(0,176,202,0.15);">
                <span class="material-symbols-outlined text-[26px]" style="color: rgb(0,176,202);">person</span>
            </div>
            <div>
                <p class="text-[14px] font-bold text-slate-700">
                    {{ $authUser->person->firstname ?? 'Usuario' }}
                </p>
                <p class="text-[12px] text-slate-400">
                    {{ $authUser->username ?? '' }}
                </p>
            </div>
        </div>

        <div class="p-4 space-y-1 pb-8">
            <a href="{{ route('user.password') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all" style="color: #64748b;"
                onmouseover="this.style.background='rgba(0,176,202,0.06)'; this.style.color='rgb(0,140,165)';"
                onmouseout="this.style.background=''; this.style.color='#64748b';">
                <span class="material-symbols-outlined text-[19px]">lock</span>
                <span class="text-[13px] font-medium">Cambiar contraseña</span>
            </a>
            <button onclick="document.getElementById('logout-form').submit()"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all"
                style="color: rgb(220,50,50);" onmouseover="this.style.background='rgba(220,50,50,0.06)';"
                onmouseout="this.style.background='';">
                <span class="material-symbols-outlined text-[19px]">logout</span>
                <span class="text-[13px] font-medium">Cerrar sesión</span>
            </button>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="GET" class="hidden">@csrf</form>
    </div>
</div>


<script>
    function toggleModulesMenu() {
        const modal = document.getElementById('modulesModal');
        const content = modal.querySelector('.rounded-t-2xl');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            requestAnimationFrame(() => content.style.transform = 'translateY(0)');
        } else {
            content.style.transform = 'translateY(100%)';
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
    }

    function toggleUserMenu() {
        const modal = document.getElementById('userModal');
        const content = modal.querySelector('.rounded-t-2xl');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            requestAnimationFrame(() => content.style.transform = 'translateY(0)');
        } else {
            content.style.transform = 'translateY(100%)';
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
    }

    function toggleModule(index) {
        const content = document.getElementById('moduleContent' + index);
        const arrow = document.querySelector('.module-arrow-' + index);
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            content.style.maxHeight = content.scrollHeight + 'px';
            arrow.style.transform = 'rotate(180deg)';
        } else {
            content.style.maxHeight = '0';
            arrow.style.transform = 'rotate(0deg)';
            setTimeout(() => content.classList.add('hidden'), 300);
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (!document.getElementById('modulesModal').classList.contains('hidden')) toggleModulesMenu();
        if (!document.getElementById('userModal').classList.contains('hidden')) toggleUserMenu();
    });
</script>
