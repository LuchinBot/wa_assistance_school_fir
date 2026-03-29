////////////////////////////////////////////////////
/*---------------- CORE MODAL --------------------*/
////////////////////////////////////////////////////

function openModal(selector) {
    const modal = $(selector);
    if (!modal.length) return;

    modal.removeClass("opacity-0 pointer-events-none");

    modal
        .find(".modal-content")
        .removeClass("scale-95 opacity-0")
        .addClass("scale-100 opacity-100");
}

function closeModal(selector) {
    const modal = $(selector);
    if (!modal.length) return;

    modal
        .find(".modal-content")
        .removeClass("scale-100 opacity-100")
        .addClass("scale-95 opacity-0");

    setTimeout(() => {
        modal.addClass("opacity-0 pointer-events-none");
    }, 200);
}

////////////////////////////////////////////////////
/*------------- GENERIC CONFIRM MODAL ------------*/
////////////////////////////////////////////////////

function openConfirmModal(modalSelector, onConfirm) {
    const modal = $(modalSelector);

    modal.removeData("confirm-callback");
    modal.data("confirm-callback", onConfirm);

    openModal(modalSelector);
}

function closeConfirmModal(modalSelector) {
    const modal = $(modalSelector);

    modal.removeData("confirm-callback");
    closeModal(modalSelector);
}

function setupConfirmModal(modalSelector, confirmBtn, cancelBtn) {
    const modal = $(modalSelector);

    if (modal.data("initialized")) return;
    modal.data("initialized", true);

    $(confirmBtn).on("click", function () {
        const cb = modal.data("confirm-callback");

        if (typeof cb === "function") {
            cb();
        }

        closeConfirmModal(modalSelector);
    });

    $(cancelBtn).on("click", () => closeConfirmModal(modalSelector));

    modal.on("click", function (e) {
        if (e.target === this) closeConfirmModal(modalSelector);
    });
}

////////////////////////////////////////////////////
/*------------------ MODAL DELETE ----------------*/
////////////////////////////////////////////////////

export function openDeleteModal(id, onConfirm) {
    openConfirmModal("#deleteModal", onConfirm);
}

export function closeDeleteModal() {
    closeConfirmModal("#deleteModal");
}

export function setupDeleteModal() {
    setupConfirmModal("#deleteModal", "#btnConfirmDelete", "#btnCancelDelete");
}

////////////////////////////////////////////////////
/*------------------ MODAL RESET -----------------*/
////////////////////////////////////////////////////

export function openResetModal(id, onConfirm) {
    openConfirmModal("#resetModal", onConfirm);
}

export function closeResetModal() {
    closeConfirmModal("#resetModal");
}

export function setupResetModal() {
    setupConfirmModal("#resetModal", "#btnConfirmReset", "#btnCancelReset");
}

////////////////////////////////////////////////////
/*------------------ MODAL OPENING ---------------*/
////////////////////////////////////////////////////

export function openOpeningModal() {
    openModal("#openingModal");

    const formatted = new Date().toLocaleDateString("es-PE", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });

    $("#opening_date_label").text(
        formatted.charAt(0).toUpperCase() + formatted.slice(1),
    );
}

export function closeOpeningModal() {
    closeModal("#openingModal");
}

////////////////////////////////////////////////////
/*------------------ MODAL CLOSING ---------------*/
////////////////////////////////////////////////////

export function openClosingModal() {
    openModal("#closingModal");
}

export function closeClosingModal() {
    closeModal("#closingModal");
}

////////////////////////////////////////////////////
/*------------------ MODAL PREVIEW IMAGE ---------*/
////////////////////////////////////////////////////

export function openPreviewModal(src) {
    const modal = $("#previewModal");
    const img = modal.find("img");

    img.attr("src", src);
    modal.removeClass("hidden");
}

export function closePreviewModal() {
    const modal = $("#previewModal");

    modal.addClass("hidden");
    modal.find("img").attr("src", "");
}

export function initPreviewModal() {
    const closeBtn = $("#closePreviewModal");
    if (!closeBtn.length) return;

    closeBtn.on("click", closePreviewModal);
}
