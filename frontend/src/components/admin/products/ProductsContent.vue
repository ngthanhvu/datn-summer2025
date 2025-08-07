<template>
    <div class="products-page">
        <div class="page-header">
            <div class="header-content">
                <h1>Quản lý sản phẩm</h1>
                <p class="text-gray-600">Quản lý danh sách sản phẩm của bạn</p>
            </div>
        </div>

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

const categories = ref([])
const brands = ref([])

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

const loadData = async () => {
    isLoading.value = true
    try {
        await Promise.all([
            productStore.fetchProducts(),
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
    loadData()
})

const handleDelete = async (product) => {
    if (confirm(`Xoá sản phẩm: ${product.name}?`)) {
        try {
            await productStore.deleteProduct(product.id)
            products.value = products.value.filter(p => p.id !== product.id)
            push.success('Đã xoá sản phẩm.')
        } catch (error) {
            push.error('Có lỗi khi xoá sản phẩm')
        }
    }
}

const handleRefresh = async () => {
    await loadData()
    push.success('Đã tải lại dữ liệu')
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
</style>
