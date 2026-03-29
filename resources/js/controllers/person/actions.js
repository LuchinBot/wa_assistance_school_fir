import {
    openOpeningModal,
    openClosingModal,
    openDeleteModal,
    closeOpeningModal,
    closeClosingModal,
} from "../../core/modal";

export default function registerPageActions(controller) {
    bindOpening(controller);
    bindClosing(controller);
}

/* =========================
   OPENING
========================= */

function bindOpening(controller) {
    const btn = document.querySelector("#btn-opening");
    if (!btn) return;

    btn.addEventListener("click", openOpeningModal);

    document
        .querySelector("#closeOpeningModal")
        ?.addEventListener("click", closeOpeningModal);

    document
        .querySelector("#formOpening")
        ?.addEventListener("submit", async (e) => {
            e.preventDefault();

            showLoading();

            try {
                await fetch(`/${controller}/opening`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrf(),
                        Accept: "application/json",
                    },
                    body: new FormData(e.target),
                });

                location.reload();
            } catch {
                hideLoading();
            }
        });
}

/* =========================
   CLOSING
========================= */

function bindClosing(controller) {
    const btn = document.querySelector("#btn-closing");
    if (!btn) return;

    btn.addEventListener("click", openClosingModal);

    // ✅ BOTÓN CANCELAR
    document
        .querySelector("#closeClosingModal")
        ?.addEventListener("click", closeClosingModal);

    // ✅ CONFIRMAR
    document
        .querySelector("#btnConfirmClosing")
        ?.addEventListener("click", async () => {
            showLoading();

            await fetch(`/${controller}/closing`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrf(),
                },
            });

            location.reload();
        });
}

/* =========================
   HELPERS
========================= */

function csrf() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

function showLoading() {
    document.querySelector("#loadingSpinner")?.classList.remove("hidden");
}

function hideLoading() {
    document.querySelector("#loadingSpinner")?.classList.add("hidden");
}
