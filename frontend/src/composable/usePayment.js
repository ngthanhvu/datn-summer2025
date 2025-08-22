import axios from 'axios'
import api from '../utils/api'

export function usePayment() {
    const baseUrl = import.meta.env.VITE_API_BASE_URL || ''

    if (!baseUrl) {
        console.error('API Base URL is not configured')
        throw new Error('API configuration error')
    }

    // Sử dụng instance axios chung từ utility
    const API = api

    const generateVnpayUrl = async (orderData) => {
        const response = await API.post(`/api/payment/vnpay`, {
            order_data: orderData
        })
        return response.data
    }

    const generateMomoUrl = async (orderData) => {
        const response = await API.post(`/api/payment/momo`, {
            order_data: orderData
        })
        return response.data
    }

    return {
        generateVnpayUrl,
        generateMomoUrl,
    }
}

export default usePayment