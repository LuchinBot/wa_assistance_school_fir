export function showResult(icon, message, dni = "", type = "") {
    const box = document.getElementById("result-box");

    if (!box) return;

    box.innerHTML = `
        <div class="text-center p-6">
            <div class="text-4xl mb-2">${icon}</div>
            <div class="font-bold">${message}</div>
            ${dni ? `<div class="text-sm text-gray-500">${dni}</div>` : ""}
        </div>
    `;

    box.className =
        "transition-all rounded-xl " +
        {
            success: "bg-green-50 text-green-700",
            warning: "bg-yellow-50 text-yellow-700",
            error: "bg-red-50 text-red-700",
            loading: "bg-blue-50 text-blue-700",
        }[type];
}
