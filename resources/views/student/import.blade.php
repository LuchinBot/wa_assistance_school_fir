{{-- resources/views/student/import.blade.php --}}
@extends('layouts.app')

@section('title', (env('APP_NAME') ?? 'SCA') . ' - Importar Estudiantes')

@section('content')
    <div class="">

        {{-- TOPBAR --}}
        <div class="bg-white px-6 lg:px-10 py-4 flex items-center gap-4" style="border-bottom: 1px solid #e8edf2;">

            <a href="{{ route('student.list') }}"
                class="group flex items-center justify-center w-8 h-8 rounded-lg flex-shrink-0 transition-all duration-200 active:scale-95 bg-slate-100 hover:bg-slate-200">
                <span
                    class="material-symbols-outlined text-[17px] text-slate-500 group-hover:-translate-x-0.5 transition-transform">
                    arrow_back
                </span>
            </a>

            <div class="w-px h-5 bg-slate-200 flex-shrink-0"></div>

            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('home') }}" class="font-medium transition-colors" style="color: #94a3b8;"
                    onmouseover="this.style.color='rgb(0,176,202)'" onmouseout="this.style.color='#94a3b8'">
                    Dashboard
                </a>
                <span class="material-symbols-outlined text-[14px]" style="color: #cbd5e1;">chevron_right</span>
                <a href="{{ route('student.list') }}" class="font-medium transition-colors" style="color: #94a3b8;"
                    onmouseover="this.style.color='rgb(0,176,202)'" onmouseout="this.style.color='#94a3b8'">
                    Estudiantes
                </a>
                <span class="material-symbols-outlined text-[14px]" style="color: #cbd5e1;">chevron_right</span>
                <span class="font-medium text-slate-600">Importar</span>
            </div>

            <span class="ml-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                style="background: rgba(0,176,202,0.1); color: rgb(0,140,165); border: 1px solid rgba(0,176,202,0.2);">
                Carga masiva
            </span>
        </div>

        {{-- ALERT CONTAINER --}}
        <div id="alertContainer" class="fixed top-16 right-1 md:right-5 z-[100] w-full max-w-sm pointer-events-none"></div>

        {{-- CONTENIDO --}}
        <div class="px-6 lg:px-10 py-6 max-w-4xl mx-auto space-y-4">

            {{-- INSTRUCCIONES --}}
            <div class="bg-white rounded-lg" style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                <div class="px-6 py-4 flex items-center gap-3" style="border-bottom: 1px solid #f1f5f9;">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg flex-shrink-0"
                        style="background: rgba(0,176,202,0.08); border: 1px solid rgba(0,176,202,0.15);">
                        <span class="material-symbols-outlined text-[16px]" style="color: rgb(0,140,165);">info</span>
                    </div>
                    <h2 class="text-sm font-semibold text-slate-700">Instrucciones de importación</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold text-white mt-0.5"
                                style="background: rgb(0,176,202);">1</div>
                            <div>
                                <p class="text-xs font-semibold text-slate-700 mb-0.5">Formato del archivo</p>
                                <p class="text-xs text-slate-500">El Excel debe tener 4 columnas en orden:</p>
                                <code class="text-[11px] mt-1 inline-block px-2 py-0.5 rounded"
                                    style="background: #f1f5f9; color: #475569;">grade | section | type_document |
                                    identify_number</code>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold text-white mt-0.5"
                                style="background: rgb(0,176,202);">2</div>
                            <div>
                                <p class="text-xs font-semibold text-slate-700 mb-0.5">Solo DNI peruano</p>
                                <p class="text-xs text-slate-500">Filas con type_document distinto a <code
                                        class="text-[11px] px-1 py-0.5 rounded"
                                        style="background: #f1f5f9; color: #475569;">DNI</code> se omiten automáticamente.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold text-white mt-0.5"
                                style="background: rgb(0,176,202);">3</div>
                            <div>
                                <p class="text-xs font-semibold text-slate-700 mb-0.5">Nombre de grado exacto</p>
                                <p class="text-xs text-slate-500">Debe coincidir con el sistema, ej: <code
                                        class="text-[11px] px-1 py-0.5 rounded"
                                        style="background: #f1f5f9; color: #475569;">PRIMERO</code>, <code
                                        class="text-[11px] px-1 py-0.5 rounded"
                                        style="background: #f1f5f9; color: #475569;">SEGUNDO</code>.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold text-white mt-0.5"
                                style="background: rgb(0,176,202);">4</div>
                            <div>
                                <p class="text-xs font-semibold text-slate-700 mb-0.5">Sin duplicados</p>
                                <p class="text-xs text-slate-500">Si el estudiante ya existe en ese grado y sección, se
                                    omite sin error.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- UPLOAD CARD --}}
            <div class="bg-white rounded-lg" id="uploadCard"
                style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                <div class="px-6 py-4 flex items-center gap-3" style="border-bottom: 1px solid #f1f5f9;">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg flex-shrink-0"
                        style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.15);">
                        <span class="material-symbols-outlined text-[16px]"
                            style="color: rgb(22,163,74);">upload_file</span>
                    </div>
                    <h2 class="text-sm font-semibold text-slate-700">Seleccionar archivo Excel</h2>
                </div>

                <div class="p-6">
                    {{-- Drop Zone --}}
                    <div id="dropZone" onclick="document.getElementById('fileInput').click()"
                        ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)"
                        class="relative rounded-lg transition-all duration-200 cursor-pointer"
                        style="border: 2px dashed #e2e8f0; background: #fafbfc; padding: 2.5rem 1.5rem; text-align: center;">

                        <div id="dropZoneIdle">
                            <div class="flex items-center justify-center w-14 h-14 rounded-xl mx-auto mb-3"
                                style="background: rgba(0,176,202,0.07); border: 1px solid rgba(0,176,202,0.15);">
                                <span class="material-symbols-outlined text-[28px]"
                                    style="color: rgb(0,140,165);">cloud_upload</span>
                            </div>
                            <p class="text-sm font-medium text-slate-600 mb-1">Arrastra tu archivo aquí</p>
                            <p class="text-xs text-slate-400">o <u class="text-slate-500 cursor-pointer">haz clic para
                                    seleccionar</u></p>
                            <p class="text-[11px] text-slate-400 mt-2">Formatos permitidos: .xlsx, .xls · Máx. 10 MB</p>
                        </div>

                        <div id="dropZoneSelected" class="hidden">
                            <div class="flex items-center justify-center gap-3">
                                <div class="flex items-center justify-center w-12 h-12 rounded-xl flex-shrink-0"
                                    style="background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2);">
                                    <span class="material-symbols-outlined text-[22px]"
                                        style="color: rgb(22,163,74);">check_circle</span>
                                </div>
                                <div class="text-left">
                                    <p id="selectedFileName" class="text-sm font-semibold text-slate-700"></p>
                                    <p id="selectedFileSize" class="text-xs text-slate-400 mt-0.5"></p>
                                </div>
                                <button onclick="clearFile(event)"
                                    class="ml-auto flex items-center justify-center w-7 h-7 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all"
                                    title="Quitar archivo">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <input type="file" id="fileInput" accept=".xlsx,.xls" class="hidden">

                    {{-- Footer del card --}}
                    <div class="mt-4 pt-4 flex flex-col-reverse sm:flex-row items-center justify-between gap-3"
                        style="border-top: 1px solid #f1f5f9;">
                        <p class="text-[11px] text-slate-400">
                            <span class="text-red-400">*</span> El proceso puede tardar varios minutos para archivos
                            grandes
                        </p>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <a href="{{ route('student.list') }}"
                                class="flex-1 sm:flex-none h-9 px-5 flex items-center justify-center gap-1.5 text-xs font-medium text-slate-600 rounded-lg transition-all bg-slate-100 hover:bg-slate-200 active:scale-95">
                                <span class="material-symbols-outlined text-[15px]">chevron_left</span>
                                Cancelar
                            </a>
                            <button id="btnImport" disabled onclick="startImport()"
                                class="flex-1 sm:flex-none h-9 px-6 flex items-center justify-center gap-1.5 text-white text-xs font-medium rounded-lg transition-all duration-200 active:scale-95"
                                style="background: rgb(0,176,202); box-shadow: 0 2px 8px rgba(0,176,202,0.3); opacity: 0.5; cursor: not-allowed;"
                                onmouseover="if(!this.disabled){this.style.background='rgb(190,214,0)'; this.style.boxShadow='0 2px 8px rgba(190,214,0,0.3)';}"
                                onmouseout="if(!this.disabled){this.style.background='rgb(0,176,202)'; this.style.boxShadow='0 2px 8px rgba(0,176,202,0.3)';}">
                                <span class="material-symbols-outlined text-[15px]">upload</span>
                                <span>Iniciar importación</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PROGRESS CARD --}}
            <div class="bg-white rounded-lg hidden" id="progressCard"
                style="border: 1px solid #e8edf2; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">

                <div class="px-6 py-4 flex items-center gap-3" style="border-bottom: 1px solid #f1f5f9;">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg flex-shrink-0"
                        style="background: rgba(0,176,202,0.08); border: 1px solid rgba(0,176,202,0.15);">
                        <span id="progressIcon" class="material-symbols-outlined text-[16px]"
                            style="color: rgb(0,140,165);">sync</span>
                    </div>
                    <h2 class="text-sm font-semibold text-slate-700">Progreso de importación</h2>
                    <span id="progressBadge"
                        class="ml-auto px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                        style="background: rgba(0,176,202,0.1); color: rgb(0,140,165); border: 1px solid rgba(0,176,202,0.2);">
                        Procesando...
                    </span>
                </div>

                <div class="p-6 space-y-5">

                    {{-- Barra de progreso --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-slate-500 font-medium">Progreso general</span>
                            <span id="progressPercent" class="text-xs font-bold" style="color: rgb(0,140,165);">0%</span>
                        </div>
                        <div class="w-full rounded-full overflow-hidden" style="height: 8px; background: #f1f5f9;">
                            <div id="progressBar" class="h-full rounded-full transition-all duration-500"
                                style="width: 0%; background: linear-gradient(90deg, rgb(0,176,202), rgb(190,214,0));">
                            </div>
                        </div>
                    </div>

                    {{-- Contadores --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                        <div class="rounded-lg p-3 text-center" style="background: #f8fafc; border: 1px solid #e8edf2;">
                            <div class="text-2xl font-bold text-slate-700 leading-none mb-1" id="cntTotal">0</div>
                            <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wide">Total</div>
                        </div>

                        <div class="rounded-lg p-3 text-center" style="background: #f8fafc; border: 1px solid #e8edf2;">
                            <div class="text-2xl font-bold leading-none mb-1" id="cntProcessed"
                                style="color: rgb(0,140,165);">0</div>
                            <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wide">Procesados</div>
                        </div>

                        <div class="rounded-lg p-3 text-center"
                            style="background: rgba(34,197,94,0.04); border: 1px solid rgba(34,197,94,0.15);">
                            <div class="text-2xl font-bold leading-none mb-1" id="cntSuccess"
                                style="color: rgb(22,163,74);">0</div>
                            <div class="text-[11px] font-medium uppercase tracking-wide"
                                style="color: rgba(22,163,74,0.7);">Exitosos</div>
                        </div>

                        <div class="rounded-lg p-3 text-center"
                            style="background: rgba(239,68,68,0.04); border: 1px solid rgba(239,68,68,0.15);">
                            <div class="text-2xl font-bold leading-none mb-1" id="cntErrors"
                                style="color: rgb(220,38,38);">0</div>
                            <div class="text-[11px] font-medium uppercase tracking-wide"
                                style="color: rgba(220,38,38,0.7);">Errores</div>
                        </div>
                    </div>

                    {{-- Omitidos (secundario) --}}
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg"
                        style="background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.15);">
                        <span class="material-symbols-outlined text-[15px]" style="color: rgb(217,119,6);">info</span>
                        <span class="text-xs text-slate-500">Omitidos (extranjeros / ya registrados):</span>
                        <span id="cntSkipped" class="text-xs font-bold ml-auto" style="color: rgb(217,119,6);">0</span>
                    </div>

                    {{-- Mensaje de estado --}}
                    <div id="statusMessage" class="flex items-center gap-2 px-4 py-3 rounded-lg text-sm"
                        style="background: rgba(0,176,202,0.06); border: 1px solid rgba(0,176,202,0.2); color: rgb(0,110,130);">
                        <span class="material-symbols-outlined text-[16px] animate-spin" id="statusIcon">sync</span>
                        <span id="statusText">Procesando registros, por favor espere...</span>
                    </div>

                    {{-- Log de errores --}}
                    <div id="errorLogWrap" class="hidden">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-[15px] text-red-500">warning</span>
                            <h6 class="text-xs font-semibold text-red-600 uppercase tracking-wide">Últimos errores /
                                omisiones</h6>
                        </div>
                        <div id="errorLog" class="rounded-lg p-3 text-[11px] leading-relaxed"
                            style="background: #fff8f8; border: 1px solid #fee2e2; max-height: 200px; overflow-y: auto; font-family: 'Courier New', monospace; color: #64748b;">
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <style>
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        #dropZone:hover {
            border-color: rgba(0, 176, 202, 0.5) !important;
            background: rgba(0, 176, 202, 0.03) !important;
        }

        #dropZone.drag-over {
            border-color: rgb(0, 176, 202) !important;
            background: rgba(0, 176, 202, 0.06) !important;
        }

        #btnImport:not(:disabled) {
            opacity: 1 !important;
            cursor: pointer !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        let selectedFile = null;
        let batchId = null;
        let pollInterval = null;

        // ─── File input ───────────────────────────────────────
        document.getElementById('fileInput').addEventListener('change', function() {
            if (this.files[0]) setFile(this.files[0]);
        });

        function handleDragOver(e) {
            e.preventDefault();
            document.getElementById('dropZone').classList.add('drag-over');
        }

        function handleDragLeave(e) {
            document.getElementById('dropZone').classList.remove('drag-over');
        }

        function handleDrop(e) {
            e.preventDefault();
            handleDragLeave(e);
            if (e.dataTransfer.files[0]) setFile(e.dataTransfer.files[0]);
        }

        function setFile(file) {
            const allowed = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel'
            ];
            if (!allowed.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
                showAlert('Solo se permiten archivos Excel (.xlsx o .xls)', 'error');
                return;
            }
            selectedFile = file;

            document.getElementById('dropZoneIdle').classList.add('hidden');
            document.getElementById('dropZoneSelected').classList.remove('hidden');
            document.getElementById('selectedFileName').textContent = file.name;
            document.getElementById('selectedFileSize').textContent = (file.size / 1024).toFixed(1) + ' KB';

            const btn = document.getElementById('btnImport');
            btn.disabled = false;
        }

        function clearFile(e) {
            e.stopPropagation();
            selectedFile = null;
            document.getElementById('fileInput').value = '';
            document.getElementById('dropZoneIdle').classList.remove('hidden');
            document.getElementById('dropZoneSelected').classList.add('hidden');
            const btn = document.getElementById('btnImport');
            btn.disabled = true;
        }

        // ─── Importar ─────────────────────────────────────────
        async function startImport() {
            if (!selectedFile) return;

            const btn = document.getElementById('btnImport');
            btn.disabled = true;
            btn.style.opacity = '0.6';

            // Mostrar panel de progreso
            document.getElementById('progressCard').classList.remove('hidden');
            document.getElementById('progressCard').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            const formData = new FormData();
            formData.append('file', selectedFile);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const res = await fetch('{{ route('student.import.upload') }}', {
                    method: 'POST',
                    body: formData
                });
                const json = await res.json();

                if (!json.success) {
                    setStatusError(json.message ?? 'Error al subir el archivo.');
                    return;
                }

                batchId = json.batch_id;
                document.getElementById('cntTotal').textContent = json.total;

                pollInterval = setInterval(pollStatus, 3000);

            } catch (err) {
                setStatusError('Error de red: ' + err.message);
            }
        }

        // ─── Polling ──────────────────────────────────────────
        async function pollStatus() {
            try {
                const res = await fetch(`{{ url('student/import/status') }}/${batchId}`);
                const data = await res.json();
                if (!data.success) return;

                document.getElementById('cntProcessed').textContent = data.processed;
                document.getElementById('cntSuccess').textContent = data.success_count;
                document.getElementById('cntSkipped').textContent = data.skipped;
                document.getElementById('cntErrors').textContent = data.errors.length;

                const pct = data.percent;
                document.getElementById('progressBar').style.width = pct + '%';
                document.getElementById('progressPercent').textContent = pct + '%';

                if (data.errors.length > 0) {
                    document.getElementById('errorLogWrap').classList.remove('hidden');
                    const logEl = document.getElementById('errorLog');
                    logEl.innerHTML = data.errors.map(e =>
                        `<div class="${e.startsWith('[ERROR]') ? 'text-red-500' : 'text-amber-600'} py-0.5">${escapeHtml(e)}</div>`
                    ).join('');
                    logEl.scrollTop = logEl.scrollHeight;
                }

                if (data.status === 'done') {
                    clearInterval(pollInterval);
                    pollInterval = null;

                    // Barra verde
                    document.getElementById('progressBar').style.background =
                        'linear-gradient(90deg, rgb(34,197,94), rgb(190,214,0))';

                    // Badge
                    const badge = document.getElementById('progressBadge');
                    badge.textContent = 'Completado';
                    badge.style.background = 'rgba(34,197,94,0.1)';
                    badge.style.color = 'rgb(22,163,74)';
                    badge.style.borderColor = 'rgba(34,197,94,0.2)';

                    // Icono
                    document.getElementById('progressIcon').textContent = 'check_circle';
                    document.getElementById('progressIcon').style.color = 'rgb(22,163,74)';

                    // Mensaje
                    const msg = document.getElementById('statusMessage');
                    msg.style.background = 'rgba(34,197,94,0.06)';
                    msg.style.borderColor = 'rgba(34,197,94,0.2)';
                    msg.style.color = 'rgb(21,128,61)';
                    document.getElementById('statusIcon').classList.remove('animate-spin');
                    document.getElementById('statusIcon').textContent = 'check_circle';
                    document.getElementById('statusText').textContent =
                        `Importación completada — ${data.success_count} registrados, ${data.skipped} omitidos, ${data.errors.length} errores.`;
                }

            } catch (e) {
                /* red temporal */ }
        }

        function setStatusError(msg) {
            clearInterval(pollInterval);
            const el = document.getElementById('statusMessage');
            el.style.background = 'rgba(239,68,68,0.06)';
            el.style.borderColor = 'rgba(239,68,68,0.2)';
            el.style.color = 'rgb(185,28,28)';
            document.getElementById('statusIcon').classList.remove('animate-spin');
            document.getElementById('statusIcon').textContent = 'error';
            document.getElementById('statusText').textContent = msg;
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

    </script>
@endpush
