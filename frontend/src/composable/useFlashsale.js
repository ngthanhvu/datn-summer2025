import axios from "axios";

export function useFlashsale() {
    const API_BASE_URL = import.meta.env.VITE_API_BASE_URL; 

    const getFlashSales = async () => {
        const res = await axios.get(`${API_BASE_URL}/api/flash-sales`);
        return res.data;
    };

    const getFlashSaleStatistics = async (id = null) => {
        const url = id
            ? `${API_BASE_URL}/api/flash-sales/${id}/statistics`
            : `${API_BASE_URL}/api/flash-sales/statistics`;
        const res = await axios.get(url);
        return res.data;
    };

    const toggleFlashSaleStatus = async (id, active) => {
        const res = await axios.patch(`${API_BASE_URL}/api/flash-sales/${id}/status`, { active });
        return res.data;
    };

    const createFlashSale = async (payload) => {
        const res = await axios.post(`${API_BASE_URL}/api/flash-sales`, payload);
        return res.data;
    };

    const updateFlashSale = async (id, payload) => {
        const res = await axios.put(
            `${API_BASE_URL}/api/flash-sales/${id}`,
            payload
        );
        return res.data;
    };

    const deleteFlashSale = async (id) => {
        const res = await axios.delete(`${API_BASE_URL}/api/flash-sales/${id}`);
        return res.data;
    };

    const processRepeat = async () => {
        const res = await axios.post(`${API_BASE_URL}/api/flash-sales/process-repeat`);
        return res.data;
    };

    const getFlashSaleById = async (id) => {
        const res = await axios.get(`${API_BASE_URL}/api/flash-sales/${id}`);
        return res.data;
    };

    function getMainImage(product) {
        let baseUrl = API_BASE_URL.replace(/\/$/, "");

        if (
            product.product &&
            product.product.main_image &&
            product.product.main_image.image_path
        ) {
            let img = product.product.main_image.image_path;
            if (img.startsWith("http://") || img.startsWith("https://")) return img;
            if (!img.startsWith("/")) img = "/" + img;
            if (!img.startsWith("/storage/")) img = "/storage" + img;
            return baseUrl + img;
        }

        let imagesArr = [];
        if (product.product && Array.isArray(product.product.images)) {
            imagesArr = product.product.images;
        } else if (Array.isArray(product.images)) {
            imagesArr = product.images;
        }

        if (imagesArr.length > 0) {
            let mainImg =
                imagesArr.find((img) => img.is_main == 1) || imagesArr[0];
            let img = mainImg.image_path;
            if (img.startsWith("http://") || img.startsWith("https://")) return img;
            if (!img.startsWith("/")) img = "/" + img;
            if (!img.startsWith("/storage/")) img = "/storage" + img;
            return baseUrl + img;
        }

        return "/default-product.png";
    }

    return {
        getFlashSales,
        createFlashSale,
        updateFlashSale,
        deleteFlashSale,
        getFlashSaleById,
        getMainImage,
        getFlashSaleStatistics,
        toggleFlashSaleStatus,
        processRepeat,
    };
}