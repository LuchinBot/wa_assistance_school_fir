<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'MonaSans';
            src: url('data:font/truetype;base64,{{ $fontRegularB64 }}') format('truetype');
        }

        @font-face {
            font-family: 'MonaSansBold';
            src: url('data:font/truetype;base64,{{ $fontBoldB64 }}') format('truetype');
        }

        @font-face {
            font-family: 'MonaSansExtraBold';
            src: url('data:font/truetype;base64,{{ $fontExtraBoldB64 }}') format('truetype');
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'MonaSans', sans-serif;
            font-size: 9px;
            color: #1e293b;
            background: #fff;
        }

        @page {
            margin: 0;
        }

        .header-title,
        thead th,
        .meta-label,
        .stat-label,
        .badge,
        .footer-accent,
        .group-label {
            font-family: 'MonaSansBold', sans-serif;
        }

        .stat-num,
        .section-title,
        .pct-text {
            font-family: 'MonaSansExtraBold', sans-serif;
        }

        /* ── HEADER ── */
        .page-header {
            background: #fff;
            padding: 18px 30px 0;
            border-bottom: 3px solid rgb(0, 176, 202);
        }

        .header-inner {
            display: table;
            width: 100%;
        }

        .header-logo-school {
            display: table-cell;
            width: 64px;
            vertical-align: middle;
        }

        .header-logo-school img {
            max-width: 56px;
            max-height: 56px;
        }

        .header-divider {
            display: table-cell;
            width: 2px;
            background: #e2e8f0;
            padding: 0 1px;
            vertical-align: middle;
        }

        .header-info {
            display: table-cell;
            vertical-align: middle;
            padding: 0 16px;
            text-align: center;
        }

        .header-institution {
            font-family: 'MonaSansExtraBold', sans-serif;
            font-size: 22px;
            color: #0f172a;
            line-height: 0.8;
            text-transform: uppercase;
            margin: 0;
        }

        .header-title {
            font-family: 'MonaSansBold', sans-serif;
            font-size: 19px;
            color: #0f172a;
            line-height: 0.8;
            text-transform: uppercase;
            margin: 0;
        }

        .header-subtitle {
            font-family: 'MonaSansBold', sans-serif;
            font-size: 8px;
            color: #475569;
            margin-top: 0px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .header-logo-sys {
            display: table-cell;
            width: 72px;
            vertical-align: middle;
            text-align: right;
        }

        .header-logo-sys img {
            max-width: 80px;
            max-height: 38px;
        }

        .header-stripe {
            height: 4px;
            margin-top: 14px;
            background: rgb(0, 176, 202);
        }

        /* ── META ── */
        .meta-section {
            padding: 10px 30px;
            background: #f8fafc;
            border-bottom: 1px solid #e8edf2;
        }

        .meta-table {
            display: table;
            width: 100%;
        }

        .meta-cell {
            display: table-cell;
            vertical-align: top;
            padding-right: 18px;
        }

        .meta-label {
            font-size: 7px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .meta-value {
            font-size: 10px;
            color: #1e293b;
        }

        .meta-cell-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            white-space: nowrap;
        }

        /* ── STATS GLOBALES ── */
        .stats-section {
            padding: 10px 30px 0;
        }

        .stat-cell {
            display: table-cell;
            padding: 7px 12px;
            text-align: center;
            border-radius: 6px;
            border: 1px solid #e8edf2;
            background: #f8fafc;
        }

        .stat-num {
            font-size: 17px;
            line-height: 1;
        }

        .stat-label {
            font-size: 6.5px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            margin-top: 2px;
        }

        /* ── LEYENDA ── */
        .legend-section {
            padding: 8px 30px 0;
            display: table;
        }

        .legend-item {
            display: table-cell;
            padding-right: 14px;
            vertical-align: middle;
            font-size: 7px;
            color: #64748b;
            white-space: nowrap;
        }

        .legend-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 2px;
            margin-right: 3px;
            vertical-align: middle;
        }

        /* ── SEPARADOR DE GRUPO ── */
        .group-header {
            padding: 10px 30px 4px;
            margin-top: 10px;
            display: table;
            width: 100%;
        }

        .group-header-inner {
            display: table;
            width: 100%;
            border-bottom: 2px solid rgb(0, 176, 202);
            padding-bottom: 4px;
        }

        .group-label {
            display: table-cell;
            justify-items: center;
            font-size: 10px;
            color: #0f172a;
            vertical-align: middle;
        }

        .group-label span {
            display: inline-block;
            background: rgb(0, 176, 202);
            color: #fff;
            padding: 2px 9px;
            border-radius: 4px;
            font-size: 8px;
            margin-right: 6px;
        }

        .group-stats {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            font-size: 7.5px;
            color: #64748b;
        }

        .group-stats b {
            color: #1e293b;
        }

        /* ── TABLA ── */
        .table-section {
            padding: 4px 30px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: rgb(0, 176, 202);
        }

        thead th {
            padding: 6px 7px;
            text-align: left;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #fff;
        }

        thead th.center {
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr {
            border-bottom: 1px solid #e8edf2;
        }

        tbody td {
            padding: 5px 7px;
            font-size: 8px;
            color: #334155;
            vertical-align: middle;
        }

        tbody td.center {
            text-align: center;
        }

        .num-col {
            color: #94a3b8;
            font-size: 7.5px;
        }

        .name-col {
            color: #0f172a;
            font-family: 'MonaSansBold', sans-serif;
        }

        /* Badges de estado diario */
        .badge-day {
            display: inline-block;
            justify-items: center;
            justify-content: center;
            width: 15px;
            height: 15px;
            border-radius: 3px;
            font-size: 6.5px;
            font-family: 'MonaSansBold', sans-serif;
            text-align: center;
        }

        .badge-P {
            background: rgba(16, 185, 129, 0.12);
            color: rgb(5, 150, 105);
        }

        .badge-T {
            background: rgba(245, 158, 11, 0.12);
            color: rgb(180, 115, 0);
        }

        .badge-J {
            background: rgba(234, 179, 8, 0.15);
            color: rgb(161, 136, 0);
        }

        .badge-A {
            background: rgba(239, 68, 68, 0.10);
            color: rgb(220, 50, 50);
        }

        /* Contadores por fila */
        .count-P {
            color: rgb(5, 150, 105);
            font-family: 'MonaSansBold', sans-serif;
        }

        .count-T {
            color: rgb(180, 115, 0);
            font-family: 'MonaSansBold', sans-serif;
        }

        .count-J {
            color: rgb(161, 136, 0);
            font-family: 'MonaSansBold', sans-serif;
        }

        .count-A {
            color: rgb(220, 50, 50);
            font-family: 'MonaSansBold', sans-serif;
        }

        /* Barra porcentaje */
        .pct-wrap {
            text-align: center;
        }

        .pct-text {
            font-size: 8px;
            line-height: 1;
        }

        .pct-bar-bg {
            width: 32px;
            height: 3px;
            background: #e8edf2;
            border-radius: 2px;
            overflow: hidden;
            margin: 2px auto 0;
        }

        .pct-bar-fill {
            height: 100%;
            border-radius: 2px;
        }

        /* ── FOOTER ── */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 6px 30px;
            border-top: 1px solid #e8edf2;
            background: #fff;
        }

        .footer-inner {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            vertical-align: middle;
            font-size: 7px;
            color: #94a3b8;
        }

        .footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-size: 7px;
            color: #94a3b8;
        }

        .footer-accent {
            color: rgb(0, 176, 202);
        }

        .page-break {
            page-break-after: always;
        }

        .empty-state {
            text-align: center;
            padding: 20px 0;
            color: #94a3b8;
            font-size: 10px;
        }

        .day-head {
            font-size: 6px;
            white-space: nowrap;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- FOOTER FIJO --}}
    <div class="page-footer">
        <div class="footer-inner">
            <div class="footer-left">
                <span class="footer-accent">I.E. Santa Rosa</span> · Assistance Control System · Reporte Semanal
            </div>
            <div class="footer-right">
                Generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}
                &nbsp;·&nbsp;
                Rango:
                {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '—' }}
                →
                {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '—' }}
            </div>
        </div>
    </div>

    {{-- HEADER --}}
    <div class="page-header">
        <div class="header-inner">

            <div class="header-logo-school">
                @if ($logoSchoolBase64)
                    <img src="{{ $logoSchoolBase64 }}" alt="I.E.">
                @endif
            </div>

            <div class="header-divider">&nbsp;</div>

            <div class="header-info">
                <div class="header-institution">INSTITUCIÓN EDUCATIVA SANTA ROSA</div>
                <div class="header-title">Reporte Semanal de Asistencia</div>
                <div class="header-subtitle">
                    Documento generado automáticamente · {{ now()->isoFormat('D [de] MMMM [de] YYYY') }}
                </div>
            </div>

            <div class="header-divider">&nbsp;</div>

            <div class="header-logo-sys">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Assistance School">
                @endif
            </div>

        </div>
        <div class="header-stripe"></div>
    </div>

    {{-- META ─ filtros aplicados --}}
    <div class="meta-section">
        <div class="meta-table">

            <div class="meta-cell">
                <div class="meta-label">Rango de fechas</div>
                <div class="meta-value">
                    {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '—' }}
                    →
                    {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '—' }}
                </div>
            </div>

            @if ($scheduleInfo)
                <div class="meta-cell">
                    <div class="meta-label">Horario</div>
                    <div class="meta-value">
                        {{ $scheduleInfo->turn }} · {{ $scheduleInfo->time_start }} – {{ $scheduleInfo->time_end }}
                    </div>
                </div>
            @endif

            @if ($periodInfo)
                <div class="meta-cell">
                    <div class="meta-label">Período</div>
                    <div class="meta-value">{{ $periodInfo->name }}</div>
                </div>
            @endif

            @if ($gradeInfo)
                <div class="meta-cell">
                    <div class="meta-label">Grado</div>
                    <div class="meta-value">
                        {{ $gradeInfo->name_large }}
                        @if ($gradeInfo->level)
                            ({{ $gradeInfo->level->name }})
                        @endif
                    </div>
                </div>
            @endif

            @if ($sectionInfo)
                <div class="meta-cell">
                    <div class="meta-label">Sección</div>
                    <div class="meta-value">{{ $sectionInfo->section }}</div>
                </div>
            @endif

            <div class="meta-cell">
                <div class="meta-label">Sesiones en rango</div>
                <div class="meta-value">{{ $totalDays }} día{{ $totalDays !== 1 ? 's' : '' }}</div>
            </div>

            <div class="meta-cell-right">
                <div
                    style="display:inline-block;background:rgba(0,176,202,0.08);border:1px solid rgba(0,176,202,0.25);border-radius:6px;padding:6px 14px;text-align:center;">
                    <div
                        style="font-size:20px;color:rgb(0,176,202);line-height:1;font-family:'MonaSansExtraBold',sans-serif;">
                        {{ $students->count() }}
                    </div>
                    <div
                        style="font-size:7px;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;margin-top:2px;">
                        estudiantes
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STATS GLOBALES --}}
    @php
        $globalPresent = $students->sum('present');
        $globalLate = $students->sum('late');
        $globalJustified = $students->sum('justified');
        $globalAbsent = $students->sum('absent');
        $globalAttended = $globalPresent + $globalLate + $globalJustified;
        $globalTotal = $globalAttended + $globalAbsent;
        $globalPct = $globalTotal > 0 ? round(($globalAttended / $globalTotal) * 100) : 0;
        $globalPctColor =
            $globalPct >= 80 ? 'rgb(5,150,105)' : ($globalPct >= 60 ? 'rgb(180,115,0)' : 'rgb(220,50,50)');

        // Agrupar estudiantes por grado + sección
        $grouped = $students
            ->groupBy(function ($s) {
                $gs = $s['grade_schedule'];
                // Clave de ordenación: nivel + nombre grado + sección
                $gradeOrder = $gs?->grade?->name_large ?? 'ZZZ';
                $section = $gs?->section ?? 'Z';
                return $gradeOrder . '||' . $section;
            })
            ->sortKeys();

        // Mapa de status → letra/clase
        $statusLetterMap = [
            'present' => ['letter' => 'P', 'cls' => 'badge-P'],
            'late' => ['letter' => 'T', 'cls' => 'badge-T'],
            'justified' => ['letter' => 'J', 'cls' => 'badge-J'],
            'absent' => ['letter' => 'A', 'cls' => 'badge-A'],
        ];
    @endphp

    {{-- <div class="stats-section">
        <table style="border-collapse:separate; border-spacing:5px 0;">
            <tr>
                <td class="stat-cell">
                    <div class="stat-num" style="color:rgb(0,176,202);">{{ $students->count() }}</div>
                    <div class="stat-label">Estudiantes</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-num" style="color:rgb(5,150,105);">{{ $globalPresent }}</div>
                    <div class="stat-label">Presentes</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-num" style="color:rgb(180,115,0);">{{ $globalLate }}</div>
                    <div class="stat-label">Tardanzas</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-num" style="color:rgb(161,136,0);">{{ $globalJustified }}</div>
                    <div class="stat-label">Justificados</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-num" style="color:rgb(220,50,50);">{{ $globalAbsent }}</div>
                    <div class="stat-label">Ausentes</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-num" style="color: {{ $globalPctColor }};">{{ $globalPct }}%</div>
                    <div class="stat-label">Asistencia</div>
                </td>
                <td style="width:100%;"></td>
            </tr>
        </table>
    </div> --}}

    {{-- LEYENDA --}}
    <div class="legend-section">
        <div class="legend-item">
            <span class="legend-dot" style="background:rgba(16,185,129,0.25);"></span>
            <span><b style="color:rgb(5,150,105);">P</b> = Presente</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot" style="background:rgba(245,158,11,0.25);"></span>
            <span><b style="color:rgb(180,115,0);">T</b> = Tardanza</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot" style="background:rgba(234,179,8,0.30);"></span>
            <span> <b style="color:rgb(161,136,0);">J</b> = Justificado</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot" style="background:rgba(239,68,68,0.20);"></span>
            <span><b style="color:rgb(220,50,50);">A</b> = Ausente</span>
        </div>
        <div class="legend-item" style="color:#94a3b8;">
            · Cada columna central corresponde a una sesión del rango seleccionado.
        </div>
    </div>

    {{-- ══ TABLAS POR GRUPO (grado + sección) ══ --}}
    @if ($students->isEmpty())
        <div class="empty-state">No se encontraron registros para los filtros seleccionados.</div>
    @else
        @foreach ($grouped as $groupKey => $groupStudents)
            @php
                $firstStudent = $groupStudents->first();
                $gs = $firstStudent['grade_schedule'];
                $gradeName = $gs?->grade?->name_large ?? '—';
                $sectionName = $gs?->section ?? '—';
                $levelName = $gs?->grade?->level?->name ?? '';

                // Stats del grupo
                $gPresent = $groupStudents->sum('present');
                $gLate = $groupStudents->sum('late');
                $gJustified = $groupStudents->sum('justified');
                $gAbsent = $groupStudents->sum('absent');
                $gAttended = $gPresent + $gLate + $gJustified;
                $gTotal = $gAttended + $gAbsent;
                $gPct = $gTotal > 0 ? round(($gAttended / $gTotal) * 100) : 0;
                $gPctColor = $gPct >= 80 ? 'rgb(5,150,105)' : ($gPct >= 60 ? 'rgb(180,115,0)' : 'rgb(220,50,50)');
            @endphp

            {{-- Cabecera del grupo --}}
            <div class="group-header">
                <div class="group-header-inner">
                    <div class="group-label">
                        <span>{{ $gradeName }} · Sección {{ $sectionName }}</span>
                        @if ($levelName)
                            {{ $levelName }}
                        @endif
                    </div>
                    <div class="group-stats">
                        <b>{{ $groupStudents->count() }}</b> estudiantes &nbsp;·&nbsp;
                        <b style="color:rgb(5,150,105);">{{ $gPresent }}</b> P &nbsp;
                        <b style="color:rgb(180,115,0);">{{ $gLate }}</b> T &nbsp;
                        <b style="color:rgb(161,136,0);">{{ $gJustified }}</b> J &nbsp;
                        <b style="color:rgb(220,50,50);">{{ $gAbsent }}</b> A &nbsp;·&nbsp;
                        <b style="color: {{ $gPctColor }};">{{ $gPct }}%</b> asistencia
                    </div>
                </div>
            </div>

            {{-- Tabla del grupo --}}
            <div class="table-section">
                <table>
                    <thead>
                        <tr>
                            <th style="width:20px;" class="center">#</th>
                            <th style="width:55px;">DNI</th>
                            <th>Apellidos y nombres</th>
                            {{-- Una columna por sesión --}}
                            @foreach ($sessions as $session)
                                <th class="day-head center">
                                    {{ \Carbon\Carbon::parse($session->date)->format('d/m') }}<br>
                                    <span style="font-weight:400;opacity:0.8;">
                                        {{ \Carbon\Carbon::parse($session->date)->isoFormat('ddd') }}
                                    </span>
                                </th>
                            @endforeach
                            <th class="center" style="width:20px;">P</th>
                            <th class="center" style="width:20px;">T</th>
                            <th class="center" style="width:20px;">J</th>
                            <th class="center" style="width:20px;">A</th>
                            <th class="center" style="width:46px;">% Asist.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groupStudents as $i => $student)
                            @php
                                $person = $student['person'];
                                $pct = $student['percentage'];
                                $pctColor =
                                    $pct >= 80 ? 'rgb(5,150,105)' : ($pct >= 60 ? 'rgb(180,115,0)' : 'rgb(220,50,50)');

                                // Apellidos, Nombres
                                $fullName = trim(
                                    ($person->lastname_father ?? '') .
                                        ' ' .
                                        ($person->lastname_mom ?? '') .
                                        ', ' .
                                        ($person->firstname ?? ''),
                                );
                            @endphp
                            <tr>
                                <td class="num-col center">{{ $i + 1 }}</td>
                                <td>{{ $person->identify_number ?? '—' }}</td>
                                <td class="name-col">{{ $fullName }}</td>

                                {{-- Celda por sesión --}}
                                @foreach ($sessions as $j => $session)
                                    @php
                                        $statusKey = $student['daily'][$j] ?? 'absent';
                                        $badge = $statusLetterMap[$statusKey] ?? $statusLetterMap['absent'];
                                    @endphp
                                    <td class="center">
                                        <span class="badge-day {{ $badge['cls'] }}">{{ $badge['letter'] }}</span>
                                    </td>
                                @endforeach

                                <td class="center count-P">{{ $student['present'] }}</td>
                                <td class="center count-T">{{ $student['late'] }}</td>
                                <td class="center count-J">{{ $student['justified'] }}</td>
                                <td class="center count-A">{{ $student['absent'] }}</td>

                                <td class="center">
                                    <div class="pct-wrap">
                                        <div class="pct-text" style="color: {{ $pctColor }};">
                                            {{ $pct }}%</div>
                                        <div class="pct-bar-bg">
                                            <div class="pct-bar-fill"
                                                style="width:{{ $pct }}%; background: {{ $pctColor }};">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

</body>

</html>
