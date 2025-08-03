<template>
    <div class="p-6">
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Quản lý kho</h1>
                    <p class="text-gray-600">Quản lý hàng tồn kho và mức tồn kho sản phẩm của bạn</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button @click="fetchInventories"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        Tải lại
                    </button>
                    <router-link to="/admin/inventory/import"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-200 cursor-pointer">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Nhập/Xuất kho
                    </router-link>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Sản phẩm</label>
                        <select v-model="filters.product_id" class="w-full border border-gray-300 rounded-md p-2">
                            <option value="">Tất cả sản phẩm</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                                {{ product.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tồn kho</label>
                        <select v-model="filters.stock_status" class="w-full border border-gray-300 rounded-md p-2">
                            <option value="">Tất cả</option>
                            <option value="in_stock">Còn hàng</option>
                            <option value="low_stock">Sắp hết hàng</option>
                            <option value="out_of_stock">Hết hàng</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Sắp xếp theo</label>
                        <select v-model="filters.sort" class="w-full border border-gray-300 rounded-md p-2">
                            <option value="name_asc">Tên A-Z</option>
                            <option value="name_desc">Tên Z-A</option>
                            <option value="stock_asc">Tồn kho tăng dần</option>
                            <option value="stock_desc">Tồn kho giảm dần</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold pl-1">Danh sách tồn kho</h2>
                        <div class="text-sm text-gray-500 pr-1">
                            Tổng số: {{ filteredInventories.length }} sản phẩm
                        </div>
                    </div>
                    <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
                        <table class="min-w-full">
                            <thead class="border-b border-gray-300">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Sản phẩm</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Màu sắc</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Kích thước</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Tồn kho</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Giá</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template v-if="loading">
                                    <tr v-for="n in 8" :key="n">
                                        <td class="px-6 py-4">
                                            <div class="bg-gray-200 h-4 rounded w-32 animate-pulse">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="bg-gray-200 h-4 rounded w-16 animate-pulse">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="bg-gray-200 h-4 rounded w-12 animate-pulse">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="bg-gray-200 h-4 rounded w-20 animate-pulse">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="bg-gray-200 h-4 rounded w-10 animate-pulse">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="bg-gray-200 h-4 rounded w-16 animate-pulse">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="bg-gray-200 h-4 rounded w-20 animate-pulse">
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr v-for="item in filteredInventories" :key="item.variant.id">
                                        <td class="px-6 py-4">{{ item.variant.product.name }}</td>
                                        <td class="px-6 py-4">{{ item.variant.color }}</td>
                                        <td class="px-6 py-4">{{ item.variant.size }}</td>
                                        <td class="px-6 py-4">{{ item.variant.sku }}</td>
                                        <td class="px-6 py-4">{{ item.quantity }}</td>
                                        <td class="px-6 py-4">{{ formatPrice(item.variant.price) }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="{
                                                'px-2 py-1 rounded-full text-xs font-semibold': true,
                                                'bg-green-100 text-green-700 border border-green-300': item.quantity > 10,
                                                'bg-yellow-100 text-yellow-700 border border-yellow-300': item.quantity > 0 && item.quantity <= 10,
                                                'bg-red-100 text-red-700 border border-red-300': item.quantity === 0
                                            }">
                                                {{ getStockStatus(item.quantity) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredInventories.length === 0">
                                        <td colspan="7" class="px-4 py-3 text-center text-gray-500">Không có
                                            dữ liệu</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useInventories } from '../../../composable/useInventorie'
const { getInventories } = useInventories()
const inventories = ref([])
const products = ref([])
const filters = ref({
    product_id: '',
    stock_status: '',
    sort: 'name_asc'
})

const loading = ref(false)

const fetchInventories = async () => {
    try {
        loading.value = true
        const data = await getInventories()
        inventories.value = data
    } catch (error) {
        console.error('Error fetching inventories:', error)
    } finally {
        loading.value = false
    }
}

const fetchProducts = () => {
    const uniqueProducts = new Map()
    inventories.value.forEach(item => {
        if (!uniqueProducts.has(item.variant.product.id)) {
            uniqueProducts.set(item.variant.product.id, item.variant.product)
        }
    })
    products.value = Array.from(uniqueProducts.values())
}

const getStockStatus = (quantity) => {
    if (quantity === 0) return 'Hết hàng'
    if (quantity <= 10) return 'Sắp hết hàng'
    return 'Còn hàng'
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const filteredInventories = computed(() => {
    let result = [...inventories.value]

    if (filters.value.product_id) {
        result = result.filter(item => item.variant.product_id === parseInt(filters.value.product_id))
    }

    if (filters.value.stock_status) {
        switch (filters.value.stock_status) {
            case 'in_stock':
                result = result.filter(item => item.quantity > 10)
                break
            case 'low_stock':
                result = result.filter(item => item.quantity > 0 && item.quantity <= 10)
                break
            case 'out_of_stock':
                result = result.filter(item => item.quantity === 0)
                break
        }
    }

    switch (filters.value.sort) {
        case 'name_asc':
            result.sort((a, b) => a.variant.product.name.localeCompare(b.variant.product.name))
            break
        case 'name_desc':
            result.sort((a, b) => b.variant.product.name.localeCompare(a.variant.product.name))
            break
        case 'stock_asc':
            result.sort((a, b) => a.quantity - b.quantity)
            break
        case 'stock_desc':
            result.sort((a, b) => b.quantity - a.quantity)
            break
    }

    return result
})

watch(filters, () => {
}, { deep: true })

onMounted(async () => {
    await fetchInventories()
    fetchProducts()
})
</script>

<style scoped>
table {
    width: 100%;
}

th,
td {
    font-size: 0.875rem;
}
</style>