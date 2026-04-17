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
            font-size: 8.5px;
            color: #1e293b;
            background: #fff;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        /* ── HEADER ── */
        .page-header {
            padding: 14px 24px 0;
            border-bottom: 3px solid rgb(0, 176, 202);
        }

        .header-inner {
            display: table;
            width: 100%;
        }

        .header-logo-school {
            display: table-cell;
            width: 52px;
            vertical-align: middle;
        }

        .header-logo-school img {
            max-width: 46px;
            max-height: 46px;
        }

        .header-divider {
            display: table-cell;
            width: 1px;
            background: #e2e8f0;
            padding: 0;
            vertical-align: middle;
        }

        .header-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 1px;
            padding: 0 14px;
            text-align: center;
        }

        .header-institution {
            font-family: 'MonaSansExtraBold', sans-serif;
            font-size: 18px;
            color: #0f172a;
            text-transform: uppercase;
            line-height: 1;
            /* 👈 más natural */
        }

        .header-title {
            font-family: 'MonaSansBold', sans-serif;
            font-size: 16px;
            color: rgb(0, 176, 202);
            text-transform: uppercase;
            line-height: 1;
        }

        .header-subtitle {
            font-family: 'MonaSans', sans-serif;
            font-size: 10px;
            color: #64748b;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1;
        }

        .header-logo-sys {
            display: table-cell;
            width: 68px;
            vertical-align: middle;
            text-align: right;
        }

        .header-logo-sys img {
            max-width: 64px;
            max-height: 32px;
        }

        .header-stripe {
            height: 3px;
            margin-top: 10px;
            background: rgb(0, 176, 202);
        }

        /* ── META ── */
        .meta-section {
            padding: 7px 24px;
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
            padding-right: 14px;
        }

        .meta-label {
            font-family: 'MonaSansBold', sans-serif;
            font-size: 6.5px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .meta-value {
            font-family: 'MonaSansBold', sans-serif;
            font-size: 8.5px;
            color: #1e293b;
        }

        .meta-cell-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            white-space: nowrap;
        }

        .meta-badge {
            display: inline-block;
            background: rgba(0, 176, 202, 0.08);
            border: 1px solid rgba(0, 176, 202, 0.25);
            border-radius: 6px;
            padding: 5px 12px;
            text-align: center;
        }

        .meta-badge-num {
            font-family: 'MonaSansExtraBold', sans-serif;
            font-size: 18px;
            color: rgb(0, 176, 202);
            line-height: 1;
        }

        .meta-badge-label {
            font-family: 'MonaSansBold', sans-serif;
            font-size: 6.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 2px;
        }

        /* ── LEYENDA ── */
        .legend-section {
            padding: 5px 24px;
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
        }

        .legend-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 7px;
            color: #475569;
            white-space: nowrap;
        }

        .legend-text {
            white-space: nowrap;
        }

        .legend-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 2px;
            margin-right: 3px;
        }


        /* ── SEPARADOR DE GRUPO ── */
        .group-header {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 8px 24px;
        }

        .group-header-inner {
            width: 100%;
        }

        /* 🔥 ESTE ES EL CONTENEDOR CLAVE */
        .group-center {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            /* espacio entre tag y nivel */
        }

        /* Tu tag (ajustado) */
        .group-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgb(0, 176, 202);
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-family: 'MonaSansBold', sans-serif;
        }

        /* Texto del nivel */
        .group-level {
            font-family: 'MonaSansBold', sans-serif;
            font-size: 9px;
            color: #64748b;
        }


        .stat-item {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            white-space: nowrap;
            font-size: 7px;
            color: #64748b;
        }

        .stat-item b {
            font-family: 'MonaSansBold', sans-serif;
            color: #1e293b;
        }

        .stat-P b {
            color: rgb(5, 150, 105);
        }

        .stat-T b {
            color: rgb(180, 115, 0);
        }

        .stat-J b {
            color: rgb(161, 136, 0);
        }

        .stat-A b {
            color: rgb(220, 50, 50);
        }

        .stat-sep {
            color: #94a3b8;
        }

        /* ── TABLA ── */
        .table-section {
            padding: 3px 24px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: rgb(0, 176, 202);
        }

        thead th {
            font-family: 'MonaSansBold', sans-serif;
            padding: 5px 6px;
            text-align: left;
            font-size: 6.5px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #fff;
        }

        thead th.center {
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr {
            border-bottom: 1px solid #edf0f4;
        }

        tbody td {
            padding: 4px 6px;
            font-size: 7.5px;
            color: #334155;
            vertical-align: middle;
        }

        tbody td.center {
            text-align: center;
        }

        .num-col {
            color: #94a3b8;
            font-size: 7px;
            text-align: center;
        }

        .name-col {
            font-family: 'MonaSansBold', sans-serif;
            color: #0f172a;
            font-size: 7.5px;
        }

        .dni-col {
            font-size: 7px;
            color: #64748b;
        }

        /* ── BADGES DE DÍA ── */
        .badge-day {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 3px;
            font-family: 'MonaSansBold', sans-serif;
            font-size: 6.5px;
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

        .badge-dash {
            background: #f1f5f9;
            color: #cbd5e1;
        }

        /* ── CONTADORES POR FILA ── */
        .count-P {
            font-family: 'MonaSansBold', sans-serif;
            color: rgb(5, 150, 105);
            font-size: 7.5px;
        }

        .count-T {
            font-family: 'MonaSansBold', sans-serif;
            color: rgb(180, 115, 0);
            font-size: 7.5px;
        }

        .count-J {
            font-family: 'MonaSansBold', sans-serif;
            color: rgb(161, 136, 0);
            font-size: 7.5px;
        }

        .count-A {
            font-family: 'MonaSansBold', sans-serif;
            color: rgb(220, 50, 50);
            font-size: 7.5px;
        }

        /* ── BARRA % ── */
        .pct-wrap {
            text-align: center;
        }

        .pct-text {
            font-family: 'MonaSansExtraBold', sans-serif;
            font-size: 7.5px;
            line-height: 1;
        }

        .pct-bar-bg {
            width: 28px;
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

        /* ── FOOTER FIJO ── */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 5px 24px;
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
            font-size: 6.5px;
            color: #94a3b8;
        }

        .footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-size: 6.5px;
            color: #94a3b8;
        }

        .footer-accent {
            font-family: 'MonaSansBold', sans-serif;
            color: rgb(0, 176, 202);
        }

        /* ── SALTO DE PÁGINA ── */
        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* Estado vacío */
        .empty-state {
            text-align: center;
            padding: 30px 0;
            color: #94a3b8;
            font-size: 10px;
        }

        /* Cabecera de día en tabla */
        .day-head {
            font-size: 6px;
            white-space: nowrap;
            text-align: center;
        }

        /* Resumen de grupo al pie de tabla */
        .group-footer {
            padding: 4px 24px 6px;
        }

        .group-footer-inner {
            display: table;
            width: 100%;
            background: rgba(0, 176, 202, 0.04);
            border: 1px solid rgba(0, 176, 202, 0.15);
            border-radius: 4px;
            padding: 4px 10px;
        }

        .gf-cell {
            display: table-cell;
            vertical-align: middle;
            font-size: 7px;
            color: #475569;
            padding-right: 14px;
        }

        .gf-cell b {
            color: #0f172a;
        }
    </style>
</head>

<body>

    {{-- ── FOOTER FIJO ── --}}
    <div class="page-footer">
        <div class="footer-inner">
            <div class="footer-left">
                <span class="footer-accent">I.E. Francisco Izquierdo Ríos</span>
                · Reporte Semanal de Asistencia
                @if ($scheduleInfo)
                    · {{ $scheduleInfo->turn }}
                @endif
            </div>
            <div class="footer-right">
                Generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}
                &nbsp;·&nbsp;
                {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '—' }}
                →
                {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '—' }}
            </div>
        </div>
    </div>

    {{-- ── HEADER ── --}}
    <div class="page-header">
        <div class="header-inner">

            <div class="header-logo-school">
                @if ($logoSchoolBase64)
                    <img src="{{ $logoSchoolBase64 }}" alt="I.E.">
                @endif
            </div>

            <div class="header-divider">&nbsp;</div>

            <div class="header-info">
                <div class="header-institution">Institución Educativa Francisco Izquierdo Ríos</div>
                <div class="header-title">Reporte Semanal de Asistencia</div>
                <div class="header-subtitle">
                    Documento generado automáticamente · {{ now()->isoFormat('D [de] MMMM [de] YYYY') }}
                </div>
            </div>

            <div class="header-divider">&nbsp;</div>

            <div class="header-logo-sys">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="SCA">
                @endif
            </div>

        </div>
        <div class="header-stripe"></div>
    </div>

    {{-- ── META ── --}}
    <div class="meta-section">
        <div class="meta-table">

            <div class="meta-cell">
                <div class="meta-label">Rango de fechas</div>
                <div class="meta-value">
                    {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->isoFormat('ddd D MMM') : '—' }}
                    →
                    {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->isoFormat('ddd D MMM YYYY') : '—' }}
                </div>
            </div>

            @if ($scheduleInfo)
                <div class="meta-cell">
                    <div class="meta-label">Horario</div>
                    <div class="meta-value">{{ $scheduleInfo->turn }} · {{ $scheduleInfo->time_start }} –
                        {{ $scheduleInfo->time_end }}</div>
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
                <div class="meta-label">Sesiones</div>
                <div class="meta-value">{{ $totalDays }} día{{ $totalDays !== 1 ? 's' : '' }}</div>
            </div>

            <div class="meta-cell-right">
                <div class="meta-badge">
                    <div class="meta-badge-num">{{ $students->count() }}</div>
                    <div class="meta-badge-label">Estudiantes</div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── LEYENDA ── --}}
    <div class="legend-section">
        <div class="legend-inner">
            <div class="legend-item">
                <span class="legend-dot" style="background:rgba(16,185,129,0.25);"></span>
                <span class="legend-text">
                    <b style="color:rgb(5,150,105);">P</b> = Presente
                </span>
            </div>
            <div class="legend-item">
                <span class="legend-dot" style="background:rgba(245,158,11,0.25);"></span>
                <span class="legend-text">
                    <b style="color:rgb(180,115,0);">T</b> = Tardanza
                </span>
            </div>
            <div class="legend-item">
                <span class="legend-dot" style="background:rgba(234,179,8,0.30);"></span>
                <span class="legend-text">
                    <b style="color:rgb(161,136,0);">J</b> = Justificado
                </span>
            </div>
            <div class="legend-item">
                <span class="legend-dot" style="background:rgba(239,68,68,0.20);"></span>
                <span class="legend-text">
                    <b style="color:rgb(220,50,50);">A</b> = Ausente
                </span>
            </div>
            <div class="legend-item" style="color:#94a3b8;font-size:6.5px;">
                · Cada columna central = una sesión del rango seleccionado.
            </div>
        </div>
    </div>

    @php
        /*
         * Mapa de status → letra y clase de badge
         */
        $statusLetterMap = [
            'present' => ['letter' => 'P', 'cls' => 'badge-P'],
            'late' => ['letter' => 'T', 'cls' => 'badge-T'],
            'justified' => ['letter' => 'J', 'cls' => 'badge-J'],
            'absent' => ['letter' => 'A', 'cls' => 'badge-A'],
        ];

        /*
         * Agrupar por grado + sección (ya vienen ordenados desde el controller)
         */
        $grouped = $students->groupBy(function ($s) {
            $gs = $s['grade_schedule'];
            return ($gs?->grade?->name_large ?? 'ZZZ') . '||' . ($gs?->section ?? 'Z');
        });

        $MAX_PER_PAGE = 40; // máx estudiantes por hoja por grupo
    @endphp

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

                // Dividir en páginas de MAX_PER_PAGE estudiantes
                $chunks = $groupStudents->chunk($MAX_PER_PAGE);
                $totalChunks = $chunks->count();
            @endphp

            @foreach ($chunks as $chunkIndex => $chunk)
                @php $isFirstChunk = ($chunkIndex === 0); @endphp

                {{-- Cabecera del grupo: solo en el primer chunk --}}
                @if ($isFirstChunk)
                    <div class="group-header">
                        <div class="group-header-inner">
                            <div class="group-center">
                                <span class="group-tag">
                                    {{ $gradeName }} · Sec. {{ $sectionName }}
                                </span>

                                @if ($levelName)
                                    <span class="group-level">{{ $levelName }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Continuación: pequeña etiqueta de grupo --}}
                    <div style="padding:6px 24px 2px;">
                        <div
                            style="font-family:'MonaSansBold',sans-serif;font-size:7.5px;color:#64748b;border-bottom:1px solid #e2e8f0;padding-bottom:3px;">
                            <span style="color:rgb(0,176,202);">{{ $gradeName }} · Sección
                                {{ $sectionName }}</span>
                        </div>
                    </div>
                @endif

                {{-- ── TABLA DEL CHUNK ── --}}
                <div class="table-section">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:18px;" class="center">#</th>
                                <th style="width:52px;">DNI</th>
                                <th>Apellidos y Nombres</th>
                                {{-- Columnas por sesión --}}
                                @foreach ($sessions as $session)
                                    <th class="day-head center">
                                        {{ \Carbon\Carbon::parse($session->date)->format('d/m') }}<br>
                                        <span style="font-family:'MonaSans',sans-serif;font-weight:400;opacity:0.85;">
                                            {{ \Carbon\Carbon::parse($session->date)->isoFormat('ddd') }}
                                        </span>
                                    </th>
                                @endforeach
                                <th class="center" style="width:18px;color:rgb(5,150,105);">P</th>
                                <th class="center" style="width:18px;color:rgb(245,158,11);">T</th>
                                <th class="center" style="width:18px;color:rgb(161,136,0);">J</th>
                                <th class="center" style="width:18px;color:rgb(220,50,50);">A</th>
                                <th class="center" style="width:42px;">% Asist.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $globalOffset = $chunkIndex * $MAX_PER_PAGE; @endphp
                            @foreach ($chunk as $i => $student)
                                @php
                                    $person = $student['person'];
                                    $pct = $student['percentage'];
                                    $pctColor =
                                        $pct >= 80
                                            ? 'rgb(5,150,105)'
                                            : ($pct >= 60
                                                ? 'rgb(180,115,0)'
                                                : 'rgb(220,50,50)');

                                    // Apellidos, Nombres
                                    $apellidos = trim(
                                        ($person->lastname_father ?? '') . ' ' . ($person->lastname_mom ?? ''),
                                    );
                                    $fullName = $apellidos . ', ' . ($person->firstname ?? '');
                                @endphp
                                <tr>
                                    <td class="num-col">{{ $globalOffset + $i + 1 }}</td>
                                    <td class="dni-col">{{ $person->identify_number ?? '—' }}</td>
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
                                            <div class="pct-text" style="color:{{ $pctColor }};">
                                                {{ $pct }}%</div>
                                            <div class="pct-bar-bg">
                                                <div class="pct-bar-fill"
                                                    style="width:{{ $pct }}%;background:{{ $pctColor }};">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Resumen al pie del último chunk del grupo --}}
                @if ($chunkIndex === $totalChunks - 1)
                    <div class="group-footer">
                        <div class="group-footer-inner">
                            <div class="gf-cell">
                                <b>{{ $groupStudents->count() }}</b> estudiantes en total
                            </div>
                            <div class="gf-cell">
                                Presentes: <b style="color:rgb(5,150,105);">{{ $gPresent }}</b>
                            </div>
                            <div class="gf-cell">
                                Tardanzas: <b style="color:rgb(180,115,0);">{{ $gLate }}</b>
                            </div>
                            <div class="gf-cell">
                                Justificados: <b style="color:rgb(161,136,0);">{{ $gJustified }}</b>
                            </div>
                            <div class="gf-cell">
                                Ausentes: <b style="color:rgb(220,50,50);">{{ $gAbsent }}</b>
                            </div>
                            <div class="gf-cell">
                                % Asistencia: <b style="color:{{ $gPctColor }};">{{ $gPct }}%</b>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Salto de página entre chunks o entre grupos (no al final) --}}
                @php
                    $isLastGroup = $loop->parent->last;
                    $isLastChunk = $chunkIndex === $totalChunks - 1;
                @endphp
                @if (!($isLastGroup && $isLastChunk))
                    <div class="page-break"></div>
                @endif
            @endforeach {{-- /chunks --}}
        @endforeach {{-- /grouped --}}

    @endif

</body>

</html>
