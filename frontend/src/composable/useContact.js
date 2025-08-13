import { ref } from 'vue'
import axios from 'axios'

export function useContact() {
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL // Dùng biến môi trường Vite

    const contacts = ref([])
    const contactDetail = ref(null)
    const loading = ref(false)
    const error = ref(null)
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })

    const fetchContacts = async (params = {}) => {
        loading.value = true
        error.value = null
        try {
            const queryParams = new URLSearchParams()

            // Thêm các tham số tìm kiếm và lọc
            if (params.search) queryParams.append('search', params.search)
            if (params.status) queryParams.append('status', params.status)
            if (params.per_page) queryParams.append('per_page', params.per_page)
            if (params.page) queryParams.append('page', params.page)

            const res = await axios.get(`${apiBaseUrl}/api/contacts?${queryParams.toString()}`)
            contacts.value = res.data.data
            pagination.value = {
                current_page: res.data.current_page,
                last_page: res.data.last_page,
                per_page: res.data.per_page,
                total: res.data.total
            }
            return res.data
        } catch (err) {
            error.value = err
            throw err
        } finally {
            loading.value = false
        }
    }

    const fetchContactDetail = async (id) => {
        loading.value = true
        error.value = null
        try {
            const res = await axios.get(`${apiBaseUrl}/api/contacts/${id}`)
            contactDetail.value = res.data
            return res.data
        } catch (err) {
            error.value = err
            throw err
        } finally {
            loading.value = false
        }
    }

    const replyContact = async (id, admin_reply) => {
        try {
            const res = await axios.post(`${apiBaseUrl}/api/contacts/${id}/reply`, { admin_reply })
            return res.data
        } catch (err) {
            error.value = err
            throw err
        }
    }

    const deleteContact = async (id) => {
        try {
            const res = await axios.delete(`${apiBaseUrl}/api/contacts/${id}`)
            return res.data
        } catch (err) {
            error.value = err
            throw err
        }
    }

    const getContactStats = async () => {
        try {
            const res = await axios.get(`${apiBaseUrl}/api/contacts/stats`)
            return res.data
        } catch (err) {
            error.value = err
            throw err
        }
    }

    const sendContact = async (form) => {
        try {
            const res = await axios.post(`${apiBaseUrl}/api/contacts`, form)
            return res.data
        } catch (err) {
            error.value = err
            throw err
        }
    }

    return {
        contacts,
        contactDetail,
        loading,
        error,
        pagination,
        fetchContacts,
        fetchContactDetail,
        replyContact,
        deleteContact,
        getContactStats,
        sendContact
    }
}