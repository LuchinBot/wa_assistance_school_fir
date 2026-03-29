export default async function sendAttendance(dni) {
    const token = document.querySelector('meta[name="csrf-token"]').content;

    try {
        const res = await fetch("/assistance/attendance/validate", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify({ dni }),
        });

        const data = await res.json();

        window.dispatchEvent(
            new CustomEvent("attendance:result", { detail: data }),
        );
    } catch {
        window.dispatchEvent(new CustomEvent("attendance:error"));
    }
}
