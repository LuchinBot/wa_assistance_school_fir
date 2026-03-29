@extends('layouts.app')

@section('title', env('APP_NAME') . ' - Detalle de ' . $extend['title'])

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 py-6 md:py-8">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
            
            <!-- Header con breadcrumb -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <a href="{{ route($extend['controller'] . '.list') }}"
                        class="group flex items-center justify-center w-10 h-10 bg-white border-2 border-slate-200 rounded-xl text-slate-600 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all shadow-sm">
                        <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            Detalle de <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-red-800">Inscripción</span>
                        </h1>
                        <p class="text-slate-600 font-medium text-sm mt-1">
                            Filial: <span class="text-slate-900 font-bold">{{ $data->filial->name_large ?? 'No asignada' }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Alerta de rechazo -->
            @if ($data->status == 'R')
                <div class="mb-6 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-600 rounded-r-2xl shadow-sm overflow-hidden">
                    <div class="p-5 flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-black text-blue-900 text-base mb-1">Inscripción Rechazada</h4>
                            <p class="text-sm text-blue-700 leading-relaxed">{{ $data->reason ?? 'No se especificó un motivo.' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($data->status == 'A' || $data->status == 'V')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

                    <!-- Sidebar: Foto y datos básicos -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Card: Perfil -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-lg text-center">
                            <div class="relative w-40 h-40 mx-auto mb-4">
                                <img src="{{ $data->journalist->person->photo ? $data->journalist->person->photo : asset('img/default-avatar.png') }}"
                                    alt="Foto de perfil"
                                    class="w-full h-full object-cover rounded-2xl border-4 border-slate-100 shadow-xl">
                            </div>
                            <h2 class="text-lg font-black text-slate-900 leading-tight mb-1">
                                {{ $data->journalist->person->firstname }}
                            </h2>
                            <p class="text-base text-slate-600 font-semibold mb-3">
                                {{ $data->journalist->person->lastname_father }} {{ $data->journalist->person->lastname_mom }}
                            </p>
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-red-50 to-red-100 border border-red-200">
                                <span class="material-symbols-outlined text-blue-600 text-sm">badge</span>
                                <span class="text-blue-700 text-xs font-black tracking-wide">DNI {{ $data->journalist->person->identify_number }}</span>
                            </div>
                        </div>

                        <!-- Card: Datos CTI -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-lg">
                            <div class="flex items-center gap-2 mb-5">
                                <span class="material-symbols-outlined text-slate-400 text-xl">corporate_fare</span>
                                <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider">Datos CTI</h3>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Fecha de ingreso</p>
                                    <p class="text-slate-900 font-bold text-sm">{{ \Carbon\Carbon::parse($data->affiliation_date)->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Medio laboral actual</p>
                                    <p class="text-slate-900 font-bold text-sm">{{ $data->name_media }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main content -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Card: Información Personal -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-slate-600 text-xl">person</span>
                                    <h3 class="font-black text-slate-900 text-base">Información Personal y Contacto</h3>
                                </div>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Fecha de nacimiento</p>
                                    <p class="text-slate-900 font-bold text-sm">
                                        {{ \Carbon\Carbon::parse($data->journalist->person->birthday)->format('d \d\e F, Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Celular</p>
                                    <p class="text-slate-900 font-bold text-sm">{{ $data->journalist->person->phone }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Domicilio</p>
                                    <p class="text-slate-900 font-bold text-sm">{{ $data->journalist->person->address }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Ubicación</p>
                                    <p class="text-slate-900 font-bold text-sm">
                                        {{ $data->journalist->person->district }}, {{ $data->journalist->person->province }}
                                        ({{ $data->journalist->person->department }})
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Formación Académica -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-slate-600 text-xl">school</span>
                                    <h3 class="font-black text-slate-900 text-base">Formación Académica</h3>
                                </div>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Grado de instrucción</p>
                                    <p class="text-slate-900 font-bold text-sm">{{ $data->journalist->person->grade_instruction }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Grado académico</p>
                                    <p class="text-slate-900 font-bold text-sm">{{ $data->journalist->person->grade_academic }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Profesión</p>
                                    <p class="text-slate-900 font-bold text-sm">{{ $data->journalist->person->profession }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Historial -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-slate-600 text-xl">history</span>
                                    <h3 class="font-black text-slate-900 text-base">Historial de Años Activos</h3>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="flex flex-wrap gap-2">
                                    @forelse($periods as $period)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-green-50 to-green-100 border border-green-200 text-green-700 rounded-xl text-xs font-bold">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                            {{ $period->period->name_year }}
                                        </span>
                                    @empty
                                        <p class="text-slate-400 italic text-sm">No hay registros de años activos.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Card: Documentación -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-slate-600 text-xl">folder_open</span>
                                        <h3 class="font-black text-slate-900 text-base">Adjuntados</h3>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-[10px] font-bold uppercase tracking-wide">
                                        <span class="material-symbols-outlined text-xs">verified</span>
                                        Digitalizado
                                    </span>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @php
                                        $docs = [
                                            ['label' => 'Ficha Padrón Socio', 'url' => $data->ficha_padron_socio, 'icon' => 'description', 'color' => 'blue'],
                                            ['label' => 'Declaración Jurada', 'url' => $data->declaracion_jurada, 'icon' => 'gavel', 'color' => 'emerald'],
                                            ['label' => 'Constancia de Trabajo', 'url' => $data->constancia_trabajo, 'icon' => 'work', 'color' => 'amber'],
                                        ];
                                    @endphp

                                    @foreach ($docs as $doc)
                                        @if ($doc['url'])
                                            <a href="{{ $doc['url'] }}" target="_blank"
                                                class="group relative overflow-hidden flex items-center p-4 bg-gradient-to-br from-white to-slate-50 border-2 border-slate-200 rounded-xl hover:border-{{ $doc['color'] }}-500 hover:shadow-lg transition-all duration-300">
                                                <div class="w-11 h-11 rounded-xl bg-{{ $doc['color'] }}-100 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                                    <span class="material-symbols-outlined text-{{ $doc['color'] }}-600 text-xl">{{ $doc['icon'] }}</span>
                                                </div>
                                                <div class="ml-3 overflow-hidden flex-1">
                                                    <p class="text-xs font-bold text-slate-800 truncate mb-0.5">{{ $doc['label'] }}</p>
                                                    <p class="text-[10px] text-slate-500 group-hover:text-{{ $doc['color'] }}-600 font-semibold flex items-center gap-1 transition-colors">
                                                        Ver archivo
                                                        <span class="material-symbols-outlined text-xs group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                                                    </p>
                                                </div>
                                            </a>
                                        @else
                                            <div class="flex items-center p-4 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50 opacity-60">
                                                <div class="w-11 h-11 rounded-xl bg-slate-200 flex items-center justify-center shrink-0">
                                                    <span class="material-symbols-outlined text-slate-400 text-xl">close</span>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-xs font-semibold text-slate-400 italic">Sin cargar</p>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        @if ($data->status == 'A')
                            <div class="flex flex-col sm:flex-row gap-3 p-5 bg-gradient-to-r from-slate-50 to-slate-100 border border-slate-200 rounded-2xl">
                                <button type="button" onclick="openApproveModal()"
                                    class="flex-1 inline-flex justify-center items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-bold rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all shadow-lg hover:shadow-xl active:scale-95">
                                    <span class="material-symbols-outlined text-xl">check_circle</span>
                                    Aceptar Inscripción
                                </button>

                                <button type="button" onclick="openRejectModal()"
                                    class="flex-1 inline-flex justify-center items-center gap-2 px-6 py-3.5 bg-white border-2 border-red-300 text-blue-600 font-bold rounded-xl hover:bg-blue-50 hover:border-red-500 transition-all shadow-sm active:scale-95">
                                    <span class="material-symbols-outlined text-xl">cancel</span>
                                    Rechazar / Observar
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modales -->
                @if ($data->status == 'A')
                    <!-- Modal: Aprobar -->
                    <div id="approveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4 animate-in fade-in duration-200">
                        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                            <div class="p-6 text-center">
                                <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <span class="material-symbols-outlined text-emerald-600 text-4xl">check_circle</span>
                                </div>
                                <h3 class="text-xl font-black text-slate-900 mb-2">¿Confirmar Aprobación?</h3>
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    Al aprobar, el periodista será dado de alta oficialmente en la filial <span class="font-bold text-slate-900">{{ $data->filial->name_large }}</span>.
                                </p>
                            </div>
                            <div class="p-4 bg-slate-50 flex gap-3">
                                <button onclick="closeApproveModal()"
                                    class="flex-1 px-4 py-2.5 text-slate-600 font-bold hover:bg-slate-200 rounded-xl transition-colors">
                                    Cancelar
                                </button>
                                <button onclick="confirmApprove({{ $data->codperiod_registration }})"
                                    class="flex-1 px-4 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-md">
                                    Sí, Aprobar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal: Rechazar -->
                    <div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4 animate-in fade-in duration-200">
                        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                            <div class="p-6 border-b border-slate-200">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                        <span class="material-symbols-outlined text-blue-600 text-xl">cancel</span>
                                    </div>
                                    <h3 class="text-lg font-black text-slate-900">Rechazar Inscripción</h3>
                                </div>
                                <p class="text-sm text-slate-600">Detalla el motivo del rechazo para informar al solicitante.</p>
                            </div>
                            <div class="p-6">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2 tracking-widest">Observaciones técnicas</label>
                                <textarea id="observationText" rows="4" minlength="20"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none text-sm text-slate-700 resize-none"
                                    placeholder="Ej: El archivo 'Ficha Padrón' no es legible..."></textarea>
                                <div id="msgErrorBox" class="hidden mt-3 p-3 bg-blue-100 border border-red-300 text-blue-700 rounded-lg text-xs font-semibold"></div>
                            </div>
                            <div class="p-4 bg-slate-50 flex gap-3">
                                <button onclick="closeRejectModal()"
                                    class="flex-1 px-4 py-2.5 text-slate-600 font-bold hover:bg-slate-200 rounded-xl transition-colors">
                                    Cancelar
                                </button>
                                <button onclick="confirmReject({{ $data->codperiod_registration }})"
                                    class="flex-1 px-4 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-md">
                                    Confirmar Rechazo
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <script>
        var controller = "{{ $extend['controller'] }}";
    </script>

    @section('script')
        <script src="{{ asset("js/$extend[controller]/view.js") }}"></script>
    @stop
@endsection