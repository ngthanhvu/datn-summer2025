// useAddress.js
import axios from 'axios'

export const useAddress = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    const PROVINCE_API = axios.create({
        baseURL: 'https://provinces.open-api.vn/api/'
    })

    // Lấy danh sách tỉnh/thành phố
    const getProvinces = async () => {
        try {
            const response = await PROVINCE_API.get('p/')
            return response.data
        } catch (error) {
            console.error('Error getting provinces:', error)
            throw error
        }
    }

    // Lấy danh sách quận/huyện theo tỉnh
    const getDistricts = async (provinceCode) => {
        try {
            const response = await PROVINCE_API.get(`p/${provinceCode}?depth=2`)
            return response.data.districts
        } catch (error) {
            console.error('Error getting districts:', error)
            throw error
        }
    }

    // Lấy danh sách xã/phường theo quận/huyện
    const getWards = async (districtCode) => {
        try {
            const response = await PROVINCE_API.get(`d/${districtCode}?depth=2`)
            return response.data.wards
        } catch (error) {
            console.error('Error getting wards:', error)
            throw error
        }
    }

    // Lấy danh sách địa chỉ của user
    const getAddresses = async () => {
        try {
            const response = await API.get('/api/addresses')
            return response.data
        } catch (error) {
            console.error('Error getting addresses:', error)
            throw error
        }
    }

    // Thêm địa chỉ mới
    const createAddress = async (addressData) => {
        try {
            const response = await API.post('/api/addresses', addressData)
            return response.data
        } catch (error) {
            console.error('Error creating address:', error)
            throw error
        }
    }

    // Cập nhật địa chỉ
    const updateAddress = async (id, addressData) => {
        try {
            const response = await API.put(`/api/addresses/${id}`, addressData)
            return response.data
        } catch (error) {
            console.error('Error updating address:', error)
            throw error
        }
    }

    // Xóa địa chỉ
    const deleteAddress = async (id) => {
        try {
            const response = await API.delete(`/api/addresses/${id}`)
            return response.data
        } catch (error) {
            console.error('Error deleting address:', error)
            throw error
        }
    }

    return {
        getProvinces,
        getDistricts,
        getWards,
        getAddresses,
        createAddress,
        updateAddress,
        deleteAddress
    }
}