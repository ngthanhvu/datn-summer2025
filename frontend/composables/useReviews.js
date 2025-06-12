
import axios from 'axios'
import { useAuth } from './useAuth'

export const useReviews = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
    const API = axios.create({ baseURL: apiBaseUrl })
    const { getToken } = useAuth()

    // Get all reviews for a product by slug
    const getReviewsByProductSlug = async (productSlug) => {
        try {
            const response = await API.get(`/api/product-reviews/product/${productSlug}`)
            return response.data
        } catch (error) {
            console.error('Lỗi không thể hiển thị:', error)
            throw error
        }
    }

    // Add a new review
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
            return response.data
        } catch (error) {
            console.error('Error adding review:', error)
            throw error
        }
    }

    // Update an existing review
    const updateReview = async (reviewId, reviewData) => {
        try {
            const token = await getToken()
            const formData = new FormData()
            
            Object.keys(reviewData).forEach(key => {
                if (key !== 'images' && key !== 'delete_image_ids') {
                    formData.append(key, reviewData[key])
                }
            })
            
            // Add images to be uploaded
            if (reviewData.images) {
                reviewData.images.forEach(image => {
                    formData.append('images[]', image)
                })
            }
            
            // Add image IDs to be deleted
            if (reviewData.delete_image_ids) {
                reviewData.delete_image_ids.forEach(id => {
                    formData.append('delete_image_ids[]', id)
                })
            }
            
            const response = await API.post(`/api/product-reviews/${reviewId}?_method=PUT`, formData, {
                headers: {
                    'Authorization': `Bearer ${token}`, // Sửa tokenValue thành token
                    'Content-Type': 'multipart/form-data'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error updating review:', error)
            throw error
        }
    }

    // Delete a review
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
            console.error('Error deleting review:', error)
            throw error
        }
    }

    // Check if user has already reviewed a product
    const checkUserReview = async (userId, productSlug) => {
        try {
            const token = await getToken()
            const response = await API.get(`/api/product-reviews/check/${userId}/${productSlug}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
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
        checkUserReview // Thêm hàm mới vào return
    }
}