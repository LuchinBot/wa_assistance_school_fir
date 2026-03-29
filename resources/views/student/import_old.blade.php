{{-- resources/views/student/import.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">

            {{-- Header --}}
            <div class="d-flex align-items-center mb-4 mt-3">
                <a href="{{ route('student.list') }}" class="btn btn-sm btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Importar Estudiantes</h4>
                    <small class="text-muted">Carga masiva desde archivo Excel (.xlsx)</small>
                </div>
            </div>

            {{-- Instrucciones --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-semibold text-primary mb-3">
                        <i class="bi bi-info-circle me-1"></i> Instrucciones
                    </h6>
                    <ul class="mb-0 text-muted small">
                        <li>El archivo Excel debe tener exactamente <strong>4 columnas</strong> en este orden:</li>
                        <li class="ms-3 mt-1">
                            <code>grade | section | type_document | identify_number</code>
                        </li>
                        <li class="mt-2">Solo se importarán filas cuyo <strong>type_document sea DNI</strong>. Los estudiantes extranjeros se omitirán automáticamente.</li>
                        <li>Los grados deben coincidir <strong>exactamente</strong> con los registrados en el sistema (ej: <code>PRIMERO</code>, <code>SEGUNDO</code>).</li>
                        <li>Si el estudiante ya existe en ese grado y sección, será omitido (no se duplica).</li>
                        <li>El proceso puede tardar varios minutos según la cantidad de registros.</li>
                    </ul>
                </div>
            </div>

            {{-- Formulario --}}
            <div class="card border-0 shadow-sm mb-4" id="uploadCard">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">
                        <i class="bi bi-file-earmark-excel me-1 text-success"></i> Seleccionar archivo
                    </h6>

                    <div id="dropZone"
                         class="border border-2 border-dashed rounded-3 p-5 text-center text-muted mb-3"
                         style="cursor:pointer; border-color:#dee2e6 !important; transition: all .2s"
                         ondragover="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)"
                         ondrop="handleDrop(event)"
                         onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-cloud-upload fs-2 d-block mb-2 text-secondary"></i>
                        <span id="dropZoneText">Arrastra tu archivo aquí o <u>haz clic para seleccionar</u></span>
                    </div>

                    <input type="file" id="fileInput" accept=".xlsx,.xls" class="d-none">

                    <div class="d-flex justify-content-end mt-3">
                        <button id="btnImport" class="btn btn-primary px-4" disabled onclick="startImport()">
                            <i class="bi bi-upload me-1"></i> Iniciar importación
                        </button>
                    </div>
                </div>
            </div>

            {{-- Panel de progreso (oculto al inicio) --}}
            <div class="card border-0 shadow-sm d-none" id="progressCard">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">
                        <i class="bi bi-activity me-1 text-primary"></i> Progreso de importación
                    </h6>

                    {{-- Barra de progreso --}}
                    <div class="progress mb-2" style="height:22px;">
                        <div id="progressBar"
                             class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                             role="progressbar"
                             style="width: 0%">
                            0%
                        </div>
                    </div>

                    {{-- Contadores --}}
                    <div class="row text-center mt-3 mb-4">
                        <div class="col">
                            <div class="fs-4 fw-bold text-secondary" id="cntTotal">0</div>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col">
                            <div class="fs-4 fw-bold text-primary" id="cntProcessed">0</div>
                            <small class="text-muted">Procesados</small>
                        </div>
                        <div class="col">
                            <div class="fs-4 fw-bold text-success" id="cntSuccess">0</div>
                            <small class="text-muted">Exitosos</small>
                        </div>
                        <div class="col">
                            <div class="fs-4 fw-bold text-warning" id="cntSkipped">0</div>
                            <small class="text-muted">Omitidos</small>
                        </div>
                        <div class="col">
                            <div class="fs-4 fw-bold text-danger" id="cntErrors">0</div>
                            <small class="text-muted">Errores</small>
                        </div>
                    </div>

                    {{-- Estado --}}
                    <div id="statusMessage" class="alert alert-info py-2 text-center">
                        <i class="bi bi-hourglass-split me-1"></i> Procesando registros...
                    </div>

                    {{-- Log de errores --}}
                    <div id="errorLogWrap" class="d-none">
                        <h6 class="fw-semibold text-danger mb-2">
                            <i class="bi bi-exclamation-triangle me-1"></i> Últimos errores / omisiones
                        </h6>
                        <div id="errorLog"
                             class="bg-light border rounded p-3 small"
                             style="max-height:220px; overflow-y:auto; font-family:monospace; font-size:.8rem;">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedFile = null;
let batchId      = null;
let pollInterval = null;

// ─── Drag & Drop ──────────────────────────────────────
function handleDragOver(e) {
    e.preventDefault();
    document.getElementById('dropZone').style.borderColor = '#0d6efd';
    document.getElementById('dropZone').style.background  = '#f0f5ff';
}
function handleDragLeave(e) {
    document.getElementById('dropZone').style.borderColor = '';
    document.getElementById('dropZone').style.background  = '';
}
function handleDrop(e) {
    e.preventDefault();
    handleDragLeave(e);
    const file = e.dataTransfer.files[0];
    if (file) setFile(file);
}

document.getElementById('fileInput').addEventListener('change', function () {
    if (this.files[0]) setFile(this.files[0]);
});

function setFile(file) {
    const allowed = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                     'application/vnd.ms-excel'];
    if (!allowed.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
        alert('Solo se permiten archivos Excel (.xlsx o .xls)');
        return;
    }
    selectedFile = file;
    document.getElementById('dropZoneText').innerHTML =
        `<i class="bi bi-file-earmark-check text-success me-1"></i> <strong>${file.name}</strong> (${(file.size/1024).toFixed(1)} KB)`;
    document.getElementById('btnImport').disabled = false;
}

// ─── Iniciar importación ──────────────────────────────
async function startImport() {
    if (!selectedFile) return;

    document.getElementById('btnImport').disabled = true;
    document.getElementById('uploadCard').querySelector('.card-body').style.opacity = '.5';
    document.getElementById('progressCard').classList.remove('d-none');

    const formData = new FormData();
    formData.append('file', selectedFile);
    formData.append('_token', '{{ csrf_token() }}');

    try {
        const res = await fetch('{{ route("student.import.upload") }}', {
            method: 'POST',
            body: formData
        });

        const json = await res.json();

        if (!json.success) {
            showFinalError(json.message ?? 'Error al subir el archivo.');
            return;
        }

        batchId = json.batch_id;
        document.getElementById('cntTotal').textContent = json.total;

        // Iniciar polling cada 3 segundos
        pollInterval = setInterval(pollStatus, 3000);

    } catch (err) {
        showFinalError('Error de red: ' + err.message);
    }
}

// ─── Polling de estado ────────────────────────────────
async function pollStatus() {
    try {
        const res  = await fetch(`{{ url('student/import/status') }}/${batchId}`);
        const data = await res.json();

        if (!data.success) return;

        // Actualizar contadores
        document.getElementById('cntProcessed').textContent = data.processed;
        document.getElementById('cntSuccess').textContent   = data.success_count;
        document.getElementById('cntSkipped').textContent   = data.skipped;
        document.getElementById('cntErrors').textContent    = data.errors.length;

        // Barra
        const pct = data.percent;
        const bar = document.getElementById('progressBar');
        bar.style.width     = pct + '%';
        bar.textContent     = pct + '%';

        // Errores
        if (data.errors.length > 0) {
            document.getElementById('errorLogWrap').classList.remove('d-none');
            const logEl = document.getElementById('errorLog');
            logEl.innerHTML = data.errors
                .map(e => `<div class="${e.startsWith('[ERROR]') ? 'text-danger' : 'text-warning'}">${escapeHtml(e)}</div>`)
                .join('');
            logEl.scrollTop = logEl.scrollHeight;
        }

        // Finalizado
        if (data.status === 'done') {
            clearInterval(pollInterval);
            pollInterval = null;

            bar.classList.remove('progress-bar-animated', 'progress-bar-striped');
            bar.classList.add('bg-success');

            const msg = document.getElementById('statusMessage');
            msg.className = 'alert alert-success py-2 text-center';
            msg.innerHTML = `
                <i class="bi bi-check-circle me-1"></i>
                <strong>Importación completada.</strong>
                ${data.success_count} estudiantes registrados,
                ${data.skipped} omitidos,
                ${data.errors.length} errores.
            `;
        }

    } catch(e) {
        // silenciar errores de red temporales
    }
}

function showFinalError(msg) {
    clearInterval(pollInterval);
    document.getElementById('statusMessage').className = 'alert alert-danger py-2 text-center';
    document.getElementById('statusMessage').innerHTML =
        `<i class="bi bi-x-circle me-1"></i> ${escapeHtml(msg)}`;
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush