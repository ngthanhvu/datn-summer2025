import { reactive } from 'vue'

export function useLazyLoading() {
    const loadingStates = reactive({
        flashSale: false,
        trending: false,
        coupons: false,
        newProducts: false,
        banner: false,
        categoriesProducts: false,
        hotBrands: false,
        reviewLatest: false,
        featuredBlogs: false
    })

    const loadedStates = reactive({
        flashSale: false,
        trending: false,
        coupons: false,
        newProducts: false,
        banner: false,
        categoriesProducts: false,
        hotBrands: false,
        reviewLatest: false,
        featuredBlogs: false
    })

    const data = reactive({
        flashSale: null,
        trending: null,
        coupons: null,
        newProducts: null,
        banner: null,
        categoriesProducts: null,
        hotBrands: null,
        reviewLatest: null,
        featuredBlogs: null
    })

    const isComponentLoaded = (componentName) => {
        return loadedStates[componentName] || false
    }

    const isComponentLoading = (componentName) => {
        return loadingStates[componentName] || false
    }

    const loadComponentData = async (componentName, apiCall) => {
        if (isComponentLoaded(componentName) || isComponentLoading(componentName)) {
            return data[componentName]
        }

        try {
            loadingStates[componentName] = true

            const result = await apiCall()

            data[componentName] = result
            loadedStates[componentName] = true

            return result
        } catch (error) {
            console.error(`Error loading ${componentName} data:`, error)
            throw error
        } finally {
            loadingStates[componentName] = false
        }
    }

    const resetComponent = (componentName) => {
        loadingStates[componentName] = false
        loadedStates[componentName] = false
        data[componentName] = null
    }

    const resetAll = () => {
        Object.keys(loadingStates).forEach(key => {
            resetComponent(key)
        })
    }

    const preloadCriticalComponents = async (apiCalls) => {
        const promises = []

        if (apiCalls.banner && !isComponentLoaded('banner')) {
            promises.push(loadComponentData('banner', apiCalls.banner))
        }

        if (apiCalls.categoriesList && !isComponentLoaded('categoriesList')) {
            promises.push(loadComponentData('categoriesList', apiCalls.categoriesList))
        }

        try {
            await Promise.all(promises)
        } catch (error) {
            console.error('Error preloading critical components:', error)
        }
    }

    return {
        loadingStates,
        loadedStates,
        data,
        isComponentLoaded,
        isComponentLoading,
        loadComponentData,
        resetComponent,
        resetAll,
        preloadCriticalComponents
    }
} 