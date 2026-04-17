@extends('layouts.guest')

@section('title', (env('APP_NAME') ?? 'Assistance School') . ' — Consulta de Asistencias')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<style>

body {
    background: #f1f5f9;
    min-height: 100vh;
}

/* ─────────────────────────────────────────────────
   DESIGN TOKENS
───────────────────────────────────────────────── */
:root {
    --teal:       #0d9488;
    --teal-lt:    #14b8a6;
    --teal-xl:    #5eead4;
    --teal-bg:    #f0fdfa;
    --teal-bd:    #99f6e4;
    --green:      #16a34a;
    --green-lt:   #22c55e;
    --red:        #dc2626;
    --red-lt:     #ef4444;
    --amber:      #d97706;
    --amber-lt:   #f59e0b;
    --border:     #e2e8f0;
    --border-lt:  #f1f5f9;
    --surface:    #ffffff;
    --bg:         #f1f5f9;
    --text:       #0f172a;
    --text-2:     #334155;
    --muted:      #64748b;
    --muted-lt:   #94a3b8;
    --r-sm:  10px;
    --r-md:  14px;
    --r-lg:  18px;
    --r-xl:  22px;
    --sh:    0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --sh-md: 0 4px 16px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.03);
    --sh-teal: 0 4px 14px rgba(13,148,136,.25);
}

/* ─────────────────────────────────────────────────
   ANIMATIONS
───────────────────────────────────────────────── */
@keyframes spin {
    to { transform: rotate(360deg); }
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes slideIn {
    from { opacity: 0; transform: translateX(16px); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes pulseRing {
    0%   { box-shadow: 0 0 0 0   rgba(13,148,136,.5); }
    70%  { box-shadow: 0 0 0 8px rgba(13,148,136,0); }
    100% { box-shadow: 0 0 0 0   rgba(13,148,136,0); }
}
@keyframes pulseDot {
    0%,100% { box-shadow: 0 0 0 2px rgba(34,197,94,.35); }
    50%     { box-shadow: 0 0 0 5px rgba(34,197,94,.08); }
}
@keyframes shimmer {
    from { background-position: -400px 0; }
    to   { background-position:  400px 0; }
}

.u-fade-up  { animation: fadeUp  .42s cubic-bezier(.22,1,.36,1) both; }
.u-slide-in { animation: slideIn .38s cubic-bezier(.22,1,.36,1) both; }
.u-d1 { animation-delay: .04s; }
.u-d2 { animation-delay: .09s; }
.u-d3 { animation-delay: .14s; }
.u-d4 { animation-delay: .19s; }
.u-d5 { animation-delay: .24s; }

/* ─────────────────────────────────────────────────
   TOPBAR
───────────────────────────────────────────────── */
.g-topbar {
    position: sticky;
    top: 0;
    z-index: 100;
    height: 56px;
    background: rgba(255,255,255,.9);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--border);
    box-shadow: 0 1px 0 rgba(0,0,0,.04);
}
.g-topbar__inner {
    max-width: 980px;
    margin: 0 auto;
    height: 100%;
    padding: 0 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.g-topbar__logo { height: 28px; display: block; flex-shrink: 0; }
.g-topbar__right { display: flex; align-items: center; gap: 8px; }

/* live badge */
.live-badge {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 600; color: var(--muted);
}
.live-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--green-lt);
    animation: pulseDot 2s infinite;
    flex-shrink: 0;
}
@media (max-width: 400px) { .live-badge { display: none; } }

/* role badge */
.g-role-badge {
    display: none;
    align-items: center; gap: 4px;
    padding: 3px 9px;
    border-radius: 99px;
    background: var(--teal-bg);
    border: 1px solid var(--teal-bd);
    font-size: 10px; font-weight: 800;
    color: var(--teal);
    text-transform: uppercase;
    letter-spacing: .07em;
    white-space: nowrap;
}
.g-role-badge .material-symbols-outlined { font-size: 12px; }
.g-role-badge.is-visible { display: flex; }

/* new search btn */
.g-btn-reset {
    display: none;
    align-items: center; gap: 5px;
    height: 34px; padding: 0 12px;
    border-radius: var(--r-sm);
    border: 1.5px solid var(--border);
    background: var(--surface);
    font-size: 12px; font-weight: 700; font-family: inherit;
    color: var(--muted);
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.g-btn-reset:hover { border-color: #cbd5e1; color: var(--text-2); background: #f8fafc; }
.g-btn-reset .material-symbols-outlined { font-size: 15px; }
.g-btn-reset.is-visible { display: flex; }
/* En muy pequeño: solo icono */
@media (max-width: 479px) { .g-btn-reset__label { display: none; } }

/* ─────────────────────────────────────────────────
   PAGE WRAPPER
───────────────────────────────────────────────── */
.g-main {
    max-width: 980px;
    margin: 0 auto;
    padding: 20px 16px 88px;
}
@media (max-width: 479px) { .g-main { padding: 14px 12px 80px; } }

/* ─────────────────────────────────────────────────
   WIZARD PROGRESS BAR
───────────────────────────────────────────────── */
.wz-bar {
    display: flex;
    align-items: center;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-md);
    padding: 10px 14px;
    margin-bottom: 20px;
    box-shadow: var(--sh);
    gap: 0;
}
.wz-step {
    display: flex; align-items: center; gap: 7px;
    flex: 1; min-width: 0;
}
.wz-dot {
    width: 26px; height: 26px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800;
    border: 2px solid var(--border);
    background: var(--bg);
    color: var(--muted-lt);
    flex-shrink: 0;
    transition: background .25s, border-color .25s, color .25s;
}
.wz-dot .material-symbols-outlined { font-size: 12px; }
.wz-dot.is-active {
    background: var(--teal); border-color: var(--teal); color: #fff;
    animation: pulseRing 1.8s infinite;
}
.wz-dot.is-done { background: #dcfce7; border-color: #86efac; color: var(--green); }
.wz-label {
    font-size: 11.5px; font-weight: 600; color: var(--muted-lt);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    transition: color .25s;
}
.wz-label.is-active { color: var(--teal); font-weight: 700; }
.wz-label.is-done   { color: var(--green); }
.wz-sep {
    flex: 1; height: 1.5px; min-width: 12px;
    background: var(--border); margin: 0 8px;
    transition: background .35s;
}
.wz-sep.is-done { background: #86efac; }
@media (max-width: 380px) {
    .wz-label { display: none; }
    .wz-sep   { margin: 0 4px; }
}

/* ─────────────────────────────────────────────────
   STEP PANELS
───────────────────────────────────────────────── */
.g-step { display: none; }
.g-step.is-active { display: block; }

/* ─────────────────────────────────────────────────
   STEP 1 — ROL
───────────────────────────────────────────────── */
.s1-wrap { max-width: 460px; margin: 0 auto; text-align: center; }

.s1-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 99px;
    background: var(--teal-bg); border: 1px solid var(--teal-bd);
    font-size: 11px; font-weight: 700; color: var(--teal);
    margin-bottom: 14px;
}
.s1-chip .material-symbols-outlined { font-size: 13px; }

.s1-title {
    font-size: clamp(1.5rem, 6vw, 2.1rem);
    font-weight: 800; color: var(--text);
    letter-spacing: -.025em; line-height: 1.15;
    margin-bottom: 8px;
}
.s1-title span { color: var(--teal-lt); }

.s1-sub {
    font-size: 13.5px; font-weight: 500; color: var(--muted);
    margin-bottom: 28px; line-height: 1.5;
}

/* Role grid */
.role-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 12px; margin-bottom: 20px;
}
@media (max-width: 340px) { .role-grid { grid-template-columns: 1fr; } }

.role-card {
    position: relative; overflow: hidden;
    cursor: pointer;
    display: flex; flex-direction: column; align-items: center;
    gap: 10px; padding: 22px 16px;
    border-radius: var(--r-lg);
    border: 2px solid var(--border);
    background: var(--surface);
    box-shadow: var(--sh);
    transition: border-color .18s, transform .18s, box-shadow .18s;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    text-align: center;
}
.role-card::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(145deg, var(--teal-bg), transparent 60%);
    opacity: 0;
    transition: opacity .2s;
    pointer-events: none;
}
.role-card:hover  { border-color: var(--teal-xl); transform: translateY(-2px); box-shadow: var(--sh-md); }
.role-card:active { transform: scale(.97); }
.role-card:hover::after { opacity: 1; }
.role-card.is-selected {
    border-color: var(--teal-lt);
    box-shadow: 0 0 0 3px rgba(13,148,136,.14), var(--sh-md);
}
.role-card.is-selected::after { opacity: 1; }

/* check icon */
.role-card__check {
    position: absolute; top: 9px; right: 9px;
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--teal-lt);
    display: none; align-items: center; justify-content: center;
    z-index: 1;
}
.role-card__check .material-symbols-outlined { font-size: 12px; color: #fff; }
.role-card.is-selected .role-card__check { display: flex; }

.role-card__icon {
    width: 54px; height: 54px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 25px; position: relative; z-index: 1;
}
.role-card__name {
    font-size: 13.5px; font-weight: 700; color: var(--text);
    position: relative; z-index: 1;
}
.role-card__desc {
    font-size: 11.5px; color: var(--muted); line-height: 1.45;
    position: relative; z-index: 1;
}

/* ─────────────────────────────────────────────────
   BUTTONS
───────────────────────────────────────────────── */
.btn-primary {
    display: inline-flex; align-items: center; justify-content: center; gap: 7px;
    height: 48px; padding: 0 26px;
    border-radius: var(--r-md);
    background: linear-gradient(135deg, var(--teal-lt), var(--teal));
    color: #fff;
    font-size: 14px; font-weight: 700; font-family: inherit;
    border: none; cursor: pointer;
    box-shadow: var(--sh-teal);
    transition: transform .15s, box-shadow .15s, opacity .15s;
    -webkit-tap-highlight-color: transparent;
    user-select: none;
}
.btn-primary .material-symbols-outlined { font-size: 17px; }
.btn-primary:hover   { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(13,148,136,.35); }
.btn-primary:active  { transform: scale(.97); box-shadow: var(--sh); }
.btn-primary:disabled { opacity: .4; cursor: not-allowed; transform: none; box-shadow: none; }
.btn-primary.is-full  { width: 100%; }

.btn-ghost {
    display: inline-flex; align-items: center; gap: 5px;
    height: 42px; padding: 0 14px;
    border-radius: var(--r-sm);
    border: 1.5px solid var(--border);
    background: var(--surface);
    font-size: 13px; font-weight: 600; font-family: inherit;
    color: var(--muted); cursor: pointer;
    transition: all .15s;
    flex-shrink: 0;
    -webkit-tap-highlight-color: transparent;
}
.btn-ghost .material-symbols-outlined { font-size: 16px; }
.btn-ghost:hover  { border-color: #cbd5e1; color: var(--text-2); background: #f8fafc; }
.btn-ghost:active { transform: scale(.97); }

/* ─────────────────────────────────────────────────
   STEP 2 — DNI
───────────────────────────────────────────────── */
.s2-wrap { max-width: 420px; margin: 0 auto; }

.s2-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-xl);
    padding: 28px 24px;
    box-shadow: var(--sh-md);
}
@media (max-width: 479px) { .s2-card { padding: 22px 18px; border-radius: var(--r-lg); } }

.s2-icon {
    width: 56px; height: 56px; border-radius: 16px;
    background: var(--teal-bg); border: 1px solid var(--teal-bd);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
}
.s2-icon .material-symbols-outlined { font-size: 27px; color: var(--teal); }

.s2-title {
    font-size: 19px; font-weight: 800; color: var(--text);
    text-align: center; letter-spacing: -.02em;
    margin-bottom: 5px;
}
.s2-sub {
    font-size: 13px; color: var(--muted); font-weight: 500;
    text-align: center; line-height: 1.5; margin-bottom: 22px;
}

/* Field label */
.f-label {
    display: block;
    font-size: 10px; font-weight: 800;
    text-transform: uppercase; letter-spacing: .09em;
    color: var(--muted-lt); margin-bottom: 6px;
}

/* DNI input */
.g-input {
    width: 100%; height: 48px;
    padding: 0 14px;
    font-size: 16px; font-weight: 600;
    font-family: 'JetBrains Mono', monospace;
    letter-spacing: .06em;
    background: var(--bg);
    border: 2px solid var(--border);
    border-radius: var(--r-md);
    color: var(--text);
    outline: none;
    transition: border-color .18s, box-shadow .18s, background .18s;
    -webkit-appearance: none;
}
.g-input:focus { border-color: var(--teal-lt); background: #fff; box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
.g-input.is-error { border-color: var(--red-lt); background: #fff5f5; box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
.g-input::placeholder {
    font-family: 'Instrument Sans', sans-serif;
    font-weight: 500; font-size: 14px;
    letter-spacing: 0; color: #cbd5e1;
}

/* Error banner */
.g-error {
    display: none;
    align-items: flex-start; gap: 7px;
    margin-top: 8px; padding: 9px 11px;
    border-radius: 9px;
    background: rgba(239,68,68,.07);
    border: 1px solid rgba(239,68,68,.18);
    font-size: 12px; font-weight: 600; color: var(--red);
    line-height: 1.4;
}
.g-error .material-symbols-outlined { font-size: 15px; flex-shrink: 0; margin-top: 1px; }
.g-error.is-visible { display: flex; }

/* Actions row */
.s2-actions {
    display: flex; gap: 8px; margin-top: 18px;
}
.s2-actions .btn-primary { flex: 1; }

/* Privacy note */
.s2-note {
    display: flex; align-items: center; justify-content: center; gap: 4px;
    margin-top: 14px;
    font-size: 11.5px; font-weight: 500; color: var(--muted-lt);
}
.s2-note .material-symbols-outlined { font-size: 13px; }

/* ─────────────────────────────────────────────────
   STEP 3 — PANEL
───────────────────────────────────────────────── */
.panel-stack { display: flex; flex-direction: column; gap: 14px; }

/* Student header */
.s-header {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-xl);
    overflow: hidden;
    box-shadow: var(--sh);
}
.s-header__bar { height: 3px; background: linear-gradient(90deg, var(--teal-lt) 0%, #6ee7b7 100%); }
.s-header__body {
    padding: 16px 18px;
    display: flex; align-items: flex-start; gap: 14px;
}
@media (max-width: 479px) { .s-header__body { padding: 14px 14px; } }

.s-avatar {
    width: 50px; height: 50px; border-radius: 14px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--teal-lt), var(--teal));
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; font-weight: 800; color: #fff;
    letter-spacing: -.01em;
    box-shadow: 0 3px 10px rgba(13,148,136,.28);
}
.s-info { flex: 1; min-width: 0; }
.s-name {
    font-size: 16px; font-weight: 800; color: var(--text);
    letter-spacing: -.02em; line-height: 1.2;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
@media (max-width: 360px) { .s-name { font-size: 14px; } }
.s-meta { font-size: 12px; color: var(--muted); font-weight: 500; margin-top: 3px; line-height: 1.4; }
.s-badges { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 9px; }
.s-badge {
    padding: 3px 9px; border-radius: 7px;
    font-size: 11px; font-weight: 700;
}
.s-badge--period { background: var(--teal-bg);  border: 1px solid var(--teal-bd); color: var(--teal); }
.s-badge--grade  { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; }
.s-badge--turn   { background: #faf5ff; border: 1px solid #e9d5ff; color: #7c3aed; }

/* KPI grid */
.kpi-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
@media (min-width: 480px) { .kpi-grid { grid-template-columns: repeat(4, 1fr); } }

.kpi-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 14px 12px;
    display: flex; align-items: center; gap: 10px;
    box-shadow: var(--sh);
    transition: transform .15s, box-shadow .15s;
    position: relative; overflow: hidden;
}
.kpi-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2.5px;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: var(--sh-md); }

.kpi-card--present::before { background: linear-gradient(90deg,#22c55e,#4ade80); }
.kpi-card--absent::before  { background: linear-gradient(90deg,#ef4444,#f87171); }
.kpi-card--late::before    { background: linear-gradient(90deg,#f59e0b,#fcd34d); }
.kpi-card--rate::before    { background: linear-gradient(90deg,var(--teal),var(--teal-lt)); }

.kpi-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.kpi-icon .material-symbols-outlined { font-size: 17px; }
.kpi-card--present .kpi-icon { background: #dcfce7; color: var(--green); }
.kpi-card--absent  .kpi-icon { background: #fee2e2; color: var(--red); }
.kpi-card--late    .kpi-icon { background: #fef3c7; color: var(--amber); }
.kpi-card--rate    .kpi-icon { background: var(--teal-bg); color: var(--teal); }

.kpi-body { min-width: 0; }
.kpi-val  {
    font-size: 22px; font-weight: 800;
    letter-spacing: -.03em; line-height: 1;
}
.kpi-card--present .kpi-val { color: var(--green); }
.kpi-card--absent  .kpi-val { color: var(--red); }
.kpi-card--late    .kpi-val { color: var(--amber); }
.kpi-card--rate    .kpi-val { color: var(--teal); }
.kpi-lbl {
    font-size: 9.5px; font-weight: 800;
    text-transform: uppercase; letter-spacing: .08em;
    color: var(--muted-lt); margin-top: 2px;
}

/* Charts row */
.charts-row {
    display: grid; grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 600px) { .charts-row { grid-template-columns: 2fr 1fr; } }

.g-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 16px 16px;
    box-shadow: var(--sh);
}
@media (max-width: 479px) { .g-card { padding: 14px 12px; } }

.g-card__head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 12px; gap: 8px; flex-wrap: wrap;
}
.g-card__title {
    display: flex; align-items: center; gap: 6px;
    font-size: 12.5px; font-weight: 700; color: var(--text-2);
}
.g-card__title .material-symbols-outlined { font-size: 15px; color: var(--teal-lt); }
.g-card__badge {
    font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .07em;
    padding: 3px 8px; border-radius: 99px;
    background: var(--teal-bg); border: 1px solid var(--teal-bd); color: var(--teal);
}
.chart-area { height: 155px; position: relative; }
@media (min-width: 600px) { .chart-area { height: 175px; } }

/* ─────────────────────────────────────────────────
   TABLE CARD
───────────────────────────────────────────────── */
.tbl-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-xl);
    overflow: hidden;
    box-shadow: var(--sh);
}
.tbl-card__head {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-lt);
    display: flex; align-items: center;
    flex-wrap: wrap; gap: 10px;
    justify-content: space-between;
}
.tbl-filters {
    display: flex; align-items: center;
    gap: 6px; flex-wrap: wrap;
}
@media (max-width: 599px) { .tbl-filters { width: 100%; } }

/* filter controls */
.f-sel {
    height: 34px; padding: 0 26px 0 10px;
    border-radius: 8px;
    border: 1.5px solid var(--border);
    background: var(--bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2394a3b8'/%3E%3C/svg%3E") no-repeat right 8px center;
    font-size: 12px; font-weight: 600; font-family: inherit;
    color: var(--text-2);
    appearance: none; -webkit-appearance: none;
    outline: none; cursor: pointer;
    transition: border-color .15s;
    min-width: 0; flex-shrink: 0;
}
.f-sel:focus { border-color: var(--teal-lt); }
@media (max-width: 599px) { .f-sel { flex: 1; min-width: 100px; } }

.f-search-wrap { position: relative; flex: 1; min-width: 110px; }
@media (min-width: 600px) { .f-search-wrap { max-width: 148px; } }
.f-search-icon {
    position: absolute; left: 8px; top: 50%; transform: translateY(-50%);
    font-size: 14px; color: var(--muted-lt); pointer-events: none;
}
.f-search {
    width: 100%; height: 34px; padding: 0 10px 0 28px;
    border-radius: 8px;
    border: 1.5px solid var(--border);
    background: var(--bg);
    font-size: 12px; font-weight: 600; font-family: inherit;
    color: var(--text-2);
    outline: none;
    transition: border-color .15s;
    -webkit-appearance: none;
}
.f-search:focus { border-color: var(--teal-lt); }
.f-search::placeholder { color: #cbd5e1; font-weight: 500; }

/* table scroll */
.tbl-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.tbl-scroll::-webkit-scrollbar { height: 3px; }
.tbl-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

table.g-tbl { width: 100%; border-collapse: collapse; }
.g-tbl thead th {
    padding: 10px 14px;
    font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
    color: var(--muted-lt);
    background: #f8fafc;
    border-bottom: 1.5px solid var(--border);
    white-space: nowrap; text-align: left;
}
.g-tbl tbody tr { transition: background .1s; }
.g-tbl tbody tr:hover { background: #f8fafc; }
.g-tbl tbody td {
    padding: 11px 14px;
    font-size: 13px; color: var(--text-2);
    border-bottom: 1px solid var(--border-lt);
    vertical-align: middle;
}
.g-tbl tbody tr:last-child td { border-bottom: none; }
.td-mono  { font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 600; }
.td-muted { color: var(--muted); font-size: 12px; }

/* ── Mobile: card layout para la tabla ── */
@media (max-width: 499px) {
    .g-tbl thead { display: none; }
    .g-tbl, .g-tbl tbody { display: block; width: 100%; }
    .g-tbl tbody tr {
        display: grid;
        grid-template-columns: 1fr auto;
        grid-template-rows: auto auto;
        column-gap: 10px; row-gap: 4px;
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-lt);
    }
    .g-tbl tbody tr:last-child { border-bottom: none; }
    .g-tbl tbody td { display: block; padding: 0; border: none; font-size: 13px; }

    /* Fecha — top-left */
    .g-tbl tbody td:nth-child(1) {
        grid-column: 1; grid-row: 1;
        font-weight: 700; color: var(--text); font-size: 13px;
    }
    /* Turno — top-left secundario (lo ponemos inline con fecha) */
    .g-tbl tbody td:nth-child(2) {
        display: none; /* se muestra dentro de la observación row */
    }
    /* Hora — top-right */
    .g-tbl tbody td:nth-child(3) {
        grid-column: 2; grid-row: 1;
        text-align: right; align-self: center;
    }
    /* Estado — bottom-left */
    .g-tbl tbody td:nth-child(4) {
        grid-column: 1; grid-row: 2; align-self: center;
    }
    /* Observación — bottom-right */
    .g-tbl tbody td:nth-child(5) {
        grid-column: 2; grid-row: 2;
        text-align: right; font-size: 11px; color: var(--muted-lt);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        align-self: center;
    }
}

/* table footer */
.tbl-card__foot {
    padding: 10px 14px;
    border-top: 1px solid var(--border-lt);
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px;
}
.tbl-info { font-size: 11px; font-weight: 600; color: var(--muted-lt); }

/* pagination */
.g-pagination { display: flex; gap: 3px; flex-wrap: wrap; }
.pg-btn {
    min-width: 30px; height: 30px; padding: 0 6px;
    border-radius: 7px;
    border: 1.5px solid var(--border);
    background: var(--surface);
    font-size: 12px; font-weight: 700; font-family: inherit;
    color: var(--muted);
    cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    transition: all .12s;
    -webkit-tap-highlight-color: transparent;
}
.pg-btn .material-symbols-outlined { font-size: 14px; }
.pg-btn:hover:not(:disabled):not(.is-active) {
    border-color: var(--teal-xl); color: var(--teal); background: var(--teal-bg);
}
.pg-btn.is-active { background: var(--teal); border-color: var(--teal); color: #fff; }
.pg-btn:disabled  { opacity: .3; cursor: not-allowed; }

/* ─────────────────────────────────────────────────
   STATUS PILLS
───────────────────────────────────────────────── */
.pill {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 3px 8px; border-radius: 99px;
    font-size: 11px; font-weight: 700; white-space: nowrap;
}
.pill .material-symbols-outlined { font-size: 11px; }
.pill--present { background: rgba(34,197,94,.1);   color: var(--green); border: 1px solid rgba(34,197,94,.22); }
.pill--absent  { background: rgba(239,68,68,.1);   color: var(--red);   border: 1px solid rgba(239,68,68,.22); }
.pill--late    { background: rgba(245,158,11,.12); color: var(--amber); border: 1px solid rgba(245,158,11,.22); }

/* ─────────────────────────────────────────────────
   EMPTY & LOADING STATES
───────────────────────────────────────────────── */
.g-empty {
    display: flex; flex-direction: column; align-items: center;
    gap: 7px; padding: 36px 16px; text-align: center;
}
.g-empty .material-symbols-outlined { font-size: 34px; color: #cbd5e1; }
.g-empty p { font-size: 12.5px; color: var(--muted-lt); font-weight: 500; }

.g-loading {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; padding: 36px 16px;
    font-size: 12.5px; color: var(--muted-lt); font-weight: 500;
}
.spinner {
    width: 20px; height: 20px; flex-shrink: 0;
    border: 2.5px solid var(--border);
    border-top-color: var(--teal-lt);
    border-radius: 50%;
    animation: spin .65s linear infinite;
}
</style>
@endpush

@section('content')

{{-- Datos para JS --}}
<script>
(function() {
    window.__guestCfg = {
        routes: {
            student  : @json(route('guest.api.student')),
            records  : @json(route('guest.api.records')),
            sessions : @json(route('guest.api.sessions')),
        },
        periods : @json($periods),
    };
})();
</script>

{{-- ══════════════════════════════════════════════
     TOP BAR
══════════════════════════════════════════════ --}}
<header class="g-topbar">
    <div class="g-topbar__inner">
        <a href="{{ route('guest.index') }}">
            <img src="{{ asset('img/logotipo.png') }}" alt="Logo" class="g-topbar__logo">
        </a>
        <div class="g-topbar__right">
            <div class="live-badge">
                <span class="live-dot"></span>
                <span id="js-clock">{{ now()->format('H:i') }}</span>
            </div>
            <div id="js-role-badge" class="g-role-badge">
                <span class="material-symbols-outlined">badge</span>
                <span id="js-role-text">Invitado</span>
            </div>
            <button id="js-btn-reset" class="g-btn-reset" type="button">
                <span class="material-symbols-outlined">logout</span>
                <span class="g-btn-reset__label">Nueva búsqueda</span>
            </button>
        </div>
    </div>
</header>

<main class="g-main">

    {{-- ── Wizard bar ── --}}
    <div class="wz-bar u-fade-up" role="navigation" aria-label="Pasos del proceso">
        <div class="wz-step">
            <div class="wz-dot" id="wd-1">1</div>
            <span class="wz-label" id="wl-1">Identificación</span>
        </div>  
        <div class="wz-sep" id="ws-1"></div>
        <div class="wz-step">
            <div class="wz-dot" id="wd-2">2</div>
            <span class="wz-label" id="wl-2">Buscar estudiante</span>
        </div>
        <div class="wz-sep" id="ws-2"></div>
        <div class="wz-step">
            <div class="wz-dot" id="wd-3">
                <span class="material-symbols-outlined">dashboard</span>
            </div>
            <span class="wz-label" id="wl-3">Asistencias</span>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         STEP 1 — Selección de rol
    ════════════════════════════════════════ --}}
    <div id="g-step-1" class="g-step is-active" aria-label="Paso 1: Identificación">
        <div class="s1-wrap">
            <div class="s1-chip u-fade-up">
                <span class="material-symbols-outlined">school</span>
                I.E. Francisco Izquierdo Ríos · Asistencias
            </div>

            <h1 class="s1-title u-fade-up u-d1">
                ¿Cómo ingresas <span>hoy?</span>
            </h1>
            <p class="s1-sub u-fade-up u-d2 font-normal">
                Selecciona tu rol para consultar asistencias
            </p>

            <div class="role-grid" role="radiogroup" aria-label="Selecciona tu rol">
                <div class="role-card u-fade-up u-d2" data-role="parent"
                     role="radio" aria-checked="false" tabindex="0"
                     onclick="GuestApp.selectRole(this)"
                     onkeydown="if(event.key==='Enter'||event.key===' '){GuestApp.selectRole(this)}">
                    <div class="role-card__check" aria-hidden="true">
                        <span class="material-symbols-outlined">check</span>
                    </div>
                    <div class="role-card__icon" style="background:#f0fdfa" aria-hidden="true">👨‍👩‍👧‍👦</div>
                    <div class="role-card__name">Padre / Tutor</div>
                    <div class="role-card__desc">Consulta de tu hijo/a</div>
                </div>
                <div class="role-card u-fade-up u-d3" data-role="staff"
                     role="radio" aria-checked="false" tabindex="0"
                     onclick="GuestApp.selectRole(this)"
                     onkeydown="if(event.key==='Enter'||event.key===' '){GuestApp.selectRole(this)}">
                    <div class="role-card__check" aria-hidden="true">
                        <span class="material-symbols-outlined">check</span>
                    </div>
                    <div class="role-card__icon" style="background:#eef2ff" aria-hidden="true">👩‍🏫</div>
                    <div class="role-card__name">Docente / Personal</div>
                    <div class="role-card__desc">Consulta de un estudiante</div>
                </div>
            </div>

            <button id="js-btn-continue" class="btn-primary is-full u-fade-up u-d4"
                    type="button" disabled onclick="GuestApp.goStep2()">
                Continuar
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         STEP 2 — DNI
    ════════════════════════════════════════ --}}
    <div id="g-step-2" class="g-step" aria-label="Paso 2: Buscar estudiante">
        <div class="s2-wrap">
            <div class="s2-card u-slide-in">
                <div class="s2-icon" aria-hidden="true">
                    <span class="material-symbols-outlined">person_search</span>
                </div>
                <h2 class="s2-title">DNI del Estudiante</h2>
                <p class="s2-sub">
                    Ingresa el número de documento
                </p>
                <input id="js-dni" type="text" inputmode="numeric" maxlength="12"
                       class="g-input text-center" placeholder="12345678"
                       autocomplete="off" spellcheck="false"
                       aria-describedby="js-error-text">

                <div id="js-error" class="g-error" role="alert" aria-live="polite">
                    <span class="material-symbols-outlined" aria-hidden="true">error</span>
                    <span id="js-error-text">No se encontró ningún estudiante con ese DNI.</span>
                </div>

                <div class="s2-actions">
                    <button class="btn-ghost" type="button" onclick="GuestApp.goStep1()">
                        <span class="material-symbols-outlined">arrow_back</span>
                        Volver
                    </button>
                    <button id="js-btn-search" class="btn-primary" type="button"
                            disabled onclick="GuestApp.searchStudent()">
                        <span id="js-search-icon" class="material-symbols-outlined">search</span>
                        <span id="js-search-label">Buscar</span>
                    </button>
                </div>
            </div>

            <p class="s2-note u-slide-in u-d1">
                <span class="material-symbols-outlined" aria-hidden="true">lock</span>
                Solo lectura · Datos confidenciales
            </p>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         STEP 3 — Panel del estudiante
    ════════════════════════════════════════ --}}
    <div id="g-step-3" class="g-step" aria-label="Paso 3: Historial de asistencias">
        <div class="panel-stack">

            {{-- Student header --}}
            <div class="s-header u-fade-up">
                <div class="s-header__bar" aria-hidden="true"></div>
                <div class="s-header__body">
                    <div id="js-avatar" class="s-avatar" aria-hidden="true">—</div>
                    <div class="s-info">
                        <div id="js-s-name" class="s-name">—</div>
                        <div id="js-s-meta" class="s-meta">—</div>
                        <div id="js-s-badges" class="s-badges"></div>
                    </div>
                </div>
            </div>

            {{-- KPIs --}}
            <div class="kpi-grid" role="list" aria-label="Resumen de asistencias">
                <div class="kpi-card kpi-card--present u-fade-up u-d1" role="listitem">
                    <div class="kpi-icon" aria-hidden="true">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                    <div class="kpi-body">
                        <div id="js-kpi-present" class="kpi-val">—</div>
                        <div class="kpi-lbl">Presentes</div>
                    </div>
                </div>
                <div class="kpi-card kpi-card--absent u-fade-up u-d2" role="listitem">
                    <div class="kpi-icon" aria-hidden="true">
                        <span class="material-symbols-outlined">cancel</span>
                    </div>
                    <div class="kpi-body">
                        <div id="js-kpi-absent" class="kpi-val">—</div>
                        <div class="kpi-lbl">Ausentes</div>
                    </div>
                </div>
                <div class="kpi-card kpi-card--late u-fade-up u-d3" role="listitem">
                    <div class="kpi-icon" aria-hidden="true">
                        <span class="material-symbols-outlined">schedule</span>
                    </div>
                    <div class="kpi-body">
                        <div id="js-kpi-late" class="kpi-val">—</div>
                        <div class="kpi-lbl">Tardanzas</div>
                    </div>
                </div>
                <div class="kpi-card kpi-card--rate u-fade-up u-d4" role="listitem">
                    <div class="kpi-icon" aria-hidden="true">
                        <span class="material-symbols-outlined">trending_up</span>
                    </div>
                    <div class="kpi-body">
                        <div id="js-kpi-rate" class="kpi-val">—</div>
                        <div class="kpi-lbl">% Asistencia</div>
                    </div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="charts-row">
                <div class="g-card u-fade-up u-d1">
                    <div class="g-card__head">
                        <div class="g-card__title">
                            <span class="material-symbols-outlined" aria-hidden="true">show_chart</span>
                            Tendencia semanal
                        </div>
                        <span class="g-card__badge" aria-hidden="true">Semanas</span>
                    </div>
                    <div class="chart-area">
                        <canvas id="js-chart-trend" aria-label="Gráfico de tendencia semanal"></canvas>
                    </div>
                </div>
                <div class="g-card u-fade-up u-d2">
                    <div class="g-card__head">
                        <div class="g-card__title">
                            <span class="material-symbols-outlined" aria-hidden="true">donut_large</span>
                            Distribución
                        </div>
                        <span class="g-card__badge" aria-hidden="true">Total</span>
                    </div>
                    <div class="chart-area">
                        <canvas id="js-chart-donut" aria-label="Gráfico de distribución de asistencias"></canvas>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="tbl-card u-fade-up u-d3">
                <div class="tbl-card__head">
                    <div class="g-card__title">
                        <span class="material-symbols-outlined" aria-hidden="true">table_view</span>
                        Historial de asistencias
                    </div>
                    <div class="tbl-filters" role="group" aria-label="Filtros">
                        <select id="js-f-period" class="f-sel" aria-label="Filtrar por periodo"
                                onchange="GuestApp.applyFilters()">
                            <option value="">Todos los periodos</option>
                            @foreach($periods as $per)
                            <option value="{{ $per->codperiod }}"
                                {{ $per->is_active === 'Y' ? 'selected' : '' }}>
                                {{ $per->name }}{{ $per->is_active === 'Y' ? ' ✓' : '' }}
                            </option>
                            @endforeach
                        </select>
                        <select id="js-f-status" class="f-sel" aria-label="Filtrar por estado"
                                onchange="GuestApp.applyFilters()">
                            <option value="">Todos los estados</option>
                            <option value="present">Presente</option>
                            <option value="absent">Ausente</option>
                            <option value="late">Tardanza</option>
                        </select>
                        <div class="f-search-wrap">
                            <span class="f-search-icon material-symbols-outlined" aria-hidden="true">search</span>
                            <input id="js-f-search" type="text" class="f-search"
                                   placeholder="Buscar fecha..."
                                   aria-label="Buscar por fecha"
                                   oninput="GuestApp.applyFilters()">
                        </div>
                    </div>
                </div>

                <div class="tbl-scroll">
                    <table class="g-tbl" aria-label="Historial de asistencias">
                        <thead>
                            <tr>
                                <th scope="col">Fecha</th>
                                <th scope="col">Turno</th>
                                <th scope="col">Hora</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Observación</th>
                            </tr>
                        </thead>
                        <tbody id="js-tbl-body">
                            <tr>
                                <td colspan="5">
                                    <div class="g-loading">
                                        <div class="spinner" aria-hidden="true"></div>
                                        Cargando historial...
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="tbl-card__foot">
                    <span id="js-tbl-info" class="tbl-info" aria-live="polite">— registros</span>
                    <nav id="js-pagination" class="g-pagination" aria-label="Paginación"></nav>
                </div>
            </div>

        </div>{{-- /panel-stack --}}
    </div>{{-- /step3 --}}

</main>

@push('scripts')
<script>
/* ═══════════════════════════════════════════════════════════════
   GuestApp — completamente aislado, sin tocar nada global
   Todos los accesos al DOM tienen guard (if !el return)
═══════════════════════════════════════════════════════════════ */
;(function () {
    'use strict';

    /* ── Config ── */
    const CFG     = window.__guestCfg ?? { routes:{}, periods:[] };
    const SS_KEY  = 'guestAppV3';
    const PAGE_SZ = 10;
    const C = {
        teal : '#14b8a6',
        green: '#22c55e',
        red  : '#ef4444',
        amber: '#f59e0b',
    };

    /* ── State ── */
    let _role       = null;
    let _data       = null;   // { student, enrollment, assistances, stats }
    let _allRecs    = [];
    let _filtered   = [];
    let _page       = 1;
    let _chartTrend = null;
    let _chartDonut = null;

    /* ── Safe DOM helpers (TODOS tienen guard) ── */
    const el   = id => document.getElementById(id) ?? null;
    const setText = (id, txt) => { const e = el(id); if (e) e.textContent = txt; };
    const esc  = v  => String(v ?? '—')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

    /* ─────────────────────────────────────────────
       BOOT
    ───────────────────────────────────────────── */
    function boot () {
        // Reloj — guard: solo si existe el elemento
        const clockEl = el('js-clock');
        if (clockEl) {
            setInterval(() => {
                clockEl.textContent = new Date()
                    .toLocaleTimeString('es-PE',{hour:'2-digit',minute:'2-digit'});
            }, 30000);
        }

        // Enter en DNI
        const dniEl = el('js-dni');
        if (dniEl) {
            dniEl.addEventListener('keydown', e => {
                if (e.key === 'Enter') GuestApp.searchStudent();
            });
            dniEl.addEventListener('input', () => onDniInput());
        }

        // Botón reset
        const resetBtn = el('js-btn-reset');
        if (resetBtn) resetBtn.addEventListener('click', clearSession);

        // Intentar restaurar sesión guardada
        try {
            const saved = JSON.parse(sessionStorage.getItem(SS_KEY) ?? 'null');
            if (saved?.student && Array.isArray(saved.assistances)) {
                _role    = saved.role ?? 'parent';
                _data    = saved;
                _allRecs = saved.assistances ?? [];
                _filtered= [..._allRecs];
                _activateStep(3);
                renderPanel();
                return;
            }
        } catch (_) { /* ignore */ }

        _activateStep(1);
    }

    /* ─────────────────────────────────────────────
       WIZARD
    ───────────────────────────────────────────── */
    function _activateStep (n) {
        [1, 2, 3].forEach(i => {
            const panel = el(`g-step-${i}`);
            if (!panel) return;
            panel.classList.toggle('is-active', i === n);
        });
        _updateWizardBar(n);
        _updateTopBar(n);
        if (n === 2) setTimeout(() => el('js-dni')?.focus(), 280);
    }

    function _updateWizardBar (active) {
        [[1,'wd-1','wl-1'], [2,'wd-2','wl-2'], [3,'wd-3','wl-3']].forEach(([i, dotId, lblId]) => {
            const dot = el(dotId); const lbl = el(lblId);
            if (!dot || !lbl) return;
            dot.classList.remove('is-active','is-done');
            lbl.classList.remove('is-active','is-done');
            if      (i < active)  { dot.classList.add('is-done');   lbl.classList.add('is-done');   }
            else if (i === active) { dot.classList.add('is-active'); lbl.classList.add('is-active'); }
        });
        [['ws-1', 1], ['ws-2', 2]].forEach(([id, threshold]) => {
            const sep = el(id); if (!sep) return;
            sep.classList.toggle('is-done', active > threshold);
        });
    }

    function _updateTopBar (step) {
        const badge   = el('js-role-badge');
        const roleText= el('js-role-text');
        const resetBtn= el('js-btn-reset');
        if (step === 3) {
            badge?.classList.add('is-visible');
            if (roleText) roleText.textContent = _role === 'staff' ? 'Personal' : 'Padre/Tutor';
            resetBtn?.classList.add('is-visible');
        } else {
            badge?.classList.remove('is-visible');
            resetBtn?.classList.remove('is-visible');
        }
    }

    /* ─────────────────────────────────────────────
       STEP 1
    ───────────────────────────────────────────── */
    function selectRole (card) {
        if (!card) return;
        document.querySelectorAll('.role-card').forEach(c => {
            c.classList.remove('is-selected');
            c.setAttribute('aria-checked','false');
        });
        card.classList.add('is-selected');
        card.setAttribute('aria-checked','true');
        _role = card.dataset.role ?? null;

        const btn = el('js-btn-continue');
        if (btn) { btn.disabled = false; }
    }

    /* ─────────────────────────────────────────────
       STEP 2
    ───────────────────────────────────────────── */
    function onDniInput () {
        const inp = el('js-dni'); if (!inp) return;
        inp.value = inp.value.replace(/\D/g,'');
        const btn = el('js-btn-search');
        if (btn) btn.disabled = inp.value.length < 3;
        _hideError();
        if (inp.value.length >= 3) inp.classList.remove('is-error');
    }

    function _showError (msg) {
        const inp = el('js-dni');
        const err = el('js-error');
        const txt = el('js-error-text');
        inp?.classList.add('is-error');
        if (txt) txt.textContent = msg;
        err?.classList.add('is-visible');
    }

    function _hideError () {
        el('js-error')?.classList.remove('is-visible');
        el('js-dni')?.classList.remove('is-error');
    }

    async function searchStudent () {
        const inp = el('js-dni'); if (!inp) return;
        const dni = inp.value.trim();
        if (dni.length < 3) return;

        _hideError();
        _setSearchLoading(true);

        try {
            const url  = `${CFG.routes.student}?dni=${encodeURIComponent(dni)}`;
            const res  = await fetch(url, { headers:{'X-Requested-With':'XMLHttpRequest'} });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            if (!data.student) {
                _showError('No se encontró ningún estudiante con ese DNI.');
                return;
            }

            _data    = data;
            _allRecs = data.assistances ?? [];
            _filtered= [..._allRecs];

            try {
                sessionStorage.setItem(SS_KEY, JSON.stringify({ ...data, role: _role }));
            } catch (_) { /* cuota llena, no crítico */ }

            _activateStep(3);
            renderPanel();

        } catch (err) {
            _showError('Error de conexión. Verifica tu red e intenta de nuevo.');
        } finally {
            _setSearchLoading(false);
        }
    }

    function _setSearchLoading (on) {
        const btn  = el('js-btn-search');
        const icon = el('js-search-icon');
        const lbl  = el('js-search-label');
        if (!btn) return;
        btn.disabled = on;
        if (icon) icon.textContent = on ? 'hourglass_empty' : 'search';
        if (lbl)  lbl.textContent  = on ? 'Buscando...' : 'Buscar';
    }

    /* ─────────────────────────────────────────────
       STEP 3 — PANEL
    ───────────────────────────────────────────── */
    function renderPanel () {
        if (!_data) return;
        const { student, enrollment, stats } = _data;

        /* Avatar */
        const initials = (student.fullname ?? '')
            .split(' ').filter(Boolean).slice(0,2)
            .map(w => w[0] ?? '').join('').toUpperCase() || '?';
        setText('js-avatar', initials);

        /* Nombre y meta */
        setText('js-s-name', student.fullname ?? '—');
        const metaParts = [`DNI: ${student.dni ?? '—'}`];
        if (enrollment) metaParts.push(`${enrollment.grade} ${enrollment.section}`);
        else            metaParts.push('Sin matrícula activa');
        setText('js-s-meta', metaParts.join(' · '));

        /* Badges */
        const badgesEl = el('js-s-badges');
        if (badgesEl) {
            const b = [];
            if (enrollment?.period)  b.push(`<span class="s-badge s-badge--period">${esc(enrollment.period)}</span>`);
            if (enrollment?.grade)   b.push(`<span class="s-badge s-badge--grade">${esc(enrollment.grade)} ${esc(enrollment.section)}</span>`);
            if (enrollment?.turn)    b.push(`<span class="s-badge s-badge--turn">Turno ${esc(enrollment.turn)}</span>`);
            badgesEl.innerHTML = b.join('');
        }

        /* KPIs */
        setText('js-kpi-present', stats?.present ?? '—');
        setText('js-kpi-absent',  stats?.absent  ?? '—');
        setText('js-kpi-late',    stats?.late    ?? '—');
        setText('js-kpi-rate',    stats ? `${stats.rate}%` : '—');

        /* Charts — esperar a que Chart.js esté listo */
        _whenChartReady(() => {
            renderDonut(stats);
            renderTrend(_allRecs);
        });

        /* Tabla */
        applyFilters();
    }

    function _whenChartReady (fn, tries = 0) {
        if (typeof Chart !== 'undefined') { fn(); return; }
        if (tries > 20) return;
        setTimeout(() => _whenChartReady(fn, tries + 1), 150);
    }

    /* ── Donut chart ── */
    function renderDonut (stats) {
        const canvas = el('js-chart-donut'); if (!canvas) return;
        _chartDonut?.destroy();
        try {
            _chartDonut = new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Presente','Ausente','Tardanza'],
                    datasets:[{
                        data: [stats?.present??0, stats?.absent??0, stats?.late??0],
                        backgroundColor: [C.green, C.red, C.amber],
                        borderWidth: 2, borderColor: '#fff', hoverOffset: 5,
                    }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '70%',
                    plugins: {
                        legend: { position:'bottom', labels:{ padding:12, boxWidth:9, font:{size:10,weight:'700'} } },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } },
                    },
                },
            });
        } catch(e) { /* Chart.js no disponible */ }
    }

    /* ── Trend chart ── */
    function renderTrend (records) {
        const canvas = el('js-chart-trend'); if (!canvas) return;
        _chartTrend?.destroy();

        /* Agrupar por semana (lunes como inicio) */
        const weeks = {};
        records.forEach(r => {
            const d = new Date(r.raw_date ?? '');
            if (isNaN(d.getTime())) return;
            const mon = new Date(d);
            mon.setDate(d.getDate() - ((d.getDay() + 6) % 7));
            const key = mon.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit' });
            if (!weeks[key]) weeks[key] = { present:0, absent:0, late:0 };
            const st = (r.status ?? '').toLowerCase();
            if (st in weeks[key]) weeks[key][st]++;
        });

        const labels  = Object.keys(weeks);
        const present = labels.map(w => weeks[w].present);
        const absent  = labels.map(w => weeks[w].absent);
        const late    = labels.map(w => weeks[w].late);

        try {
            _chartTrend = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        _lineDs('Presentes', present, C.green, 'rgba(34,197,94,.08)'),
                        _lineDs('Ausentes',  absent,  C.red,   'rgba(239,68,68,.07)'),
                        _lineDs('Tardanzas', late,    C.amber, 'rgba(245,158,11,.07)', true),
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode:'index', intersect:false },
                    plugins: {
                        legend: { position:'top', labels:{ padding:12, boxWidth:9, font:{size:10,weight:'700'} } },
                    },
                    scales: {
                        x: { grid:{display:false}, ticks:{font:{size:9}, maxRotation:0, autoSkipPadding:8} },
                        y: { beginAtZero:true, grid:{color:'rgba(0,0,0,.04)'}, ticks:{font:{size:9}} },
                    },
                },
            });
        } catch(e) { /* Chart.js no disponible */ }
    }

    function _lineDs (label, data, color, bg, dashed = false) {
        return {
            label, data, borderColor: color, backgroundColor: bg,
            fill: true, tension: .4, pointRadius: 2.5,
            pointBackgroundColor: color, borderWidth: 2.5,
            ...(dashed ? { borderDash:[4,3] } : {}),
        };
    }

    /* ── Tabla ── */
    function applyFilters () {
        const period = el('js-f-period')?.value  ?? '';
        const status = el('js-f-status')?.value  ?? '';
        const search = (el('js-f-search')?.value ?? '').toLowerCase().trim();

        _filtered = _allRecs.filter(r => {
            const okP = !period || String(r.period_code) === String(period);
            const okS = !status || (r.status ?? '') === status;
            const okQ = !search
                || (r.date ?? '').toLowerCase().includes(search)
                || (r.turn ?? '').toLowerCase().includes(search);
            return okP && okS && okQ;
        });
        _page = 1;
        renderTable();
    }

    function renderTable () {
        const tbody = el('js-tbl-body'); if (!tbody) return;
        const total = _filtered.length;
        const pages = Math.max(1, Math.ceil(total / PAGE_SZ));
        const start = (_page - 1) * PAGE_SZ;
        const slice = _filtered.slice(start, start + PAGE_SZ);

        if (!slice.length) {
            tbody.innerHTML = `<tr><td colspan="5">
                <div class="g-empty">
                    <span class="material-symbols-outlined" aria-hidden="true">search_off</span>
                    <p>Sin registros con esos filtros</p>
                </div>
            </td></tr>`;
            setText('js-tbl-info', '0 registros');
            const pg = el('js-pagination'); if (pg) pg.innerHTML = '';
            return;
        }

        tbody.innerHTML = slice.map(r => `
            <tr>
                <td class="td-mono" style="color:var(--text);font-weight:700">${esc(r.date)}</td>
                <td class="td-muted">${esc(r.turn ?? '—')}</td>
                <td>
                    <span class="td-mono" style="color:var(--teal-lt)">
                        ${esc(r.time_entry ?? '—')}
                    </span>
                </td>
                <td>${_pill(r.status)}</td>
                <td class="td-muted"
                    style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                    title="${esc(r.observation ?? '')}">
                    ${esc(r.observation ?? '—')}
                </td>
            </tr>
        `).join('');

        setText('js-tbl-info',
            `${start+1}–${Math.min(start+PAGE_SZ, total)} de ${total} registros`);
        _renderPagination(pages);
    }

    function _renderPagination (pages) {
        const pg = el('js-pagination'); if (!pg) return;

        const mkBtn = (html, p, disabled = false, active = false) => {
            const cls   = `pg-btn${active ? ' is-active' : ''}`;
            const click = (!disabled && !active) ? `onclick="GuestApp.goPage(${p})"` : '';
            return `<button class="${cls}" type="button" ${disabled?'disabled':''} ${click}
                            ${active?`aria-current="page"`:''}>${html}</button>`;
        };

        let html = mkBtn(
            '<span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>',
            _page - 1, _page === 1
        );
        for (let i = 1; i <= pages; i++) {
            if (pages > 7 && Math.abs(i - _page) > 2 && i !== 1 && i !== pages) {
                if (i === _page - 3 || i === _page + 3) html += mkBtn('…', i, true);
                continue;
            }
            html += mkBtn(i, i, false, i === _page);
        }
        html += mkBtn(
            '<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>',
            _page + 1, _page === pages
        );
        pg.innerHTML = html;
    }

    function goPage (p) {
        const pages = Math.ceil(_filtered.length / PAGE_SZ);
        if (p < 1 || p > pages) return;
        _page = p;
        renderTable();
        /* scroll suave hasta la tabla */
        el('js-tbl-body')?.closest('.tbl-card')?.scrollIntoView({ behavior:'smooth', block:'nearest' });
    }

    /* ─────────────────────────────────────────────
       SESSION
    ───────────────────────────────────────────── */
    function clearSession () {
        try { sessionStorage.removeItem(SS_KEY); } catch (_) { /* ignore */ }

        _role = null; _data = null; _allRecs = []; _filtered = []; _page = 1;
        _chartTrend?.destroy(); _chartTrend = null;
        _chartDonut?.destroy(); _chartDonut = null;

        /* Reset step 1 */
        document.querySelectorAll('.role-card').forEach(c => {
            c.classList.remove('is-selected');
            c.setAttribute('aria-checked','false');
        });
        const btnContinue = el('js-btn-continue');
        if (btnContinue) btnContinue.disabled = true;

        /* Reset step 2 */
        const dniEl = el('js-dni');
        if (dniEl) dniEl.value = '';
        _hideError();

        _activateStep(1);
    }

    /* ─────────────────────────────────────────────
       UTILS
    ───────────────────────────────────────────── */
    function _pill (status) {
        const map = {
            present: ['pill pill--present','check_circle','Presente'],
            absent:  ['pill pill--absent', 'cancel',      'Ausente'],
            late:    ['pill pill--late',   'schedule',    'Tardanza'],
        };
        const k    = (status ?? '').toLowerCase();
        const info = map[k] ?? ['pill','help', status ?? '—'];
        return `<span class="${info[0]}">
            <span class="material-symbols-outlined" aria-hidden="true">${info[1]}</span>${esc(info[2])}
        </span>`;
    }

    /* ─────────────────────────────────────────────
       PUBLIC API
    ───────────────────────────────────────────── */
    window.GuestApp = {
        selectRole,
        goStep1  : () => _activateStep(1),
        goStep2  : () => { if (_role) _activateStep(2); },
        searchStudent,
        applyFilters,
        goPage,
        clearSession,
    };

    /* ─────────────────────────────────────────────
       INIT — sólo cuando el DOM esté listo
    ───────────────────────────────────────────── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

})(); /* IIFE — nada se filtra al scope global */
</script>
@endpush

@endsection