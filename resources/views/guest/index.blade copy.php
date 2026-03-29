@extends('layouts.guest')

@section('title', (env('APP_NAME') ?? 'Assistance School') . ' — ' . $extend['title'])

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }

    @keyframes pulse-dot {
        0%,100% { box-shadow: 0 0 0 2px rgba(52,211,153,.3); }
        50%      { box-shadow: 0 0 0 5px rgba(52,211,153,.06); }
    }
    .live-dot { animation: pulse-dot 2s infinite; }

    @keyframes fade-up {
        from { opacity:0; transform:translateY(8px); }
        to   { opacity:1; transform:translateY(0); }
    }
    .fade-up  { animation: fade-up .3s ease both; }
    .delay-1  { animation-delay:.07s }
    .delay-2  { animation-delay:.14s }
    .delay-3  { animation-delay:.21s }
    .delay-4  { animation-delay:.28s }

    select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2394a3b8'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 2rem !important;
        appearance: none;
        -webkit-appearance: none;
    }

    .tbl-scroll::-webkit-scrollbar { height: 4px; }
    .tbl-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }

    .assist-scroll { max-height: 210px; overflow-y: auto; }
    .assist-scroll::-webkit-scrollbar { width: 3px; }
    .assist-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }
</style>
@endpush

@section('content')

{{-- Datos para JS --}}
<script>
    var controller = "{{ $extend['controller'] }}";
    var guestData  = {
        trend   : @json($trend),
        byGrade : @json($byGrade),
        today   : @json($todayStats),
    };
</script>

<div class="min-h-screen bg-slate-50">

    {{-- ── TOP BAR ── --}}
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-100 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-3">

            <a href="{{ route('guest.index') }}" class="shrink-0">
                <img src="{{ asset('img/logotipo.png') }}" alt="Assistance School" class="h-8">
            </a>

            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                    <span class="live-dot w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                    En vivo · <strong class="text-slate-600">{{ now()->format('H:i') }}</strong>
                </div>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest
                             bg-teal-50 border border-teal-200 text-teal-600 px-2.5 py-1 rounded-full select-none">
                    <span class="material-symbols-outlined" style="font-size:12px">badge</span>
                    Invitado
                </span>
            </div>

        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 pb-20 space-y-4">

        {{-- ── PAGE HEADER ── --}}
        <div>
            <h1 class="text-2xl sm:text-[1.7rem] font-extrabold text-slate-800 tracking-tight leading-tight">
                Panel de <span class="text-teal-500">Asistencia</span>
            </h1>
            <p class="text-xs font-medium text-slate-400 mt-0.5">I.E. Santa Rosa · Solo lectura</p>
        </div>

        {{-- ── FILTROS ── --}}
        <form method="GET" action="{{ route('guest.index') }}"
              class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">

            @php
                $lbl = 'block text-[9px] font-bold uppercase tracking-widest text-slate-400 mb-1.5';
                $inp = 'w-full h-9 px-3 text-[13px] font-medium text-slate-700 bg-slate-50
                        border border-slate-200 rounded-xl
                        focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100
                        transition-colors placeholder:text-slate-300';
            @endphp

            <div class="flex flex-wrap items-end gap-3">

                <div class="flex flex-col w-36">
                    <label class="{{ $lbl }}">Periodo</label>
                    <select name="period" id="fPeriod" class="{{ $inp }}">
                        <option value="">Activo</option>
                        @foreach($periods as $per)
                            <option value="{{ $per->codperiod }}"
                                {{ request('period') == $per->codperiod ? 'selected' : '' }}>
                                {{ $per->name }}{{ $per->is_active === 'Y' ? ' ✓' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col w-28">
                    <label class="{{ $lbl }}">Grado</label>
                    <select name="grade" id="fGrade" class="{{ $inp }}">
                        <option value="">Todos</option>
                        @foreach($grades as $g)
                            <option value="{{ $g->codgrade }}"
                                {{ request('grade') == $g->codgrade ? 'selected' : '' }}>
                                {{ $g->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col w-24">
                    <label class="{{ $lbl }}">Sección</label>
                    <select name="section" id="fSection" class="{{ $inp }}">
                        <option value="">Todas</option>
                        @foreach($sections as $s)
                            <option value="{{ $s }}" {{ request('section') == $s ? 'selected' : '' }}>
                                {{ $s }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col flex-1 min-w-[170px]">
                    <label class="{{ $lbl }}">Sesión</label>
                    <select name="session" id="fSession" class="{{ $inp }}">
                        <option value="">Todas las sesiones</option>
                        @foreach($sessions as $sess)
                            <option value="{{ $sess->codassistance_session }}"
                                {{ request('session') == $sess->codassistance_session ? 'selected' : '' }}>
                                {{ $sess->date }} · {{ $sess->turn }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 self-end">
                    <button type="submit"
                        class="h-9 px-4 rounded-xl bg-teal-500 hover:bg-teal-600 active:scale-95
                               text-white text-xs font-bold flex items-center gap-1.5
                               shadow-sm shadow-teal-200/60 transition-all">
                        <span class="material-symbols-outlined" style="font-size:15px">filter_list</span>
                        Aplicar
                    </button>

                    @if(request()->hasAny(['period','grade','section','session']))
                    <a href="{{ route('guest.index') }}"
                       class="h-9 w-9 rounded-xl border border-slate-200 bg-white flex items-center justify-center
                              text-slate-400 hover:text-slate-600 hover:border-slate-300
                              active:scale-95 transition-all no-underline"
                       title="Limpiar filtros">
                        <span class="material-symbols-outlined" style="font-size:16px">close</span>
                    </a>
                    @endif
                </div>

            </div>
        </form>

        {{-- ── KPI CARDS ── --}}
        @php
            $kpis = [
                [
                    'val'  => number_format($totalStudents),
                    'lbl'  => 'Estudiantes',
                    'sub'  => $gradeCount.' grados · '.$sectionCount.' secc.',
                    'icon' => 'groups',
                    'bar'  => 'from-teal-400 to-teal-500',
                    'ic'   => 'bg-teal-50 text-teal-500',
                    'vc'   => 'text-slate-800',
                    'd'    => '',
                ],
                [
                    'val'  => number_format($todayStats['present']),
                    'lbl'  => 'Presentes hoy',
                    'sub'  => $todayStats['total'] > 0
                                ? round(($todayStats['present']/$todayStats['total'])*100,1).'% del total'
                                : '—',
                    'icon' => 'check_circle',
                    'bar'  => 'from-emerald-400 to-emerald-500',
                    'ic'   => 'bg-emerald-50 text-emerald-500',
                    'vc'   => 'text-emerald-600',
                    'd'    => 'delay-1',
                ],
                [
                    'val'  => number_format($todayStats['absent']),
                    'lbl'  => 'Ausentes hoy',
                    'sub'  => $todayStats['total'] > 0
                                ? round(($todayStats['absent']/$todayStats['total'])*100,1).'% del total'
                                : '—',
                    'icon' => 'cancel',
                    'bar'  => 'from-red-400 to-red-500',
                    'ic'   => 'bg-red-50 text-red-400',
                    'vc'   => 'text-red-500',
                    'd'    => 'delay-2',
                ],
                [
                    'val'  => number_format($todayStats['late']),
                    'lbl'  => 'Tardanzas hoy',
                    'sub'  => $todayStats['total'] > 0
                                ? round(($todayStats['late']/$todayStats['total'])*100,1).'% del total'
                                : '—',
                    'icon' => 'schedule',
                    'bar'  => 'from-amber-400 to-amber-500',
                    'ic'   => 'bg-amber-50 text-amber-500',
                    'vc'   => 'text-amber-500',
                    'd'    => 'delay-3',
                ],
                [
                    'val'  => $overallRate.'%',
                    'lbl'  => '% Asistencia',
                    'sub'  => 'periodo seleccionado',
                    'icon' => 'trending_up',
                    'bar'  => 'from-lime-400 to-lime-500',
                    'ic'   => 'bg-lime-50 text-lime-600',
                    'vc'   => 'text-lime-600',
                    'd'    => 'delay-4',
                ],
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach($kpis as $k)
            <div class="fade-up {{ $k['d'] }} relative bg-white border border-slate-200 rounded-2xl
                        shadow-sm overflow-hidden hover:-translate-y-0.5 hover:shadow-md
                        transition-all duration-200">
                {{-- Acento top --}}
                <div class="h-[3px] bg-gradient-to-r {{ $k['bar'] }}"></div>
                <div class="p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $k['ic'] }} flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" style="font-size:20px">{{ $k['icon'] }}</span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[1.5rem] font-extrabold leading-none {{ $k['vc'] }}">{{ $k['val'] }}</div>
                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $k['lbl'] }}</div>
                        <div class="text-[10.5px] text-slate-400 mt-0.5 truncate">{{ $k['sub'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── CHARTS ── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="flex items-center gap-2 text-[13px] font-bold text-slate-700">
                        <span class="material-symbols-outlined text-teal-500" style="font-size:17px">show_chart</span>
                        Tendencia · 4 semanas
                    </span>
                    <span class="text-[9px] font-bold uppercase tracking-widest px-2.5 py-0.5
                                 rounded-full bg-teal-50 border border-teal-100 text-teal-600">Líneas</span>
                </div>
                <div class="h-44"><canvas id="chartTrend"></canvas></div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="flex items-center gap-2 text-[13px] font-bold text-slate-700">
                        <span class="material-symbols-outlined text-teal-500" style="font-size:17px">donut_large</span>
                        Estado hoy
                    </span>
                    <span class="text-[9px] font-bold uppercase tracking-widest px-2.5 py-0.5
                                 rounded-full bg-lime-50 border border-lime-100 text-lime-600">Hoy</span>
                </div>
                <div class="h-44"><canvas id="chartDonut"></canvas></div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <span class="flex items-center gap-2 text-[13px] font-bold text-slate-700">
                    <span class="material-symbols-outlined text-teal-500" style="font-size:17px">bar_chart</span>
                    Asistencia por grado y sección
                </span>
                <span class="text-[9px] font-bold uppercase tracking-widest px-2.5 py-0.5
                             rounded-full bg-teal-50 border border-teal-100 text-teal-600">Comparativo</span>
            </div>
            <div class="h-44"><canvas id="chartGrades"></canvas></div>
        </div>

        {{-- ── RANKING + BUSCADOR + TABLA ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-[230px_1fr] gap-4">

            {{-- Ranking --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
                <div class="flex items-center gap-2 text-[13px] font-bold text-slate-700 mb-4">
                    <span class="material-symbols-outlined text-lime-500" style="font-size:17px">workspace_premium</span>
                    Ranking
                </div>

                @forelse($ranking->take(8) as $i => $r)
                @php
                    [$numCls, $barClr] = $i === 0
                        ? ['bg-lime-100 text-lime-700', '#84cc16']
                        : ($i < 4
                            ? ['bg-teal-50 text-teal-600', '#14b8a6']
                            : ['bg-red-50 text-red-400', '#f87171']);
                    $bw = min((int)$r['rate'], 100);
                @endphp
                <div class="flex items-center gap-2 py-1.5 border-b border-slate-100 last:border-0">
                    <span class="w-[18px] h-[18px] rounded flex items-center justify-center
                                 text-[10px] font-extrabold shrink-0 {{ $numCls }}">{{ $i+1 }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11.5px] font-bold text-slate-700 truncate">
                            {{ $r['grade'] }} {{ $r['section'] }}
                        </div>
                        <div class="text-[10px] text-slate-400">{{ $r['students'] }} alumnos</div>
                    </div>
                    <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden shrink-0">
                        <div class="h-full rounded-full" style="width:{{ $bw }}%;background:{{ $barClr }}"></div>
                    </div>
                    <span class="text-[11px] font-bold w-7 text-right shrink-0"
                          style="color:{{ $barClr }}">{{ $bw }}%</span>
                </div>
                @empty
                <p class="text-center text-[12px] text-slate-300 py-6">Sin datos</p>
                @endforelse
            </div>

            {{-- Derecha --}}
            <div class="flex flex-col gap-4">

                {{-- Buscador alumno --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-teal-500" style="font-size:17px">person_search</span>
                        <span class="text-[13px] font-bold text-slate-700">Buscar alumno por DNI</span>
                    </div>
                    <div class="p-4">
                        <div class="flex gap-2 mb-3">
                            <div class="relative flex-1">
                                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2
                                             text-slate-300 pointer-events-none" style="font-size:16px">search</span>
                                <input id="studentSearch" type="text"
                                       placeholder="Ingresa el DNI..."
                                       class="w-full h-9 pl-8 pr-3 bg-slate-50 border border-slate-200 rounded-xl
                                              text-[13px] font-medium text-slate-700
                                              focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100
                                              placeholder:text-slate-300 transition-colors">
                            </div>
                            <button id="btnStudentSearch"
                                class="h-9 px-4 rounded-xl bg-teal-500 hover:bg-teal-600 active:scale-95
                                       text-white text-xs font-bold transition-all">
                                Buscar
                            </button>
                        </div>
                        <div id="studentResult">
                            <p class="text-center text-[11.5px] text-slate-300 py-3">
                                Ingresa el DNI del alumno para ver su historial
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Tabla --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 flex flex-wrap items-center
                                justify-between gap-3">
                        <span class="flex items-center gap-2 text-[13px] font-bold text-slate-700">
                            <span class="material-symbols-outlined text-teal-500" style="font-size:17px">table_view</span>
                            Registros
                        </span>
                        <div class="flex items-center gap-2">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2
                                             text-slate-300 pointer-events-none" style="font-size:15px">search</span>
                                <input id="tableSearch" type="text" placeholder="DNI, nombre..."
                                       class="h-8 w-40 pl-8 pr-2.5 bg-slate-50 border border-slate-200 rounded-xl
                                              text-[12px] font-medium text-slate-700
                                              focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100
                                              placeholder:text-slate-300 transition-colors">
                            </div>
                            <select id="statusFilter"
                                class="h-8 pl-2.5 bg-slate-50 border border-slate-200 rounded-xl
                                       text-[12px] font-medium text-slate-700
                                       focus:outline-none focus:border-teal-400 transition-colors">
                                <option value="">Todos</option>
                                <option value="present">Presente</option>
                                <option value="absent">Ausente</option>
                                <option value="late">Tardanza</option>
                            </select>
                        </div>
                    </div>

                    <div class="tbl-scroll overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    @foreach(['DNI','Nombre','Grado','Secc.','Fecha','Hora','Estado'] as $col)
                                    <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-widest
                                               text-slate-400 whitespace-nowrap">{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-10">
                                        <span class="material-symbols-outlined text-3xl text-slate-200 block mb-1">
                                            hourglass_empty
                                        </span>
                                        <span class="text-[12px] text-slate-300">Cargando...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-5 py-3 border-t border-slate-100 flex flex-wrap items-center
                                justify-between gap-2">
                        <span id="paginationInfo" class="text-[11px] font-semibold text-slate-400">
                            — registros
                        </span>
                        <div id="paginationBtns" class="flex gap-1 flex-wrap"></div>
                    </div>
                </div>

            </div>{{-- /right --}}
        </div>{{-- /grid --}}

    </main>
</div>

@push('scripts')
<script>
(function () {
    const fPeriod  = document.getElementById('fPeriod');
    const fGrade   = document.getElementById('fGrade');
    const fSection = document.getElementById('fSection');
    const fSession = document.getElementById('fSession');

    function reloadSessions() {
        if (!fSession) return;
        const p = new URLSearchParams();
        if (fPeriod?.value)  p.set('period',  fPeriod.value);
        if (fGrade?.value)   p.set('grade',   fGrade.value);
        if (fSection?.value) p.set('section', fSection.value);

        fSession.innerHTML = '<option value="">Cargando...</option>';
        fSession.disabled  = true;

        fetch(`/guest/api/sessions?${p}`)
            .then(r => r.json())
            .then(({ sessions = [] }) => {
                fSession.innerHTML = '<option value="">Todas las sesiones</option>';
                sessions.forEach(s => {
                    const o = document.createElement('option');
                    o.value = s.codassistance_session;
                    o.textContent = `${s.date} · ${s.turn}`;
                    fSession.appendChild(o);
                });
                fSession.disabled = false;
            })
            .catch(() => {
                fSession.innerHTML = '<option value="">Error</option>';
                fSession.disabled  = false;
            });
    }

    fPeriod ?.addEventListener('change', reloadSessions);
    fGrade  ?.addEventListener('change', reloadSessions);
    fSection?.addEventListener('change', reloadSessions);
})();
</script>
@endpush

@endsection