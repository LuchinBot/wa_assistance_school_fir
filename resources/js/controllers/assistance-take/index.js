import initQrScanner from "../../core/qrScanner";
import { initAttendanceUI } from "./ui";

let lastQrValue = null;
let lastScanTime = 0;
const SCAN_COOLDOWN = 4000;

export default function initAssistanceTake() {
    initAttendanceUI();

    const manualBtn = document.getElementById("manual-btn");
    const manualInput = document.getElementById("manual-dni");

    manualBtn?.addEventListener("click", () => {
        const dni = manualInput.value.trim();
        if (dni) {
            sendAttendance(dni);
            manualInput.value = "";
        }
    });

    manualInput?.addEventListener("keydown", (e) => {
        if (e.key === "Enter") manualBtn.click();
    });

    initQrScanner((dni) => {
        const now = Date.now();
        if (dni === lastQrValue && now - lastScanTime < SCAN_COOLDOWN) return;
        lastQrValue = dni;
        lastScanTime = now;
        sendAttendance(dni);
    });
}

async function sendAttendance(dni) {
    if (!dni) return;
    showResult("", "Procesando...", dni, "loading");

    try {
        const res = await fetch("/assistance/attendance/validate", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf(),
            },
            body: JSON.stringify({
                dni,
                late: getLateStatus(),
                early: getEarlyStatus(),
                observation: getObservation(),
            }),
        });

        const data = await res.json();

        if (data.success) {
            if (data.already_registered) {
                showResult("", data.message, "", "warning");
                addRecent(dni, "Ya registrado", "warning");
            } else {
                showResult("", data.message, "", "success");
                addRecent(dni, "Presente", "success");
            }
        } else {
            showResult("", data.message, "", "warning");
            addRecent(dni, data.message, "warning");
        }
    } catch {
        showResult("", "Error de conexión", "", "error");
        addRecent(dni, "Error", "error");
    }
}

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? "";
}

function getEarlyStatus() {
    return document.getElementById("early-toggle")?.checked ? "early" : null;
}

function getLateStatus() {
    return document.getElementById("late-toggle")?.checked ? "late" : "present";
}
function getObservation() {
    return document.getElementById("obs-input")?.value.trim() ?? "";
}
