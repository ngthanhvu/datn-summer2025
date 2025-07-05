import axios from 'axios'
import { useAuth } from './useAuth'

export const useReviews = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
    const API = axios.create({
        baseURL: apiBaseUrl,
        timeout: 10000 // Thêm timeout 10 giây
    })
    const { getToken } = useAuth()

    // Cache system
    const cache = new Map()
    const CACHE_DURATION = 2 * 60 * 1000 // 2 phút cho reviews

    const getCachedData = (key) => {
        const cached = cache.get(key)
        if (cached && Date.now() - cached.timestamp < CACHE_DURATION) {
            return cached.data
        }
        return null
    }

    const setCachedData = (key, data) => {
        cache.set(key, {
            data,
            timestamp: Date.now()
        })
    }

    const clearCache = () => {
        cache.clear()
    }

    const getReviewsByProductSlug = async (productSlug, page = 1, perPage = 3) => {
        try {
            const cacheKey = `reviews_${productSlug}_${page}_${perPage}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get(`/api/product-reviews/product/${productSlug}`, {
                params: {
                    page,
                    per_page: perPage
                }
            })
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Lỗi không thể hiển thị:', error)
            throw error
        }
    }

    const addReview = async (reviewData) => {
        try {
            const token = await getToken()
            const formData = new FormData()
            Object.keys(reviewData).forEach(key => {
                if (key !== 'images') {
                    formData.append(key, reviewData[key])
                }
            })
            if (reviewData.images) {
                reviewData.images.forEach(image => {
                    formData.append('images[]', image)
                })
            }
            const response = await API.post('/api/product-reviews', formData, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data'
                }
            })
            // Clear cache khi có thay đổi
            clearCache()
            return response.data
        } catch (error) {
            console.error('Error adding review:', error)
            throw error
        }
    }

    const updateReview = async (reviewId, reviewData) => {
        try {
            const token = await getToken()
            const formData = new FormData()

            Object.keys(reviewData).forEach(key => {
                if (key !== 'images' && key !== 'delete_image_ids') {
                    formData.append(key, reviewData[key])
                }
            })

            if (reviewData.images) {
                reviewData.images.forEach(image => {
                    formData.append('images[]', image)
                })
            }

            if (reviewData.delete_image_ids) {
                reviewData.delete_image_ids.forEach(id => {
                    formData.append('delete_image_ids[]', id)
                })
            }

            const response = await API.post(`/api/product-reviews/${reviewId}?_method=PUT`, formData, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data'
                }
            })
            // Clear cache khi có thay đổi
            clearCache()
            return response.data
        } catch (error) {
            console.error('Error updating review:', error)
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
            // Clear cache khi có thay đổi
            clearCache()
            return response.data
        } catch (error) {
            console.error('Error deleting review:', error)
            throw error
        }
    }

    const checkUserReview = async (userId, productSlug) => {
        try {
            const cacheKey = `user_review_${userId}_${productSlug}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const token = await getToken()
            const response = await API.get(`/api/product-reviews/check/${userId}/${productSlug}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error checking user review:', error)
            throw error
        }
    }

    return {
        getReviewsByProductSlug,
        addReview,
        updateReview,
        deleteReview,
        checkUserReview,
        clearCache
    }
}