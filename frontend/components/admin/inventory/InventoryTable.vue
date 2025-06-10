<template>
    <div class="tw-p-6">
        <div class="tw-mb-6">
            <h1 class="tw-text-2xl tw-font-bold tw-mb-4">Tổng quan kho</h1>

            <!-- Filters -->
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-md tw-mb-6">
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4">
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Sản phẩm</label>
                        <select v-model="filters.product_id" class="tw-w-full tw-border tw-rounded-md tw-p-2">
                            <option value="">Tất cả sản phẩm</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                                {{ product.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Tồn kho</label>
                        <select v-model="filters.stock_status" class="tw-w-full tw-border tw-rounded-md tw-p-2">
                            <option value="">Tất cả</option>
                            <option value="in_stock">Còn hàng</option>
                            <option value="low_stock">Sắp hết hàng</option>
                            <option value="out_of_stock">Hết hàng</option>
                        </select>
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Sắp xếp theo</label>
                        <select v-model="filters.sort" class="tw-w-full tw-border tw-rounded-md tw-p-2">
                            <option value="name_asc">Tên A-Z</option>
                            <option value="name_desc">Tên Z-A</option>
                            <option value="stock_asc">Tồn kho tăng dần</option>
                            <option value="stock_desc">Tồn kho giảm dần</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow-md">
                <div class="tw-p-4">
                    <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                        <h2 class="tw-text-xl tw-font-semibold">Danh sách tồn kho</h2>
                        <div class="tw-text-sm tw-text-gray-500">
                            Tổng số: {{ filteredInventories.length }} sản phẩm
                        </div>
                    </div>
                    <div class="tw-overflow-x-auto">
                        <table class="tw-min-w-full">
                            <thead>
                                <tr class="tw-bg-gray-50">
                                    <th
                                        class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Sản phẩm</th>
                                    <th
                                        class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Màu sắc</th>
                                    <th
                                        class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Kích thước</th>
                                    <th
                                        class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        SKU</th>
                                    <th
                                        class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Tồn kho</th>
                                    <th
                                        class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Giá</th>
                                    <th
                                        class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="tw-divide-y tw-divide-gray-200">
                                <tr v-for="item in filteredInventories" :key="item.variant.id">
                                    <td class="tw-px-6 tw-py-4">{{ item.variant.product.name }}</td>
                                    <td class="tw-px-6 tw-py-4">{{ item.variant.color }}</td>
                                    <td class="tw-px-6 tw-py-4">{{ item.variant.size }}</td>
                                    <td class="tw-px-6 tw-py-4">{{ item.variant.sku }}</td>
                                    <td class="tw-px-6 tw-py-4">{{ item.quantity }}</td>
                                    <td class="tw-px-6 tw-py-4">{{ formatPrice(item.variant.price) }}</td>
                                    <td class="tw-px-6 tw-py-4">
                                        <span :class="{
                                            'tw-text-green-600': item.quantity > 10,
                                            'tw-text-yellow-600': item.quantity > 0 && item.quantity <= 10,
                                            'tw-text-red-600': item.quantity === 0
                                        }">
                                            {{ getStockStatus(item.quantity) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="filteredInventories.length === 0">
                                    <td colspan="7" class="tw-px-4 tw-py-3 tw-text-center tw-text-gray-500">Không có dữ
                                        liệu</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin',
    middleware: 'admin'
})

const { getInventories } = useInventories()
const inventories = ref([])
const products = ref([])
const filters = ref({
    product_id: '',
    stock_status: '',
    sort: 'name_asc'
})

// Fetch inventory data
const fetchInventories = async () => {
    try {
        const data = await getInventories()
        inventories.value = data
    } catch (error) {
        console.error('Error fetching inventories:', error)
    }
}

// Fetch products from inventory data
const fetchProducts = () => {
    const uniqueProducts = new Map()
    inventories.value.forEach(item => {
        if (!uniqueProducts.has(item.variant.product.id)) {
            uniqueProducts.set(item.variant.product.id, item.variant.product)
        }
    })
    products.value = Array.from(uniqueProducts.values())
}

// Get stock status
const getStockStatus = (quantity) => {
    if (quantity === 0) return 'Hết hàng'
    if (quantity <= 10) return 'Sắp hết hàng'
    return 'Còn hàng'
}

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

// Filtered inventories
const filteredInventories = computed(() => {
    let result = [...inventories.value]

    // Filter by product
    if (filters.value.product_id) {
        result = result.filter(item => item.variant.product_id === parseInt(filters.value.product_id))
    }

    // Filter by stock status
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

    // Sort
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

// Watch filters changes
watch(filters, () => {
    // You can add additional logic here if needed
}, { deep: true })

// Fetch data on component mount
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