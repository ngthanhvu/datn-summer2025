import api from '../utils/api'

// Sử dụng instance axios chung từ utility
const API = api

export const useFlashsale = () => {
    const getFlashSales = async () => {
        try {
            const response = await API.get('/api/flash-sales')
            return response.data
        } catch (error) {
            console.error('❌ Error getting flash sales:', error)
            return []
        }
    }

    const getFlashSaleById = async (id) => {
        try {
            const response = await API.get(`/api/flash-sales/${id}`)
            return response.data
        } catch (error) {
            console.error(`❌ Error getting flash sale with ID ${id}:`, error)
            throw error
        }
    }

    const createFlashSale = async (payload) => {
        try {
            const response = await API.post('/api/flash-sales', payload)
            return response.data
        } catch (error) {
            console.error('❌ Error creating flash sale:', error)
            throw error
        }
    }

    const updateFlashSale = async (id, payload) => {
        try {
            const response = await API.put(`/api/flash-sales/${id}`, payload)
            return response.data
        } catch (error) {
            console.error(`❌ Error updating flash sale with ID ${id}:`, error)
            throw error
        }
    }

    const deleteFlashSale = async (id) => {
        try {
            const response = await API.delete(`/api/flash-sales/${id}`)
            return response.data
        } catch (error) {
            console.error(`❌ Error deleting flash sale with ID ${id}:`, error)
            throw error
        }
    }

    function getMainImage(product) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL.replace(/\/$/, "")

        if (
            product.product &&
            product.product.main_image &&
            product.product.main_image.image_path
        ) {
            let img = product.product.main_image.image_path
            if (img.startsWith("http://") || img.startsWith("https://")) return img
            if (!img.startsWith("/")) img = "/" + img
            if (!img.startsWith("/storage/")) img = "/storage" + img
            return baseUrl + img
        }

        let imagesArr = []
        if (product.product && Array.isArray(product.product.images)) {
            imagesArr = product.product.images
        } else if (Array.isArray(product.images)) {
            imagesArr = product.images
        }

        if (imagesArr.length > 0) {
            let mainImg = imagesArr.find((img) => img.is_main == 1) || imagesArr[0]
            let img = mainImg.image_path
            if (img.startsWith("http://") || img.startsWith("https://")) return img
            if (!img.startsWith("/")) img = "/" + img
            if (!img.startsWith("/storage/")) img = "/storage" + img
            return baseUrl + img
        }

        return "/default-product.png"
    }

    return {
        getFlashSales,
        getFlashSaleById,
        createFlashSale,
        updateFlashSale,
        deleteFlashSale,
        getMainImage
    }
}
