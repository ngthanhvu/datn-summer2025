import { ref } from 'vue'
import api from '../utils/api'

export const useHealth = () => {
    const health = ref({})
    const loading = ref(false)
    const error = ref('')

    // Instance API chung
    const API = api

    /**
     * Gọi API /api/health
     */
    const getHealth = async () => {
        loading.value = true
        error.value = null
        try {
            const res = await API.get('/api/health')
            health.value = res.data
            console.log(health.value)
            return health.value
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    /**
     * Lấy status tổng quát
     */
    const getStatus = () => {
        if (!health.value) return 'unknown'
        return health.value.status
    }

    /**
     * Lấy danh sách checks chi tiết
     */
    const getChecks = () => {
        if (!health.value) return {}
        return health.value.checks || {}
    }

    return {
        health,
        loading,
        error,
        getHealth,
        getStatus,
        getChecks,
    }
}
