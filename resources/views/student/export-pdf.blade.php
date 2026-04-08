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
            font-size: 10px;
            color: #1e293b;
            background: #fff;
        }

        @page {
            margin: 0;
        }

        .header-title,
        thead th,
        .meta-label,
        .badge,
        .footer-accent {
            font-family: 'MonaSansBold', sans-serif;
        }

        .stat-num,
        .section-title {
            font-family: 'MonaSansExtraBold', sans-serif;
        }

        /* HEADER */
        .page-header {
            background: #fff;
            padding: 18px 36px 0;
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
            max-width: 58px;
            max-height: 58px;
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
            padding: 0 18px;
            text-align: center;
        }

        .header-institution {
            font-family: 'MonaSansBold', sans-serif;
            font-size: 16px;
            color: #0f172a;
            line-height: 1;
        }

        .header-title {
            font-size: 12px;
            color: rgb(0, 176, 202);
            margin-top: 2px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .header-subtitle {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .header-logo-sys {
            display: table-cell;
            width: 64px;
            vertical-align: middle;
            text-align: right;
        }

        .header-logo-sys img {
            max-width: 84px;
            max-height: 40px;
        }

        .header-stripe {
            height: 4px;
            margin-top: 14px;
            background: rgb(0, 176, 202);
        }

        /* META */
        .meta-section {
            padding: 12px 36px;
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
            padding-right: 20px;
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

        /* STATS */
        .stats-section {
            padding: 12px 36px 0;
        }

        .stat-cell {
            display: table-cell;
            padding: 8px 14px;
            text-align: center;
            border-radius: 6px;
            border: 1px solid #e8edf2;
            background: #f8fafc;
        }

        .stat-num {
            font-size: 18px;
            line-height: 1;
        }

        .stat-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            margin-top: 2px;
        }

        /* TABLA */
        .section-title {
            padding: 14px 36px 8px;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #94a3b8;
        }

        .table-section {
            padding: 0 36px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: rgb(0, 176, 202);
        }

        thead th {
            padding: 7px 8px;
            text-align: left;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #fff;
        }

        thead th.center {
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f0fbfd;
        }

        tbody tr {
            border-bottom: 1px solid #e8edf2;
        }

        tbody td {
            padding: 6px 8px;
            font-size: 9px;
            color: #334155;
            vertical-align: middle;
        }

        tbody td.center {
            text-align: center;
        }

        .num-col {
            color: #94a3b8;
            font-size: 8px;
        }

        .name-col {
            color: #0f172a;
        }

        /* FOOTER */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 7px 36px;
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

        .empty-state {
            text-align: center;
            padding: 24px 0;
            color: #94a3b8;
            font-size: 10px;
        }
    </style>
</head>

<body>

    {{-- FOOTER FIJO --}}
    <div class="page-footer">
        <div class="footer-inner">
            <div class="footer-left">
                <span class="footer-accent">I.E. Santa Rosa</span> · Assistance Control System

                · Assistance School
            </div>
            <div class="footer-right">
                Generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}
            </div>
        </div>
    </div>

    {{-- HEADER --}}
    <div class="page-header">
        <div class="header-inner">
            <div class="header-logo-school">
                @if ($logoSchoolBase64)
                    <img src="{{ $logoSchoolBase64 }}" alt="I.E. Santa Rosa">
                @endif
            </div>
            <div class="header-divider">&nbsp;</div>
            <div class="header-info">
                <div class="header-institution">I.E. FRANCISCO IZQUIERDO RÍOS</div>
                <div class="header-title">Reporte de Estudiantes</div>
                <div class="header-subtitle">Documento generado automáticamente · {{ now()->format('d \d\e F \d\e Y') }}
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

    {{-- META --}}
    <div class="meta-section">
        <div class="meta-table">
            <div class="meta-cell">
                <div class="meta-label">Sección</div>
                <div class="meta-value">{{ $gradeScheduleInfo ?? 'Todas las secciones' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Periodo</div>
                <div class="meta-value">{{ $periodName ?? 'Todos los periodos' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Búsqueda</div>
                <div class="meta-value">{{ $keyword ?? '—' }}</div>
            </div>
            <div class="meta-cell-right">
                <div
                    style="display:inline-block;background:rgba(0,176,202,0.08);border:1px solid rgba(0,176,202,0.25);border-radius:6px;padding:6px 14px;text-align:center;">
                    <div style="font-size:20px;color:rgb(0,176,202);line-height:1;">{{ $data->count() }}</div>
                    <div
                        style="font-size:7px;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;margin-top:2px;">
                        estudiantes</div>
                </div>
            </div>
        </div>
    </div>

    {{-- STATS --}}
    <div class="stats-section">
        <table style="border-collapse:separate; border-spacing:6px 0;">
            <tr>
                <td class="stat-cell">
                    <div class="stat-num" style="color:rgb(0,176,202);">{{ $data->count() }}</div>
                    <div class="stat-label">Total</div>
                </td>
                @php
                    $byGrade = $data->groupBy(
                        fn($s) => $s->currentEnrollment?->grade_schedule?->grade?->name_large ?? 'Sin grado',
                    );
                @endphp
                @foreach ($byGrade as $gradeName => $students)
                    <td class="stat-cell">
                        <div class="stat-num" style="color:#0891b2;">{{ $students->count() }}</div>
                        <div class="stat-label">{{ $gradeName }}</div>
                    </td>
                @endforeach
                <td style="width:100%;"></td>
            </tr>
        </table>
    </div>

    {{-- TABLA --}}
    <div class="section-title">Listado de estudiantes</div>
    <div class="table-section">
        @if ($data->isEmpty())
            <div class="empty-state">No hay estudiantes para los filtros seleccionados</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:24px;" class="center">#</th>
                        <th>DNI</th>
                        <th>Nombre completo</th>
                        <th>Teléfono</th>
                        <th>Grado</th>
                        <th class="center">Secc.</th>
                        <th class="center">Turno</th>
                        <th class="center">Registro</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $i => $student)
                        @php
                            $gs = $student->currentEnrollment?->grade_schedule;

                            $fullName = trim(
                                ($student->person->firstname ?? '') .
                                    ' ' .
                                    ($student->person->lastname_father ?? '') .
                                    ' ' .
                                    ($student->person->lastname_mom ?? ''),
                            );
                        @endphp
                        <tr>
                            <td class="num-col center">{{ $i + 1 }}</td>
                            <td>{{ $student->person->identify_number ?? '-' }}</td>
                            <td class="name-col">{{ $fullName ?: '-' }}</td>
                            <td class="center">{{ $student->person->phone ?? '-' }}</td>
                            <td>{{ $gs?->grade?->name_large ?? '-' }}</td>
                            <td class="center">{{ $gs?->section ?? '-' }}</td>
                            <td class="center">{{ $gs?->schedule?->turn ?? '-' }}</td>
                            <td class="center">
                                {{ $student->created_at ? \Carbon\Carbon::parse($student->created_at)->format('d/m/Y') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</body>

</html>
