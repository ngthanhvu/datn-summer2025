import axios from 'axios'

export function usePayment() {
    // Lấy API base URL từ biến môi trường (.env)
    const baseUrl = import.meta.env.VITE_API_BASE_URL || ''

    if (!baseUrl) {
        console.error('API Base URL is not configured')
        throw new Error('API configuration error')
    }

    // Với backend mới, truyền toàn bộ orderData (chưa có orderId)
    const generateVnpayUrl = async (orderData) => {
        // orderData là object chứa toàn bộ thông tin đơn hàng
        const response = await axios.post(`${baseUrl}/api/payment/vnpay`, {
            order_data: orderData
        })
        return response.data
    }

    const generateMomoUrl = async (orderData) => {
        const response = await axios.post(`${baseUrl}/api/payment/momo`, {
            order_data: orderData
        })
        return response.data
    }

    const generatePaypalUrl = async (orderData) => {
        const response = await axios.post(`${baseUrl}/api/payment/paypal`, {
            order_data: orderData
        })
        return response.data
    }

    return {
        generateVnpayUrl,
        generateMomoUrl,
        generatePaypalUrl
    }
}

export default usePayment