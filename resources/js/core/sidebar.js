export function initSidebar() {
    const isDesktop = () => window.innerWidth >= 1024;
    let sidebarVisible = isDesktop();

    function applyState() {
        if (sidebarVisible) {
            $(".sidebar-panel").css("transform", "translateX(0)");
            $(".main-panel").css("margin-left", isDesktop() ? "308px" : "0px");
        } else {
            $(".sidebar-panel").css("transform", "translateX(-320px)");
            $(".main-panel").css("margin-left", "0px");
        }
    }

    // Estado inicial SIN animación
    $(".main-panel").css("transition", "none");
    applyState();
    setTimeout(() => $(".main-panel").css("transition", ""), 50);

    // Toggle
    $(".menubar").on("click", function () {
        sidebarVisible = !sidebarVisible;
        applyState();
    });

    // Resize
    $(window).on("resize", function () {
        sidebarVisible = isDesktop();
        applyState();
    });
}
