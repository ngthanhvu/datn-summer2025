<template>
    <div class="tw-max-w-4xl tw-mx-auto tw-p-6">
        <!-- Header -->
        <div class="tw-mb-8">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div>
                    <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-mb-2">Nhập kho sản phẩm</h1>
                    <p class="tw-text-gray-600">Tạo phiếu nhập kho mới cho các sản phẩm</p>
                </div>
                <NuxtLink to="/admin/inventory"
                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-gray-600 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-gray-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-gray-500 focus:tw-ring-offset-2 tw-transition-colors tw-duration-200">
                    <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Quay lại
                </NuxtLink>
            </div>
        </div>

        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200">
            <form @submit.prevent="submitForm">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                    <h2 class="tw-text-xl tw-font-semibold tw-text-gray-900">Thông tin phiếu nhập</h2>
                </div>

                <div class="tw-p-6 tw-space-y-6">
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Loại giao
                                dịch</label>
                            <div class="tw-flex tw-space-x-4">
                                <label class="tw-flex tw-items-center">
                                    <input type="radio" v-model="formData.type" value="import"
                                        class="tw-h-4 tw-w-4 tw-text-blue-600 focus:tw-ring-blue-500 tw-border-gray-300">
                                    <span class="tw-ml-2 tw-text-sm tw-text-gray-700">Nhập kho</span>
                                </label>
                                <label class="tw-flex tw-items-center">
                                    <input type="radio" v-model="formData.type" value="export"
                                        class="tw-h-4 tw-w-4 tw-text-blue-600 focus:tw-ring-blue-500 tw-border-gray-300">
                                    <span class="tw-ml-2 tw-text-sm tw-text-gray-700">Xuất kho</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="notes" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Ghi
                                chú</label>
                            <input type="text" id="note" v-model="formData.note"
                                class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                                placeholder="Nhập ghi chú cho phiếu nhập/xuất">
                        </div>
                    </div>
                    <div>
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Danh sách sản phẩm</h3>
                            <button type="button" @click="addProductItem"
                                class="tw-inline-flex tw-items-center tw-px-3 tw-py-2 tw-bg-blue-600 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-blue-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2">
                                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Thêm sản phẩm
                            </button>
                        </div>
                        <div class="tw-space-y-4">
                            <div v-for="(item, index) in formData.items" :key="index"
                                class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 tw-bg-gray-50">
                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-5 tw-gap-4 tw-items-end">
                                    <div class="md:tw-col-span-2">
                                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Sản
                                            phẩm <span class="tw-text-red-500">*</span></label>
                                        <select v-model="item.variant_id"
                                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                                            required>
                                            <option value="">Chọn sản phẩm</option>
                                            <option v-for="variant in variants" :key="variant.id" :value="variant.id">
                                                {{ variant.product.name }} - {{ variant.color }} - {{ variant.size }}
                                                (SKU: {{ variant.sku }})
                                            </option>
                                        </select>
                                        <div v-if="item.variant_id" class="tw-flex tw-items-center tw-gap-4 tw-mt-2">
                                            <img v-if="getVariantImage(item.variant_id)"
                                                :src="getVariantImage(item.variant_id)" alt="Ảnh biến thể"
                                                class="tw-w-16 tw-h-16 tw-object-cover tw-rounded" />
                                            <div v-if="getVariantInfo(item.variant_id)">
                                                <div class="tw-text-sm tw-font-medium">Tên: {{
                                                    getVariantInfo(item.variant_id).product?.name }}</div>
                                                <div class="tw-text-xs">Màu: <span class="tw-font-semibold">{{
                                                    getVariantInfo(item.variant_id).color }}</span></div>
                                                <div class="tw-text-xs">Size: <span class="tw-font-semibold">{{
                                                    getVariantInfo(item.variant_id).size }}</span></div>
                                                <div class="tw-text-xs">SKU: <span class="tw-font-semibold">{{
                                                    getVariantInfo(item.variant_id).sku }}</span></div>
                                                <div class="tw-text-xs">Giá: <span class="tw-font-semibold">{{
                                                    formatCurrency(getVariantInfo(item.variant_id).price) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Số
                                            lượng <span class="tw-text-red-500">*</span></label>
                                        <input type="number" v-model.number="item.quantity" min="1"
                                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                                            placeholder="Số lượng" required>
                                    </div>
                                    <div>
                                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                                            {{ formData.type === 'import' ? 'Giá nhập' : 'Giá xuất' }} (VNĐ) <span
                                                class="tw-text-red-500">*</span>
                                        </label>
                                        <input type="number" v-model.number="item.unit_price" min="0" step="1000"
                                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                                            placeholder="Giá" required>
                                    </div>
                                    <div>
                                        <button type="button" @click="removeProductItem(index)"
                                            class="tw-w-full tw-px-3 tw-py-2 tw-bg-red-600 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-red-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-500 focus:tw-ring-offset-2">
                                            <svg class="tw-w-4 tw-h-4 tw-mx-auto" fill="none" stroke="currentColor"
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

                    <div v-if="formData.items.length > 0" class="tw-bg-blue-50 tw-rounded-lg tw-p-6">
                        <h3 class="tw-text-lg tw-font-medium tw-text-blue-900 tw-mb-4">Tổng kết phiếu {{ formData.type
                            === 'import'
                            ? 'nhập' : 'xuất' }}</h3>
                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4">
                            <div class="tw-text-center">
                                <p class="tw-text-sm tw-text-blue-600">Tổng số sản phẩm</p>
                                <p class="tw-text-2xl tw-font-bold tw-text-blue-900">{{ formData.items.length }}</p>
                            </div>
                            <div class="tw-text-center">
                                <p class="tw-text-sm tw-text-blue-600">Tổng số lượng</p>
                                <p class="tw-text-2xl tw-font-bold tw-text-blue-900">{{ totalQuantity }}</p>
                            </div>
                            <div class="tw-text-center">
                                <p class="tw-text-sm tw-text-blue-600">Tổng giá trị</p>
                                <p class="tw-text-2xl tw-font-bold tw-text-blue-900">{{ formatCurrency(totalValue) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw-px-6 tw-py-4 tw-border-t tw-border-gray-200 tw-bg-gray-50 tw-rounded-b-xl">
                    <div class="tw-flex tw-justify-end tw-space-x-3">
                        <router-link to="/inventory/stock"
                            class="tw-px-6 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg hover:tw-bg-gray-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-gray-500">
                            Hủy
                        </router-link>
                        <button type="submit" :disabled="!isFormValid || loading" :class="[
                            'tw-px-6 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-rounded-lg focus:tw-outline-none focus:tw-ring-2',
                            formData.type === 'import'
                                ? 'tw-bg-green-600 hover:tw-bg-green-700 focus:tw-ring-green-500'
                                : 'tw-bg-red-600 hover:tw-bg-red-700 focus:tw-ring-red-500',
                            (!isFormValid || loading) ? 'tw-opacity-50 tw-cursor-not-allowed' : ''
                        ]">
                            <span v-if="loading" class="tw-inline-flex tw-items-center">
                                <svg class="tw-animate-spin -tw-ml-1 tw-mr-2 tw-h-4 tw-w-4 tw-text-white" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="tw-opacity-75" fill="currentColor"
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
import { useProducts } from '~/composables/useProducts';
import { useInventories } from '~/composables/useInventorie';
const notyf = useNuxtApp().$notyf

const { createStockMovement } = useInventories();
const { getVariant } = useProducts();

const variants = ref([])
const loading = ref(false)
const formData = ref({
    type: 'import',
    note: '',
    items: []
})
const isFormValid = computed(() => {
    if (formData.value.items.length === 0) return false
    return formData.value.items.every(item => {
        return item.variant_id && item.quantity > 0 && item.unit_price > 0
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
    formData.value.items.push({ variant_id: '', quantity: 1, unit_price: 0 })
}
const removeProductItem = (index) => {
    formData.value.items.splice(index, 1)
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
        notyf.success('Tạo phiếu thành công!')
        navigateTo('/admin/inventory')
    } catch (err) {
        notyf.error('Có lỗi xảy ra khi tạo phiếu!')
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
    // Lấy danh sách variants từ API
    variants.value = await getVariant();
    console.log(variants.value);

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