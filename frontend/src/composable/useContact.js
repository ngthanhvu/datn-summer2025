import { ref } from 'vue'
import axios from 'axios'

export function useContact() {
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL // Dùng biến môi trường Vite

    const contacts = ref([])
    const contactDetail = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const fetchContacts = async () => {
        loading.value = true
        error.value = null
        try {
            const res = await axios.get(`${apiBaseUrl}/api/contacts`)
            contacts.value = res.data
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
        fetchContacts,
        fetchContactDetail,
        replyContact,
        deleteContact,
        sendContact
    }
}
