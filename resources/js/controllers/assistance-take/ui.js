const ICONS = {
    success: "check_circle",
    warning: "warning",
    error: "error",
    loading: "progress_activity",
};

let cooldown = false;
const recentData = [];

export function initAttendanceUI() {
    /* ── RELOJ ── */
    function updateClock() {
        const now = new Date();
        const t = [now.getHours(), now.getMinutes()]
            .map((n) => String(n).padStart(2, "0"))
            .join(":");
        const el = document.getElementById("live-time");
        if (el) el.textContent = t;
    }
    setInterval(updateClock, 1000);
    updateClock();

    /* ── TARDANZA ── */
    const lateCard = document.getElementById("late-card");
    const lateToggle = document.getElementById("late-toggle");
    const obsWrap = document.getElementById("obs-wrap");
    const obsInput = document.getElementById("obs-input");
    const latePill = document.getElementById("late-indicator");
    const topbar = document.getElementById("att-topbar-right");

    /* ── TEMPRANO ── */
    const earlyCard = document.getElementById("early-card");
    const earlyToggle = document.getElementById("early-toggle");
    const earlyPill = document.getElementById("early-indicator");

    function syncEarly() {
        const on = earlyToggle?.checked;
        earlyCard?.classList.toggle("active", on);
        earlyPill?.classList.toggle("att-hidden", !on);
        topbar?.classList.toggle("att-hidden", !on && !lateToggle?.checked);
    }

    earlyCard?.addEventListener("click", () => {
        if (lateToggle.checked) {
            // NUEVO
            earlyToggle && (earlyToggle.checked = false);
            syncEarly();
        }

        // Mutuamente excluyente con tardanza
        if (!earlyToggle.checked) {
            lateToggle.checked = false;
            syncLate();
        }
        earlyToggle.checked = !earlyToggle.checked;
        syncEarly();
    });
    earlyToggle?.addEventListener("change", syncEarly);

    function syncLate() {
        const on = lateToggle.checked;
        lateCard?.classList.toggle("active", on);
        obsWrap?.classList.toggle("att-hidden", !on);
        latePill?.classList.toggle("att-hidden", !on);
        topbar?.classList.toggle("att-hidden", !on);
        if (!on && obsInput) obsInput.value = "";
    }

    lateCard?.addEventListener("click", () => {
        lateToggle.checked = !lateToggle.checked;
        syncLate();
    });
    lateToggle?.addEventListener("change", syncLate);

    /* ── TOAST (showResult) ── */
    window.showResult = function (icon, text, sub, type) {
        const container = document.getElementById("toast-container");
        if (!container) return;

        const toast = document.createElement("div");
        toast.className = `att-toast ${type}`;
        toast.innerHTML = `
            <span class="material-symbols-outlined att-toast-icon">${ICONS[type] ?? "info"}</span>
            <div class="att-toast-body">
                <div class="att-toast-text">${text}</div>
                ${sub ? `<div class="att-toast-sub">${sub}</div>` : ""}
            </div>`;

        container.prepend(toast);

        setTimeout(
            () => {
                toast.style.transition = "opacity .4s, transform .4s";
                toast.style.opacity = "0";
                toast.style.transform = "translateY(-8px)";
                setTimeout(() => toast.remove(), 420);
            },
            type === "loading" ? 1200 : 3200,
        );
    };

    /* ── RECIENTES (addRecent) ── */
    function renderRecent() {
        const ul = document.getElementById("recent-list");
        if (!ul) return;

        if (!recentData.length) {
            ul.innerHTML =
                '<li class="att-recent-empty">Aún no hay registros en esta sesión</li>';
            return;
        }

        ul.innerHTML = recentData
            .slice(0, 15)
            .map((r) => {
                const t = r.time.toLocaleTimeString("es-PE", {
                    hour: "2-digit",
                    minute: "2-digit",
                });
                return `
            <li class="att-recent-item">
                <span class="att-recent-dot ${r.type}"></span>
                <span class="att-recent-dni">${r.dni}</span>
                <span class="att-recent-msg">${r.msg}</span>
                <span class="att-recent-time">${t}</span>
            </li>`;
            })
            .join("");
    }

    window.addRecent = function (dni, msg, type) {
        recentData.unshift({ dni, msg, type, time: new Date() });
        renderRecent();
    };

    document
        .getElementById("clear-recent-btn")
        ?.addEventListener("click", () => {
            recentData.length = 0;
            renderRecent();
        });
}
window.getEarlyStatus = function () {
    return document.getElementById("early-toggle")?.checked ? "early" : null;
};
