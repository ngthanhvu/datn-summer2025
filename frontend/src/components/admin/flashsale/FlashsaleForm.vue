<template>
    <div class="bg-[#f7f8fa] p-3 sm:p-6 min-h-screen text-sm">
        <div class="flex justify-between items-center mb-4 sm:mb-6 pt-3 sm:pt-6 pl-3 sm:pl-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold mb-2">{{ props.editData ? 'Cập nhật' : 'Thêm' }} chiến dịch
                    Flash Sale
                </h1>
                <div class="text-gray-500 mb-4 text-sm">Điền thông tin để {{ props.editData ? 'cập nhật' : 'tạo' }}
                    chương trình
                    Flash Sale</div>
            </div>
        </div>
        <div v-if="success" class="text-green-600 mb-2">{{ success }}</div>
        <div class="flex flex-col lg:flex-row gap-4 lg:gap-8">
            <div class="bg-white rounded shadow p-4 sm:p-6 lg:w-2/5 mb-4 lg:mb-0 text-sm">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Tên chiến dịch <span class="text-red-500">*
                            </span></label>
                        <input v-model="form.name"
                            class="input w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 text-sm"
                            placeholder="Nhập tên chiến dịch" />
                        <div v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="flex-1">
                            <label class="block text-sm font-medium mb-1">Thời gian bắt đầu <span
                                    class="text-red-500">*</span></label>
                            <input type="datetime-local" v-model="form.start"
                                class="input border-gray-300 w-full border rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 text-sm" />
                            <div v-if="errors.start" class="text-red-500 text-xs mt-1">{{ errors.start }}</div>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium mb-1">Thời gian kết thúc <span
                                    class="text-red-500">*</span></label>
                            <input type="datetime-local" v-model="form.end"
                                class="input border-gray-300 w-full border rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 text-sm" />
                            <div v-if="errors.end" class="text-red-500 text-xs mt-1">{{ errors.end }}</div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-sm">Tự động lặp lại</label>
                            <button @click="form.repeat = !form.repeat"
                                :class="form.repeat ? 'bg-[#3bb77e]' : 'bg-gray-300'"
                                class="relative w-12 h-7 rounded-full transition-colors duration-300 outline-none flex items-center cursor-pointer">
                                <span :class="form.repeat ? 'translate-x-6' : 'translate-x-1'"
                                    class="absolute w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-300"></span>
                            </button>
                        </div>
                        <div v-if="form.repeat" class="flex items-center gap-2">
                            <label class="text-sm">Số phút lặp lại</label>
                            <input v-model="form.repeatMinutes" type="number" min="1"
                                class="input w-20 border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 text-sm"
                                placeholder="Phút" />
                        </div>
                    </div>
                    <div v-if="form.repeat" class="bg-blue-50 border border-blue-200 rounded p-3 text-xs text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Tính năng lặp lại:</strong> Khi chương trình kết thúc, hệ thống sẽ tự động tạo lại chương trình mới sau {{
                            form.repeatMinutes || 60 }} phút. Tên mới sẽ có dạng "Tên cũ (Lặp lại - Ngày giờ)". Số lượng đã bán sẽ được reset về 0.
                    </div>
                    <div v-if="form.repeat && errors.repeatMinutes" class="text-red-500 text-xs">{{ errors.repeatMinutes
                    }}</div>
                    <div class="flex flex-col gap-3 mt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                            <div class="flex items-center gap-2">
                                <label class="text-sm">Tự động tăng số lượng bán</label>
                                <button @click="form.autoIncrease = !form.autoIncrease"
                                    :class="form.autoIncrease ? 'bg-[#3bb77e]' : 'bg-gray-300'"
                                    class="relative w-12 h-7 rounded-full transition-colors duration-300 outline-none flex items-center cursor-pointer">
                                    <span :class="form.autoIncrease ? 'translate-x-6' : 'translate-x-1'"
                                        class="absolute w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-300"></span>
                                </button>
                            </div>
                            <div v-if="form.autoIncrease" class="flex items-center gap-2">
                                <label class="text-sm">Tăng mỗi 1 giờ</label>
                                <input v-model="form.increaseAmount" type="number" min="1"
                                    class="input w-20 border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 text-sm"
                                    placeholder="SL" />
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm">Active</label>
                            <button @click="form.active = !form.active"
                                :class="form.active ? 'bg-[#3bb77e]' : 'bg-gray-300'"
                                class="relative w-12 h-7 rounded-full transition-colors duration-300 outline-none flex items-center cursor-pointer">
                                <span :class="form.active ? 'translate-x-6' : 'translate-x-1'"
                                    class="absolute w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-300"></span>
                            </button>
                        </div>
                    </div>
                    <div v-if="form.autoIncrease"
                        class="bg-green-50 border border-green-200 rounded p-3 text-xs text-green-700">
                        <i class="fas fa-clock mr-1"></i>
                        <strong>Tính năng tăng số lượng:</strong> Hệ thống sẽ tự động tăng số lượng đã bán thêm {{ form.increaseAmount || 1 }} mỗi 1 giờ, không
                        vượt quá số lượng tổng. Tính năng này giúp tạo cảm giác Flash Sale đang bán chạy.
                    </div>
                    <div v-if="form.autoIncrease && errors.increaseAmount" class="text-red-500 text-xs">{{
                        errors.increaseAmount }}</div>
                    <div class="flex flex-col sm:flex-row gap-2 mt-6">
                        <button
                            class="px-3 py-2 bg-[#3BB77E] text-white cursor-pointer rounded hover:bg-[#74c09d] text-sm"
                            :disabled="loading" @click="submit">{{
                                loading ?
                                    'Đang lưu...' :
                                    (props.editData ? 'Cập nhật' : 'Hoàn tất') }}</button>
                        <button
                            class="px-3 py-2 bg-[#81aacc] text-white rounded hover:bg-[#498dc4] cursor-pointer text-sm"
                            @click="goToSelectProducts">Thêm sản phẩm</button>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded shadow p-4 sm:p-6 lg:w-3/5 text-sm">
                <h3 class="font-semibold text-lg sm:text-xl mb-2">Sản phẩm Flash Sale</h3>

                <!-- Desktop table view -->
                <div
                    class="hidden lg:block overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
                    <table class="w-full bg-white rounded text-sm text-center">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="px-3 py-2">Ảnh</th>
                                <th class="px-3 py-2">Tên sản phẩm</th>
                                <th class="px-3 py-2">Giá thường</th>
                                <th class="px-3 py-2">Giá KM</th>
                                <th class="px-3 py-2">Giá Flash Sale</th>
                                <th class="px-3 py-2">Đã bán</th>
                                <th class="px-3 py-2">Số lượng</th>
                                <th class="px-3 py-2">SL Thật</th>
                                <th class="px-3 py-2">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, idx) in paginatedProducts" :key="item.id">
                                <td class="px-3 py-2">
                                    <img :src="getMainImage(item)" class="w-10 h-10 rounded mx-auto" />
                                </td>
                                <td class="px-3 py-2">{{ truncate(item.name) }}</td>
                                <td class="px-3 py-2">{{ item.product?.price ? formatPrice(item.product.price) :
                                    (item.price ? formatPrice(item.price) : 'N/A') }}</td>
                                <td class="px-3 py-2">{{ item.product?.discount_price ?
                                    formatPrice(item.product.discount_price) : (item.discount_price ?
                                        formatPrice(item.discount_price) : 'N/A') }}</td>
                                <td class="px-3 py-2">
                                    <input v-model="item.flashPrice"
                                        class="input w-24 border border-gray-300 rounded p-1 text-sm text-center"
                                        placeholder="Giá FS" />
                                    <div v-if="errors.products && errors.products[item.id]"
                                        class="text-red-500 text-xs mt-1 text-center">
                                        {{ errors.products[item.id] }}
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <input v-model="item.sold"
                                        class="input w-12 border border-gray-300 rounded p-1 text-sm text-center"
                                        placeholder="Đã bán" />
                                </td>
                                <td class="px-3 py-2">
                                    <input v-model="item.quantity"
                                        class="input w-16 border border-gray-300 rounded p-1 text-sm text-center"
                                        placeholder="SL" />
                                </td>
                                <td class="px-3 py-2">
                                    <label class="relative inline-flex items-center cursor-pointer justify-center">
                                        <input type="checkbox" v-model="item.realQty" class="sr-only peer" />
                                        <div
                                            class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-[#3BB77E] transition-colors">
                                        </div>
                                        <div
                                            class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-5">
                                        </div>
                                    </label>
                                </td>
                                <td class="px-3 py-2">
                                    <button class="text-red-500 hover:text-red-700 cursor-pointer"
                                        @click="removeProduct(idx)" title="Xóa">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" width="20" height="20" class="mx-auto">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="paginatedProducts.length === 0">
                                <td colspan="9" class="text-center py-4">Không có sản phẩm</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile card view -->
                <div class="lg:hidden space-y-3">
                    <div v-if="paginatedProducts.length === 0" class="text-center py-4 text-gray-500">
                        Không có sản phẩm
                    </div>
                    <div v-for="(item, idx) in paginatedProducts" :key="item.id"
                        class="border border-gray-200 rounded-lg p-3 bg-white">
                        <div class="flex items-start gap-3 mb-3">
                            <img :src="getMainImage(item)" class="w-12 h-12 rounded flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-sm text-gray-900 truncate">{{ item.name }}</h4>
                                <div class="text-xs text-gray-500 mt-1">
                                    <div>Giá thường: {{ item.product?.price ? formatPrice(item.product.price) :
                                        (item.price ? formatPrice(item.price) : 'N/A') }}</div>
                                    <div>Giá KM: {{ item.product?.discount_price ?
                                        formatPrice(item.product.discount_price) : (item.discount_price ?
                                            formatPrice(item.discount_price) : 'N/A') }}</div>
                                </div>
                            </div>
                            <button class="text-red-500 hover:text-red-700 p-1" @click="removeProduct(idx)" title="Xóa">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block text-gray-600 mb-1">Giá Flash Sale</label>
                                <input v-model="item.flashPrice"
                                    class="input w-full border border-gray-300 rounded p-2 text-sm"
                                    placeholder="Giá FS" />
                                <div v-if="errors.products && errors.products[item.id]"
                                    class="text-red-500 text-xs mt-1">
                                    {{ errors.products[item.id] }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-1">Đã bán</label>
                                <input v-model="item.sold"
                                    class="input w-full border border-gray-300 rounded p-2 text-sm"
                                    placeholder="Đã bán" />
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-1">Số lượng</label>
                                <input v-model="item.quantity"
                                    class="input w-full border border-gray-300 rounded p-2 text-sm" placeholder="SL" />
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-1">SL Thật</label>
                                <div class="flex items-center pt-2">
                                    <input type="checkbox" v-model="item.realQty" class="w-4 h-4" />
                                    <span class="ml-2 text-xs text-gray-600">Sử dụng số lượng thật</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center items-center gap-2 mt-4">
                    <button class="px-3 py-1 rounded border border-gray-300 text-sm cursor-pointer"
                        :disabled="productPage === 1" @click="productPage > 1 && (productPage--)">&lt;</button>
                    <span class="text-sm">Trang {{ productPage }} / {{ productTotalPages }}</span>
                    <button class="px-3 py-1 rounded border border-gray-300 text-sm cursor-pointer"
                        :disabled="productPage === productTotalPages"
                        @click="productPage < productTotalPages && (productPage++)">&gt;</button>
                </div>
                <div v-if="errors.products && typeof errors.products === 'string'"
                    class="text-red-500 text-sm mt-3 text-center">
                    {{ errors.products }}
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { useFlashsale } from '../../../composable/useFlashsale'
import { useProducts } from '../../../composable/useProducts'
import { useRouter } from 'vue-router'
import { push } from 'notivue'
function formatPrice(price) {
    if (price === null || price === undefined || price === '') return 'N/A'
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(Number(price))
}
function truncate(str, n = 30) {
    if (!str) return ''
    return str.length > n ? str.slice(0, n) + '...' : str
}
const props = defineProps({
    editData: Object
})

// Debug: Log editData prop
const products = ref([])
const productPage = ref(1)
const productPageSize = 5
const productTotalPages = computed(() => Math.ceil(products.value.length / productPageSize))
const paginatedProducts = computed(() => {
    const start = (productPage.value - 1) * productPageSize
    return products.value.slice(start, start + productPageSize)
})
const form = ref({
    name: '',
    start: '',
    end: '',
    repeat: false,
    repeatMinutes: 60,
    autoIncrease: false,
    increaseAmount: 1,
    active: true
})
const loading = ref(false)
const errors = ref({})
const success = ref('')
const { createFlashSale, updateFlashSale, getMainImage } = useFlashsale()
const { getProducts } = useProducts()
const allProducts = ref([])
const router = useRouter()
function goToSelectProducts() {
    localStorage.setItem('flashsale_form_data', JSON.stringify(form.value));

    if (props.editData && props.editData.id) {
        localStorage.setItem(`flashsale_edit_${props.editData.id}`, JSON.stringify(products.value))
        router.push(`/admin/flashsale/select?flashSaleId=${props.editData.id}`)
    } else {
        localStorage.setItem('flashsale_selected_products', JSON.stringify(products.value))
        router.push('/admin/flashsale/select')
    }
}
onMounted(async () => {
    allProducts.value = await getProducts()
    const savedForm = localStorage.getItem('flashsale_form_data');
    if (savedForm) {
        try {
            Object.assign(form.value, JSON.parse(savedForm));
            localStorage.removeItem('flashsale_form_data');
        } catch { }
    }
    const selected = localStorage.getItem('flashsale_selected_products')
    const flashSaleId = props.editData?.id
    const editSelected = flashSaleId ? localStorage.getItem(`flashsale_edit_${flashSaleId}`) : null

    const productsToLoad = editSelected || selected

    if (productsToLoad) {
        try {
            products.value = JSON.parse(productsToLoad)
            localStorage.removeItem('flashsale_selected_products')
            if (editSelected) {
                localStorage.removeItem(`flashsale_edit_${flashSaleId}`)
            }
        } catch { }
    }
})
watch(() => props.editData, (val) => {
    if (val) {
        form.value = {
            name: val.name || '',
            start: val.start_time ? val.start_time.slice(0, 16) : (val.start || ''),
            end: val.end_time ? val.end_time.slice(0, 16) : (val.end || ''),
            repeat: val.repeat || false,
            repeatMinutes: val.repeat_minutes || val.repeatMinutes || 60,
            autoIncrease: val.auto_increase || val.autoIncrease || false,
            increaseAmount: val.increase_amount || val.increaseAmount || 1,
            active: val.active !== undefined ? val.active : true
        }
        if (val.products && val.products.length > 0 && products.value.length === 0) {
            products.value = val.products.map(p => {
                const productData = p.product || {}
                return {
                    id: p.product_id || productData.id,
                    product_id: p.product_id || productData.id,
                    name: productData.name || p.name,
                    price: productData.price || p.price,
                    discount_price: productData.discount_price || p.discount_price,
                    flashPrice: p.flash_price || p.flashPrice || '',
                    quantity: p.quantity || 100,
                    sold: p.sold || 0,
                    realQty: p.real_qty !== undefined ? p.real_qty : true,
                    image: productData.main_image?.image_path || productData.image || '/default-product.png',
                    product: productData,
                    variantAllocations: (productData.variants || []).map(v => ({ variant_id: v.id, qty: 0 }))
                }
            })
        }
    }
}, { immediate: true })

watch(() => form.value.name, () => {
    if (errors.value.name) delete errors.value.name
})
watch(() => form.value.start, () => {
    if (errors.value.start) delete errors.value.start
    if (errors.value.end) delete errors.value.end
})
watch(() => form.value.end, () => {
    if (errors.value.end) delete errors.value.end
})
watch(() => form.value.repeatMinutes, () => {
    if (errors.value.repeatMinutes) delete errors.value.repeatMinutes
})
watch(() => form.value.increaseAmount, () => {
    if (errors.value.increaseAmount) delete errors.value.increaseAmount
})

watch(() => products.value, () => {
    if (errors.value.products) delete errors.value.products
}, { deep: true })

function addProduct(product) {
    const existingIndex = products.value.findIndex(p => p.id === product.id)
    if (existingIndex === -1) {
        products.value.push({
            ...product,
            flashPrice: '',
            quantity: 100,
            sold: 0,
            realQty: true,
            variantAllocations: (product.variants || []).map(v => ({ variant_id: v.id, qty: 0 }))
        })
    }
}
function removeProduct(idx) {
    products.value.splice(idx, 1)
}

function increaseSold(item) {
    const currentSold = Number(item.sold) || 0
    const increaseAmount = Number(form.value.increaseAmount) || 1
    const maxQuantity = Number(item.quantity) || 0

    const newSold = Math.min(currentSold + increaseAmount, maxQuantity)
    item.sold = newSold.toString()
}
function getAlloc(item, variantId) {
    if (!item.variantAllocations) item.variantAllocations = []
    let found = item.variantAllocations.find(a => a.variant_id === variantId)
    if (!found) {
        found = { variant_id: variantId, qty: 0 }
        item.variantAllocations.push(found)
    }
    return found
}

function sumAlloc(item) {
    return (item.variantAllocations || []).reduce((s, a) => s + (Number(a.qty) || 0), 0)
}

function validateForm() {
    errors.value = {}

    if (!form.value.name || form.value.name.trim() === '') {
        errors.value.name = 'Tên chiến dịch không được để trống'
    }

    if (!form.value.start) {
        errors.value.start = 'Thời gian bắt đầu không được để trống'
    }

    if (!form.value.end) {
        errors.value.end = 'Thời gian kết thúc không được để trống'
    }

    if (form.value.start && form.value.end && new Date(form.value.start) >= new Date(form.value.end)) {
        errors.value.end = 'Thời gian kết thúc phải sau thời gian bắt đầu'
    }

    if (form.value.repeat && (!form.value.repeatMinutes || form.value.repeatMinutes < 1)) {
        errors.value.repeatMinutes = 'Số phút lặp lại phải lớn hơn 0'
    }

    if (form.value.autoIncrease && (!form.value.increaseAmount || form.value.increaseAmount < 1)) {
        errors.value.increaseAmount = 'Số lượng tăng phải lớn hơn 0'
    }

    if (!products.value || products.value.length === 0) {
        errors.value.products = 'Phải có ít nhất một sản phẩm'
    } else {
        products.value.forEach((product, index) => {
            if (!product.flashPrice || product.flashPrice === '') {
                if (!errors.value.products) errors.value.products = {}
                errors.value.products[product.id] = 'Giá Flash Sale không được để trống'
            } else if (isNaN(Number(product.flashPrice)) || Number(product.flashPrice) <= 0) {
                if (!errors.value.products) errors.value.products = {}
                errors.value.products[product.id] = 'Giá Flash Sale phải là số dương'
            }

            if (!product.quantity || product.quantity === '') {
                if (!errors.value.products) errors.value.products = {}
                errors.value.products[product.id] = 'Số lượng không được để trống'
            } else if (isNaN(Number(product.quantity)) || Number(product.quantity) <= 0) {
                if (!errors.value.products) errors.value.products = {}
                errors.value.products[product.id] = 'Số lượng phải là số dương'
            }

            if (product.sold !== undefined && product.sold !== '') {
                if (isNaN(Number(product.sold)) || Number(product.sold) < 0) {
                    if (!errors.value.products) errors.value.products = {}
                    errors.value.products[product.id] = 'Số lượng đã bán phải là số không âm'
                }

                if (Number(product.sold) > Number(product.quantity)) {
                    if (!errors.value.products) errors.value.products = {}
                    errors.value.products[product.id] = 'Số lượng đã bán không được vượt quá số lượng tổng'
                }
            }
        })
    }

    return Object.keys(errors.value).length === 0
}

async function submit() {
    if (!validateForm()) {
        return
    }

    success.value = ''
    loading.value = true
    try {
        const payload = {
            name: form.value.name,
            start_time: form.value.start,
            end_time: form.value.end,
            repeat: form.value.repeat,
            repeat_minutes: form.value.repeatMinutes,
            auto_increase: form.value.autoIncrease,
            increase_amount: form.value.increaseAmount,
            active: form.value.active,
            products: products.value.map(p => ({
                product_id: p.product_id ? p.product_id : p.id,
                flash_price: p.flashPrice !== '' ? Number(p.flashPrice) : '',
                quantity: Number(p.quantity) || 0,
                sold: Number(p.sold) || 0,
                real_qty: p.realQty !== undefined ? p.realQty : true
            }))
        }
        let res
        if (props.editData && props.editData.id) {
            res = await updateFlashSale(props.editData.id, payload)
            push.success('Cập nhật flash sale thành công!')
        } else {
            res = await createFlashSale(payload)
            push.success('Tạo flash sale thành công!')
            setTimeout(() => router.push('/admin/flashsale'), 1000)
        }
        success.value = 'Lưu flash sale thành công'
        localStorage.removeItem('flashsale_form_data');
    } catch (e) {
        const apiMsg = e?.response?.data?.error
        const apiErrors = e?.response?.data?.errors

        if (apiErrors && typeof apiErrors === 'object') {
            errors.value = apiErrors
        } else if (typeof apiMsg === 'string' && apiMsg) {
            success.value = ''
            push.error(apiMsg)
        } else {
            push.error(e.message || 'Có lỗi xảy ra khi lưu flash sale')
        }

        if (Array.isArray(e?.response?.data?.insufficient_variants)) {
            console.warn('Insufficient variants:', e.response.data.insufficient_variants)
        }
    } finally {
        loading.value = false
    }
}
</script>