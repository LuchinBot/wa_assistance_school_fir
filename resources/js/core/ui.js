import { toggleLoading } from "./loader";
import { initMenu } from "./menu";
import { initSidebar } from "./sidebar";
import { initDropdowns } from "./dropdown";

export default function initUI() {
    console.log("UI initialized");

    initMenu();
    initSidebar();
    initDropdowns();

    toggleLoading(false);
}
