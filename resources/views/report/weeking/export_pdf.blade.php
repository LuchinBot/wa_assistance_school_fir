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
            font-size: 7px;
            color: #1e293b;
            background: #fff;
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }

        /* ── HEADER ── */
        .page-header {
            padding: 8px 16px 0;
            border-bottom: 2px solid rgb(0, 176, 202);
        }

        .header-inner {
            display: table;
            width: 100%;
        }

        .header-logo-school {
            display: table-cell;
            width: 38px;
            vertical-align: middle;
        }

        .header-logo-school img {
            max-width: 34px;
            max-height: 34px;
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
            gap: 0px;
            padding: 0 10px;
            text-align: center;
        }

        .header-institution {
            font-family: 'MonaSansExtraBold', sans-serif;
            font-size: 14px;
            color: #0f172a;
            line-height: 1;
            text-transform: uppercase;
            margin: 0;
        }

        .header-title {
            font-family: 'MonaSansBold', sans-serif;
            font-size: 11px;
            color: #0f172a;
            line-height: 1.1;
            text-transform: uppercase;
            margin: 0;
        }

        .header-subtitle {
            font-family: 'MonaSansBold', sans-serif;
            font-size: 7px;
            color: rgb(0, 176, 202);
            margin-top: 1px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .header-logo-sys {
            display: table-cell;
            width: 52px;
            vertical-align: middle;
            text-align: right;
        }

        .header-logo-sys img {
            max-width: 48px;
            max-height: 24px;
        }

        .header-stripe {
            height: 2px;
            margin-top: 6px;
            background: rgb(0, 176, 202);
        }

        /* ── SEPARADOR DE GRUPO ── */
        .group-header {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding: 4px 16px 2px;
        }

        .group-header-inner {
            width: 100%;
        }

        .group-center {
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            gap: 5px;
        }

        .group-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgb(221, 217, 217);
            color: #000000;
            border-left: 2px solid black;
            padding: 1px 6px;
            font-size: 8px;
            font-family: 'MonaSansBold', sans-serif;
        }

        .group-level {
            font-family: 'MonaSansBold', sans-serif;
            font-size: 8px;
            color: #64748b;
        }

        /* ── TABLA ── */
        .table-section {
            padding: 2px 16px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        thead tr {
            background: rgb(0, 176, 202);
        }

        thead th {
            font-family: 'MonaSansBold', sans-serif;
            padding: 3px 2px;
            text-align: left;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #fff;
            overflow: hidden;
        }

        thead th.center {
            text-align: center;
        }

        /* Columnas fijas siempre alineadas a la izquierda */
        thead th.col-num,
        thead th.col-dni,
        thead th.col-name {
            text-align: left !important;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr {
            border-bottom: 1px solid #edf0f4;
        }

        tbody td {
            padding: 2px 2px;
            font-size: 7px;
            color: #334155;
            vertical-align: middle;
            overflow: hidden;
            text-align: left;
        }

        tbody td.center {
            text-align: center;
        }

        /* Las 3 columnas fijas: width:1px + white-space:nowrap = encogerse al contenido */
        .num-col,
        thead th.col-num {
            width: 1px;
            white-space: nowrap;
            color: #94a3b8;
            font-size: 7px;
            text-align: center !important;
            padding-left: 4px !important;
            padding-right: 4px !important;
        }

        .dni-col,
        thead th.col-dni {
            width: 1px;
            white-space: nowrap;
            font-size: 7px;
            font-family: 'MonaSansBold', sans-serif;
            color: #000000;
            text-align: left !important;
            padding-left: 4px !important;
            padding-right: 6px !important;
        }

        .name-col,
        thead th.col-name {
            white-space: nowrap;
            font-family: 'MonaSansBold', sans-serif;
            color: #000000;
            font-size: 7px;
            text-transform: uppercase;
            text-align: left !important;
            padding-left: 4px !important;
            padding-right: 8px !important;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 155px;
        }

        /* ── BADGES DE DÍA ── */
        .badge-day {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 13px;
            height: 13px;
            border-radius: 2px;
            font-family: 'MonaSansBold', sans-serif;
            font-size: 7px;
            line-height: 1;
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

        /* ── BARRA % ── */
        .pct-wrap {
            text-align: center;
        }

        .pct-text {
            font-family: 'MonaSansExtraBold', sans-serif;
            font-size: 7px;
            line-height: 1;
        }

        .pct-bar-bg {
            width: 22px;
            height: 2px;
            background: #e8edf2;
            border-radius: 2px;
            overflow: hidden;
            margin: 1px auto 0;
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
            padding: 3px 16px;
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
            font-size: 6px;
            color: #94a3b8;
        }

        .footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-size: 6px;
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
            font-size: 6.5px;
            white-space: nowrap;
            text-align: center;
            padding: 2px 1px !important;
        }

        /* Resumen de grupo al pie de tabla */
        .group-footer {
            padding: 2px 16px 4px;
        }

        .group-footer-inner {
            display: table;
            width: 100%;
            background: rgba(0, 176, 202, 0.04);
            border: 1px solid rgba(0, 176, 202, 0.15);
            border-radius: 4px;
            padding: 3px 8px;
        }

        .gf-cell {
            display: table-cell;
            vertical-align: middle;
            font-size: 7px;
            color: #475569;
            padding-right: 10px;
            font-family: 'MonaSansBold', sans-serif;
        }

        .gf-cell b {
            color: #0f172a;
        }
    </style>
</head>

<body>

    {{-- ── HEADER ── --}}
    <div style="padding:8px 16px;border-bottom:2px solid rgb(0,176,202);display:table;width:100%;box-sizing:border-box;">

        {{-- Logo IE --}}
        <div style="display:table-cell;width:28px;vertical-align:middle;">
            @if ($logoSchoolBase64)
                <img src="{{ $logoSchoolBase64 }}" alt="I.E." style="max-width:24px;max-height:24px;">
            @endif
        </div>

        {{-- Separador --}}
        <div style="display:table-cell;width:1px;background:#e2e8f0;vertical-align:middle;">&nbsp;</div>

        {{-- Texto central --}}
        <div style="display:table-cell;vertical-align:middle;padding:0 12px;text-align:center;">
            <span
                style="font-family:'MonaSansExtraBold',sans-serif;font-size:11px;color:#0f172a;text-transform:uppercase;letter-spacing:0.02em;">{{ env('APP_IE') }}</span>
            <span style="font-family:'MonaSansBold',sans-serif;font-size:9px;color:#475569;margin:0 6px;">·</span>
            <span
                style="font-family:'MonaSansBold',sans-serif;font-size:9px;color:#0f172a;text-transform:uppercase;">Reporte
                de Asistencia por Fechas</span>
            <span style="font-family:'MonaSansBold',sans-serif;font-size:9px;color:#475569;margin:0 6px;">·</span>
            <span
                style="font-family:'MonaSans',sans-serif;font-size:7.5px;color:rgb(0,176,202);letter-spacing:0.03em;">{{ now()->isoFormat('D [de] MMMM [de] YYYY') }}</span>
        </div>

        {{-- Separador --}}
        <div style="display:table-cell;width:1px;background:#e2e8f0;vertical-align:middle;">&nbsp;</div>

        {{-- Logo sistema --}}
        <div style="display:table-cell;width:48px;vertical-align:middle;text-align:right;">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" alt="SCA" style="max-width:44px;max-height:20px;">
            @endif
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
         * Agrupar por grado + sección
         */
        $grouped = $students->groupBy(function ($s) {
            $gs = $s['grade_schedule'];
            return ($gs?->grade?->name_large ?? 'ZZZ') . '||' . ($gs?->section ?? 'Z');
        });

        $MAX_PER_PAGE = 40;
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
                                    {{ $gradeName }} · ({{ $sectionName }})
                                </span>
                                @if ($levelName)
                                    <span class="group-level">{{ $levelName }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div style="padding:4px 16px 2px;">
                        <div
                            style="font-family:'MonaSansBold',sans-serif;font-size:7px;color:#64748b;border-bottom:1px solid #e2e8f0;padding-bottom:2px;">
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
                                <th class="col-num center" style="width:14px;">#</th>
                                <th class="col-dni" style="width:52px;">DNI</th>
                                <th class="col-name">Apellidos y Nombres</th>
                                @foreach ($sessions as $session)
                                    <th class="day-head center" style="width:14px;">
                                        {{ \Carbon\Carbon::parse($session->date)->format('d') }}
                                        {{ \Carbon\Carbon::parse($session->date)->isoFormat('MMM') }}<br>
                                        <span
                                            style="font-family:'MonaSans',sans-serif;font-weight:400;font-size:5.5px;opacity:0.85;text-transform:none;letter-spacing:0; text-transform: uppercase;">
                                            {{ \Carbon\Carbon::parse($session->date)->isoFormat('ddd') }}
                                        </span>
                                    </th>
                                @endforeach
                                <th class="center" style="width:30px;">%</th>
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

                                    $apellidos = trim(
                                        ($person->lastname_father ?? '') . ' ' . ($person->lastname_mom ?? ''),
                                    );
                                    $fullName = $apellidos . ', ' . ($person->firstname ?? '');
                                @endphp
                                <tr>
                                    <td class="num-col">{{ $globalOffset + $i + 1 }}</td>
                                    <td class="dni-col">{{ $person->identify_number ?? '—' }}</td>
                                    <td class="name-col" title="{{ $fullName }}">{{ $fullName }}</td>

                                    @foreach ($sessions as $j => $session)
                                        @php
                                            $statusKey = $student['daily'][$j] ?? 'absent';
                                            $badge = $statusLetterMap[$statusKey] ?? $statusLetterMap['absent'];
                                        @endphp
                                        <td class="center" style="padding:1px 0;">
                                            <span class="badge-day {{ $badge['cls'] }}">{{ $badge['letter'] }}</span>
                                        </td>
                                    @endforeach

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
                            <div class="gf-cell"><b>{{ $groupStudents->count() }}</b> estudiantes</div>
                            <div class="gf-cell">Presentes: <b style="color:rgb(5,150,105);">{{ $gPresent }}</b>
                            </div>
                            <div class="gf-cell">Tardanzas: <b style="color:rgb(180,115,0);">{{ $gLate }}</b>
                            </div>
                            <div class="gf-cell">Justificados: <b style="color:rgb(161,136,0);">{{ $gJustified }}</b>
                            </div>
                            <div class="gf-cell">Ausentes: <b style="color:rgb(220,50,50);">{{ $gAbsent }}</b>
                            </div>
                            <div class="gf-cell">% Asistencia: <b
                                    style="color:{{ $gPctColor }};">{{ $gPct }}%</b></div>
                        </div>
                    </div>
                @endif

                {{-- Salto de página entre chunks o entre grupos --}}
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
