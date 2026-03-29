import initQrScanner from "../core/qrScanner";
import sendAttendance from "../services/attendanceService";

export default function initAssistanceTake() {
    const manualInput = document.querySelector("#manual-dni");
    const manualBtn = document.querySelector("#manual-btn");

    if (!manualInput) return;

    // ====================
    // Manual DNI
    // ====================

    manualBtn?.addEventListener("click", () => {
        const dni = manualInput.value.trim();
        if (dni) sendAttendance(dni);
    });

    manualInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            const dni = manualInput.value.trim();
            if (dni) sendAttendance(dni);
        }
    });

    // ====================
    // QR Scanner
    // ====================

    initQrScanner((dni) => {
        sendAttendance(dni);
    });
}
