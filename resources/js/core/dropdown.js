export function initDropdowns() {
    $(".box-user").on("click", function (e) {
        e.stopPropagation();
        $(".box-user-collapse").fadeToggle(200);
    });

    $(document).on("click", function () {
        $(".box-user-collapse").fadeOut(150);
    });
}
