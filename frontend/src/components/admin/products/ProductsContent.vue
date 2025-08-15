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
            <ProductsTable :columns="columns" :data="products" :categories="formattedCategories"
                :brands="formattedBrands" :isLoading="isLoading" :itemsPerPage="10" :pagination="pagination"
                :currentPage="currentPage" @delete="handleDelete" @refresh="handleRefresh"
                @page-change="handlePageChange" />
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import ProductsTable from './ProductsTable.vue'
import { useProducts } from '../../../composable/useProducts'
import { useCategoryStore } from '../../../stores/categories'
import { useBrandStore } from '../../../stores/brands'
import { push } from 'notivue'
import Swal from 'sweetalert2'

const categoryStore = useCategoryStore()
const brandStore = useBrandStore()

const { getProducts, deleteProduct } = useProducts()

const categories = ref([])
const brands = ref([])
const currentPage = ref(1)
const products = ref([])
const isLoading = ref(false)
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
})

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

const loadData = async (page = 1, forceRefresh = false) => {
    isLoading.value = true
    try {
        const params = forceRefresh ? { page, _t: Date.now() } : { page }

        const [productsResponse] = await Promise.all([
            getProducts(params, page)
        ])

        await Promise.all([
            categoryStore.fetchCategories(),
            brandStore.fetchBrands()
        ])

        // Đảm bảo dữ liệu được load thành công
        if (categoryStore.error) {
            console.error('Error loading categories:', categoryStore.error)
            push.error('Lỗi khi tải danh mục')
        }
        if (brandStore.error) {
            console.error('Error loading brands:', brandStore.error)
            push.error('Lỗi khi tải thương hiệu')
        }

        if (productsResponse) {
            pagination.value = {
                current_page: productsResponse.pagination?.current_page || 1,
                last_page: productsResponse.pagination?.last_page || 1,
                per_page: productsResponse.pagination?.per_page || 10,
                total: productsResponse.pagination?.total || 0,
                from: productsResponse.pagination?.from || 0,
                to: productsResponse.pagination?.to || 0,
                next_page_url: productsResponse.pagination?.next_page_url,
                prev_page_url: productsResponse.pagination?.prev_page_url,
                links: productsResponse.pagination?.links || []
            }
        }

        const rawProducts = productsResponse.products || []

        const finalCategories = categoryStore.categories || []
        const finalBrands = brandStore.brands || []

        // Gán dữ liệu trực tiếp cho reactive refs
        categories.value = finalCategories
        brands.value = finalBrands

        products.value = rawProducts.map(p => {
            const mainImage = p.images?.find(img => img.is_main === 1)?.image_path || ''
            const subImages = p.images?.filter(img => img.is_main === 0).map(img => img.image_path) || []
            const category = (finalCategories || []).find(c => c.id === p.categories_id)?.name || ''
            const brand = (finalBrands || []).find(b => b.id === p.brand_id)?.name || ''

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

// Thêm computed để đảm bảo dữ liệu được reactive
const formattedCategories = computed(() => {
    const result = categories.value.map(cat => ({
        value: cat.name,
        label: cat.name
    }))
    return result
})

const formattedBrands = computed(() => {
    const result = brands.value.map(brand => ({
        value: brand.name,
        label: brand.name
    }))
    return result
})

const handleDelete = async (product) => {
    const result = await Swal.fire({
        title: 'Xác nhận xoá?',
        html: `Bạn có chắc muốn xoá sản phẩm <b>${product.name}</b>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xoá',
        cancelButtonText: 'Huỷ',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6'
    })

    if (!result.isConfirmed) return

    try {
        await deleteProduct(product.id)

        const currentPageItemCount = products.value.length
        if (currentPageItemCount === 1 && currentPage.value > 1) {
            currentPage.value = currentPage.value - 1
        }

        await loadData(currentPage.value, true)

        push.success('Xoá sản phẩm thành công')
    } catch (error) {
        console.error('Error deleting product:', error)
        Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: 'Có lỗi khi xoá sản phẩm'
        })
    }
}

const handleRefresh = async () => {
    await loadData(currentPage.value, true)
    push.success('Đã tải lại dữ liệu')
}

const handlePageChange = async (page) => {
    currentPage.value = page
    await loadData(page, true)
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
