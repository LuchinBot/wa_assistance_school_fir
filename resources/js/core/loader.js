export function toggleLoading(show) {
    const $overlay = $("#loadingOverlay");
    const $content = $("#mainContent");

    if (show) {
        $overlay
            .show()
            .removeClass("opacity-0 pointer-events-none")
            .addClass("opacity-100");

        $content.addClass("opacity-0 scale-[0.98] pointer-events-none");
    } else {
        $overlay
            .removeClass("opacity-100")
            .addClass("opacity-0 pointer-events-none");

        $content
            .removeClass("opacity-0 scale-[0.98] pointer-events-none")
            .addClass("opacity-100");

        $overlay.one("transitionend", function () {
            if (!$overlay.hasClass("opacity-100")) $(this).hide();
        });
    }
}
