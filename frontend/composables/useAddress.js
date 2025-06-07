// useAddress.js
import axios from 'axios'
import { ref } from 'vue'
import Swal from 'sweetalert2'

export const useAddress = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
    const notyf = useNuxtApp().$notyf

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    const PROVINCE_API = axios.create({
        baseURL: 'https://provinces.open-api.vn/api/'
    })

    const form = ref({
        full_name: '',
        phone: '',
        province: '',
        district: '',
        ward: '',
        street: ''
    })

    const errors = ref({})

    const getProvinces = async () => {
        try {
            const response = await PROVINCE_API.get('p/')
            return response.data
        } catch (error) {
            console.error('Error getting provinces:', error)
            throw error
        }
    }

    const getDistricts = async (provinceCode) => {
        try {
            const response = await PROVINCE_API.get(`p/${provinceCode}?depth=2`)
            return response.data.districts
        } catch (error) {
            console.error('Error getting districts:', error)
            throw error
        }
    }

    const getWards = async (districtCode) => {
        try {
            const response = await PROVINCE_API.get(`d/${districtCode}?depth=2`)
            return response.data.wards
        } catch (error) {
            console.error('Error getting wards:', error)
            throw error
        }
    }

    const getAddresses = async () => {
        try {
            const response = await API.get('/api/addresses')
            return response.data
        } catch (error) {
            console.error('Error getting addresses:', error)
            throw error
        }
    }

    const createAddress = async (addressData) => {
        try {
            const response = await API.post('/api/addresses', addressData)
            return response.data
        } catch (error) {
            console.error('Error creating address:', error)
            throw error
        }
    }

    const updateAddress = async (id, addressData) => {
        try {
            const response = await API.put(`/api/addresses/${id}`, addressData)
            return response.data
        } catch (error) {
            console.error('Error updating address:', error)
            throw error
        }
    }

    const deleteAddress = async (id) => {
        try {
            const result = await Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: "Bạn không thể hoàn tác sau khi xóa!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Có, xóa nó!',
                cancelButtonText: 'Hủy'
            })

            if (result.isConfirmed) {
                const response = await API.delete(`/api/addresses/${id}`)
                notyf.success('Đã xóa địa chỉ thành công!')
                return response.data
            }
            return null
        } catch (error) {
            console.error('Error deleting address:', error)
            notyf.error('Có lỗi xảy ra khi xóa địa chỉ.')
            throw error
        }
    }

    const validateForm = () => {
        const err = {}
        if (!form.value.full_name) err.full_name = 'Họ và tên không được để trống'
        else if (form.value.full_name.length > 100) err.full_name = 'Họ và tên tối đa 100 ký tự'
        if (!form.value.phone) err.phone = 'Số điện thoại không được để trống'
        else if (!/^(0|\+84)[1-9][0-9]{8,9}$/.test(form.value.phone)) err.phone = 'Số điện thoại không hợp lệ'
        if (!form.value.province) err.province = 'Vui lòng chọn tỉnh/thành phố'
        if (!form.value.district) err.district = 'Vui lòng chọn quận/huyện'
        if (!form.value.ward) err.ward = 'Vui lòng chọn xã/phường'
        if (!form.value.street) err.street = 'Thôn/xóm không được để trống'
        else if (form.value.street.length > 100) err.street = 'Thôn/xóm tối đa 100 ký tự'
        errors.value = err
        return Object.keys(err).length === 0
    }

    const getFullAddress = (address) => {
        return `${address.street}, ${address.ward}, ${address.district}, ${address.province}`
    }

    const resetForm = () => {
        form.value = {
            full_name: '',
            phone: '',
            province: '',
            district: '',
            ward: '',
            street: ''
        }
        errors.value = {}
    }

    const setFormData = (data) => {
        form.value = { ...data }
    }

    return {
        form,
        errors,
        getProvinces,
        getDistricts,
        getWards,
        getAddresses,
        createAddress,
        updateAddress,
        deleteAddress,
        validateForm,
        getFullAddress,
        resetForm,
        setFormData
    }
}