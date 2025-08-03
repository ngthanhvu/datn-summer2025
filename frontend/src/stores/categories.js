// stores/useCategoryStore.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useCategories } from '../composable/useCategories'

export const useCategoryStore = defineStore('category', () => {
    const categories = ref([])
    const loading = ref(false)
    const error = ref(null)

    const { getCategories } = useCategories()

    const fetchCategories = async () => {
        loading.value = true
        error.value = null
        try {
            categories.value = await getCategories()
        } catch (err) {
            error.value = err
        } finally {
            loading.value = false
        }
    }

    return {
        categories,
        loading,
        error,
        fetchCategories
    }
})
