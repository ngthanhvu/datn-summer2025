import axios from 'axios'

export const useHome = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

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

    const getBrandsWithProductCount = async () => {
        try {
            const response = await API.get('/api/brands')
            const brands = response.data

            return brands
        } catch (error) {
            console.error('Error getting brands with product count:', error)
            return []
        }
    }

    const getLatestReviews = async (perPage = 6) => {
        try {
            const response = await API.get('/api/reviews/latest', {
                params: { per_page: perPage }
            })
            return response.data.data
        } catch (error) {
            console.error('Error getting latest reviews:', error)
            return []
        }
    }

    const getReviewStats = async () => {
        try {
            const response = await API.get('/api/product-reviews');

            const reviews = Array.isArray(response.data) ? response.data : [];

            const totalReviews = reviews.length;

            const averageRating = totalReviews > 0
                ? (reviews.reduce((sum, review) => sum + review.rating, 0) / totalReviews).toFixed(1)
                : 0;

            const verifiedReviews = reviews.filter(review => review.is_verified).length;

            return {
                totalReviews,
                averageRating: parseFloat(averageRating),
                verifiedReviews
            };
        } catch (error) {
            console.error('Error getting review stats:', error);
            return {
                totalReviews: 0,
                averageRating: 0,
                verifiedReviews: 0
            };
        }
    };


    const getCategories = async () => {
        try {
            const response = await API.get('/api/categories')
            return response.data
        } catch (error) {
            console.error('Error getting categories:', error)
            return []
        }
    }

    const formatPrice = (price) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price)
    }

    const formatDate = (dateString) => {
        const date = new Date(dateString)
        return date.toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        })
    }

    const logBrandStats = async () => {
        try {
            const brands = await getBrandsWithProductCount()

            const totalBrands = brands.length
            const totalProducts = brands.reduce((sum, brand) => sum + (brand.products_count || 0), 0)
            const activeBrands = brands.filter(brand => brand.is_active).length

            const topBrands = brands
                .sort((a, b) => (b.products_count || 0) - (a.products_count || 0))
                .slice(0, 3)

            topBrands.forEach((brand, index) => {
            })

            const emptyBrands = brands.filter(brand => !brand.products_count || brand.products_count === 0)
            if (emptyBrands.length > 0) {
                emptyBrands.forEach(brand => {
                })
            }

            return {
                totalBrands,
                totalProducts,
                activeBrands,
                topBrands,
                emptyBrands
            }
        } catch (error) {
            console.error('❌ Lỗi khi lấy thống kê thương hiệu:', error)
            return null
        }
    }

    return {
        getNewProducts,
        getProductsByCategory,
        getBrandsWithProductCount,
        getLatestReviews,
        getReviewStats,
        getCategories,
        formatPrice,
        formatDate,
        logBrandStats
    }
} 