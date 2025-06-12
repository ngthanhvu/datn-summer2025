import axios from 'axios'
import { useAuth } from './useAuth'

export const useAdminReviews = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
    const API = axios.create({ baseURL: apiBaseUrl })
    const { getToken } = useAuth()

    const getAllReviews = async () => {
        try {
            const token = await getToken()
            const response = await API.get('/api/product-reviews', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Lỗi khi lấy danh sách đánh giá:', error)
            throw error
        }
    }

    const updateReviewStatus = async (reviewId, status) => {
        try {
            const token = await getToken()
            const response = await API.put(`/api/product-reviews/${reviewId}`, {
                is_approved: status === 'approved',
                is_hidden: status === 'rejected'
            }, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Lỗi khi cập nhật trạng thái đánh giá:', error)
            throw error
        }
    }

    const deleteReview = async (reviewId) => {
        try {
            const token = await getToken()
            const response = await API.delete(`/api/product-reviews/${reviewId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Lỗi khi xóa đánh giá:', error)
            throw error
        }
    }

    const addAdminReply = async (reviewId, replyData) => {
        try {
            const token = await getToken()
            const response = await API.post(`/api/product-reviews/${reviewId}/admin-reply`, replyData, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Lỗi khi thêm phản hồi admin:', error)
            throw error
        }
    }

    const getReviewsByCategory = async (categoryId) => {
        try {
            const token = await getToken()
            const response = await API.get(`/api/product-reviews/category/${categoryId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Lỗi khi lấy đánh giá theo danh mục:', error)
            throw error
        }
    }

    const getReviewsByBrand = async (brandId) => {
        try {
            const token = await getToken()
            const response = await API.get(`/api/product-reviews/brand/${brandId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Lỗi khi lấy đánh giá theo thương hiệu:', error)
            throw error
        }
    }

    const updateAdminReply = async (replyId, content) => {
        try {
            const token = await getToken()
            const response = await API.put(`/api/product-reviews/${replyId}/admin-reply`, {
                content: content
            }, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Lỗi khi cập nhật phản hồi admin:', error)
            throw error
        }
    }

    return {
        getAllReviews,
        updateReviewStatus,
        addAdminReply,
        updateAdminReply,
        getReviewsByCategory,
        getReviewsByBrand,
        deleteReview
    }
}