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

        {{-- CONTENIDO --}}
        <div class="px-6 lg:px-10 py-6 max-w-3xl mx-auto">

            <form id="mainForm" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" id="recordId" value="{{ $justification->codjustification ?? '' }}">
                <input type="hidden" name="redirect" value="{{ $redirect ?? '' }}">

                <div class="bg-white rounded-lg" style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                    {{-- Campos --}}
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-slate-500">
                                    Estudiante <span class="text-red-400">*</span>
                                </label>

                                <select name="codenrollment" id="codenrollment"
                                    class="tom-select flex-1 min-w-0 h-10 px-0 text-sm text-black rounded-md outline-none transition-all duration-200 appearance-none"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <option value="">Seleccione...</option>
                                    @foreach ($enrollments as $en)
                                        <option value="{{ $en->codenrollment }}"
                                            {{ isset($justification) && $justification->codenrollment == $en->codenrollment ? 'selected' : '' }}>
                                            {{ $en->student->person->identify_number ?? '' }} -
                                            {{ $en->student->person->firstname ?? '' }}
                                            {{ $en->student->person->lastname_father ?? '' }}
                                            {{ $en->student->person->lastname_mom ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="codenrollment"></span>
                            </div>

                            {{-- Tipo --}}
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-slate-500">
                                    Tipo <span class="text-red-400">*</span>
                                </label>

                                <select name="type" id="type"
                                    class="tom-select flex-1 min-w-0 h-10 px-0 text-sm text-black rounded-md outline-none transition-all duration-200 appearance-none"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <option value="">Seleccione...</option>
                                    <option value="JT"
                                        {{ isset($justification) && $justification->type == 'JT' ? 'selected' : '' }}>
                                        Temporal (Solo por hoy)
                                    </option>
                                    <option value="JI"
                                        {{ isset($justification) && $justification->type == 'JI' ? 'selected' : '' }}>
                                        Indefinida (Sin retorno)
                                    </option>
                                </select>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="type"></span>
                            </div>

                            {{-- Sesión --}}
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-sm font-medium text-slate-500">
                                   Toma de asistencia (solo temporal)
                                </label>

                                <select name="codassistance_session" id="codassistance_session"
                                    class="tom-select flex-1 min-w-0 h-10 px-0 text-sm text-black rounded-md outline-none transition-all duration-200 appearance-none"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <option value=""></option>
                                    @foreach ($assistance_sessions as $s)
                                        <option value="{{ $s->codassistance_session }}"
                                            {{ isset($justification) && $justification->codassistance_session == $s->codassistance_session ? 'selected' : '' }}>
                                            {{ $s->date . ' (' . $s->schedule->turn . ')' ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="codassistance_session"></span>
                            </div>

                            {{-- Motivo --}}
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-sm font-medium text-slate-500">
                                    Motivo <span class="text-red-400">*</span>
                                </label>

                                <textarea name="reason" rows="3"
                                    class="w-full p-3.5 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 placeholder:text-slate-300"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">{{ $justification->reason ?? '' }}</textarea>
                            </div>

                        </div>
                    </div>
                    {{-- FOOTER --}}
                    <div class="px-6 py-4 flex flex-col-reverse sm:flex-row items-center justify-between gap-3"
                        style="border-top: 1px solid #f1f5f9; background: #fafbfc;">

                        <p class="text-[11px] text-slate-400">
                            <span class="text-red-400">*</span> Campos obligatorios
                        </p>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <a href="{{ request('redirect') ?? route($extend['controller'] . '.list') }}"
                                class="flex-1 sm:flex-none h-9 px-5 flex items-center justify-center gap-1.5 text-xs font-normal text-slate-600 rounded-lg transition-all bg-slate-200 hover:bg-slate-300 active:scale-95">
                                <span class="material-symbols-outlined text-[15px]">chevron_left</span>
                                Cancelar
                            </a>
                            <button type="submit" id="btnSubmit"
                                class="flex-1 sm:flex-none h-9 px-6 flex items-center justify-center gap-1.5 text-white text-xs font-normal rounded-lg transition-all duration-200 active:scale-95"
                                style="background: rgb(0,176,202); box-shadow: 0 2px 8px rgba(0,176,202,0.3);"
                                onmouseover="this.style.background='rgb(190,214,0)'; this.style.boxShadow='0 2px 8px rgba(190,214,0,0.3)'; this.style.color='white';"
                                onmouseout="this.style.background='rgb(0,176,202)'; this.style.boxShadow='0 2px 8px rgba(0,176,202,0.3)'; this.style.color='white';">
                                <span id="btnSubmitText">
                                    {{ isset($justification) ? 'Actualizar' : 'Guardar ' }}
                                </span>
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        const controller = "{{ $extend['controller'] }}";
        const recordId = "{{ $justification->codjustification ?? '' }}";
        const totalRecordsOld = {{ $extend['totalRecord'] }};
        const totalRecords = {{ $extend['totalRecord'] }};
    </script>>
@endsection
