import axios from 'axios'

const usePayment = () => {
    const config = useRuntimeConfig()
    const baseUrl = config.public.apiBaseUrl

    if (!baseUrl) {
        console.error('API Base URL is not configured')
        throw new Error('API configuration error')
    }

    const generateVnpayUrl = async (orderId, amount) => {
        const response = await axios.post(`${baseUrl}/api/payment/vnpay`, {
            order_id: orderId,
            amount: amount
        })
    }

    const generateMomoUrl = async (orderId, amount) => {
        const response = await axios.post(`${baseUrl}/api/payment/momo`, {
            order_id: orderId,
            amount: amount
        })
    }

    const generatePaypalUrl = async (orderId, amount) => {
        const response = await axios.post(`${baseUrl}/api/payment/paypal`, {
            order_id: orderId,
            amount: amount
        })
    }

    return {
        generateVnpayUrl,
        generateMomoUrl,
        generatePaypalUrl
    }
}

