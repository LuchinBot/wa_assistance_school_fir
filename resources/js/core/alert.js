const container = $("#alertContainer");

const types = {
    success: {
        icon: "check_circle",
        iconColor: "rgb(0,176,202)",
        borderColor: "rgba(0,176,202,0.3)",
        accentColor: "rgb(0,176,202)",
    },
    error: {
        icon: "error",
        iconColor: "rgb(220,50,50)",
        borderColor: "rgba(220,50,50,0.25)",
        accentColor: "rgb(220,50,50)",
    },
    waiting: {
        icon: "sync",
        iconColor: "rgb(190,214,0)",
        borderColor: "rgba(190,214,0,0.3)",
        accentColor: "rgb(190,214,0)",
    },
};

function show(type, message, duration = 4000) {
    const config = types[type] ?? types.error;
    const isWaiting = type === "waiting";

    const html = `
        <div class="alert-item"
            style="
                background: white;
                border: 1px solid ${config.borderColor};
                border-left: 3px solid ${config.accentColor};
                border-radius: 10px;
                box-shadow: 0 4px 16px rgba(0,0,0,0.08), 0 1px 4px rgba(0,0,0,0.04);
                padding: 12px 14px;
                display: flex;
                align-items: flex-start;
                gap: 10px;
                opacity: 0;
                transform: translateX(16px);
                transition: opacity .25s ease, transform .25s ease;
                margin-bottom:8px;
            "
        >
            <span class="material-symbols-outlined ${isWaiting ? "animate-spin" : ""}"
                style="font-size:18px;color:${config.iconColor};margin-top:1px;">
                ${config.icon}
            </span>

            <div style="flex:1;min-width:0;">
                <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0;">
                    ${message}
                </p>
            </div>

            ${
                !isWaiting
                    ? `
            <button class="close-alert"
                style="
                    color:#94a3b8;
                    width:20px;
                    height:20px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    border-radius:6px;
                ">
                <span class="material-symbols-outlined" style="font-size:15px;">close</span>
            </button>`
                    : ""
            }
        </div>
    `;

    const $alert = $(html);

    container.append($alert);

    requestAnimationFrame(() => {
        $alert.css({
            opacity: "1",
            transform: "translateX(0)",
        });
    });

    $alert.find(".close-alert").on("click", () => hide($alert));

    if (!isWaiting && duration > 0) {
        const timer = setTimeout(() => hide($alert), duration);
        $alert.data("timer", timer);

        const $bar = $(`
            <div style="
                position:absolute;
                bottom:0;
                left:0;
                height:2px;
                width:100%;
                background:${config.accentColor};
                opacity:.4;
                border-radius:0 0 10px 10px;
                transition: width ${duration}ms linear;
            "></div>
        `);

        $alert.css("position", "relative").append($bar);

        requestAnimationFrame(() => {
            setTimeout(() => $bar.css("width", "0%"), 50);
        });
    }
}

function hide($el) {
    clearTimeout($el.data("timer"));

    $el.css({
        opacity: "0",
        transform: "translateX(16px)",
    });

    setTimeout(() => {
        $el.remove();
    }, 250);
}

export const showSuccess = (msg, duration) => show("success", msg, duration);
export const showError = (msg, duration) => show("error", msg, duration);
export const showWaiting = (msg) => show("waiting", msg, 0);
