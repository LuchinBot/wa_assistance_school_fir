export default function initQrScanner(onScan) {
    // Esperar a que la librería esté lista
    if (!window.Html5Qrcode) {
        console.warn("Html5Qrcode no disponible");
        return;
    }

    const html5QrCode = new Html5Qrcode("reader");

    const config = {
        fps: 12,
        qrbox: { width: 180, height: 180 },
        aspectRatio: 1,
    };

    // Actualizar badge de estado
    const camDot = document.getElementById("cam-dot");
    const camLabel = document.getElementById("cam-label");

    function setCamStatus(type, label) {
        if (camLabel) camLabel.textContent = label;
        if (camDot) {
            camDot.className = "cam-dot";
            if (type === "red") camDot.classList.add("red");
        }
    }

    Html5Qrcode.getCameras()
        .then((devices) => {
            if (!devices || !devices.length) {
                throw new Error("No se encontraron cámaras");
            }

            const cam =
                devices.find((d) => /back|rear|environment/i.test(d.label)) ||
                devices[devices.length - 1];

            return html5QrCode.start(
                { deviceId: { exact: cam.id } },
                config,
                (text) => onScan(text.trim()),
            );
        })
        .then(() => {
            setCamStatus("green", "Listo");
        })
        .catch(() => {
            // Intentar con facingMode como fallback
            html5QrCode
                .start({ facingMode: "environment" }, config, (text) =>
                    onScan(text.trim()),
                )
                .then(() => {
                    setCamStatus("green", "Listo");
                })
                .catch((err) => {
                    console.error("Error cámara:", err);
                    setCamStatus("red", "Sin cámara");
                    document
                        .getElementById("no-camera-msg")
                        ?.classList.remove("hidden");
                    document
                        .getElementById("scan-overlay")
                        ?.classList.add("hidden");
                });
        });
}
