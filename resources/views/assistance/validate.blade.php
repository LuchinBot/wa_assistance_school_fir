@extends('layouts.app')

@section('title', (config('app.name', 'SCA')) . ' - ' . ($extend['title'] ?? 'Asistencia'))

@section('content')
@php
    // Determinamos el estado (Compatibilidad con PHP 7.x y 8.x)
    $state = 'error';

    if (isset($success)) {
        if ($success && $message === 'Asistencia registrada correctamente') {
            $state = 'success';
        } elseif ($success && $message === 'Asistencia ya registrada') {
            $state = 'already';
        } elseif (!$success && $message === 'Fuera del horario permitido') {
            $state = 'late';
        }
    }

    // Configuración de UI por estado (Usando array asociativo estándar)
    $configs = [
        'success' => [
            'color' => 'emerald',
            'icon' => 'how_to_reg',
            'title' => 'Asistencia registrada',
            'subtitle' => 'Registro dentro del horario permitido',
        ],
        'already' => [
            'color' => 'blue',
            'icon' => 'verified',
            'title' => 'Ya registrado',
            'subtitle' => 'El registro ya existe en el sistema',
        ],
        'late' => [
            'color' => 'amber',
            'icon' => 'schedule_send',
            'title' => 'Fuera de horario',
            'subtitle' => 'Requiere justificación de tardanza',
        ],
        'error' => [
            'color' => 'red',
            'icon' => 'error',
            'title' => 'Error de sistema',
            'subtitle' => 'No se pudo procesar la solicitud',
        ],
    ];

    $config = $configs[$state];
    $theme = $config['color'];
@endphp

<div class="min-h-[90-screen] bg-slate-50 flex items-start justify-center pt-12 px-4 antialiased">
    <div class="w-full max-w-md animate-in fade-in slide-in-from-bottom-4 duration-500">
        
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 overflow-hidden border border-{{ $theme }}-100">
            
            {{-- Header Dinámico --}}
            <div class="bg-{{ $theme }}-500 px-6 py-8 flex flex-col items-center text-center gap-3">
                <div class="bg-white/20 ring-8 ring-white/10 rounded-full p-3 mb-2">
                    <span class="material-symbols-outlined text-white text-4xl leading-none">
                        {{ $config['icon'] }}
                    </span>
                </div>
                <div>
                    <h2 class="text-white font-black text-2xl tracking-tight leading-tight">
                        {{ $config['title'] }}
                    </h2>
                    <p class="text-{{ $theme }}-100 text-sm font-medium opacity-90">
                        {{ $config['subtitle'] }}
                    </p>
                </div>
            </div>

            {{-- Cuerpo Dinámico --}}
            <div class="p-6">
                
                @if($state === 'success')
                    <div class="space-y-1">
                        <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Estudiante</span>
                            <span class="font-mono font-bold text-slate-700">{{ $data->codstudent ?? '—' }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center p-4">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Hora Ingreso</span>
                            <span class="text-slate-700 font-semibold bg-emerald-50 px-3 py-1 rounded-lg">
                                {{ isset($data) ? \Carbon\Carbon::parse($data->time_entry)->format('h:i:s A') : '—' }}
                            </span>
                        </div>
                    </div>

                @elseif($state === 'late')
                    <div class="space-y-5">
                        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                            <p class="text-amber-800 text-sm leading-relaxed text-center">
                                "{{ $message }}"
                            </p>
                        </div>

                        <div class="space-y-3">                            
                            <a id="btn-late" 
                               href="{{ route('assistance.attendance.validate', ['dni' => request()->route('dni') ?? last(request()->segments()), 'late' => 'late']) }}"
                               class="flex items-center justify-center w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-amber-200 active:scale-[0.98]">
                                REGISTRAR TARDANZA
                            </a>
                        </div>
                    </div>

                @else
                    {{-- Bloque para Error o Ya registrado --}}
                    <div class="text-center py-4">
                        <p class="text-slate-600 mb-6 leading-relaxed">
                            {{ $message ?? 'Ha ocurrido un detalle inesperado al procesar el código.' }}
                        </p>
                        
                        <button onclick="location.reload()" 
                                class="w-full py-4 rounded-2xl font-bold tracking-wide transition-all border-2 
                                {{ $state === 'error' 
                                    ? 'bg-red-500 border-red-500 text-white hover:bg-red-600 shadow-lg shadow-red-200' 
                                    : 'bg-white border-blue-500 text-blue-600 hover:bg-blue-50' }}">
                            {{ $state === 'error' ? 'REINTENTAR' : 'VOLVER A INTENTAR' }}
                        </button>
                    </div>
                @endif

            </div>

            {{-- Footer / Branding --}}
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 text-center">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">
                    Assistance Control System &bull; {{ date('Y') }}
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Dinamismo para el link de tardanza con observación
    const textArea = document.getElementById('observation');
    const btnLate = document.getElementById('btn-late');

    if(textArea && btnLate) {
        textArea.addEventListener('input', (e) => {
            const baseUrl = "{{ route('assistance.attendance.validate', ['dni' => request()->route('dni') ?? last(request()->segments()), 'late' => 'late']) }}";
            btnLate.href = `${baseUrl}&obs=${encodeURIComponent(e.target.value)}`;
        });
    }
</script>

@endsection