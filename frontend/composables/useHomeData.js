import { useHomeStore } from '~/stores/useHomeStore'

export const useHomeData = () => {
    const homeStore = useHomeStore()

    // Load all home data with smart caching
    const loadHomeData = async (forceRefresh = false) => {
        try {
            if (forceRefresh) {
                homeStore.clearCache()
            }

            // Load all data in parallel for better performance
            const [
                categories,
                newProducts,
                brands,
                latestReviews,
                coupons
            ] = await Promise.all([
                homeStore.fetchCategories(),
                homeStore.fetchNewProducts(10),
                homeStore.fetchBrands(),
                homeStore.fetchLatestReviews(6),
                homeStore.fetchCoupons()
            ])

            return {
                categories,
                newProducts,
                brands,
                latestReviews,
                coupons
            }
        } catch (error) {
            console.error('Error loading home data:', error)
            throw error
        }
    }

    // Load specific data type
    const loadData = async (type, options = {}) => {
        try {
            switch (type) {
                case 'categories':
                    return await homeStore.fetchCategories()
                case 'products':
                    return await homeStore.fetchNewProducts(options.limit || 10)
                case 'brands':
                    return await homeStore.fetchBrands()
                case 'reviews':
                    return await homeStore.fetchLatestReviews(options.limit || 6)
                case 'coupons':
                    return await homeStore.fetchCoupons()
                case 'categoryProducts':
                    return await homeStore.fetchCategoryProducts(options.categoryId, options.limit || 20)
                default:
                    throw new Error(`Unknown data type: ${type}`)
            }
        } catch (error) {
            console.error(`Error loading ${type}:`, error)
            throw error
        }
    }

    // Check if data is available and fresh
    const hasData = (type) => {
        return homeStore.hasValidData(type)
    }

    // Get loading state for specific data type
    const isLoading = (type) => {
        const loadingStates = {
            categories: homeStore.isLoadingCategories,
            products: homeStore.isLoadingProducts,
            brands: homeStore.isLoadingBrands,
            reviews: homeStore.isLoadingReviews,
            coupons: homeStore.isLoadingCoupons
        }
        return loadingStates[type] || false
    }

    // Get data from store
    const getData = (type) => {
        const dataMap = {
            categories: homeStore.categories,
            products: homeStore.newProducts,
            brands: homeStore.brands,
            reviews: homeStore.latestReviews,
            coupons: homeStore.coupons,
            categoryProducts: homeStore.categoryProducts
        }
        return dataMap[type] || []
    }

    // Clear cache for specific data type or all
    const clearCache = (type) => {
        homeStore.clearCache(type)
    }

    // Reset all data
    const reset = () => {
        homeStore.reset()
    }

    return {
        loadHomeData,
        loadData,
        hasData,
        isLoading,
        getData,
        clearCache,
        reset,
        store: homeStore
    }
} 