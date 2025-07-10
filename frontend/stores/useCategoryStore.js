import { defineStore } from 'pinia'
import axios from 'axios'

export const useCategoryStore = defineStore('categoryStore', {
    state: () => ({
        categories: [],
        isLoadingCategories: false,
        error: null
    }),
    actions: {
        async fetchCategories() {
            this.isLoadingCategories = true
            this.error = null
            try {
                const res = await axios.get('/api/categories')
                this.categories = res.data
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingCategories = false
            }
        },
        async createCategory(categoryFormData) {
            try {
                const res = await axios.post('/api/categories', categoryFormData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json'
                    }
                })
                // Sau khi tạo mới, fetch lại danh sách để đồng bộ
                await this.fetchCategories()
                return res.data
            } catch (err) {
                this.error = err
                throw err
            }
        },
        async deleteCategory(id) {
            try {
                await axios.delete(`/api/categories/${id}`)
                await this.fetchCategories()
            } catch (err) {
                this.error = err
                throw err
            }
        },
        async bulkDeleteCategories(selectedCategories) {
            try {
                const ids = Array.from(selectedCategories)
                await axios.post('/api/categories/bulk-delete', { ids })
                await this.fetchCategories()
            } catch (err) {
                this.error = err
                throw err
            }
        },
        async fetchCategoryById(id) {
            try {
                const res = await axios.get(`/api/categories/${id}`)
                return res.data
            } catch (err) {
                this.error = err
                throw err
            }
        },
        async updateCategory(id, categoryFormData) {
            try {
                const res = await axios.post(`/api/categories/${id}?_method=PUT`, categoryFormData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json'
                    }
                })
                await this.fetchCategories()
                return res.data
            } catch (err) {
                this.error = err
                throw err
            }
        }
    }
}) 