import axios from 'axios'

export const useHome = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    // Lấy sản phẩm mới
    const getNewProducts = async (limit = 10) => {
        try {
            const response = await API.get('/api/products', {
                params: {
                    sort_by: 'created_at',
                    sort_direction: 'desc',
                    limit: limit
                }
            })
            return response.data
        } catch (error) {
            console.error('Error getting new products:', error)
            return []
        }
    }

    // Lấy sản phẩm theo danh mục
    const getProductsByCategory = async (categoryId = null, limit = 10) => {
        try {
            const params = { limit }
            if (categoryId) {
                params.category = categoryId
            }
            const response = await API.get('/api/products', { params })
            return response.data
        } catch (error) {
            console.error('Error getting products by category:', error)
            return []
        }
    }

    // Lấy thương hiệu với số lượng sản phẩm
    const getBrandsWithProductCount = async () => {
        try {
            const response = await API.get('/api/brands')
            const brands = response.data

            // Lấy số lượng sản phẩm cho mỗi thương hiệu
            const brandsWithCount = await Promise.all(
                brands.map(async (brand) => {
                    try {
                        const productsResponse = await API.get('/api/products', {
                            params: { brand: brand.id, limit: 1 }
                        })
                        return {
                            ...brand,
                            productCount: productsResponse.data.length > 0 ? productsResponse.data[0].total || 0 : 0
                        }
                    } catch (error) {
                        return {
                            ...brand,
                            productCount: 0
                        }
                    }
                })
            )

            return brandsWithCount
        } catch (error) {
            console.error('Error getting brands with product count:', error)
            return []
        }
    }

    // Lấy đánh giá gần nhất
    const getLatestReviews = async (limit = 6) => {
        try {
            const response = await API.get('/api/product-reviews', {
                params: {
                    sort_by: 'created_at',
                    sort_direction: 'desc',
                    limit: limit
                }
            })
            return response.data
        } catch (error) {
            console.error('Error getting latest reviews:', error)
            return []
        }
    }

    // Lấy thống kê đánh giá
    const getReviewStats = async () => {
        try {
            // Lấy tất cả đánh giá để tính toán
            const response = await API.get('/api/product-reviews')
            const reviews = response.data

            const totalReviews = reviews.length
            const averageRating = reviews.length > 0
                ? (reviews.reduce((sum, review) => sum + review.rating, 0) / reviews.length).toFixed(1)
                : 0
            const verifiedReviews = reviews.filter(review => review.is_verified).length

            return {
                totalReviews,
                averageRating: parseFloat(averageRating),
                verifiedReviews
            }
        } catch (error) {
            console.error('Error getting review stats:', error)
            return {
                totalReviews: 0,
                averageRating: 0,
                verifiedReviews: 0
            }
        }
    }

    // Lấy danh mục
    const getCategories = async () => {
        try {
            const response = await API.get('/api/categories')
            return response.data
        } catch (error) {
            console.error('Error getting categories:', error)
            return []
        }
    }

    // Format giá tiền
    const formatPrice = (price) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price)
    }

    // Format ngày tháng
    const formatDate = (dateString) => {
        const date = new Date(dateString)
        return date.toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        })
    }

    return {
        getNewProducts,
        getProductsByCategory,
        getBrandsWithProductCount,
        getLatestReviews,
        getReviewStats,
        getCategories,
        formatPrice,
        formatDate
    }
} 