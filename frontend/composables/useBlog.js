import axios from "axios";
import { ref } from "vue";

export const useBlog = () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.public.apiBaseUrl;

    const API = axios.create({
        baseURL: apiBaseUrl
    })

}