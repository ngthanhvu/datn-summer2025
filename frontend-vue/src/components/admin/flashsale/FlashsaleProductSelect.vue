<template>
    <div class="bg-[#f7f8fa] p-6 min-h-screen">
        <div class="bg-[#3BB77E] text-white p-4 text-xl font-bold rounded-t">Thêm Flash Sale</div>
        <div class="bg-white p-6 rounded-b shadow">
            <div class="mb-4 flex items-center gap-4">
                <input v-model="search"
                    class="input w-80 border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                    placeholder="Gõ tên sản phẩm để tìm kiếm" />
                <button class="bg-[#3BB77E] text-white rounded px-4 py-2 cursor-pointer" @click="showDiscount = true"><i
                        class="fa fa-percent"></i> Áp dụng giảm giá hàng loạt</button>
            </div>
            <div class="mb-8">
                <h3 class="font-bold mb-2">Tất cả sản phẩm</h3>
                <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
                    <table class="w-full bg-white rounded">
                        <thead class="border-b border-gray-300">
                            <tr>
                                <th class="px-2 py-2">Ảnh</th>
                                <th class="px-2 py-2">Tên sản phẩm</th>
                                <th class="px-2 py-2">Giá thường</th>
                                <th class="px-2 py-2">Giá KM</th>
                                <th class="px-2 py-2">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in paginatedProducts" :key="item.id"
                                class="border-b border-gray-300 text-center">
                                <td class="px-2 py-2 flex items-center justify-center"><img :src="getMainImage(item)"
                                        class="w-10 h-10 rounded" /></td>
                                <td class="px-2 py-2">{{ item.name }}</td>
                                <td class="px-2 py-2">{{ item.price }}</td>
                                <td class="px-2 py-2">{{ item.discount_price }}</td>
                                <td class="px-2 py-2">
                                    <button class="bg-[#3BB77E] text-white px-2 py-1 rounded cursor-pointer"
                                        @click="addProduct(item)"
                                        :disabled="selectedProducts.some(p => p.id === item.id)"> <i
                                            class="fas fa-plus"></i> </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-center items-center gap-2 mt-2">
                    <button class="px-3 py-1 rounded bg-gray-200" :disabled="page === 1"
                        @click="page > 1 && (page--)">&lt;</button>
                    <span>Trang {{ page }} / {{ totalPages }}</span>
                    <button class="px-3 py-1 rounded bg-gray-200" :disabled="page === totalPages"
                        @click="page < totalPages && (page++)">&gt;</button>
                </div>
            </div>
            <div class="mb-4">
                <h3 class="font-bold mb-2">Sản phẩm đã chọn</h3>
                <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
                    <table class="w-full bg-white rounded text-center">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="px-2 py-2">Ảnh</th>
                                <th class="px-2 py-2">Tên sản phẩm</th>
                                <th class="px-2 py-2">Giá thường</th>
                                <th class="px-2 py-2">Giá KM</th>
                                <th class="px-2 py-2">Giá Flash sale</th>
                                <th class="px-2 py-2">Đã bán</th>
                                <th class="px-2 py-2">Số lượng</th>
                                <th class="px-2 py-2">SL Thật</th>
                                <th class="px-2 py-2">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(item, idx) in paginatedSelectedProducts" :key="item.id"
                                class="border-b border-gray-300">
                                <!-- Ảnh -->
                                <td class="px-2 py-2">
                                    <div class="flex justify-center items-center">
                                        <img :src="getMainImage(item)" class="w-10 h-10 rounded" />
                                    </div>
                                </td>

                                <!-- Các cột text -->
                                <td class="px-2 py-2">{{ item.name }}</td>
                                <td class="px-2 py-2">{{ item.price }}</td>
                                <td class="px-2 py-2">{{ item.discount_price }}</td>

                                <!-- Input -->
                                <td class="px-2 py-2">
                                    <input v-model="item.flashPrice" class="input w-20 text-center"
                                        placeholder="Giá FS" />
                                </td>
                                <td class="px-2 py-2">
                                    <input v-model="item.sold" class="input w-16 text-center" placeholder="Đã bán" />
                                </td>
                                <td class="px-2 py-2">
                                    <input v-model="item.quantity" class="input w-16 text-center" placeholder="SL" />
                                </td>

                                <!-- Checkbox -->
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
                                <!-- Nút xóa -->
                                <td class="px-2 py-2">
                                    <div class="flex justify-center">
                                        <button class="bg-red-500 text-white px-2 py-1 rounded cursor-pointer"
                                            @click="remove(idx + (selectedPage - 1) * selectedPageSize)">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="selectedProducts.length === 0" class="text-center text-gray-500 mt-2">
                        Bạn chưa chọn sản phẩm nào
                    </div>
                    <div class="flex justify-center items-center gap-2 mt-2">
                        <button class="px-3 py-1 rounded bg-gray-200" :disabled="selectedPage === 1"
                            @click="selectedPage > 1 && (selectedPage--)">&lt;</button>
                        <span>Trang {{ selectedPage }} / {{ selectedTotalPages }}</span>
                        <button class="px-3 py-1 rounded bg-gray-200" :disabled="selectedPage === selectedTotalPages"
                            @click="selectedPage < selectedTotalPages && (selectedPage++)">&gt;</button>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button class="bg-[#3BB77E] text-white px-6 py-2 rounded cursor-pointer" @click="apply">Áp dụng</button>
                <button class="bg-gray-400 text-white px-6 py-2 ml-2 rounded cursor-pointer" @click="goBack"><i
                        class="fas fa-arrow-left"></i> Quay lại</button>
            </div>
            <!-- Popup giảm giá hàng loạt -->
            <div v-if="showDiscount"
                class="fixed top-0 left-0 w-full h-full bg-black/30 z-50 flex items-center justify-center">
                <div class="bg-white shadow-lg rounded p-6 z-50 w-96">
                    <div class="font-bold mb-2">Thiết lập giảm giá hàng loạt</div>
                    <div class="flex gap-2 mb-2">
                        <button :class="discountType === '%' ? 'bg-blue-600 text-white' : 'bg-gray-200'"
                            class="px-3 py-1 rounded cursor-pointer" @click="discountType = '%'">%</button>
                        <button :class="discountType === '$' ? 'bg-blue-600 text-white' : 'bg-gray-200'"
                            class="px-3 py-1 rounded cursor-pointer" @click="discountType = '$'">$</button>
                        <button :class="discountType === '₫' ? 'bg-blue-600 text-white' : 'bg-gray-200'"
                            class="px-3 py-1 rounded cursor-pointer" @click="discountType = '₫'">Đồng ₫</button>
                    </div>
                    <input v-model.number="discountValue" type="number" class="border border-gray-300 w-full mb-2 p-2"
                        placeholder="Nhập giá trị giảm" />
                    <div class="flex justify-end gap-2">
                        <button class="bg-gray-300 text-black px-4 py-1 rounded cursor-pointer"
                            @click="showDiscount = false">Đóng</button>
                        <button class="bg-blue-600 text-white px-4 py-1 rounded cursor-pointer"
                            @click="applyDiscount">Áp dụng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useProducts } from '../../../composable/useProducts'
import { useFlashsale } from '../../../composable/useFlashsale'
import { useRouter } from "vue-router";
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
    @apply border rounded px-2 py-1;
}

.btn-danger {
    @apply bg-red-600 text-white px-2 py-1 rounded;
}

.btn-primary {
    @apply bg-[#3BB77E] text-white px-2 py-1 rounded;
}
</style> -->