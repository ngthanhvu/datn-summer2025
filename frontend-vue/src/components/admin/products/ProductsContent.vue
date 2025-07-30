<template>
    <div class="products-page">
        <div class="page-header flex items-center justify-between">
            <div>
                <h1>Quản lý sản phẩm</h1>
                <p class="text-gray-600">Quản lý danh sách sản phẩm của bạn</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="handleRefresh"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Tải lại
                </button>
            </div>
        </div>

        <!-- <ProductsTable :columns="columns" :data="products" :categories="categories" :brands="brands"
            :isLoading="isLoading" @delete="handleDelete" @refresh="handleRefresh" /> -->
        <ProductsTable :columns="columns" :data="products" :categories="categories" :brands="brands"
            :isLoading="isLoading" :itemsPerPage="10" @delete="handleDelete" @refresh="handleRefresh" />

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

onMounted(async () => {
    isLoading.value = true
    await Promise.all([
        productStore.fetchProducts(),
        categoryStore.fetchCategories(),
        brandStore.fetchBrands()
    ])

    const rawProducts = productStore.products
    const categories = categoryStore.categories
    const brands = brandStore.brands

    products.value = rawProducts.map(p => {
        const mainImage = p.images.find(img => img.is_main === 1)?.image_path || ''
        const subImages = p.images.filter(img => img.is_main === 0).map(img => img.image_path)
        const category = categories.find(c => c.id === p.categories_id)?.name || ''
        const brand = brands.find(b => b.id === p.brand_id)?.name || ''

        return {
            ...p,
            main_image: mainImage,
            sub_images: subImages,
            category,
            brand
        }
    })

    isLoading.value = false
})

const handleDelete = async (product) => {
    if (confirm(`Xoá sản phẩm: ${product.name}?`)) {
        await productStore.deleteProduct(product.id)
        products.value = products.value.filter(p => p.id !== product.id)
        push.success('Đã xoá sản phẩm.')
    }
}

const handleRefresh = async () => {
    await productStore.fetchProducts()
}
</script>


<style scoped>
.products-page {
    padding: 1.5rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}
</style>
