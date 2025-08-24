<template>
    <div class="max-w-4xl mx-auto p-4 sm:p-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Nhập kho sản phẩm</h1>
                    <p class="text-gray-600">Tạo phiếu nhập kho mới cho các sản phẩm</p>
                </div>
                <router-link to="/admin/inventory"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200 cursor-pointer">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Quay lại
                </router-link>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <form @submit.prevent="submitForm">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Thông tin phiếu nhập</h2>
                </div>

                <div class="p-4 sm:p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Loại giao
                                dịch</label>
                            <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
                                <label class="flex items-center">
                                    <input type="radio" v-model="formData.type" value="import"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Nhập kho</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" v-model="formData.type" value="export"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Xuất kho</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Ghi
                                chú</label>
                            <input type="text" id="note" v-model="formData.note"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                placeholder="Nhập ghi chú cho phiếu nhập/xuất">
                        </div>
                    </div>
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
                            <h3 class="text-lg font-medium text-gray-900">Danh sách sản phẩm</h3>
                            <button type="button" @click="addProductItem"
                                class="w-full sm:w-auto cursor-pointer inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Thêm sản phẩm
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div v-for="(item, index) in formData.items" :key="index"
                                class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 items-end">
                                    <div class="lg:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sản
                                            phẩm <span class="text-red-500">*</span></label>
                                        <!-- Chọn sản phẩm trước -->
                                        <select v-model="item.product_id" @change="onProductChange(index)"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 mb-3"
                                            required>
                                            <option value="">Chọn sản phẩm</option>
                                            <option v-for="product in uniqueProducts" :key="product.id"
                                                :value="product.id">
                                                {{ product.name }}
                                            </option>
                                        </select>

                                        <!-- Sau đó chọn biến thể (size, màu) -->
                                        <select v-model="item.variant_id" :disabled="!item.product_id"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                            required>
                                            <option value="">Chọn size và màu</option>
                                            <option v-for="variant in getVariantsByProduct(item.product_id)"
                                                :key="variant.id" :value="variant.id">
                                                {{ variant.color }} - {{ variant.size }} (SKU: {{ variant.sku }})
                                            </option>
                                        </select>

                                        <div v-if="item.variant_id"
                                            class="flex flex-col sm:flex-row sm:items-center gap-4 mt-2">
                                            <img v-if="getVariantImage(item.variant_id)"
                                                :src="getVariantImage(item.variant_id)" alt="Ảnh biến thể"
                                                class="w-16 h-16 object-cover rounded" />
                                            <div v-if="getVariantInfo(item.variant_id)" class="flex-1">
                                                <div class="text-sm font-medium">Tên: {{
                                                    getVariantInfo(item.variant_id).product?.name }}</div>
                                                <div class="text-xs">Màu: <span class="font-semibold">{{
                                                    getVariantInfo(item.variant_id).color }}</span></div>
                                                <div class="text-xs">Size: <span class="font-semibold">{{
                                                    getVariantInfo(item.variant_id).size }}</span></div>
                                                <div class="text-xs">SKU: <span class="font-semibold">{{
                                                    getVariantInfo(item.variant_id).sku }}</span></div>
                                                <div class="text-xs">Giá: <span class="font-semibold">{{
                                                    formatCurrency(getVariantInfo(item.variant_id).price) }}</span>
                                                </div>
                                                <div class="text-xs">Số lượng kho: <span class="font-semibold">{{
                                                    getVariantInfo(item.variant_id)?.inventory?.quantity ?? 0
                                                        }}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Số
                                            lượng <span class="text-red-500">*</span></label>
                                        <input type="number" v-model.number="item.quantity" min="1"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                            placeholder="Số lượng" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ formData.type === 'import' ? 'Giá nhập' : 'Giá xuất' }} (VNĐ) <span
                                                class="text-red-500">*</span>
                                        </label>
                                        <input type="number" v-model.number="item.unit_price" min="0" step="1000"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                            placeholder="Giá" required>
                                    </div>
                                    <div>
                                        <button type="button" @click="removeProductItem(index)"
                                            class="w-full px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="formData.items.length > 0" class="bg-blue-50 rounded-lg p-4 sm:p-6">
                        <h3 class="text-lg font-medium text-blue-900 mb-4">Tổng kết phiếu {{ formData.type
                            === 'import'
                            ? 'nhập' : 'xuất' }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="text-center">
                                <p class="text-sm text-blue-600">Tổng số sản phẩm</p>
                                <p class="text-2xl font-bold text-blue-900">{{ formData.items.length }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-blue-600">Tổng số lượng</p>
                                <p class="text-2xl font-bold text-blue-900">{{ totalQuantity }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-blue-600">Tổng giá trị</p>
                                <p class="text-2xl font-bold text-blue-900">{{ formatCurrency(totalValue) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                    <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                        <router-link to="/inventory/stock"
                            class="w-full sm:w-auto px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                            Hủy
                        </router-link>
                        <button type="submit" :disabled="!isFormValid || loading" :class="[
                            'w-full sm:w-auto px-6 py-2 text-sm font-medium text-white rounded-lg focus:outline-none focus:ring-2 cursor-pointer',
                            formData.type === 'import'
                                ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
                                : 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
                            (!isFormValid || loading) ? 'opacity-50 cursor-not-allowed' : ''
                        ]">
                            <span v-if="loading" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Đang xử lý...
                            </span>
                            <span v-else>
                                {{ formData.type === 'import' ? 'Tạo phiếu nhập' : 'Tạo phiếu xuất' }}
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { push } from 'notivue'
// import { useProducts } from '~/composables/useProducts';
import { useProducts } from '../../../composable/useProducts';
import { useInventories } from '../../../composable/useInventorie';
import { useRouter } from 'vue-router'

const router = useRouter()

const { createStockMovement } = useInventories();
const { getVariant } = useProducts();

const variants = ref([])
const loading = ref(false)
const formData = ref({
    type: 'import',
    note: '',
    items: []
})

// Computed để lấy danh sách sản phẩm duy nhất
const uniqueProducts = computed(() => {
    const products = []
    const seen = new Set()

    variants.value.forEach(variant => {
        if (variant.product && !seen.has(variant.product.id)) {
            seen.add(variant.product.id)
            products.push(variant.product)
        }
    })

    return products.sort((a, b) => a.name.localeCompare(b.name))
})

const getVariantsByProduct = (productId) => {
    if (!productId) return []
    return variants.value.filter(variant => variant.product && variant.product.id === productId)
}

const isFormValid = computed(() => {
    if (formData.value.items.length === 0) return false
    return formData.value.items.every(item => {
        return item.product_id && item.variant_id && item.quantity > 0 && item.unit_price > 0
    })
})
const totalQuantity = computed(() => formData.value.items.reduce((sum, item) => sum + (item.quantity || 0), 0))
const totalValue = computed(() => formData.value.items.reduce((sum, item) => sum + ((item.quantity || 0) * (item.unit_price || 0)), 0))
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount || 0)
}
const addProductItem = () => {
    formData.value.items.push({ product_id: '', variant_id: '', quantity: 1, unit_price: 0 })
}

const removeProductItem = (index) => {
    formData.value.items.splice(index, 1)
}

// Xử lý khi thay đổi sản phẩm
const onProductChange = (index) => {
    formData.value.items[index].variant_id = ''
}

const submitForm = async () => {
    loading.value = true
    try {
        const payload = {
            type: formData.value.type,
            note: formData.value.note,
            items: formData.value.items.map(item => ({
                variant_id: item.variant_id,
                quantity: item.quantity,
                unit_price: item.unit_price
            }))
        }
        await createStockMovement(payload)
        push.success('Tạo phiếu thành công!')
        router.push('/admin/inventory')
    } catch (err) {
        push.error('Có lỗi xảy ra khi tạo phiếu!')
    } finally {
        loading.value = false
    }
}
const getVariantImage = (variantId) => {
    const variant = variants.value.find(v => v.id === variantId)
    if (variant && variant.images && variant.images.length > 0) {
        return variant.images[0].image_path
    }
    return null
}
const getVariantInfo = (variantId) => {
    return variants.value.find(v => v.id === variantId)
}
onMounted(async () => {
    variants.value = await getVariant();

    addProductItem();
})
</script>

<script>
export default {
    filters: {
        currency(val) {
            if (!val) return '0 đ'
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val)
        }
    }
}
</script>