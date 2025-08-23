<template>
    <div class="bg-[#f7f8fa] p-3 sm:p-6 min-h-screen">
        <div class="bg-[#3BB77E] text-white p-3 sm:p-4 text-lg sm:text-xl font-bold rounded-t">Thêm Flash Sale</div>
        <div class="bg-white p-3 sm:p-6 rounded-b shadow">
            <div class="mb-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
                <input v-model="search"
                    class="input flex-1 sm:w-80 border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 text-sm"
                    placeholder="Gõ tên sản phẩm để tìm kiếm" />
                <button class="bg-[#3BB77E] text-white rounded px-4 py-2 cursor-pointer text-sm whitespace-nowrap"
                    @click="openDiscountModal"><i class="fa fa-percent"></i> Áp dụng giảm giá hàng loạt</button>
            </div>
            <div class="mb-6 sm:mb-8">
                <h3 class="font-bold mb-2 text-sm sm:text-base">Tất cả sản phẩm</h3>

                <!-- Loading state -->
                <div v-if="loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#3BB77E]"></div>
                    <p class="mt-2 text-gray-600">Đang tải danh sách sản phẩm...</p>
                </div>

                <!-- Error state -->
                <div v-else-if="error" class="text-center py-8">
                    <div class="text-red-500 mb-2">
                        <i class="fas fa-exclamation-triangle text-2xl"></i>
                    </div>
                    <p class="text-red-600 mb-2">{{ error }}</p>
                    <button @click="retryLoad" class="bg-[#3BB77E] text-white px-4 py-2 rounded">
                        Thử lại
                    </button>
                </div>

                <!-- Empty state -->
                <div v-else-if="allProducts.length === 0" class="text-center py-8">
                    <div class="text-gray-400 mb-2">
                        <i class="fas fa-box-open text-4xl"></i>
                    </div>
                    <p class="text-gray-600">Không có sản phẩm nào</p>
                </div>

                <!-- Products list -->
                <div v-else>
                    <!-- Desktop table view -->
                    <div
                        class="hidden lg:block overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
                        <table class="w-full bg-white rounded">
                            <thead class="border-b border-gray-300">
                                <tr>
                                    <th class="px-2 py-2 text-sm">Ảnh</th>
                                    <th class="px-2 py-2 text-sm">Tên sản phẩm</th>
                                    <th class="px-2 py-2 text-sm">Giá thường</th>
                                    <th class="px-2 py-2 text-sm">Giá KM</th>
                                    <th class="px-2 py-2 text-sm">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in paginatedProducts" :key="item.id"
                                    class="border-b border-gray-300 text-center">
                                    <td class="px-2 py-2 flex items-center justify-center"><img
                                            :src="getMainImage(item)" class="w-10 h-10 rounded" /></td>
                                    <td class="px-2 py-2 text-sm">{{ item.name }}</td>
                                    <td class="px-2 py-2 text-sm">{{ item.price }}</td>
                                    <td class="px-2 py-2 text-sm">{{ item.discount_price }}</td>
                                    <td class="px-2 py-2">
                                        <span v-if="selectedProducts.some(p => p.id === item.id)"
                                            class="text-green-600 font-semibold text-xs">Đã chọn</span>
                                        <button v-else
                                            class="bg-[#3BB77E] text-white px-2 py-1 rounded cursor-pointer text-xs"
                                            @click="addProduct(item)"> <i class="fas fa-plus"></i> </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile card view -->
                    <div class="lg:hidden space-y-3">
                        <div v-for="item in paginatedProducts" :key="item.id"
                            class="border border-gray-200 rounded-lg p-3 bg-white">
                            <div class="flex items-center gap-3">
                                <img :src="getMainImage(item)" class="w-12 h-12 rounded flex-shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-sm text-gray-900 truncate">{{ item.name }}</h4>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <div>Giá thường: {{ item.price }}</div>
                                        <div>Giá KM: {{ item.discount_price }}</div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <span v-if="selectedProducts.some(p => p.id === item.id)"
                                        class="text-green-600 font-semibold text-xs bg-green-50 px-2 py-1 rounded">Đã
                                        chọn</span>
                                    <button v-else
                                        class="bg-[#3BB77E] text-white px-3 py-1 rounded cursor-pointer text-xs"
                                        @click="addProduct(item)">
                                        <i class="fas fa-plus mr-1"></i>Chọn
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 mt-4">
                        <button class="px-3 py-1 rounded border border-gray-300 text-sm cursor-pointer"
                            :disabled="page === 1" @click="page > 1 && (page--)">&lt;</button>
                        <span class="text-sm">Trang {{ page }} / {{ totalPages }}</span>
                        <button class="px-3 py-1 rounded border border-gray-300 text-sm cursor-pointer"
                            :disabled="page === totalPages" @click="page < totalPages && (page++)">&gt;</button>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <h3 class="font-bold mb-2 text-sm sm:text-base">Sản phẩm đã chọn</h3>

                <!-- Desktop table view -->
                <div
                    class="hidden lg:block overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
                    <table class="w-full bg-white rounded text-center">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="px-2 py-2 text-sm">Ảnh</th>
                                <th class="px-2 py-2 text-sm">Tên sản phẩm</th>
                                <th class="px-2 py-2 text-sm">Giá thường</th>
                                <th class="px-2 py-2 text-sm">Giá KM</th>
                                <th class="px-2 py-2 text-sm">Giá Flash sale</th>
                                <th class="px-2 py-2 text-sm">Đã bán</th>
                                <th class="px-2 py-2 text-sm">Số lượng</th>
                                <th class="px-2 py-2 text-sm">SL Thật</th>
                                <th class="px-2 py-2 text-sm">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, idx) in paginatedSelectedProducts" :key="item.id"
                                class="border-b border-gray-300">
                                <td class="px-2 py-2">
                                    <div class="flex justify-center items-center">
                                        <img :src="getMainImage(item)" class="w-10 h-10 rounded" />
                                    </div>
                                </td>
                                <td class="px-2 py-2 text-sm">{{ item.name }}</td>
                                <td class="px-2 py-2 text-sm">{{ item.price }}</td>
                                <td class="px-2 py-2 text-sm">{{ item.discount_price }}</td>
                                <td class="px-2 py-2">
                                    <input v-model="item.flashPrice"
                                        class="input w-20 text-center border border-gray-300 rounded text-sm"
                                        placeholder="Giá FS" />
                                </td>
                                <td class="px-2 py-2">
                                    <input v-model="item.sold"
                                        class="input w-16 text-center border border-gray-300 rounded text-sm"
                                        placeholder="Đã bán" />
                                </td>
                                <td class="px-2 py-2">
                                    <input v-model="item.quantity"
                                        class="input w-16 text-center border border-gray-300 rounded text-sm"
                                        placeholder="SL" />
                                </td>
                                <td class="px-2 py-2">
                                    <div class="flex justify-center items-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" v-model="item.realQty" class="sr-only peer" />
                                            <div class="w-11 h-6 bg-gray-200 rounded-full peer-focus:ring-2 peer-focus:ring-[#3BB77E]
                peer-checked:bg-[#3BB77E] transition-colors"></div>
                                            <div class="absolute left-[2px] top-[2px] bg-white border border-gray-300 rounded-full h-5 w-5 transition-all
                peer-checked:translate-x-full peer-checked:border-white"></div>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-2 py-2">
                                    <div class="flex justify-center">
                                        <button class="bg-red-500 text-white px-2 py-1 rounded cursor-pointer text-xs"
                                            @click="remove(idx + (selectedPage - 1) * selectedPageSize)">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="paginatedSelectedProducts.length === 0">
                                <td colspan="9" class="px-2 py-2 text-sm text-center">Bạn chưa chọn sản phẩm nào</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile card view -->
                <div class="lg:hidden space-y-3">
                    <div v-if="selectedProducts.length === 0" class="text-center text-gray-500 py-6">
                        Bạn chưa chọn sản phẩm nào
                    </div>
                    <div v-for="(item, idx) in paginatedSelectedProducts" :key="item.id"
                        class="border border-gray-200 rounded-lg p-3 bg-white">
                        <div class="flex items-start gap-3 mb-3">
                            <img :src="getMainImage(item)" class="w-12 h-12 rounded flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-sm text-gray-900 truncate">{{ item.name }}</h4>
                                <div class="text-xs text-gray-500 mt-1">
                                    <div>Giá thường: {{ item.price }}</div>
                                    <div>Giá KM: {{ item.discount_price }}</div>
                                </div>
                            </div>
                            <button class="text-red-500 hover:text-red-700 p-1"
                                @click="remove(idx + (selectedPage - 1) * selectedPageSize)" title="Xóa">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block text-gray-600 mb-1">Giá Flash Sale</label>
                                <input v-model="item.flashPrice"
                                    class="input w-full border border-gray-300 rounded p-2 text-sm"
                                    placeholder="Giá FS" />
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

                <div v-if="selectedProducts.length > 0" class="flex justify-center items-center gap-2 mt-4">
                    <button class="px-3 py-1 rounded border border-gray-300 text-sm cursor-pointer"
                        :disabled="selectedPage === 1" @click="selectedPage > 1 && (selectedPage--)">&lt;</button>
                    <span class="text-sm">Trang {{ selectedPage }} / {{ selectedTotalPages }}</span>
                    <button class="px-3 py-1 rounded border border-gray-300 text-sm cursor-pointer"
                        :disabled="selectedPage === selectedTotalPages"
                        @click="selectedPage < selectedTotalPages && (selectedPage++)">&gt;</button>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row justify-end gap-2 mt-4">
                <button class="bg-[#3BB77E] text-white px-6 py-2 rounded cursor-pointer text-sm order-2 sm:order-1"
                    @click="apply">Áp dụng</button>
                <button class="bg-gray-400 text-white px-6 py-2 rounded cursor-pointer text-sm order-1 sm:order-2"
                    @click="goBack"><i class="fas fa-arrow-left"></i> Quay lại</button>
            </div>
            <!-- Popup giảm giá hàng loạt -->
            <div v-if="showDiscount"
                class="fixed top-0 left-0 w-full h-full bg-black/30 z-50 flex items-center justify-center p-4">
                <div class="bg-white shadow-lg rounded p-4 sm:p-6 z-50 w-full max-w-sm sm:max-w-md">
                    <div class="font-bold mb-3 text-sm sm:text-base">Thiết lập giảm giá hàng loạt</div>
                    <div class="flex gap-2 mb-3">
                        <button :class="discountType === '%' ? 'bg-blue-600 text-white' : 'bg-gray-200'"
                            class="px-3 py-1 rounded cursor-pointer text-sm flex-1"
                            @click="discountType = '%'">%</button>
                        <button :class="discountType === '₫' ? 'bg-blue-600 text-white' : 'bg-gray-200'"
                            class="px-3 py-1 rounded cursor-pointer text-sm flex-1" @click="discountType = '₫'">Đồng
                            ₫</button>
                    </div>
                    <input v-model.number="discountValue" type="number"
                        :class="['border w-full p-2 text-sm mb-1', validationError ? 'border-red-500' : 'border-gray-300']" 
                        :placeholder="discountType === '%' ? 'Nhập % giảm (tối đa 99%)' : 'Nhập số tiền giảm'" />
                    
                    <!-- Validation message -->
                    <div v-if="validationError" class="text-red-500 text-xs mb-2 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ validationError }}
                    </div>
                    <div v-else class="mb-2"></div>
                    <div class="flex flex-col sm:flex-row justify-end gap-2">
                        <button
                            class="bg-gray-300 text-black px-4 py-2 rounded cursor-pointer text-sm order-2 sm:order-1"
                            @click="closeDiscountModal">Đóng</button>
                        <button
                            class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer text-sm order-1 sm:order-2"
                            @click="applyDiscount">Áp dụng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useProducts } from '../../../composable/useProducts'
import { useFlashsale } from '../../../composable/useFlashsale'
import { useRouter, useRoute } from "vue-router";
const search = ref('')
const page = ref(1)
const pageSize = 5 

const totalPages = computed(() => {
    const pages = Math.ceil(filteredAllProducts.value.length / pageSize)
    return pages
})
const paginatedProducts = computed(() => {
    const start = (page.value - 1) * pageSize
    const result = filteredAllProducts.value.slice(start, start + pageSize)
    return result
})
const { getProducts } = useProducts()
const allProducts = ref([])
const loading = ref(false)
const error = ref('')
const { getMainImage } = useFlashsale()
const router = useRouter()
const route = useRoute()
const { getFlashSaleById } = useFlashsale()

const selectedProducts = ref([])
const showDiscount = ref(false)
const discountType = ref('%')
const discountValue = ref(0)
const validationError = ref('')

const selectedPage = ref(1)
const selectedPageSize = 5
const selectedTotalPages = computed(() => {
    const pages = Math.ceil(selectedProducts.value.length / selectedPageSize)
    return pages
})
const paginatedSelectedProducts = computed(() => {
    const start = (selectedPage.value - 1) * selectedPageSize
    const result = selectedProducts.value.slice(start, start + selectedPageSize)
    return result
})

// Function to load products
async function loadProducts() {
    loading.value = true
    error.value = ''
    try {
        const data = await getProducts({}, 1, 100)

        if (data && data.products && Array.isArray(data.products)) {
            allProducts.value = data.products.map(p => {
                let img = '/default-product.png';
                if (p.images && Array.isArray(p.images) && p.images.length > 0) {
                    const mainImg = p.images.find(img => img.is_main == 1);
                    img = mainImg ? mainImg.image_path : p.images[0].image_path;
                }
                return {
                    ...p,
                    image: img
                }
            })
        } else if (Array.isArray(data)) {
            allProducts.value = data.map(p => {
                let img = '/default-product.png';
                if (p.images && Array.isArray(p.images) && p.images.length > 0) {
                    const mainImg = p.images.find(img => img.is_main == 1);
                    img = mainImg ? mainImg.image_path : p.images[0].image_path;
                }
                return {
                    ...p,
                    image: img
                }
            })
        } else {
            error.value = 'Cấu trúc dữ liệu không đúng'
            allProducts.value = []
        }
    } catch (e) {
        error.value = e.message || 'Không lấy được danh sách sản phẩm'
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    await loadProducts()

    const flashSaleId = route.query.flashSaleId
    if (flashSaleId) {
        const savedProducts = localStorage.getItem(`flashsale_edit_${flashSaleId}`)
        if (savedProducts) {
            try {
                selectedProducts.value = JSON.parse(savedProducts)
            } catch (e) {
                console.error('Lỗi parse saved products:', e)
            }
        } else {
            try {
                const flashSale = await getFlashSaleById(flashSaleId)
                if (flashSale && flashSale.products) {
                    selectedProducts.value = flashSale.products.map(p => {
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
                            product: productData
                        }
                    })
                }
            } catch (e) {
                console.error('Lỗi lấy flash sale:', e)
            }
        }
    }
})

// Watch discount type changes to reset validation
watch(discountType, (newType) => {
    validationError.value = ''
    // Re-validate current value with new type
    if (discountValue.value <= 0) {
        validationError.value = 'Giá trị giảm giá phải lớn hơn 0!'
    } else if (newType === '%' && discountValue.value > 99) {
        validationError.value = 'Giảm giá không được vượt quá 99%!'
    } else if (newType === '₫' && selectedProducts.value.length > 0) {
        // Check if discount would result in negative flash price
        const hasNegativePrice = selectedProducts.value.some(p => {
            const base = Number(p.price) || 0
            return base - discountValue.value < 0
        })
        
        if (hasNegativePrice) {
            validationError.value = 'Giá giảm không được âm! Vui lòng giảm số tiền giảm giá.'
        }
    }
})

// Watch discount value to show validation
watch(discountValue, (newValue) => {
    validationError.value = ''
    
    if (newValue <= 0) {
        validationError.value = 'Giá trị giảm giá phải lớn hơn 0!'
    } else if (discountType.value === '%' && newValue > 99) {
        validationError.value = 'Giảm giá không được vượt quá 99%!'
    } else if (discountType.value === '₫' && selectedProducts.value.length > 0) {
        // Check if discount would result in negative flash price
        const hasNegativePrice = selectedProducts.value.some(p => {
            const base = Number(p.price) || 0
            return base - newValue < 0
        })
        
        if (hasNegativePrice) {
            validationError.value = 'Giá giảm không được âm! Vui lòng giảm số tiền giảm giá.'
        }
    }
})

const filteredAllProducts = computed(() => {
    if (!search.value) return allProducts.value
    const filtered = allProducts.value.filter(p => p.name.toLowerCase().includes(search.value.toLowerCase()))
    return filtered
})

function addProduct(product) {
    if (!selectedProducts.value.find(p => p.id === product.id)) {
        const newProduct = {
            ...product,
            flashPrice: '',
            quantity: 10,
            sold: product.sold ?? 0,
            realQty: true
        }
        selectedProducts.value.push(newProduct)
    }
}

function remove(idx) {
    const removed = selectedProducts.value.splice(idx, 1)
}

function openDiscountModal() {
    validationError.value = ''
    discountValue.value = 0
    showDiscount.value = true
}

function closeDiscountModal() {
    showDiscount.value = false
    validationError.value = ''
    discountValue.value = 0
}
function apply() {
    localStorage.setItem('flashsale_selected_products', JSON.stringify(selectedProducts.value))

    const flashSaleId = route.query.flashSaleId
    if (flashSaleId) {
        localStorage.setItem(`flashsale_edit_${flashSaleId}`, JSON.stringify(selectedProducts.value))
    }

    if (flashSaleId) {
        router.push(`/admin/flashsale/${flashSaleId}/edit`)
    } else {
        router.push('/admin/flashsale/create')
    }
}
function applyDiscount() {
    // Clear previous validation error
    validationError.value = ''
    
    // Validation: Giá trị giảm giá phải là số dương
    if (discountValue.value <= 0) {
        validationError.value = 'Giá trị giảm giá phải lớn hơn 0!'
        return
    }
    
    // Validation: Không cho phép giảm giá quá 99%
    if (discountType.value === '%' && discountValue.value > 99) {
        validationError.value = 'Giảm giá không được vượt quá 99%!'
        return
    }
    
    // Check if discount would result in negative flash price
    if (discountType.value === '₫') {
        const hasNegativePrice = selectedProducts.value.some(p => {
            const base = Number(p.price) || 0
            return base - discountValue.value < 0
        })
        
        if (hasNegativePrice) {
            validationError.value = 'Giá giảm không được âm! Vui lòng giảm số tiền giảm giá.'
            return
        }
    }
    
    // Apply discount to all selected products
    selectedProducts.value.forEach(p => {
        let base = Number(p.price) || 0
        if (discountType.value === '%') {
            // Đảm bảo giảm giá không quá 99%
            const finalDiscount = Math.min(discountValue.value, 99)
            p.flashPrice = base ? Math.round(base * (1 - finalDiscount / 100)) : ''
        } else if (discountType.value === '$' || discountType.value === '₫') {
            p.flashPrice = base ? Math.max(0, base - discountValue.value) : ''
        }
    })
    
    // Close modal and reset
    showDiscount.value = false
    validationError.value = ''
}
function goBack() {
    router.back()
}
function retryLoad() {
    loadProducts()
}
</script>

<!-- <style scoped>
.input {
    @apply border rounded px-2 py-1;
}

.btn-danger {
    @apply bg-red-600 text-white px-2 py-1 rounded;
}

.btn-primary {
    @apply bg-[#3BB77E] text-white px-2 py-1 rounded;
}
</style> -->