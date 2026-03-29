const CONFIG = {
    baseUrl: "/public/statistics/data",
    charts: {},
};

$(document).ready(function () {
    initializeElements();
    loadAllStatistics();
});

function initializeElements() {
    // Chart.js configuración global
    Chart.defaults.font.family = "'Inter', 'system-ui', sans-serif";
    Chart.defaults.plugins.legend.position = "bottom";
}

function showLoading() {
    $("#loadingOverlay").removeClass("hidden").addClass("flex");
}

function hideLoading() {
    $("#loadingOverlay").addClass("hidden").removeClass("flex");
}

async function loadAllStatistics() {
    showLoading();

    try {
        await Promise.all([
            loadGeneralStats(),
            loadFilialStats(),
            loadAgeStats(),
            loadYearStats(),
        ]);
    } catch (error) {
        console.error("Error cargando estadísticas:", error);
        alert(
            "Error al cargar las estadísticas. Por favor, recargue la página.",
        );
    } finally {
        hideLoading();
    }
}

async function loadGeneralStats() {
    try {
        const response = await $.ajax({
            url: CONFIG.baseUrl,
            method: "GET",
            dataType: "json",
            data: { type: "general" },
        });

        console.log("General Stats Response:", response);

        // Validar que la respuesta tenga los datos esperados
        if (!response || typeof response.total === "undefined") {
            throw new Error("Respuesta inválida del servidor");
        }

        $("#totalPeriodistas").text(response.total.toLocaleString());
        $("#habilitados").text(response.habilitados.toLocaleString());
        $("#inhabilitados").text(response.inhabilitados.toLocaleString());
        $("#porcentajeHabilitados").text(
            `${response.porcentaje_habilitados}% del total`,
        );

        const porcentajeInhabilitados = (
            100 - response.porcentaje_habilitados
        ).toFixed(2);
        $("#porcentajeInhabilitados").text(
            `${porcentajeInhabilitados}% del total`,
        );

        animateNumbers();
    } catch (error) {
        console.error("Error en estadísticas generales:", error);
        $("#totalPeriodistas").text("Error");
        $("#habilitados").text("Error");
        $("#inhabilitados").text("Error");
    }
}

async function loadFilialStats() {
    try {
        const response = await $.ajax({
            url: CONFIG.baseUrl,
            method: "GET",
            dataType: "json",
            data: { type: "filial" },
        });

        console.log("Filial Stats Response:", response);

        if (!response || !response.filiales) {
            throw new Error("Respuesta inválida del servidor");
        }

        createFilialChart(response);
    } catch (error) {
        console.error("Error en estadísticas por filial:", error);
        $("#filialChart")
            .parent()
            .html(
                '<p class="text-center text-blue-500">Error al cargar datos</p>',
            );
    }
}

async function loadAgeStats() {
    try {
        const response = await $.ajax({
            url: CONFIG.baseUrl,
            method: "GET",
            dataType: "json",
            data: { type: "age" },
        });

        console.log("Age Stats Response:", response);

        if (!response || !response.rangos) {
            throw new Error("Respuesta inválida del servidor");
        }

        createAgeChart(response);
    } catch (error) {
        console.error("Error en estadísticas por edad:", error);
        $("#ageChart")
            .parent()
            .html(
                '<p class="text-center text-blue-500">Error al cargar datos</p>',
            );
    }
}

async function loadYearStats() {
    try {
        const response = await $.ajax({
            url: CONFIG.baseUrl,
            method: "GET",
            dataType: "json",
            data: { type: "year" },
        });

        console.log("Year Stats Response:", response);

        if (!response || !response.years) {
            throw new Error("Respuesta inválida del servidor");
        }

        createYearChart(response);
    } catch (error) {
        console.error("Error en estadísticas por año:", error);
        $("#yearChart")
            .parent()
            .html(
                '<p class="text-center text-blue-500">Error al cargar datos</p>',
            );
    }
}

function createFilialChart(data) {
    const ctx = document.getElementById("filialChart");

    if (CONFIG.charts.filial) {
        CONFIG.charts.filial.destroy();
    }

    CONFIG.charts.filial = new Chart(ctx, {
        type: "bar",
        data: {
            labels: data.filiales,
            datasets: [
                {
                    label: "Habilitados",
                    data: data.habilitados,
                    backgroundColor: "rgba(34, 197, 94, 0.7)",
                    borderColor: "rgba(34, 197, 94, 1)",
                    borderWidth: 2,
                },
                {
                    label: "Inhabilitados",
                    data: data.inhabilitados,
                    backgroundColor: "rgba(239, 68, 68, 0.7)",
                    borderColor: "rgba(239, 68, 68, 1)",
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `${
                                context.dataset.label
                            }: ${context.parsed.y.toLocaleString()}`;
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                    },
                },
            },
        },
    });
}

function createAgeChart(data) {
    const ctx = document.getElementById("ageChart");

    if (CONFIG.charts.age) {
        CONFIG.charts.age.destroy();
    }

    const colors = [
        "rgba(99, 102, 241, 0.7)",
        "rgba(139, 92, 246, 0.7)",
        "rgba(168, 85, 247, 0.7)",
        "rgba(217, 70, 239, 0.7)",
        "rgba(236, 72, 153, 0.7)",
        "rgba(244, 63, 94, 0.7)",
    ];

    CONFIG.charts.age = new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: data.rangos,
            datasets: [
                {
                    data: data.cantidades,
                    backgroundColor: colors,
                    borderColor: colors.map((c) => c.replace("0.7", "1")),
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "right",
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const total = context.dataset.data.reduce(
                                (a, b) => a + b,
                                0,
                            );
                            const percentage = (
                                (context.parsed / total) *
                                100
                            ).toFixed(1);
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        },
                    },
                },
            },
        },
    });
}

function createYearChart(data) {
    const ctx = document.getElementById("yearChart");

    if (CONFIG.charts.year) {
        CONFIG.charts.year.destroy();
    }

    CONFIG.charts.year = new Chart(ctx, {
        type: "line",
        data: {
            labels: data.years,
            datasets: [
                {
                    label: "Nuevas Afiliaciones",
                    data: data.cantidades,
                    backgroundColor: "rgba(59, 130, 246, 0.2)",
                    borderColor: "rgba(59, 130, 246, 1)",
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: "rgba(59, 130, 246, 1)",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `Afiliaciones: ${context.parsed.y.toLocaleString()}`;
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                    },
                },
                x: {
                    grid: {
                        display: false,
                    },
                },
            },
        },
    });
}

function animateNumbers() {
    $(".text-3xl").each(function () {
        const $this = $(this);
        const text = $this.text();

        if (
            text !== "-" &&
            text !== "Error" &&
            !isNaN(text.replace(/,/g, ""))
        ) {
            $this.css("opacity", "0").animate({ opacity: 1 }, 600);
        }
    });
}

function query(id) {
    $.ajax({
        url: `${CONFIG.baseUrl}/${id}`,
        method: "GET",
        dataType: "json",
        success: function (response) {
            console.log("Query success:", response);
        },
        error: function (xhr, status, error) {
            console.error("Query error:", error);
        },
    });
}
