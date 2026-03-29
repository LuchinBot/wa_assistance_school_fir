@extends('layouts.app')

@section('title', 'Tomar Asistencia')

@section('content')

    {{-- TOAST CONTAINER --}}
    <div id="toast-container"
        style="position: fixed; top: 60px; left: 0; right: 0; z-index: 9999; pointer-events: none; padding: 0;">
    </div>

    <div class="att-root">

        {{-- ── TOPBAR ── --}}
        <div class="att-topbar">
            <div class="att-topbar-left">
                <div class="att-topbar-dot"></div>
                <span class="att-topbar-label">ASISTENCIA</span>
            </div>
            <div class="att-topbar-center">
                <span id="live-time" class="att-topbar-time">00:00</span>
            </div>
            <div class="att-topbar-right att-hidden" id="att-topbar-right">
                <span id="late-indicator" class="att-late-pill att-hidden">TARDANZA</span>
                <span id="early-indicator" class="att-early-pill att-hidden">TEMPRANO</span> {{-- NUEVO --}}
            </div>
        </div>

        {{-- ── SCANNER ── --}}
        <div class="att-scanner-section">
            <div class="att-scanner-box" id="scanner-box">

                {{-- QR Reader --}}
                <div id="reader"></div>

                {{-- Overlay de esquinas --}}
                <div class="scan-overlay" id="scan-overlay">
                    <div class="scan-frame">
                        <span class="corner tl"></span>
                        <span class="corner tr"></span>
                        <span class="corner bl"></span>
                        <span class="corner br"></span>
                        <div class="scan-laser"></div>
                    </div>
                </div>

                {{-- Badge estado cámara --}}
                <div class="cam-status" id="scanner-status">
                    <span class="cam-dot" id="cam-dot"></span>
                    <span id="cam-label">Iniciando...</span>
                </div>

                {{-- Sin cámara --}}
                <div class="no-cam att-hidden" id="no-camera-msg">
                    <span class="material-symbols-outlined" style="font-size:2.5rem; opacity:.4;">no_photography</span>
                    <p>Cámara no disponible</p>
                    <small>Use el ingreso manual</small>
                </div>
            </div>
        </div>

        {{-- ── PANEL INFERIOR ── --}}
        <div class="att-panel">

            {{-- Input manual --}}
            <div class="att-manual ">
                <div class="att-manual-inner">
                    <span class="material-symbols-outlined att-manual-icon">badge</span>
                    <input type="text" id="manual-dni" class="att-manual-input" placeholder="DNI del estudiante"
                        maxlength="20" autocomplete="off" inputmode="numeric" />
                    <button type="button" id="manual-btn" class="att-manual-btn">
                        <span class="material-symbols-outlined" style="font-size:1.1rem;">send</span>
                    </button>
                </div>
            </div>

            {{-- Tardanza + Observación --}}
            <div class="att-late-row">
                <div class="att-late-card mb-1" id="late-card">
                    <span class="material-symbols-outlined att-late-icon">schedule</span>
                    <div class="att-late-text">
                        <span class="att-late-title">Tardanza</span>
                        <span class="att-late-sub">Registrar fuera de horario</span>
                    </div>
                    <label class="att-toggle" onclick="event.stopPropagation()">
                        <input type="checkbox" id="late-toggle">
                        <span class="att-toggle-track">
                            <span class="att-toggle-thumb"></span>
                        </span>
                    </label>
                </div>


                {{-- NUEVO CARD --}}
                <div class="att-late-card" id="early-card">
                    <span class="material-symbols-outlined att-late-icon" style="color:#22c55e;">alarm_on</span>
                    <div class="att-late-text">
                        <span class="att-late-title">Llegó temprano</span>
                        <span class="att-late-sub">Tomado después, registra hora límite</span>
                    </div>
                    <label class="att-toggle" onclick="event.stopPropagation()">
                        <input type="checkbox" id="early-toggle">
                        <span class="att-toggle-track" style=""></span>
                    </label>
                </div>
                {{-- <div id="obs-wrap" class="att-obs-wrap">
                    <textarea id="obs-input" class="att-obs" placeholder="Motivo de la tardanza (opcional)..." rows="2"
                        maxlength="200"></textarea>
                </div> --}}
            </div>

            {{-- Últimos registros --}}
            <div class="att-recent opacity-0 md:opacity-100">
                <div class="att-recent-header">
                    <span class="att-recent-title">
                        <span class="material-symbols-outlined"
                            style="font-size:.9rem; vertical-align:middle;">history</span>
                        Últimos registros
                    </span>
                    <button type="button" id="clear-recent-btn" class="att-clear-btn">Limpiar</button>
                </div>
                <ul id="recent-list" class="att-recent-list">
                    <li class="att-recent-empty">Aún no hay registros en esta sesión</li>
                </ul>
            </div>

        </div>
    </div>

    {{-- ── MODAL RESULTADO (flotante centrado) ── --}}
    <div id="result-modal" class="res-modal att-hidden">
        <div class="res-modal-card" id="result-card">
            <div class="res-modal-icon" id="result-icon"></div>
            <div class="res-modal-text" id="result-text"></div>
            <div class="res-modal-sub" id="result-sub"></div>
        </div>
    </div>

    <style>
        .content {
            height: calc(100dvh - 60px);
            overflow: hidden;
            padding: 0 !important;
            margin: 0 !important;
        }

        .content>.relative,
        .main-panel>div.relative {
            height: 100%;
            overflow: hidden;
        }

        .att-root {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            background: #f4f6f8;
        }

        /* TOPBAR */
        .att-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .6rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e8edf2;
            flex-shrink: 0;
            z-index: 10;
        }

        .att-topbar-left {
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .att-topbar-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgb(0, 176, 202);
            animation: pulse-dot 1.6s ease-in-out infinite;
        }

        .att-topbar-label {
            font-size: .6rem;
            font-weight: 800;
            letter-spacing: .2em;
            color: rgb(0, 140, 165);
            text-transform: uppercase;
        }

        .att-topbar-time {
            font-size: .85rem;
            font-weight: 800;
            color: #475569;
            font-variant-numeric: tabular-nums;
            letter-spacing: .05em;
        }

        .att-late-pill {
            font-size: .55rem;
            font-weight: 900;
            letter-spacing: .18em;
            padding: .2rem .6rem;
            border-radius: 999px;
            background: rgba(245, 158, 11, .1);
            color: rgb(217, 119, 6);
            border: 1px solid rgba(245, 158, 11, .25);
            text-transform: uppercase;
        }

        /* SCANNER SECTION */
        .att-scanner-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: .75rem 1rem;
            min-height: 0;
        }

        .att-scanner-box {
            position: relative;
            width: 100%;
            max-width: min(80vw, 300px);
            aspect-ratio: 1/1;
            border-radius: 1.5rem;
            overflow: hidden;
            background: #1e293b;
            border: 2px solid rgba(0, 176, 202, .35);
            box-shadow: 0 0 0 1px rgba(0, 176, 202, .1), 0 20px 60px rgba(0, 176, 202, .12);
        }

        #reader {
            position: absolute;
            inset: 0;
            width: 100% !important;
            height: 100% !important;
        }

        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }

        #reader__dashboard,
        #reader__status_span,
        #reader__filescan_input {
            display: none !important;
        }

        #reader>div {
            display: none !important;
        }

        /* SCAN OVERLAY */
        .scan-overlay {
            position: absolute;
            inset: 0;
            z-index: 20;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .scan-frame {
            position: relative;
            width: 62%;
            height: 62%;
        }

        .corner {
            position: absolute;
            width: 20px;
            height: 20px;
            border-color: rgb(0, 176, 202);
            border-style: solid;
        }

        .corner.tl {
            top: 0;
            left: 0;
            border-width: 3px 0 0 3px;
            border-radius: 5px 0 0 0;
        }

        .corner.tr {
            top: 0;
            right: 0;
            border-width: 3px 3px 0 0;
            border-radius: 0 5px 0 0;
        }

        .corner.bl {
            bottom: 0;
            left: 0;
            border-width: 0 0 3px 3px;
            border-radius: 0 0 0 5px;
        }

        .corner.br {
            bottom: 0;
            right: 0;
            border-width: 0 3px 3px 0;
            border-radius: 0 0 5px 0;
        }

        .scan-laser {
            position: absolute;
            left: 0;
            right: 0;
            height: 2px;
            top: 0;
            background: linear-gradient(90deg, transparent, rgb(0, 176, 202), transparent);
            box-shadow: 0 0 8px rgba(0, 176, 202, .8);
            animation: laser 2s linear infinite;
        }

        /* CAM STATUS */
        .cam-status {
            position: absolute;
            top: .6rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 30;
            display: flex;
            align-items: center;
            gap: .35rem;
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(8px);
            padding: .25rem .7rem;
            border-radius: 999px;
            border: 1px solid #e8edf2;
            white-space: nowrap;
            font-size: .6rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #475569;
        }

        .cam-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgb(190, 214, 0);
            animation: pulse-dot 1.4s ease-in-out infinite;
        }

        .cam-dot.red {
            background: #ef4444;
            animation: none;
        }

        /* NO CAM */
        .no-cam {
            position: absolute;
            inset: 0;
            z-index: 25;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            text-align: center;
            color: rgba(255, 255, 255, .4);
            background: #1e293b;
        }

        .no-cam p {
            font-size: .8rem;
            font-weight: 600;
        }

        .no-cam small {
            font-size: .7rem;
            opacity: .6;
        }

        /* PANEL */
        .att-panel {
            flex-shrink: 0;
            background: #fff;
            border-top: 1px solid #e8edf2;
            border-radius: 1.25rem 1.25rem 0 0;
            padding: .85rem 1rem 1rem;
            display: flex;
            flex-direction: column;
            gap: .65rem;
            max-height: 46dvh;
            overflow-y: auto;
        }

        /* MANUAL */
        .att-manual-inner {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: .85rem;
            padding: .55rem .55rem .55rem .85rem;
            transition: border-color .2s, box-shadow .2s;
        }

        .att-manual-inner:focus-within {
            border-color: rgba(0, 176, 202, .5);
            box-shadow: 0 0 0 3px rgba(0, 176, 202, .08);
            background: #fff;
        }

        .att-manual-icon {
            font-size: 1.1rem;
            color: #94a3b8;
            flex-shrink: 0;
        }

        .att-manual-input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            font-size: .9rem;
            font-weight: 600;
            color: #1e293b;
            letter-spacing: .04em;
        }

        .att-manual-input::placeholder {
            color: #cbd5e1;
            font-weight: 400;
        }

        .att-manual-btn {
            width: 2.1rem;
            height: 2.1rem;
            border-radius: .6rem;
            border: none;
            background: rgb(0, 176, 202);
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s, transform .1s;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 176, 202, .3);
        }

        .att-manual-btn:hover {
            background: rgb(190, 214, 0);
            box-shadow: 0 2px 8px rgba(190, 214, 0, .3);
        }

        .att-manual-btn:active {
            transform: scale(.93);
        }

        /* TARDANZA */
        .att-late-card {
            display: flex;
            align-items: center;
            gap: .65rem;
            background: #f8fafc;
            border: 1px solid #e8edf2;
            border-radius: .85rem;
            padding: .6rem .85rem;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        }

        .att-late-card.active {
            border-color: rgba(245, 158, 11, .4);
            background: #fffbeb;
        }

        .att-late-icon {
            font-size: 1.15rem;
            color: #f59e0b;
            flex-shrink: 0;
        }

        .att-late-text {
            flex: 1;
        }

        .att-late-title {
            display: block;
            font-size: .8rem;
            font-weight: 700;
            color: #1e293b;
        }

        .att-late-sub {
            display: block;
            font-size: .65rem;
            color: #94a3b8;
            margin-top: .05rem;
        }

        /* TOGGLE */
        .att-toggle {
            cursor: pointer;
            flex-shrink: 0;
        }

        .att-toggle input {
            display: none;
        }

        .att-toggle-track {
            display: flex;
            align-items: center;
            width: 40px;
            height: 22px;
            background: #e2e8f0;
            border-radius: 999px;
            padding: 2px;
            transition: background .25s;
        }

        .att-toggle input:checked~.att-toggle-track {
            background: rgb(0, 176, 202);
        }

        .att-toggle-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .15);
            transition: transform .25s cubic-bezier(.34, 1.56, .64, 1);
            flex-shrink: 0;
        }

        .att-toggle input:checked~.att-toggle-track .att-toggle-thumb {
            transform: translateX(18px);
        }

        /* OBSERVACIÓN */
        .att-obs-wrap {
            padding-top: .25rem;
        }

        .att-obs {
            width: 100%;
            background: rgba(245, 158, 11, .05);
            border: 1px solid rgba(245, 158, 11, .3);
            border-radius: .75rem;
            padding: .55rem .75rem;
            font-size: .8rem;
            color: #1e293b;
            resize: none;
            outline: none;
            font-family: inherit;
            transition: border-color .2s;
        }

        .att-obs::placeholder {
            color: #94a3b8;
        }

        .att-obs:focus {
            border-color: rgba(245, 158, 11, .55);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .08);
        }

        /* RECIENTES */
        .att-recent-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .4rem;
        }

        .att-recent-title {
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .att-clear-btn {
            font-size: .62rem;
            font-weight: 700;
            color: #cbd5e1;
            background: none;
            border: none;
            cursor: pointer;
            text-decoration: underline;
            transition: color .2s;
        }

        .att-clear-btn:hover {
            color: #64748b;
        }

        .att-recent-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: .3rem;
            max-height: 120px;
            overflow-y: auto;
        }

        .att-recent-empty {
            font-size: .75rem;
            color: #cbd5e1;
            text-align: center;
            padding: .6rem 0;
        }

        .att-recent-item {
            display: flex;
            align-items: center;
            gap: .6rem;
            background: #f8fafc;
            border-radius: .6rem;
            padding: .45rem .7rem;
            border: 1px solid #e8edf2;
            animation: slide-up .25s ease;
        }

        .att-recent-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .att-recent-dot.success {
            background: rgb(190, 214, 0);
        }

        .att-recent-dot.warning {
            background: #f59e0b;
        }

        .att-recent-dot.error {
            background: #ef4444;
        }

        .att-recent-dot.loading {
            background: rgb(0, 176, 202);
        }

        .att-recent-dni {
            font-size: .78rem;
            font-weight: 700;
            color: #1e293b;
            flex: 1;
        }

        .att-recent-msg {
            font-size: .68rem;
            color: #64748b;
        }

        .att-recent-time {
            font-size: .62rem;
            color: #cbd5e1;
            white-space: nowrap;
        }

        /* TOAST */
        .att-toast {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: .5rem .75rem 0;
            padding: .75rem 1rem;
            border-radius: 1rem;
            pointer-events: auto;
            animation: toast-in .35s cubic-bezier(.34, 1.56, .64, 1);
            box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
            background: #fff;
        }

        .att-toast.success {
            border-left: 4px solid rgb(190, 214, 0);
        }

        .att-toast.warning {
            border-left: 4px solid #f59e0b;
        }

        .att-toast.error {
            border-left: 4px solid #ef4444;
        }

        .att-toast.loading {
            border-left: 4px solid rgb(0, 176, 202);
        }

        .att-toast-icon {
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .att-toast.success .att-toast-icon {
            color: rgb(190, 214, 0);
        }

        .att-toast.warning .att-toast-icon {
            color: #f59e0b;
        }

        .att-toast.error .att-toast-icon {
            color: #ef4444;
        }

        .att-toast.loading .att-toast-icon {
            color: rgb(0, 176, 202);
            animation: spin .8s linear infinite;
        }

        .att-toast-body {
            flex: 1;
            min-width: 0;
        }

        .att-toast-text {
            font-size: .82rem;
            font-weight: 700;
            color: #1e293b;
        }

        .att-toast-sub {
            font-size: .7rem;
            color: #64748b;
            margin-top: .1rem;
        }

        /* MODAL */
        .res-modal {
            position: fixed;
            inset: 0;
            z-index: 9000;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, .4);
            backdrop-filter: blur(6px);
            pointer-events: none;
        }

        .res-modal.att-hidden {
            display: none;
        }

        .res-modal-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .5rem;
            text-align: center;
            padding: 1.75rem 2rem;
            border-radius: 1.5rem;
            min-width: 200px;
            max-width: 280px;
            background: #fff;
            animation: pop-in .4s cubic-bezier(.34, 1.56, .64, 1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, .12);
        }

        .res-modal-card.success {
            border-top: 4px solid rgb(190, 214, 0);
        }

        .res-modal-card.warning {
            border-top: 4px solid #f59e0b;
        }

        .res-modal-card.error {
            border-top: 4px solid #ef4444;
        }

        .res-modal-card.loading {
            border-top: 4px solid rgb(0, 176, 202);
        }

        .res-modal-icon {
            font-size: 2.4rem;
            line-height: 1;
        }

        .res-modal-text {
            font-size: 1rem;
            font-weight: 800;
            color: #1e293b;
        }

        .res-modal-sub {
            font-size: .75rem;
            color: #64748b;
        }

        /* ANIMACIONES */
        @keyframes laser {
            0% {
                top: 0;
                opacity: 0
            }

            10% {
                opacity: 1
            }

            90% {
                opacity: 1
            }

            100% {
                top: 100%;
                opacity: 0
            }
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .25
            }
        }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(6px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes toast-in {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(.96)
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1)
            }
        }

        @keyframes pop-in {
            from {
                opacity: 0;
                transform: scale(.85)
            }

            to {
                opacity: 1;
                transform: scale(1)
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        /* DESKTOP */
        @media (min-width: 768px) {
            .att-root {
                flex-direction: row;
                height: 100%;
                background: #f4f6f8;
            }

            .att-topbar {
                display: none;
            }

            .att-scanner-section {
                flex: 1;
                padding: 2rem;
                border-right: 1px solid #e8edf2;
                position: relative;
            }

            .att-scanner-section::before {
                content: 'Control de Asistencia';
                position: absolute;
                top: 1.5rem;
                left: 2rem;
                font-size: .6rem;
                font-weight: 900;
                letter-spacing: .2em;
                text-transform: uppercase;
                color: #94a3b8;
            }

            .att-scanner-section::after {
                content: '';
                position: absolute;
                top: 1.6rem;
                left: 1.25rem;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: rgb(0, 176, 202);
                animation: pulse-dot 1.6s ease-in-out infinite;
            }

            .att-scanner-box {
                max-width: min(42vh, 380px);
            }

            .att-panel {
                width: 340px;
                max-height: 100%;
                border-radius: 0;
                border-top: none;
                border-left: 1px solid #e8edf2;
                padding: 1.5rem 1.25rem;
                gap: .85rem;
                overflow-y: auto;
            }

            .att-panel::before {
                content: 'Registro manual';
                display: block;
                font-size: .6rem;
                font-weight: 900;
                letter-spacing: .2em;
                text-transform: uppercase;
                color: #94a3b8;
                margin-bottom: .25rem;
            }

            .att-recent-list {
                max-height: 240px;
            }
        }

        .att-hidden {
            display: none !important;
        }

        /* TOGGLE EARLY */
        #early-card .att-toggle input:checked~.att-toggle-track {
            background: #22c55e;
        }

        #early-card.active {
            border-color: rgba(34, 197, 94, .4);
            background: #f0fdf4;
        }

        #early-card .att-late-icon {
            color: #22c55e;
        }

        .att-early-pill {
            font-size: .55rem;
            font-weight: 900;
            letter-spacing: .18em;
            padding: .2rem .6rem;
            border-radius: 999px;
            background: rgba(34, 197, 94, .1);
            color: rgb(21, 128, 61);
            border: 1px solid rgba(34, 197, 94, .25);
            text-transform: uppercase;
        }
    </style>


    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endsection
