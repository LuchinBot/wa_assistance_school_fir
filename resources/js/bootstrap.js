import axios from "axios";
import $ from "jquery";

window.$ = window.jQuery = $;
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.withCredentials = true;

// CSRF automático
const token = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token;
} else {
    console.warn("CSRF token not found");
}

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            window.location.href = "/login";
        }

        if (error.response?.status === 419) {
            location.reload();
        }

        if (error.response?.status === 500) {
            console.error("Server Error");
        }

        return Promise.reject(error);
    },
);
