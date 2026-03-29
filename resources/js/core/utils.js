export const formatDate = (dateString) => {
    if (!dateString) return "-";

    const date = new Date(dateString);
    return date.toLocaleDateString("es-PE", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

export const formatNumber = (num) => num ?? 0;

export const formatText = (text) => text || "-";

export const formatPhoneForWhatsApp = (phone) => {
    if (!phone) return null;

    let clean = phone.replace(/\D/g, "");

    if (clean.startsWith("51") && clean.length === 11) {
        return clean;
    }

    if (clean.length === 9 && clean.startsWith("9")) {
        return "51" + clean;
    }

    if (clean.length === 10 && clean.startsWith("0")) {
        return "51" + clean.slice(1);
    }

    return null;
};
