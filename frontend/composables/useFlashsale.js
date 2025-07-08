import axios from 'axios'

export function useFlashsale() {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
    const BACKEND_URL = config.public.NUXT_API_BASE_URL || ''

    const getFlashSales = async () => {
        const res = await axios.get(`${apiBaseUrl}/api/flash-sales`)
        return res.data
    }

    const createFlashSale = async (payload) => {
        const res = await axios.post(`${apiBaseUrl}/api/flash-sales`, payload)
        return res.data
    }

    const updateFlashSale = async (id, payload) => {
        const res = await axios.put(`${apiBaseUrl}/api/flash-sales/${id}`, payload)
        return res.data
    }

    const deleteFlashSale = async (id) => {
        const res = await axios.delete(`${apiBaseUrl}/api/flash-sales/${id}`)
        return res.data
    }

    const getFlashSaleById = async (id) => {
        const res = await axios.get(`${apiBaseUrl}/api/flash-sales/${id}`)
        return res.data
    }

    function getMainImage(product) {
        if (product.product && product.product.main_image && product.product.main_image.image_path) {
            let img = product.product.main_image.image_path
            if (img.startsWith('http://') || img.startsWith('https://')) return img
            if (!img.startsWith('/')) img = '/' + img
            if (!img.startsWith('/storage/')) img = '/storage' + img
            return apiBaseUrl.replace(/\/$/, '') + img
        }
        let imagesArr = []
        if (product.product && Array.isArray(product.product.images)) {
            imagesArr = product.product.images
        } else if (Array.isArray(product.images)) {
            imagesArr = product.images
        }
        if (imagesArr.length > 0) {
            let mainImg = imagesArr.find(img => img.is_main == 1) || imagesArr[0]
            let img = mainImg.image_path
            if (img.startsWith('http://') || img.startsWith('https://')) return img
            if (!img.startsWith('/')) img = '/' + img
            if (!img.startsWith('/storage/')) img = '/storage' + img
            return apiBaseUrl.replace(/\/$/, '') + img
        }
        return '/default-product.png'
    }

    return {
        getFlashSales,
        createFlashSale,
        updateFlashSale,
        deleteFlashSale,
        getFlashSaleById,
        getMainImage
    }
}
