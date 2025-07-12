import { defineStore } from 'pinia'
import axios from 'axios'

export const useReviewStore = defineStore('reviewStore', {
    state: () => ({
        reviews: [],
        isLoadingReviews: false,
        error: null
    }),
    actions: {
        async fetchReviews(productId) {
            this.isLoadingReviews = true
            this.error = null
            try {
                const res = await axios.get(`/api/products/${productId}/reviews`)
                this.reviews = res.data
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingReviews = false
            }
        },
        async addReview(reviewData) {
            this.isLoadingReviews = true
            this.error = null
            try {
                // Lấy token nếu có
                let token = ''
                if (typeof window !== 'undefined') {
                    token = localStorage.getItem('token') || ''
                }
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
                const res = await axios.post('/api/product-reviews', formData, {
                    headers: {
                        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
                        'Content-Type': 'multipart/form-data'
                    }
                })
                this.reviews.push(res.data)
                return res.data
            } catch (err) {
                this.error = err
                throw err
            } finally {
                this.isLoadingReviews = false
            }
        },
        async updateReview(reviewId, reviewData) {
            this.isLoadingReviews = true
            this.error = null
            try {
                let token = ''
                if (typeof window !== 'undefined') {
                    token = localStorage.getItem('token') || ''
                }
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
                const res = await axios.post(`/api/product-reviews/${reviewId}?_method=PUT`, formData, {
                    headers: {
                        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
                        'Content-Type': 'multipart/form-data'
                    }
                })
                return res.data
            } catch (err) {
                this.error = err
                throw err
            } finally {
                this.isLoadingReviews = false
            }
        },
        async removeReview(productId, reviewId) {
            this.isLoadingReviews = true
            this.error = null
            try {
                await axios.delete(`/api/products/${productId}/reviews/${reviewId}`)
                this.reviews = this.reviews.filter(r => r.id !== reviewId)
            } catch (err) {
                this.error = err
                throw err
            } finally {
                this.isLoadingReviews = false
            }
        },
        async getReviewsByProductSlug(productSlug, page = 1, perPage = 3, userId = null) {
            this.isLoadingReviews = true
            this.error = null
            try {
                const params = {
                    page,
                    per_page: perPage
                }
                if (userId) params.user_id = userId
                const res = await axios.get(`/api/product-reviews/product/${productSlug}`, { params })
                return res.data
            } catch (err) {
                this.error = err
                throw err
            } finally {
                this.isLoadingReviews = false
            }
        },
        async checkUserReview(userId, productSlug) {
            this.isLoadingReviews = true
            this.error = null
            try {
                const res = await axios.get(`/api/product-reviews/check/${userId}/${productSlug}`)
                return res.data
            } catch (err) {
                this.error = err
                throw err
            } finally {
                this.isLoadingReviews = false
            }
        }
    }
}) 