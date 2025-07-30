import axios from 'axios'
import { useAuth } from './useAuth'

export const useReviews = () => {
    const { getToken } = useAuth()

    const API = axios.create({
        baseURL: import.meta.env.VITE_API_BASE_URL,
        timeout: 10000
    })

    const getReviewsByProductSlug = async (productSlug, page = 1, perPage = 3, userId = null) => {
        try {
            const params = {
                page,
                per_page: perPage
            }
            if (userId) params.user_id = userId

            const res = await API.get(`/api/product-reviews/product/${productSlug}`, { params })
            return res.data
        } catch (err) {
            console.error('Lỗi khi lấy đánh giá sản phẩm:', err)
            throw err
        }
    }

    const addReview = async (reviewData) => {
        try {
            const token = await getToken()
            const formData = new FormData()

            Object.entries(reviewData).forEach(([key, value]) => {
                if (key !== 'images') formData.append(key, value)
            })

            if (reviewData.images) {
                reviewData.images.forEach(img => {
                    formData.append('images[]', img)
                })
            }

            const res = await API.post('/api/product-reviews', formData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data'
                }
            })
            return res.data
        } catch (err) {
            console.error('Lỗi khi thêm đánh giá:', err)
            throw err
        }
    }

    const updateReview = async (reviewId, reviewData) => {
        try {
            const token = await getToken()
            const formData = new FormData()

            Object.entries(reviewData).forEach(([key, value]) => {
                if (!['images', 'delete_image_ids'].includes(key)) {
                    formData.append(key, value)
                }
            })

            if (reviewData.images) {
                reviewData.images.forEach(img => {
                    formData.append('images[]', img)
                })
            }

            if (reviewData.delete_image_ids) {
                reviewData.delete_image_ids.forEach(id => {
                    formData.append('delete_image_ids[]', id)
                })
            }

            const res = await API.post(`/api/product-reviews/${reviewId}?_method=PUT`, formData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data'
                }
            })
            return res.data
        } catch (err) {
            console.error('Lỗi khi cập nhật đánh giá:', err)
            throw err
        }
    }

    const deleteReview = async (reviewId) => {
        try {
            const token = await getToken()
            const res = await API.delete(`/api/product-reviews/${reviewId}`, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            })
            return res.data
        } catch (err) {
            console.error('Lỗi khi xóa đánh giá:', err)
            throw err
        }
    }

    const checkUserReview = async (userId, productSlug) => {
        try {
            const token = await getToken()
            const res = await API.get(`/api/product-reviews/check/${userId}/${productSlug}`, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            })
            return res.data
        } catch (err) {
            console.error('Lỗi khi kiểm tra đánh giá của người dùng:', err)
            throw err
        }
    }

    return {
        getReviewsByProductSlug,
        addReview,
        updateReview,
        deleteReview,
        checkUserReview
    }
}
