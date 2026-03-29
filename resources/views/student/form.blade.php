@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'SCA') . ' - ' . $extend['title'])

@section('content')
    <div class="">

        {{-- TOPBAR --}}
        <div class="bg-white px-6 lg:px-10 py-4 flex items-center gap-4" style="border-bottom: 1px solid #e8edf2;">

            {{-- Volver --}}
            <a href="{{ route($extend['controller'] . '.list') }}"
                class="group flex items-center justify-center w-8 h-8 rounded-lg flex-shrink-0 transition-all duration-200 active:scale-95 bg-slate-100 hover:bg-slate-200">
                <span
                    class="material-symbols-outlined text-[17px] text-slate-500 group-hover:-translate-x-0.5 transition-transform">
                    arrow_back
                </span>
            </a>

            {{-- Separador --}}
            <div class="w-px h-5 bg-slate-200 flex-shrink-0"></div>

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('home') }}" class="font-medium transition-colors" style="color: #94a3b8;"
                    onmouseover="this.style.color='rgb(0,176,202)'" onmouseout="this.style.color='#94a3b8'">
                    Dashboard
                </a>
                <span class="material-symbols-outlined text-[14px]" style="color: #cbd5e1;">chevron_right</span>
                <a href="{{ route($extend['controller'] . '.list') }}" class="font-medium transition-colors"
                    style="color: #94a3b8;" onmouseover="this.style.color='rgb(0,176,202)'"
                    onmouseout="this.style.color='#94a3b8'">
                    {{ $extend['title'] }}
                </a>
                <span class="material-symbols-outlined text-[14px]" style="color: #cbd5e1;">chevron_right</span>
            </div>

            {{-- Badge estado --}}
            <span class="ml-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                style="{{ isset($students)
                    ? 'background: rgba(245,158,11,0.1); color: rgb(217,119,6); border: 1px solid rgba(245,158,11,0.2);'
                    : 'background: rgba(160,185,0,0.1); color: rgb(120,140,0); border: 1px solid rgba(160,185,0,0.2);' }}">
                {{ isset($students) ? 'Editando' : 'Nuevo' }}
            </span>
        </div>

        {{-- ALERT CONTAINER --}}
        <div id="alertContainer" class="fixed top-16 right-1 md:right-5 z-[100] w-full max-w-sm pointer-events-none"></div>

        {{-- CONTENIDO --}}
        <div class="px-6 lg:px-10 py-6 max-w-5xl mx-auto">

            <form id="mainForm" class="space-y-4" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" id="recordId" value="{{ $students->codstudent ?? '' }}">
                <input type="hidden" name="redirect" value="{{ $redirect }}">

                {{-- CARD: INFORMACIÓN DE IDENTIDAD --}}
                <div class="bg-white rounded-lg" style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                    {{-- Campos --}}
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                            {{-- Persona natural --}}
                            <div class="space-y-1.5">
                                <label for="codperson" class="flex items-center gap-1 text-xs text-slate-500">
                                    Persona natural <span class="text-red-400">*</span>
                                </label>
                                <div class="flex gap-2 items-start">
                                    <select id="codperson" name="codperson"
                                        class="tom-select flex-1 min-w-0 h-10 px-0 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 cursor-pointer"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                        onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                        <option value="">Seleccione...</option>
                                        @foreach ($persons as $person)
                                            <option value="{{ $person->codperson }}"
                                                {{ isset($students) && $students->codperson == $person->codperson ? 'selected' : '' }}>
                                                {{ $person->firstname }} {{ $person->lastname_father }}
                                                {{ $person->lastname_mom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('person.form') }}?redirect={{ route($extend['controller'] . '.form') }}"
                                        title="Añadir nueva persona"
                                        class="flex items-center justify-center w-10 h-10 rounded-lg flex-shrink-0 transition-all duration-200 active:scale-95"
                                        style="background: rgba(0,176,202,0.08); border: 1px solid rgba(0,176,202,0.2); color: rgb(0,140,165);"
                                        onmouseover="this.style.background='rgb(0,176,202)'; this.style.color='white'; this.style.borderColor='rgb(0,176,202)';"
                                        onmouseout="this.style.background='rgba(0,176,202,0.08)'; this.style.color='rgb(0,140,165)'; this.style.borderColor='rgba(0,176,202,0.2)';">
                                        <span class="material-symbols-outlined text-[17px]">add</span>
                                    </a>
                                </div>
                                <span class="error-message hidden text-[11px] font-medium text-red-500 mt-1"
                                    data-error-for="codperson"></span>
                            </div>

                            {{-- Horario por grado --}}
                            <div class="space-y-1.5">
                                <label for="codgrade_schedule" class="flex items-center gap-1 text-xs text-slate-500">
                                    Grado por horario <span class="text-red-400">*</span>
                                </label>

                                <div class="flex gap-2">
                                    <select id="codgrade_schedule" name="codgrade_schedule"
                                        class="tom-select flex-1 min-w-0 h-10 px-0 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 cursor-pointer"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                        onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                        <option value="">Seleccione...</option>
                                        @foreach ($grade_schedules as $grade_schedule)
                                            <option value="{{ $grade_schedule->codgrade_schedule }}"
                                                {{ isset($students) && $students->currentEnrollment?->codgrade_schedule == $grade_schedule->codgrade_schedule ? 'selected' : '' }}>
                                                {{ $grade_schedule->grade->name_large }}
                                                ({{ $grade_schedule->section }})
                                                | {{ $grade_schedule->grade->level->name_large }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('grade_schedule.form') }}?redirect={{ route($extend['controller'] . '.form') }}"
                                        title="Añadir nuevo grado"
                                        class="flex items-center justify-center w-10 h-10 rounded-lg flex-shrink-0 transition-all duration-200 active:scale-95"
                                        style="background: rgba(0,176,202,0.08); border: 1px solid rgba(0,176,202,0.2); color: rgb(0,140,165);"
                                        onmouseover="this.style.background='rgb(0,176,202)'; this.style.color='white'; this.style.borderColor='rgb(0,176,202)';"
                                        onmouseout="this.style.background='rgba(0,176,202,0.08)'; this.style.color='rgb(0,140,165)'; this.style.borderColor='rgba(0,176,202,0.2)';">
                                        <span class="material-symbols-outlined text-[17px]">add</span>
                                    </a>
                                </div>
                                <span class="error-message hidden text-[11px] font-medium text-red-500 mt-1"
                                    data-error-for="codgrade_schedule"></span>
                            </div>

                            {{-- Persona natural --}}
                            <div class="space-y-1.5">
                                <label for="codperiod" class="flex items-center gap-1 text-xs text-slate-500">
                                    Periodo académico <span class="text-red-400">*</span>
                                </label>
                                <div class="flex gap-2 items-start">
                                    <select id="codperiod" name="codperiod"
                                        class="tom-select flex-1 min-w-0 h-10 px-0 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 cursor-pointer"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                        onfocus="this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                        <option value="">Seleccione...</option>
                                        @foreach ($periods as $period)
                                            <option value="{{ $period->codperiod }}"
                                                {{ isset($students) && $students->currentEnrollment?->codperiod == $period->codperiod ? 'selected' : '' }}>
                                                {{ $period->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <span class="error-message hidden text-[11px] font-medium text-red-500 mt-1"
                                    data-error-for="codperiod"></span>
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
                                <span id="btnSubmitText">{{ isset($students) ? 'Actualizar' : 'Guardar' }}</span>
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
        var recordId = "{{ $students->codstudent ?? '' }}";
    </script>


@endsection
