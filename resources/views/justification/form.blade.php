@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'Assistance School') . ' - ' . $extend['title'])

@section('content')
    <div class="min-h-screen" style="background: #f4f6f8;">

        {{-- TOPBAR --}}
        <div class="bg-white px-6 lg:px-10 py-4 flex items-center gap-4" style="border-bottom: 1px solid #e8edf2;">

            <a href="{{ route($extend['controller'] . '.list') }}"
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
                    {{ $extend['title'] }}
                </a>
                <span class="material-symbols-outlined text-[14px] text-slate-300">chevron_right</span>
                <span class="font-semibold text-slate-700">
                    {{ isset($justification) ? 'Editar registro' : 'Nuevo registro' }}
                </span>
            </div>

            <span class="ml-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                style="{{ isset($justification)
                    ? 'background: rgba(245,158,11,0.1); color: rgb(217,119,6); border: 1px solid rgba(245,158,11,0.2);'
                    : 'background: rgba(160,185,0,0.1); color: rgb(120,140,0); border: 1px solid rgba(160,185,0,0.2);' }}">
                {{ isset($justification) ? 'Editando' : 'Nuevo' }}
            </span>
        </div>

        {{-- ALERT CONTAINER --}}
        <div id="alertContainer" class="fixed top-16 right-1 md:right-5 z-[100] w-full max-w-sm pointer-events-none"></div>

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

                            {{-- Enrollment --}}
                            <div class="space-y-1.5">
                                <label class="text-xs text-slate-500">
                                    Estudiante <span class="text-red-400">*</span>
                                </label>

                                <select name="codenrollment" id="codenrollment"
                                    class="tom-select flex-1 min-w-0 h-10 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 appearance-none"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <option value=""></option>
                                    @foreach ($enrollments as $en)
                                        <option value="{{ $en->codenrollment }}"
                                            {{ isset($justification) && $justification->codenrollment == $en->codenrollment ? 'selected' : '' }}>
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
                                <label class="text-xs text-slate-500">
                                    Tipo <span class="text-red-400">*</span>
                                </label>

                                <select name="type" id="type"
                                    class="tom-select flex-1 min-w-0 h-10 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 appearance-none"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    onfocus="this.style.background='white'; this.style.borderColor='rgba(0,176,202,0.5)'; this.style.boxShadow='0 0 0 3px rgba(0,176,202,0.08)';"
                                    onblur="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <option value=""></option>
                                    <option value="JT"
                                        {{ isset($justification) && $justification->type == 'JT' ? 'selected' : '' }}>
                                        Temporal
                                    </option>
                                    <option value="JI"
                                        {{ isset($justification) && $justification->type == 'JI' ? 'selected' : '' }}>
                                        Indefinida
                                    </option>
                                </select>
                                <span class="error-message hidden text-[11px] font-medium text-red-500"
                                    data-error-for="type"></span>
                            </div>

                            {{-- Sesión --}}
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs text-slate-500">
                                    Sesión de Asistencia (solo para Justificación Temporal)
                                </label>

                                <select name="codassistance_session" id="codassistance_session"
                                    class="tom-select flex-1 min-w-0 h-10 text-sm text-slate-700 rounded-md outline-none transition-all duration-200 appearance-none"
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
                                <label class="text-xs text-slate-500">
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
