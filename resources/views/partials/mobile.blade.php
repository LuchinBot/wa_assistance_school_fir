{{-- ============================================================
    NAVEGACIÓN INFERIOR MOBILE
    ============================================================ --}}
<div class="mobile-bottom-nav md:hidden">
    <div class="fixed bottom-0 left-0 right-0 border-t border-gray-200 z-40 bg-white/95 backdrop-blur-md"
        style="box-shadow: 0 -1px 0 #f3f4f6, 0 -4px 16px rgba(0,0,0,0.04);">
        <div class="flex items-center justify-around h-16 px-6">

            <button onclick="toggleUserMenu()"
                class="flex items-center justify-center w-10 h-10 rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all">
                <span class="material-symbols-outlined text-[35px]">account_circle</span>
            </button>

            <a href="{{ route('home') }}" class="relative flex items-center justify-center">
                <div
                    class="w-11 h-11 flex items-center justify-center rounded-xl -mt-0 bg-black shadow-lg shadow-gray-200">
                    <span class="material-symbols-outlined text-white text-[30px]">home</span>
                </div>
            </a>

            <button onclick="toggleModulesMenu()"
                class="flex items-center justify-center w-10 h-10 rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all">
                <span class="material-symbols-outlined text-[35px]">apps</span>
            </button>

        </div>
    </div>
</div>


{{-- ============================================================
    MODAL MÓDULOS (bottom sheet mobile)
    ============================================================ --}}
<div id="modulesModal" class="fixed inset-0 z-50 hidden backdrop-blur-sm" style="background: rgba(0,0,0,0.2);"
    onclick="toggleModulesMenu()">
    <div class="absolute bottom-0 left-0 right-0 rounded-t-2xl max-h-[85vh] flex flex-col bg-white border-t border-gray-200"
        style="box-shadow: 0 -4px 30px rgba(0,0,0,0.08); transform: translateY(100%); transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);"
        onclick="event.stopPropagation()">

        <div class="flex justify-center pt-3 pb-1">
            <div class="w-8 h-1 rounded-full bg-gray-200"></div>
        </div>

        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <p class="text-md font-bold text-black uppercase tracking-widest">Módulos</p>
            <button onclick="toggleModulesMenu()"
                class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-100 text-gray-400 hover:bg-gray-200 transition-all">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <div class="overflow-y-auto flex-1 px-3 py-3 space-y-0.5 custom-scrollbar">
            @php
                $parentModules = collect($authPermission ?? [])->filter(
                    fn($pp) => $pp &&
                        $pp->permission &&
                        $pp->permission->module &&
                        $pp->permission->module->codmodule_parent == null,
                );
            @endphp

            @if ($parentModules->count() > 0)
                @foreach ($parentModules as $profilePermission)
                    @php
                        $parentModule = $profilePermission->permission->module;
                        $authorizedChildren = $parentModule->children ?? collect();
                    @endphp
                    @if ($authorizedChildren->count() > 0)
                        <button onclick="toggleModule({{ $loop->index }})"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 transition-all text-left text-gray-800 hover:text-gray-900">
                            <span
                                class="material-symbols-outlined text-[19px] text-blue-500 flex-shrink-0">{{ $parentModule->icon }}</span>
                            <span
                                class="text-md font-normal flex-1">{{ $parentModule->name_large ?? $parentModule->name_short }}</span>
                            <span
                                class="material-symbols-outlined text-[16px] text-gray-300 transition-transform duration-200 module-arrow-{{ $loop->index }}">expand_more</span>
                        </button>

                        <div id="moduleContent{{ $loop->index }}" class="hidden"
                            style="padding-left: 24px; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out;">
                            <div class="space-y-0.5 py-1">
                                @foreach ($authorizedChildren as $child)
                                    <a href="{{ route($child->route) }}"
                                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                                        onclick="toggleModulesMenu()">
                                        <span class="w-1 h-1 rounded-full bg-gray-300 flex-shrink-0"></span>
                                        <span
                                            class="text-md font-normal">{{ $child->name_large ?? $child->name_short }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="text-center py-16">
                    <span class="material-symbols-outlined text-[48px] block mb-3 text-gray-200">apps</span>
                    <p class="text-sm text-gray-400">No hay módulos disponibles</p>
                </div>
            @endif
        </div>
    </div>
</div>
@php
    $sex = strtolower($authUser->person->codgender ?? 'm');
    $avatarStyle = $sex === 'f' ? 'lorelei' : 'notionists';
@endphp

{{-- ============================================================
    MODAL USUARIO (bottom sheet mobile)
    ============================================================ --}}
<div id="userModal" class="fixed inset-0 z-50 hidden backdrop-blur-sm" style="background: rgba(0,0,0,0.2);"
    onclick="toggleUserMenu()">
    <div class="absolute bottom-0 left-0 right-0 rounded-t-2xl bg-white border-t border-gray-100"
        style="box-shadow: 0 -4px 30px rgba(0,0,0,0.08); transform: translateY(100%); transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);"
        onclick="event.stopPropagation()">

        <div class="flex justify-center pt-3 pb-1">
            <div class="w-8 h-1 rounded-full bg-gray-200"></div>
        </div>

        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border border-gray-200">
                <img src="https://api.dicebear.com/7.x/{{ $avatarStyle }}/svg?seed={{ $authUser->coduser }}&backgroundColor=b6e3f4,c0aede,ffd5dc"
                    class="w-full h-full object-cover" alt="{{ $authUser->person->firstname }}">
            </div>
            <div>
                <p class="text-md font-bold text-black">{{ $authUser->person->firstname ?? 'Usuario' }}</p>
                <p class="text-sm text-gray-400">{{ $authUser->email ?? ('@' . $authUser->username ?? '') }}</p>
            </div>
        </div>

        <div class="p-3 space-y-0.5 pb-8">
            <a href="{{ route('user.password') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-100 transition-all">
                <span class="material-symbols-outlined text-2xl text-gray-600">lock</span>
                <span class="text-md font-normal">Cambiar contraseña</span>
            </a>
            <button onclick="document.getElementById('logout-form').submit()"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-900 hover:bg-red-50 transition-all">
                <span class="material-symbols-outlined text-2xl">logout</span>
                <span class="text-md font-semibold">Cerrar sesión</span>
            </button>
        </div>
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
        const isHidden = content.classList.contains('hidden');
        if (isHidden) {
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
