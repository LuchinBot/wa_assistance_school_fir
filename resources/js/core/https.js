import axios from "axios";
import { showError } from "./alert";

const http = axios.create({
    headers: {
        "X-Requested-With": "XMLHttpRequest",
    },
    withCredentials: true,
});

http.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 422) {
            return Promise.reject(error);
        }

        let message = "Error inesperado";

        if (error.response?.status === 403) message = "No tienes permisos";
        if (error.response?.status === 404) message = "Recurso no encontrado";
        if (error.response?.status === 500) message = "Error del servidor";

        showError(message);

        return Promise.reject(error);
    },
);

export default http;
