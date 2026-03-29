export function initMenu() {
    $(".toggle-section").on("click", function (e) {
        e.preventDefault();

        const $btn = $(this);
        const $section = $($btn.attr("href"));

        $section.toggleClass("open");
    });
}
