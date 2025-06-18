import axios from 'axios'

export const usePayment = () => {
    const config = useRuntimeConfig()
    const baseUrl = config.public.apiBaseUrl

    if (!baseUrl) {
        console.error('API Base URL is not configured')
        throw new Error('API configuration error')
    }

    const generateVnpayUrl = async (orderId, amount) => {
        console.log('Calling VNPay API with:', { orderId, amount })
        const response = await axios.post(`${baseUrl}/api/payment/vnpay`, {
            order_id: orderId,
            amount: amount
        })
        console.log('VNPay API response:', response.data)
        return response.data
    }

    const generateMomoUrl = async (orderId, amount) => {
        console.log('Calling MoMo API with:', { orderId, amount })
        const response = await axios.post(`${baseUrl}/api/payment/momo`, {
            order_id: orderId,
            amount: amount
        })
        console.log('MoMo API response:', response.data)
        return response.data
    }

    const generatePaypalUrl = async (orderId, amount) => {
        console.log('Calling PayPal API with:', { orderId, amount })
        const response = await axios.post(`${baseUrl}/api/payment/paypal`, {
            order_id: orderId,
            amount: amount
        })
        console.log('PayPal API response:', response.data)
        return response.data
    }

    return {
        generateVnpayUrl,
        generateMomoUrl,
        generatePaypalUrl
    }
}

export default usePayment

