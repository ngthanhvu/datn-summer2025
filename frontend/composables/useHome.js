import axios from 'axios'

export const useHome = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl,
        timeout: 10000 // Thêm timeout 10 giây
    })

    // Cache system
    const cache = new Map()
    const CACHE_DURATION = 3 * 60 * 1000 // 3 phút

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

    const getNewProducts = async (limit = 10) => {
        try {
            const cacheKey = `new_products_${limit}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/products', {
                params: {
                    sort_by: 'created_at',
                    sort_direction: 'desc',
                    limit: limit
                }
            })
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting new products:', error)
            return []
        }
    }

    const getProductsByCategory = async (categoryId = null, limit = 10) => {
        try {
            const cacheKey = `products_category_${categoryId}_${limit}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const params = { limit }
            if (categoryId) {
                params.category = categoryId
            }
            const response = await API.get('/api/products', { params })
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting products by category:', error)
            return []
        }
    }

    const getBrandsWithProductCount = async () => {
        try {
            const cacheKey = 'brands_with_count'
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/brands')
            const brands = response.data
            setCachedData(cacheKey, brands)
            return brands
        } catch (error) {
            console.error('Error getting brands with product count:', error)
            return []
        }
    }

    const getLatestReviews = async (perPage = 6) => {
        try {
            const cacheKey = `latest_reviews_${perPage}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/reviews/latest', {
                params: { per_page: perPage }
            })
            const data = response.data.data
            setCachedData(cacheKey, data)
            return data
        } catch (error) {
            console.error('Error getting latest reviews:', error)
            return []
        }
    }

    const getReviewStats = async () => {
        try {
            const cacheKey = 'review_stats'
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/product-reviews');

            const reviews = Array.isArray(response.data) ? response.data : [];

            const totalReviews = reviews.length;

            const averageRating = totalReviews > 0
                ? (reviews.reduce((sum, review) => sum + review.rating, 0) / totalReviews).toFixed(1)
                : 0;

            const verifiedReviews = reviews.filter(review => review.is_verified).length;

            const stats = {
                totalReviews,
                averageRating: parseFloat(averageRating),
                verifiedReviews
            };

            setCachedData(cacheKey, stats)
            return stats
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
            const cacheKey = 'categories'
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/categories')
            const data = response.data
            setCachedData(cacheKey, data)
            return data
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

    const getRecommendedProducts = async () => {
        try {
            const userCookie = useCookie('user').value
            let params = {}
            if (userCookie) {
                const user = typeof userCookie === 'string' ? JSON.parse(userCookie) : userCookie
                if (user.gender) params.gender = user.gender
                if (user.dateOfBirth) params.dateOfBirth = user.dateOfBirth
                if (user.address) params.address = user.address
            }
            const response = await API.get('/api/products/recommend', { params })
            return response.data
        } catch (error) {
            console.error('Error getting recommended products:', error)
            return []
        }
    }

    // Clear cache function
    const clearCache = () => {
        cache.clear()
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
        logBrandStats,
        getRecommendedProducts,
        clearCache
    }
} 