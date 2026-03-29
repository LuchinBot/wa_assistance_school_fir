export function initSidebar() {
    let sidebarVisible = true;

    $(".menubar").on("click", function () {
        if (sidebarVisible) {
            $(".sidebar-panel").css("transform", "translateX(-240px)");
            $(".main-panel").css("margin-left", "0px");
        } else {
            $(".sidebar-panel").css("transform", "translateX(0)");
            $(".main-panel").css("margin-left", "240px");
        }

        sidebarVisible = !sidebarVisible;
    });
}
