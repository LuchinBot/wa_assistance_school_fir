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
        .stat-label,
        .badge,
        .footer-accent {
            font-family: 'MonaSansBold', sans-serif;
        }

        .stat-num,
        .total-num,
        .badge-auna,
        .section-title {
            font-family: 'MonaSansExtraBold', sans-serif;
        }

        /* ── HEADER ─────────────────────────────────────────── */
        .page-header {
            background: #fff;
            padding: 18px 36px 0;
            border-bottom: 3px solid rgb(0, 176, 202);
        }

        .header-inner {
            display: table;
            width: 100%;
        }

        /* Logo colegio — izquierda */
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

        /* Bloque central — institución */
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
            letter-spacing: -0.01em;
            line-height: 1;
        }

        .header-title {
            font-family: 'MonaSansBold', sans-serif;
            font-family: 'MonaSans', sans-serif;
            font-size: 12px;
            color: rgb(0, 176, 202);
            margin-top: 0px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .header-subtitle {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* Logo sistema — derecha */
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

        /* ── META ───────────────────────────────────────────── */
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

        /* ── STATS ──────────────────────────────────────────── */
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

        /* ── TABLA ──────────────────────────────────────────── */
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
            background: #f8fafc;
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

        /* Ausentes */
        .absent-header {
            background: rgb(239, 68, 68) !important;
        }

        .absent-row:nth-child(even) {
            background: #fff5f5 !important;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 7.5px;
        }

        .badge-present {
            background: rgba(190, 214, 0, 0.12);
            color: rgb(85, 120, 0);
            border: 1px solid rgba(190, 214, 0, 0.3);
        }


        .badge-justified {
            background: rgba(234, 179, 8, 0.15);
            color: rgb(146, 94, 0);
            border: 1px solid rgba(234, 179, 8, 0.35);
        }


        .badge-late {
            background: rgba(245, 158, 11, 0.12);
            color: rgb(180, 100, 0);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-absent {
            background: rgba(239, 68, 68, 0.12);
            color: rgb(185, 30, 30);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* ── FOOTER ─────────────────────────────────────────── */
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

        .divider {
            margin: 16px 36px;
            border: none;
            border-top: 2px dashed #e8edf2;
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
                <span class="footer-accent">I.E. Francisco Izquierdo Ríos</span> · Assistance Control System

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

            {{-- Logo colegio izquierda --}}
            <div class="header-logo-school">
                @if ($logoSchoolBase64)
                    <img src="{{ $logoSchoolBase64 }}" alt="I.E. Francisco Izquierdo Ríos">
                @else
                    <div
                        style="width:52px;height:52px;border-radius:6px;background:rgba(0,176,202,0.1);display:flex;align-items:center;justify-content:center;">
                        <span
                            style="font-size:8px;color:rgb(0,176,202);text-align:center;line-height:1.2;">I.E.<br>Francisco<br>Izquierdo<br>Ríos</span>
                    </div>
                @endif
            </div>

            <div class="header-divider">&nbsp;</div>

            {{-- Centro: nombre institución + título reporte --}}
            <div class="header-info">
                <div class="header-institution">I.E. FRANCISCO IZQUIERDO RÍOS</div>
                <div class="header-title">Reporte de Asistencias</div>
                <div class="header-subtitle">Documento generado automáticamente · {{ now()->format('d \d\e F \d\e Y') }}
                </div>
            </div>

            <div class="header-divider">&nbsp;</div>

            {{-- Logo Assistance School derecha --}}
            <div class="header-logo-sys">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Assistance School">
                @else
                    <span style="font-size:10px;color:rgb(0,176,202);">Assistance</span>
                @endif
            </div>

        </div>
        <div class="header-stripe"></div>
    </div>

    {{-- META INFO --}}
    <div class="meta-section">
        <div class="meta-table">
            <div class="meta-cell">
                <div class="meta-label">Sesión</div>
                <div class="meta-value">
                    @if ($sessionInfo)
                        {{ $sessionInfo->schedule->turn ?? 'Sin turno' }} ·
                        {{ \Carbon\Carbon::parse($sessionInfo->date)->format('d/m/Y') }}
                        @if (!$sessionInfo->time_ending)
                            <span style="color:rgb(34,197,94);font-size:7px;">● ACTIVA</span>
                        @endif
                    @else
                        Todas las sesiones
                    @endif
                </div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Grado</div>
                <div class="meta-value">{{ $gradeInfo?->name_large ?? 'Todos los grados' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Sección</div>
                <div class="meta-value">{{ $sectionInfo ?? '—' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Periodo</div>
                <div class="meta-value">{{ $periodName ?? 'Todos los periodos' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Horario</div>
                <div class="meta-value">
                    @if ($sessionInfo?->schedule)
                        {{ $sessionInfo->schedule->time_start }} — {{ $sessionInfo->schedule->time_end }}
                    @else
                        —
                    @endif
                </div>
            </div>
            <div class="meta-cell-right">
                <div
                    style="display:inline-block;background:rgba(0,176,202,0.08);border:1px solid rgba(0,176,202,0.25);border-radius:6px;padding:6px 14px;text-align:center;">
                    <div style="font-size:20px;color:rgb(0,176,202);line-height:1;">{{ count($data) }}</div>
                    <div
                        style="font-size:7px;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;margin-top:2px;">
                        registros</div>
                </div>
            </div>
        </div>
    </div>

    {{-- STATS --}}
    @php
        $countPresent = $data->where('status', 'present')->count();
        $countJustified = $data->where('status', 'justified')->count();
        $countLate = $data->where('status', 'late')->count();
        $countAbsent = isset($absents) ? $absents->count() : 0;
        $countTotal = $countPresent + $countLate + $countAbsent;
    @endphp

    <div class="stats-section">
        <table style="border-collapse:separate; border-spacing:6px 0;">
            <tr>
                <td class="stat-cell">
                    <div class="stat-num" style="color:rgb(0,176,202);">{{ $countTotal }}</div>
                    <div class="stat-label">Total</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-num" style="color:rgb(120,160,0);">{{ $countPresent }}</div>
                    <div class="stat-label">Presentes</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-num" style="color:rgb(146, 94, 0);">{{ $countJustified }}</div>
                    <div class="stat-label">Justificados</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-num" style="color:rgb(217,119,6);">{{ $countLate }}</div>
                    <div class="stat-label">Tardanzas</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-num" style="color:rgb(220,50,50);">{{ $countAbsent }}</div>
                    <div class="stat-label">Ausentes</div>
                </td>

                @if ($countTotal > 0)
                    <td class="stat-cell">
                        <div class="stat-num" style="color:rgb(120,160,0);">
                            {{ round((($countPresent + $countLate) / $countTotal) * 100) }}%
                        </div>
                        <div class="stat-label">Asistencia</div>
                    </td>
                @endif
                <td style="width:100%;"></td>
            </tr>
        </table>
    </div>

    {{-- TABLA PRESENTES / TARDANZAS --}}
    <div class="section-title">Registro de asistencia</div>
    <div class="table-section">
        @if ($data->isEmpty())
            <div class="empty-state">No hay registros para los filtros seleccionados</div>
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
                        <th>Hora ingreso</th>
                        <th class="center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $i => $row)
                        @php
                            $statusMap = [
                                'present' => ['label' => 'Presente', 'cls' => 'badge-present'],
                                'late' => ['label' => 'Tardanza', 'cls' => 'badge-late'],
                                'justified' => ['label' => 'Justificado', 'cls' => 'badge-justified'],
                            ];
                            $status = $statusMap[$row->status] ?? ['label' => $row->status, 'cls' => ''];

                            // ← CAMBIO: de student a enrollment
                            $enrollment = $row->enrollment;
                            $gs = $enrollment?->grade_schedule;
                            $person = $enrollment?->student?->person;

                            $fullName = trim(
                                ($person->firstname ?? '') .
                                    ' ' .
                                    ($person->lastname_father ?? '') .
                                    ' ' .
                                    ($person->lastname_mom ?? ''),
                            );
                        @endphp
                        <tr>
                            <td class="num-col center">{{ $i + 1 }}</td>
                            <td>{{ $person?->identify_number ?? '-' }}</td>
                            <td class="name-col">{{ $fullName ?: '-' }}</td>
                            <td>{{ $person?->phone ?? '-' }}</td>
                            <td>{{ $gs?->grade?->name_large ?? '-' }}</td>
                            <td class="center">{{ $gs?->section ?? '-' }}</td>
                            <td>{{ $row->time_entry ? \Carbon\Carbon::parse($row->time_entry)->format('H:i:s') : '-' }}
                            </td>
                            <td class="center">
                                <span class="badge {{ $status['cls'] }}">{{ $status['label'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- TABLA AUSENTES --}}
    @if (isset($absents) && $absents->count() > 0)
        <hr class="divider">
        <div class="section-title" style="color:rgb(220,50,50);">Alumnos ausentes</div>
        <div class="table-section">
            <table>
                <thead>
                    <tr class="absent-header">
                        <th style="width:24px;" class="center">#</th>
                        <th>DNI</th>
                        <th>Nombre completo</th>
                        <th>Teléfono</th>
                        <th>Grado</th>
                        <th class="center">Secc.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($absents as $i => $enrollment)
                        @php
                            $gs = $enrollment->grade_schedule;
                            $person = $enrollment->student?->person;

                            $fullName = trim(
                                ($person->firstname ?? '') .
                                    ' ' .
                                    ($person->lastname_father ?? '') .
                                    ' ' .
                                    ($person->lastname_mom ?? ''),
                            );
                        @endphp
                        <tr class="absent-row">
                            <td class="num-col center">{{ $i + 1 }}</td>
                            <td>{{ $person?->identify_number ?? '-' }}</td>
                            <td class="name-col">{{ $fullName ?: '-' }}</td>
                            <td>{{ $person?->phone ?? '-' }}</td>
                            <td>{{ $gs?->grade?->name_large ?? '-' }}</td>
                            <td class="center">{{ $gs?->section ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</body>

</html>
