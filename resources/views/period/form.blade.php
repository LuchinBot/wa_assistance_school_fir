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
        <form id="mainForm" class="space-y-6">
            @csrf
            <input type="hidden" id="recordId" value="{{ $period->codperiod ?? '' }}">

            <div
                class="bg-white rounded-xl sm:rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">

                <div class="p-4 sm:p-6 md:p-8">
                    <!-- Una sola fila: año + 2 switches. En mobile se apilan. -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">

                        <!-- Año del periodo -->
                        <div class="space-y-2">
                            <label for="name_year"
                                class="block text-xs font-medium text-slate-500 ml-0.5">
                                Año del periodo <span class="text-blue-500">*</span>
                            </label>
                            <input type="text" id="name_year" name="name_year" maxlength="4"
                                placeholder="Ej: 2026" value="{{ $period->name_year ?? '' }}"
                                class="w-full h-11 px-3.5 bg-slate-50 border border-slate-300 rounded-md text-sm text-slate-700 placeholder:text-slate-300 outline-none transition-all duration-200 hover:border-slate-300 hover:bg-white focus:bg-white focus:border-blue-500/60 focus:ring-4 focus:ring-blue-500/[0.08]">
                            <span class="error-message text-red-800 text-xs font-normal mt-1 hidden"
                                data-error-for="name_year"></span>
                        </div>

                        <!-- Checkbox 1: ¿Periodo Actual? -->
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-slate-500 ml-0.5">¿Periodo Actual?</label>
                            <label
                                class="h-[42px] sm:h-[44px] flex items-center gap-4 cursor-pointer w-full group bg-slate-50 px-4 rounded-md border border-slate-200 hover:border-slate-300 transition-all">
                                <input type="checkbox" id="actually" name="actually" value="Y"
                                    {{ isset($period) && $period->actually == 'Y' ? 'checked' : '' }} class="sr-only">
                                <div id="actually_switch"
                                    class="relative w-14 h-7 min-w-[56px] bg-slate-300 rounded-full transition-all after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all shadow-inner">
                                </div>
                                <span id="actually_text"
                                    class="text-xs font-black uppercase tracking-wider text-slate-500 transition-colors">
                                    Inhabilitado
                                </span>
                            </label>
                        </div>

                        <!-- Checkbox 2: Inscripciones abiertas -->
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-slate-500 ml-0.5">Inscripciones abiertas</label>
                            <label
                                class="h-[42px] sm:h-[44px] flex items-center gap-4 cursor-pointer w-full group bg-slate-50 px-4 rounded-md border border-slate-200 hover:border-slate-300 transition-all">
                                <input type="checkbox" id="open_registration" name="open_registration" value="Y"
                                    {{ isset($period) && $period->open_registration == 'Y' ? 'checked' : '' }} class="sr-only">
                                <div id="open_registration_switch"
                                    class="relative w-14 h-7 min-w-[56px] bg-slate-300 rounded-full transition-all after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all shadow-inner">
                                </div>
                                <span id="open_registration_text"
                                    class="text-xs font-black uppercase tracking-wider text-slate-500 transition-colors">
                                    Inhabilitado
                                </span>
                            </label>
                        </div>

                    </div>
                </div>

                <!-- Footer Buttons -->
                <div
                    class="bg-slate-50/80 px-4 sm:px-6 md:px-8 py-4 sm:py-6 flex flex-col sm:flex-row gap-3 justify-end border-t border-slate-100">
                    <a href="{{ route($extend['controller'] . '.list') }}"
                        class="order-2 sm:order-1 px-6 sm:px-8 py-3 sm:py-3.5 flex items-center justify-center bg-white hover:bg-slate-100 text-slate-600 font-black text-[9px] sm:text-[10px] uppercase tracking-widest rounded-xl border border-slate-200 transition-all text-center">
                        Cancelar
                    </a>
                    <button type="submit" id="btnSubmit"
                        class="order-1 sm:order-2 px-8 sm:px-10 py-3.5 sm:py-4 md:py-3 bg-slate-900 hover:bg-blue-600 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-[0.15em] sm:tracking-[0.2em] rounded-xl transition-all shadow-xl shadow-slate-200 flex items-center justify-center group active:scale-95">
                        <span
                            class="material-symbols-outlined mr-2 text-[18px] sm:text-[20px] group-hover:rotate-12 transition-transform">save</span>
                        <span id="btnSubmitText">{{ isset($period) ? 'Actualizar periodo' : 'Guardar periodo' }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        var controller = "{{ $extend['controller'] }}";
        var recordId = "{{ $period->codperiod ?? '' }}";

        document.addEventListener('DOMContentLoaded', () => {

            // Función reutilizable que inicializa el comportamiento de cada switch
            function initSwitch(checkboxId) {
                const checkbox = document.getElementById(checkboxId);
                const switchEl = document.getElementById(checkboxId + '_switch');
                const text = document.getElementById(checkboxId + '_text');

                const updateVisuals = () => {
                    if (checkbox.checked) {
                        text.textContent = 'Activado';
                        text.classList.replace('text-slate-500', 'text-emerald-600');
                        switchEl.classList.replace('bg-slate-300', 'bg-emerald-500');
                        switchEl.classList.add('after:translate-x-7');
                    } else {
                        text.textContent = 'Desactivado';
                        text.classList.replace('text-emerald-600', 'text-slate-500');
                        switchEl.classList.replace('bg-emerald-500', 'bg-slate-300');
                        switchEl.classList.remove('after:translate-x-7');
                    }
                };

                checkbox.addEventListener('change', updateVisuals);
                updateVisuals(); // Estado inicial según el valor del checkbox
            }

            // Inicializar ambos switches
            initSwitch('actually');
            initSwitch('open_registration');
        });
    </script>

@section('script')
    <script src="{{ asset("js/$extend[controller]/form.js") }}"></script>
@stop

@endsection