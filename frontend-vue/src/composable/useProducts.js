import axios from 'axios'

export const useProducts = () => {
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

    const API = axios.create({
        baseURL: apiBaseUrl,
        timeout: 10000
    })

    const cache = new Map()
    const CACHE_DURATION = 5 * 60 * 1000 // 5 phút

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

    const getProducts = async (filters = {}) => {
        try {
            const cacheKey = `products_${JSON.stringify(filters)}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const params = new URLSearchParams()

            if (filters.color) {
                params.append('color', filters.color)
            }
            if (filters.min_price) {
                params.append('min_price', filters.min_price)
            }
            if (filters.max_price) {
                params.append('max_price', filters.max_price)
            }
            if (filters.category) {
                if (Array.isArray(filters.category)) {
                    filters.category.forEach(id => params.append('category[]', id))
                } else {
                    params.append('category', filters.category)
                }
            }
            if (filters.brand) {
                if (Array.isArray(filters.brand)) {
                    filters.brand.forEach(id => params.append('brand[]', id))
                } else {
                    params.append('brand', filters.brand)
                }
            }
            if (filters.sort_by) {
                params.append('sort_by', filters.sort_by)
                params.append('sort_direction', filters.sort_direction || 'asc')
            }

            const response = await API.get(`/api/products?${params.toString()}`)
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting products:', error)
            throw error
        }
    }

    const getBrands = async () => {
        try {
            const cacheKey = 'brands'
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/brands')
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting brands:', error)
            return []
        }
    }

    const getCategories = async () => {
        try {
            const cacheKey = 'categories'
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/categories')
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting categories:', error)
            return []
        }
    }

    const getVariant = async () => {
        try {
            const cacheKey = 'variants'
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const res = await API.get('/api/variants')
            setCachedData(cacheKey, res.data)
            return res.data
        } catch (err) {
            console.error('Bug:', err)
            return []
        }
    }

    const getProductById = async (id) => {
        try {
            const cacheKey = `product_${id}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get(`/api/products/${id}`)
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting product:', error)
            throw error
        }
    }

    const getProductBySlug = async (slug) => {
        try {
            const cacheKey = `product_slug_${slug}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get(`/api/products/slug/${slug}`)
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting product by slug:', error)
            throw error
        }
    }

    const createProduct = async (product) => {
        try {
            const response = await API.post('/api/products', product, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error creating product:', error)
            throw error
        }
    }

    const updateProduct = async (id, product) => {
        try {
            const response = await API.post(`/api/products/${id}?_method=PUT`, product, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error updating product:', error)
            throw error
        }
    }

    const deleteProduct = async (id) => {
        try {
            const response = await API.delete(`/api/products/${id}`)
            return response.data
        } catch (error) {
            console.error('Error deleting product:', error)
            throw error
        }
    }

    const bulkDeleteProducts = async (ids) => {
        try {
            const response = await API.delete('/api/products/delete/bulk-delete', {
                data: { ids }
            })
            return response.data
        } catch (error) {
            console.error('Error bulk deleting products:', error)
            throw error
        }
    }

    const getTokenFromCookie = () => {
        const cookie = document.cookie
            .split('; ')
            .find(row => row.startsWith('token='))
        return cookie ? cookie.split('=')[1] : null
    }

    const toggleFavorite = async (productSlug) => {
        try {
            const token = getTokenFromCookie()
            if (!token) throw new Error('Bạn chưa đăng nhập')

            const favorites = await getFavoriteProducts()
            const exists = favorites.find(item => item.product_slug === productSlug)

            if (exists) {
                await API.delete(`/api/favorites/${productSlug}`, {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })
                return false
            } else {
                await API.post('/api/favorites', { product_slug: productSlug }, {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })
                return true
            }
        } catch (error) {
            throw error
        }
    }

    const getFavoriteProducts = async () => {
        try {
            const token = getTokenFromCookie()
            if (!token) return []

            const response = await API.get('/api/favorites', {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            })
            return response.data
        } catch (error) {
            return []
        }
    }

    const isFavorite = async (productSlug) => {
        try {
            const token = getTokenFromCookie()
            if (!token) return false

            const response = await API.get(`/api/favorites/check/${productSlug}`, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            })
            return response.data.is_favorite
        } catch (error) {
            return false
        }
    }

    const getFilterOptions = async () => {
        try {
            const response = await API.get('/api/products/filter-options')
            return response.data
        } catch (error) {
            console.error('Error getting filter options:', error)
            return null
        }
    }

    const searchProducts = async (query, filters = {}) => {
        try {
            const params = new URLSearchParams()
            if (query) {
                params.append('q', query)
            }
            if (filters.color && filters.color.length > 0) {
                if (Array.isArray(filters.color)) {
                    filters.color.forEach(c => params.append('color[]', c));
                } else {
                    params.append('color', filters.color);
                }
            }
            if (filters.min_price) {
                params.append('min_price', filters.min_price)
            }
            if (filters.max_price) {
                params.append('max_price', filters.max_price)
            }
            if (filters.category && filters.category.length > 0) {
                if (Array.isArray(filters.category)) {
                    filters.category.forEach(c => params.append('category[]', c));
                } else {
                    params.append('category', filters.category);
                }
            }
            if (filters.brand && filters.brand.length > 0) {
                if (Array.isArray(filters.brand)) {
                    filters.brand.forEach(b => params.append('brand[]', b));
                } else {
                    params.append('brand', filters.brand);
                }
            }
            if (filters.size && filters.size.length > 0) {
                if (Array.isArray(filters.size)) {
                    filters.size.forEach(s => params.append('size[]', s));
                } else {
                    params.append('size', filters.size);
                }
            }

            const response = await API.get(`/api/products/search?${params.toString()}`)
            return Array.isArray(response.data) ? response.data : []
        } catch (error) {
            console.error('Error searching products:', error)
            return []
        }
    }

    const getTemplateSheet = async () => {
        try {
            const res = await API.get('/api/products/import/template', {
                responseType: 'blob',
            })
            return res.data
        } catch (err) {
            console.error('Error fetch template', err)
        }
    }

    const importFile = async (file) => {
        try {
            const res = await API.post('/api/products/import', file, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                }
            })
            return res.data
        } catch (err) {
            console.log('Error import:', err)
        }
    }

    const logCategoryStats = async () => {
        try {
            const categories = await getCategories()

            console.log('📊 THỐNG KÊ DANH MỤC VÀ SẢN PHẨM (useProducts)')
            console.log('==================================================')

            const totalCategories = categories.length
            const totalProducts = categories.reduce((sum, cat) => sum + (cat.products_count || 0), 0)
            const activeCategories = categories.filter(cat => cat.is_active).length

            console.log(`📁 Tổng số danh mục: ${totalCategories}`)
            console.log(`📦 Tổng số sản phẩm: ${totalProducts}`)
            console.log(`✅ Danh mục đang hoạt động: ${activeCategories}`)
            console.log(`❌ Danh mục không hoạt động: ${totalCategories - activeCategories}`)
            console.log('')

            // Danh mục có nhiều sản phẩm nhất
            const topCategories = categories
                .sort((a, b) => (b.products_count || 0) - (a.products_count || 0))
                .slice(0, 3)

            console.log('🏆 TOP 3 DANH MỤC CÓ NHIỀU SẢN PHẨM NHẤT:')
            topCategories.forEach((cat, index) => {
                console.log(`${index + 1}. ${cat.name}: ${cat.products_count || 0} sản phẩm`)
            })
            console.log('')

            // Danh mục không có sản phẩm
            const emptyCategories = categories.filter(cat => !cat.products_count || cat.products_count === 0)
            if (emptyCategories.length > 0) {
                console.log('⚠️ DANH MỤC KHÔNG CÓ SẢN PHẨM:')
                emptyCategories.forEach(cat => {
                    console.log(`- ${cat.name}`)
                })
                console.log('')
            }

            console.log('==================================================')

            return {
                totalCategories,
                totalProducts,
                activeCategories,
                topCategories,
                emptyCategories
            }
        } catch (error) {
            console.error('❌ Lỗi khi lấy thống kê danh mục:', error)
            return null
        }
    }

    return {
        getProducts,
        getProductById,
        getProductBySlug,
        createProduct,
        updateProduct,
        deleteProduct,
        toggleFavorite,
        getFavoriteProducts,
        isFavorite,
        getBrands,
        getCategories,
        getFilterOptions,
        searchProducts,
        getTemplateSheet,
        importFile,
        bulkDeleteProducts,
        logCategoryStats,
        getVariant
    }
}