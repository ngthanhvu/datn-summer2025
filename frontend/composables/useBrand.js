import axios from 'axios'

export const useBrand = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    const getBrands = async () => {
        const response = await API.get('/api/brands')
        return response.data
    }

    return {
        getBrands
    }
}