<template>
    <div class="products-page">
        <div class="page-header">
            <div class="header-content">
                <h1>Quản lý sản phẩm</h1>
                <p class="text-gray-600">Quản lý danh sách sản phẩm của bạn</p>
            </div>
        </div>

        <!-- Loading state -->
        <div v-if="isLoading" class="bg-white rounded-lg shadow-sm p-6">
            <!-- Header skeleton -->
            <div class="mb-6">
                <div class="skeleton-loader h-8 w-48 mb-2"></div>
                <div class="skeleton-loader h-4 w-64"></div>
            </div>

            <!-- Filters skeleton -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row gap-4 mb-4">
                    <div class="skeleton-loader h-10 w-full md:w-64"></div>
                    <div class="skeleton-loader h-10 w-full md:w-32"></div>
                    <div class="skeleton-loader h-10 w-full md:w-32"></div>
                    <div class="skeleton-loader h-10 w-full md:w-32"></div>
                </div>
                <div class="flex flex-col md:flex-row gap-2">
                    <div class="skeleton-loader h-10 w-32"></div>
                    <div class="skeleton-loader h-10 w-32"></div>
                    <div class="skeleton-loader h-10 w-32"></div>
                </div>
            </div>

            <!-- Table skeleton -->
            <div class="overflow-hidden rounded-lg border border-gray-200">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="px-6 py-3">
                                <div class="skeleton-loader h-4 w-4 mx-auto"></div>
                            </th>
                            <th class="px-3 py-2">
                                <div class="skeleton-loader h-4 w-8 mx-auto"></div>
                            </th>
                            <th v-for="i in 9" :key="i" class="px-3 py-2">
                                <div class="skeleton-loader h-4 w-20 mx-auto"></div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="n in 5" :key="n" class="border-b border-gray-200">
                            <td class="px-3 py-2">
                                <div class="skeleton-loader h-4 w-4 mx-auto"></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="skeleton-loader h-4 w-8 mx-auto"></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="skeleton-loader h-10 w-10 mx-auto rounded"></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex gap-1 justify-center">
                                    <div class="skeleton-loader h-6 w-6 rounded"></div>
                                    <div class="skeleton-loader h-6 w-6 rounded"></div>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="skeleton-loader h-4 w-24 mx-auto"></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="skeleton-loader h-4 w-20 mx-auto"></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="skeleton-loader h-4 w-16 mx-auto"></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="skeleton-loader h-4 w-16 mx-auto"></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="skeleton-loader h-6 w-12 mx-auto rounded-full"></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="skeleton-loader h-4 w-16 mx-auto"></div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex gap-2 justify-center">
                                    <div class="skeleton-loader h-8 w-8 rounded"></div>
                                    <div class="skeleton-loader h-8 w-8 rounded"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination skeleton -->
            <div class="mt-6 flex justify-between items-center">
                <div class="skeleton-loader h-4 w-48"></div>
                <div class="flex gap-2">
                    <div class="skeleton-loader h-10 w-10 rounded"></div>
                    <div class="skeleton-loader h-10 w-10 rounded"></div>
                    <div class="skeleton-loader h-10 w-10 rounded"></div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div v-else>
            <ProductsTable :columns="columns" :data="products" :categories="categories" :brands="brands"
                :isLoading="isLoading" :itemsPerPage="10" :pagination="productStore.pagination"
                :currentPage="currentPage" @delete="handleDelete" @refresh="handleRefresh"
                @page-change="handlePageChange" />
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import ProductsTable from './ProductsTable.vue'
import { useProductStore } from '../../../stores/products'
import { useCategoryStore } from '../../../stores/categories'
import { useBrandStore } from '../../../stores/brands'
import { push } from 'notivue'

const productStore = useProductStore()
const categoryStore = useCategoryStore()
const brandStore = useBrandStore()

const categories = ref([])
const brands = ref([])
const currentPage = ref(1)

const columns = [
    { key: 'main_image', label: 'Ảnh chính', type: 'main_image' },
    { key: 'sub_images', label: 'Ảnh phụ', type: 'sub_images' },
    { key: 'name', label: 'Tên sản phẩm' },
    { key: 'category', label: 'Danh mục', type: 'category' },
    { key: 'brand', label: 'Thương hiệu', type: 'brand' },
    { key: 'price', label: 'Giá bán', type: 'price' },
    { key: 'discount_price', label: 'Giá khuyến mãi', type: 'price' },
    { key: 'variants', label: 'Biến thể', type: 'variants' },
    { key: 'is_active', label: 'Trạng thái', type: 'status' }
]

const products = ref([])
const isLoading = ref(false)

const loadData = async (page = 1) => {
    isLoading.value = true
    try {
        await Promise.all([
            productStore.fetchProducts({}, page), // Thêm page parameter
            categoryStore.fetchCategories(),
            brandStore.fetchBrands()
        ])

        const rawProducts = productStore.products
        const categoriesData = categoryStore.categories
        const brandsData = brandStore.brands

        categories.value = categoriesData
        brands.value = brandsData

        products.value = rawProducts.map(p => {
            const mainImage = p.images.find(img => img.is_main === 1)?.image_path || ''
            const subImages = p.images.filter(img => img.is_main === 0).map(img => img.image_path)
            const category = categoriesData.find(c => c.id === p.categories_id)?.name || ''
            const brand = brandsData.find(b => b.id === p.brand_id)?.name || ''

            return {
                ...p,
                main_image: mainImage,
                sub_images: subImages,
                category,
                brand
            }
        })
    } catch (error) {
        console.error('Error loading data:', error)
        push.error('Có lỗi khi tải dữ liệu')
    } finally {
        isLoading.value = false
    }
}

onMounted(() => {
    loadData(currentPage.value)
})

const handleDelete = async (product) => {
    if (confirm(`Xoá sản phẩm: ${product.name}?`)) {
        try {
            await productStore.deleteProduct(product.id)
            // Reload current page after deletion
            await loadData(currentPage.value)
            push.success('Đã xoá sản phẩm.')
        } catch (error) {
            push.error('Có lỗi khi xoá sản phẩm')
        }
    }
}

const handleRefresh = async () => {
    await loadData(currentPage.value)
    push.success('Đã tải lại dữ liệu')
}

const handlePageChange = async (page) => {
    currentPage.value = page
    await loadData(page)
}
</script>

<style scoped>
.products-page {
    padding: 1rem;
}

@media (min-width: 768px) {
    .products-page {
        padding: 1.5rem;
    }
}

.page-header {
    margin-bottom: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

@media (min-width: 768px) {
    .page-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 0;
    }
}

.header-content h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

@media (min-width: 768px) {
    .header-content h1 {
        font-size: 1.875rem;
        margin-bottom: 0.5rem;
    }
}

.header-actions {
    display: flex;
    gap: 0.75rem;
}

.refresh-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background-color: #6b7280;
    color: white;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.5rem;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s;
}

.refresh-btn:hover {
    background-color: #4b5563;
}

.refresh-btn:focus {
    outline: none;
    box-shadow: 0 0 0 2px #6b7280, 0 0 0 4px rgba(107, 114, 128, 0.2);
}

/* Skeleton Loading */
.skeleton-loader {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
    background-size: 400% 100%;
    animation: skeleton-loading 1.4s ease infinite;
    border-radius: 4px;
}

@keyframes skeleton-loading {
    0% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}
</style>
