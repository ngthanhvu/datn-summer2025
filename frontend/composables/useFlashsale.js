import axios from 'axios'

export function useFlashsale() {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
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

    return {
        getFlashSales,
        createFlashSale,
        updateFlashSale,
        deleteFlashSale,
        getFlashSaleById
    }
}
