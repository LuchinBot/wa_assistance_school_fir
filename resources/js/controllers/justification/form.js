import initFormHandler from "../../modules/form/FormEngine";
import initTomSelect from "../../plugins/tomselect";

const MOTIVOS = [
    {
        label: "Enfermedad",
        texto: "El estudiante presenta un cuadro de enfermedad que le impide asistir con normalidad a clases, encontrándose actualmente en proceso de recuperación bajo supervisión médica.",
    },
    {
        label: "Transporte",
        texto: "El estudiante enfrenta dificultades de transporte que le impiden asistir a clases en la fecha indicada.",
    },
    {
        label: "Emergencia familiar",
        texto: "El estudiante se encuentra atendiendo una emergencia familiar de carácter urgente que requiere su presencia y que le impide asistir a clases en la fecha indicada.",
    },
    {
        label: "Viaje o comisión",
        texto: "El estudiante se encuentra realizando un viaje o comisión debidamente autorizada, lo que le impide estar presente en las sesiones señaladas.",
    },
    {
        label: "Duelo",
        texto: "El estudiante atraviesa un período de duelo por el fallecimiento de un familiar cercano, motivo por el cual se solicita la justificación de su inasistencia.",
    },
    {
        label: "Otro motivo",
        texto: "El estudiante tiene un motivo excepcional que le impide asistir a clases.",
    },
];

function insertarSelectorMotivos() {
    const reasonField = document.querySelector('textarea[name="reason"]');
    if (!reasonField) return;

    const wrapper = reasonField.closest(".space-y-1\\.5");
    if (!wrapper) return;

    // Crear selector de motivos rápidos
    const selectorWrapper = document.createElement("div");
    selectorWrapper.className = "flex flex-wrap gap-2 mb-2";

    MOTIVOS.forEach((motivo) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.textContent = motivo.label;
        btn.dataset.texto = motivo.texto;
        btn.className =
            "motivo-btn px-2.5 py-1 text-xs font-medium rounded-md border transition-all duration-150 active:scale-95 cursor-pointer";
        btn.style.cssText =
            "background: #f1f5f9; border-color: #e2e8f0; color: #64748b;";

        btn.addEventListener("mouseenter", () => {
            btn.style.background = "rgba(0,176,202,0.08)";
            btn.style.borderColor = "rgba(0,176,202,0.4)";
            btn.style.color = "rgb(0,140,162)";
        });
        btn.addEventListener("mouseleave", () => {
            if (!btn.classList.contains("active-motivo")) {
                btn.style.background = "#f1f5f9";
                btn.style.borderColor = "#e2e8f0";
                btn.style.color = "#64748b";
            }
        });
        btn.addEventListener("click", () => {
            // Desactivar otros botones
            selectorWrapper.querySelectorAll(".motivo-btn").forEach((b) => {
                b.classList.remove("active-motivo");
                b.style.background = "#f1f5f9";
                b.style.borderColor = "#e2e8f0";
                b.style.color = "#64748b";
            });

            // Activar este
            btn.classList.add("active-motivo");
            btn.style.background = "rgba(0,176,202,0.12)";
            btn.style.borderColor = "rgba(0,176,202,0.5)";
            btn.style.color = "rgb(0,140,162)";

            // Llenar el textarea
            if (motivo.texto) {
                reasonField.value = motivo.texto;
                reasonField.dispatchEvent(new Event("input"));
            } else {
                reasonField.value = "";
                reasonField.focus();
            }
        });

        selectorWrapper.appendChild(btn);
    });

    // Insertar antes del textarea
    wrapper.insertBefore(selectorWrapper, reasonField);
}

function initSesionCondicional() {
    const typeSelect = document.getElementById("type");
    const sesionWrapper = document
        .getElementById("codassistance_session")
        ?.closest(".space-y-1\\.5");

    if (!typeSelect || !sesionWrapper) return;

    function actualizarSesion() {
        const valor = typeSelect.value;
        const esTemporal = valor === "JT";

        if (esTemporal) {
            sesionWrapper.style.opacity = "1";
            sesionWrapper.style.pointerEvents = "auto";
            sesionWrapper
                .querySelectorAll("select, input")
                .forEach((el) => (el.disabled = false));
            sesionWrapper.style.transition =
                "opacity 0.2s ease, transform 0.2s ease";
            sesionWrapper.style.transform = "translateY(0)";
        } else {
            sesionWrapper.style.opacity = "0.4";
            sesionWrapper.style.pointerEvents = "none";
            sesionWrapper
                .querySelectorAll("select, input")
                .forEach((el) => (el.disabled = true));
            sesionWrapper.style.transition =
                "opacity 0.2s ease, transform 0.2s ease";
            sesionWrapper.style.transform = "translateY(-2px)";

            // Limpiar selección si no es temporal
            const sesionSelect = document.getElementById(
                "codassistance_session",
            );
            if (sesionSelect && sesionSelect.tomselect) {
                sesionSelect.tomselect.clear();
            } else if (sesionSelect) {
                sesionSelect.value = "";
            }
        }
    }

    // Escuchar cambio en el select de tipo
    // Compatible con TomSelect (que emite eventos en el select original)
    typeSelect.addEventListener("change", actualizarSesion);

    // Ejecutar al cargar para respetar valor existente
    actualizarSesion();
}

export default function initJustificationForm() {
    initFormHandler({
        formSelector: "#mainForm",
        submitButtonSelector: "#btnSubmit",
        baseUrl: "/justification",
    });

    initTomSelect();

    // Esperar a que TomSelect inicialice antes de conectar listeners
    setTimeout(() => {
        initSesionCondicional();
        insertarSelectorMotivos();
    }, 100);
}
