import axios from 'axios'
import { useAuth } from './useAuth'
import api from '../utils/api'

export const useReviews = () => {
    const { getToken } = useAuth()

    // Sử dụng instance axios chung từ utility
    const API = api

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

            // Log để debug
            console.log('Review data:', reviewData)
            console.log('Images:', reviewData.images)
            console.log('Images type:', typeof reviewData.images)
            console.log('Images length:', reviewData.images?.length)

            Object.entries(reviewData).forEach(([key, value]) => {
                if (key !== 'images') {
                    formData.append(key, value)
                    console.log(`Appending ${key}:`, value)
                }
            })

            if (reviewData.images && reviewData.images.length > 0) {
                reviewData.images.forEach((img, index) => {
                    console.log(`Appending image ${index}:`, img)
                    console.log(`Image type: ${typeof img}, constructor: ${img.constructor.name}`)
                    if (img instanceof File) {
                        console.log(`File details: name=${img.name}, type=${img.type}, size=${img.size}`)
                    }
                    formData.append('images[]', img)
                })
            } else {
                console.log('No images to append')
            }

            // Log FormData để debug
            console.log('FormData entries:')
            for (let [key, value] of formData.entries()) {
                console.log(`FormData ${key}:`, value)
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
            console.error('Error response:', err.response?.data)
            throw err
        }
    }

    const updateReview = async (reviewId, reviewData) => {
        try {
            const token = await getToken()
            const formData = new FormData()

            // Log để debug
            console.log('Update review data:', reviewData)
            console.log('Images:', reviewData.images)
            console.log('Images type:', typeof reviewData.images)
            console.log('Images length:', reviewData.images?.length)
            console.log('Delete image IDs:', reviewData.delete_image_ids)

            Object.entries(reviewData).forEach(([key, value]) => {
                if (!['images', 'delete_image_ids'].includes(key)) {
                    formData.append(key, value)
                    console.log(`Appending ${key}:`, value)
                }
            })

            if (reviewData.images && reviewData.images.length > 0) {
                reviewData.images.forEach((img, index) => {
                    console.log(`Appending image ${index}:`, img)
                    console.log(`Image type: ${typeof img}, constructor: ${img.constructor.name}`)
                    if (img instanceof File) {
                        console.log(`File details: name=${img.name}, type=${img.type}, size=${img.size}`)
                    }
                    formData.append('images[]', img)
                })
            } else {
                console.log('No images to append')
            }

            if (reviewData.delete_image_ids && reviewData.delete_image_ids.length > 0) {
                reviewData.delete_image_ids.forEach((id, index) => {
                    formData.append('delete_image_ids[]', id)
                    console.log(`Appending delete_image_id ${index}:`, id)
                })
            } else {
                console.log('No delete_image_ids to append')
            }

            // Log FormData để debug
            console.log('FormData entries:')
            for (let [key, value] of formData.entries()) {
                console.log(`FormData ${key}:`, value)
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
            console.error('Error response:', err.response?.data)
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
