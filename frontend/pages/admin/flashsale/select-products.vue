<template>
    <div class="tw-bg-[#f7f8fa] tw-p-6 tw-min-h-screen">
        <div class="tw-bg-[#3BB77E] tw-text-white tw-p-4 tw-text-xl tw-font-bold tw-rounded-t">Thêm Flash Sale</div>
        <div class="tw-bg-white tw-p-6 tw-rounded-b tw-shadow">
            <div class="tw-mb-4 tw-flex tw-items-center tw-gap-4">
                <input v-model="search"
                    class="tw-input tw-w-80 tw-border tw-rounded tw-p-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                    placeholder="Gõ tên sản phẩm để tìm kiếm" />
                <button class="tw-bg-[#3BB77E] tw-text-white tw-rounded tw-px-4 tw-py-2" @click="showDiscount = true"><i
                        class="fa fa-percent"></i> Áp dụng giảm giá hàng loạt</button>
            </div>
            <div class="tw-mb-8">
                <h3 class="tw-font-bold tw-mb-2">Tất cả sản phẩm</h3>
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-bg-white tw-rounded tw-shadow-sm">
                        <thead>
                            <tr class="tw-bg-gray-100 tw-text-gray-700">
                                <th class="tw-px-2 tw-py-2">Ảnh</th>
                                <th class="tw-px-2 tw-py-2">Tên sản phẩm</th>
                                <th class="tw-px-2 tw-py-2">Giá thường</th>
                                <th class="tw-px-2 tw-py-2">Giá KM</th>
                                <th class="tw-px-2 tw-py-2">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in paginatedProducts" :key="item.id" class="tw-border-b">
                                <td class="tw-px-2 tw-py-2"><img :src="getMainImage(item)"
                                        class="tw-w-10 tw-h-10 tw-rounded" /></td>
                                <td class="tw-px-2 tw-py-2">{{ item.name }}</td>
                                <td class="tw-px-2 tw-py-2">{{ item.price }}</td>
                                <td class="tw-px-2 tw-py-2">{{ item.discount_price }}</td>
                                <td class="tw-px-2 tw-py-2">
                                    <button class="tw-bg-[#3BB77E] tw-text-white tw-px-2 tw-py-1 tw-rounded"
                                        @click="addProduct(item)"
                                        :disabled="selectedProducts.some(p => p.id === item.id)"> <i
                                            class="fas fa-plus"></i> </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="tw-flex tw-justify-center tw-items-center tw-gap-2 tw-mt-2">
                        <button class="tw-px-3 tw-py-1 tw-rounded tw-bg-gray-200" :disabled="page === 1"
                            @click="page > 1 && (page--)">&lt;</button>
                        <span>Trang {{ page }} / {{ totalPages }}</span>
                        <button class="tw-px-3 tw-py-1 tw-rounded tw-bg-gray-200" :disabled="page === totalPages"
                            @click="page < totalPages && (page++)">&gt;</button>
                    </div>
                </div>
            </div>
            <div class="tw-mb-4">
                <h3 class="tw-font-bold tw-mb-2">Sản phẩm đã chọn</h3>
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-bg-white tw-rounded tw-shadow-sm">
                        <thead>
                            <tr class="tw-bg-gray-100 tw-text-gray-700">
                                <th class="tw-px-2 tw-py-2">Ảnh</th>
                                <th class="tw-px-2 tw-py-2">Tên sản phẩm</th>
                                <th class="tw-px-2 tw-py-2">Giá thường</th>
                                <th class="tw-px-2 tw-py-2">Giá KM</th>
                                <th class="tw-px-2 tw-py-2">Giá Flash sale</th>
                                <th class="tw-px-2 tw-py-2">Đã bán</th>
                                <th class="tw-px-2 tw-py-2">Số lượng</th>
                                <th class="tw-px-2 tw-py-2">SL Thật</th>
                                <th class="tw-px-2 tw-py-2">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, idx) in paginatedSelectedProducts" :key="item.id" class="tw-border-b">
                                <td class="tw-px-2 tw-py-2"><img :src="getMainImage(item)"
                                        class="tw-w-10 tw-h-10 tw-rounded" /></td>
                                <td class="tw-px-2 tw-py-2">{{ item.name }}</td>
                                <td class="tw-px-2 tw-py-2">{{ item.price }}</td>
                                <td class="tw-px-2 tw-py-2">{{ item.discount_price }}</td>
                                <td class="tw-px-2 tw-py-2"><input v-model="item.flashPrice" class="input tw-w-20"
                                        placeholder="Giá FS" /></td>
                                <td class="tw-px-2 tw-py-2"><input v-model="item.sold" class="input tw-w-16"
                                        placeholder="Đã bán" /></td>
                                <td class="tw-px-2 tw-py-2"><input v-model="item.quantity" class="input tw-w-16"
                                        placeholder="SL" /></td>
                                <!-- <td class="tw-px-2 tw-py-2"><input type="checkbox" v-model="item.realQty" /></td> -->
                                <td class="tw-px-2 tw-py-2">
                                    <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                                        <input type="checkbox" v-model="item.realQty" class="tw-sr-only tw-peer" />
                                        <div class="tw-w-11 tw-h-6 tw-bg-gray-200 tw-rounded-full tw-peer-focus:tw-ring-2 tw-peer-focus:tw-ring-[#3BB77E]
                                                peer-checked:tw-bg-[#3BB77E] tw-transition-colors"></div>
                                        <div class="tw-absolute tw-left-[2px] tw-top-[2px] tw-bg-white tw-border tw-border-gray-300
                    tw-rounded-full tw-h-5 tw-w-5 tw-transition-all
                    peer-checked:tw-translate-x-full peer-checked:tw-border-white"></div>
                                    </label>
                                </td>

                                <td class="tw-px-2 tw-py-2">
                                    <button class="tw-bg-red-500 tw-text-white tw-px-2 tw-py-1 tw-rounded"
                                        @click="remove(idx + (selectedPage - 1) * selectedPageSize)"><i
                                            class="fa fa-minus"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="selectedProducts.length === 0" class="tw-text-center tw-text-gray-500 tw-mt-2">
                        Bạn chưa chọn sản phẩm nào
                    </div>
                    <div class="tw-flex tw-justify-center tw-items-center tw-gap-2 tw-mt-2">
                        <button class="tw-px-3 tw-py-1 tw-rounded tw-bg-gray-200" :disabled="selectedPage === 1"
                            @click="selectedPage > 1 && (selectedPage--)">&lt;</button>
                        <span>Trang {{ selectedPage }} / {{ selectedTotalPages }}</span>
                        <button class="tw-px-3 tw-py-1 tw-rounded tw-bg-gray-200"
                            :disabled="selectedPage === selectedTotalPages"
                            @click="selectedPage < selectedTotalPages && (selectedPage++)">&gt;</button>
                    </div>
                </div>
            </div>
            <div class="tw-flex tw-justify-end tw-mt-4">
                <button class="tw-bg-[#3BB77E] tw-text-white tw-px-6 tw-py-2 tw-rounded" @click="apply">Áp dụng</button>
                <button class="tw-bg-gray-400 tw-text-white tw-px-6 tw-py-2 tw-ml-2 tw-rounded" @click="goBack"><i
                        class="fas fa-arrow-left"></i> Quay lại</button>
            </div>
            <!-- Popup giảm giá hàng loạt -->
            <div v-if="showDiscount"
                class="tw-fixed tw-top-0 tw-left-0 tw-w-full tw-h-full tw-bg-black/30 tw-z-50 tw-flex tw-items-center tw-justify-center">
                <div class="tw-bg-white tw-shadow-lg tw-rounded tw-p-6 tw-z-50 tw-w-96">
                    <div class="tw-font-bold tw-mb-2">Thiết lập giảm giá hàng loạt</div>
                    <div class="tw-flex tw-gap-2 tw-mb-2">
                        <button :class="discountType === '%' ? 'tw-bg-blue-600 tw-text-white' : 'tw-bg-gray-200'"
                            class="tw-px-3 tw-py-1 tw-rounded" @click="discountType = '%'">%</button>
                        <button :class="discountType === '$' ? 'tw-bg-blue-600 tw-text-white' : 'tw-bg-gray-200'"
                            class="tw-px-3 tw-py-1 tw-rounded" @click="discountType = '$'">$</button>
                        <button :class="discountType === '₫' ? 'tw-bg-blue-600 tw-text-white' : 'tw-bg-gray-200'"
                            class="tw-px-3 tw-py-1 tw-rounded" @click="discountType = '₫'">Đồng ₫</button>
                    </div>
                    <input v-model.number="discountValue" type="number" class="input tw-w-full tw-mb-2"
                        placeholder="Nhập giá trị giảm" />
                    <div class="tw-flex tw-justify-end tw-gap-2">
                        <button class="tw-bg-gray-300 tw-text-black tw-px-4 tw-py-1 tw-rounded"
                            @click="showDiscount = false">Đóng</button>
                        <button class="tw-bg-blue-600 tw-text-white tw-px-4 tw-py-1 tw-rounded"
                            @click="applyDiscount">Áp dụng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})
useHead({
    title: "Thêm sản phẩm sale"
})
import { ref, computed, onMounted } from 'vue'
import { useProducts } from '@/composables/useProducts'
import { useFlashsale } from '@/composables/useFlashsale'
import { useRouter } from 'vue-router'
import { useRoute } from 'vue-router'
const search = ref('')
const page = ref(1)
const pageSize = 5

const totalPages = computed(() => Math.ceil(filteredAllProducts.value.length / pageSize))
const paginatedProducts = computed(() => {
    const start = (page.value - 1) * pageSize
    return filteredAllProducts.value.slice(start, start + pageSize)
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

const selectedPage = ref(1)
const selectedPageSize = 5
const selectedTotalPages = computed(() => Math.ceil(selectedProducts.value.length / selectedPageSize))
const paginatedSelectedProducts = computed(() => {
    const start = (selectedPage.value - 1) * selectedPageSize
    return selectedProducts.value.slice(start, start + selectedPageSize)
})

onMounted(async () => {
    loading.value = true
    try {
        const data = await getProducts()
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

        // Kiểm tra xem có phải đang edit flash sale không
        const flashSaleId = route.query.flashSaleId
        if (flashSaleId) {
            // Nếu có flashSaleId trong query, load sản phẩm từ localStorage hoặc API
            const savedProducts = localStorage.getItem(`flashsale_edit_${flashSaleId}`)
            if (savedProducts) {
                try {
                    selectedProducts.value = JSON.parse(savedProducts)
                } catch (e) {
                    console.error('Lỗi parse saved products:', e)
                }
            } else {
                // Nếu chưa có localStorage, gọi API lấy flash sale
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
    } catch (e) {
        error.value = e.message || 'Không lấy được danh sách sản phẩm'
    } finally {
        loading.value = false
    }
})

const filteredAllProducts = computed(() => {
    if (!search.value) return allProducts.value
    return allProducts.value.filter(p => p.name.toLowerCase().includes(search.value.toLowerCase()))
})

function addProduct(product) {
    if (!selectedProducts.value.find(p => p.id === product.id)) {
        selectedProducts.value.push({
            ...product,
            flashPrice: '',
            quantity: 10,
            sold: product.sold ?? 0,
            realQty: true
        })
    }
}
function remove(idx) {
    selectedProducts.value.splice(idx, 1)
}
function apply() {
    // Lưu danh sách sản phẩm đã chọn vào localStorage
    localStorage.setItem('flashsale_selected_products', JSON.stringify(selectedProducts.value))

    // Nếu đang edit, lưu thêm vào localStorage với key riêng
    const flashSaleId = route.query.flashSaleId
    if (flashSaleId) {
        localStorage.setItem(`flashsale_edit_${flashSaleId}`, JSON.stringify(selectedProducts.value))
    }

    // Quay lại trang form flash sale
    if (flashSaleId) {
        router.push(`/admin/flashsale/${flashSaleId}/edit`)
    } else {
        router.push('/admin/flashsale/create')
    }
}
function applyDiscount() {
    selectedProducts.value.forEach(p => {
        let base = Number(p.price) || 0
        if (discountType.value === '%') {
            p.flashPrice = base ? Math.round(base * (1 - discountValue.value / 100)) : ''
        } else if (discountType.value === '$' || discountType.value === '₫') {
            p.flashPrice = base ? Math.max(0, base - discountValue.value) : ''
        }
    })
    showDiscount.value = false
}
function goBack() {
    router.back()
}
</script>

<!-- <style scoped>
.input {
    @apply tw-border tw-rounded tw-px-2 tw-py-1;
}

.btn-danger {
    @apply tw-bg-red-600 tw-text-white tw-px-2 tw-py-1 tw-rounded;
}

.btn-primary {
    @apply tw-bg-[#3BB77E] tw-text-white tw-px-2 tw-py-1 tw-rounded;
}
</style> -->