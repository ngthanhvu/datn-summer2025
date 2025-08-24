<template>
    <div class="w-full lg:flex-1 flex flex-col justify-start max-w-[480px] space-y-4 sm:space-y-6 pt-3 sm:pt-5 h-full">
        <div>
            <h1 class="text-lg sm:text-[22px] font-semibold leading-tight sm:leading-[28px] mb-2">
                {{ product?.name }}
            </h1>
            <div v-if="flashSalePrice" class="mb-2">
                <div class="bg-blue-50 p-2 rounded flex items-center justify-between mb-1">
                    <span class="text-xs text-blue-700 font-semibold">{{ flashSaleName }} giảm đến {{
                        getDiscountPercent(product.price, flashSalePrice) }}%</span>
                    <span class="text-xs">
                        Kết thúc sau
                        <span class="bg-black text-white px-1.5 py-0.5 rounded">{{ countdown.days }}</span> ngày
                        <span class="bg-black text-white px-1.5 py-0.5 rounded">{{ countdown.hours }}</span> :
                        <span class="bg-black text-white px-1.5 py-0.5 rounded">{{ countdown.minutes }}</span> :
                        <span class="bg-black text-white px-1.5 py-0.5 rounded">{{ countdown.seconds }}</span>
                    </span>
                </div>
                <div class="relative h-6 bg-gray-200 rounded-full mb-2">
                    <div class="absolute left-0 top-0 h-6 bg-blue-600 rounded-full"
                        :style="`width: ${getSoldPercent(productRaw || product)}%; transition: width 0.3s;`"></div>
                    <div class="absolute left-3 top-0 h-6 flex items-center z-10 text-white font-semibold text-sm">
                        Đã bán {{ flashSaleSold || (productRaw || product).sold || 0 }} sản phẩm
                    </div>
                </div>
            </div>
            <div class="text-sm sm:text-[15px] text-gray-600 mb-4">
                Thương hiệu:
                <a class="text-[#2f6ad8] hover:underline" href="#">
                    {{ product?.brand?.name || 'DEVGANG' }}
                </a>
                <span class="mx-2">|</span>
                Mã sản phẩm:
                <a class="text-[#2f6ad8] hover:underline" href="#">
                    {{ product.sku || 'Đang cập nhật' }}
                </a>
            </div>
            <div class="flex items-center justify-between text-[13px] font-semibold mb-2">
            </div>
            <div class="flex items-center gap-3 mb-3">
                <span class="text-lg sm:text-[22px] font-bold">
                    {{
                        selectedVariant
                            ? formatPrice(selectedVariantSalePrice)
                            : (flashSalePrice ? formatPrice(flashSalePrice) : formatPrice(displayPrice))
                    }}
                </span>
                <span v-if="selectedVariant && flashSalePercent > 0"
                    class="line-through text-gray-400 text-sm sm:text-[15px]">
                    {{ formatPrice(selectedVariant.price) }}
                </span>
                <span v-else-if="!selectedVariant && flashSalePrice"
                    class="line-through text-gray-400 text-sm sm:text-[15px]">
                    {{ formatPrice(displayPrice) }}
                </span>
                <span v-if="(selectedVariant && flashSalePercent > 0) || (!selectedVariant && flashSalePercent > 0)"
                    class="text-[#d43f3f] text-sm sm:text-[15px] font-semibold">
                    -{{ flashSalePercent }}%
                </span>
            </div>
            <div v-if="showOriginalPrice" class="text-[13px] text-gray-500 mb-4">
                (Tiết kiệm {{ formatPrice(product.price - displayPrice) }})
            </div>
            <p class="text-sm text-gray-500">Giá đã bao gồm VAT</p>

            <!-- Khuyến mãi - Ưu đãi -->
            <div
                class="border border-dashed border-blue-400 rounded-md px-3 sm:px-4 py-3 sm:py-4 mb-4 text-sm sm:text-[15px] text-gray-700 leading-5">
                <div class="flex items-center gap-1 mb-1 font-semibold text-blue-600">
                    <i class="fas fa-gift"></i>
                    <span>KHUYẾN MÃI - ƯU ĐÃI</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5">
                    <li>
                        Nhập mã <span class="font-semibold">DEVGANG</span> thêm 5% đơn hàng
                        <a class="text-red-600 hover:underline" href="#">Sao chép</a>
                    </li>
                    <li>Hỗ trợ 10.000 phí Ship cho đơn hàng từ 200.000₫</li>
                    <li>Miễn phí Ship cho đơn hàng từ 300.000₫</li>
                    <li>Đổi trả trong 30 ngày nếu sản phẩm lỗi bất kì</li>
                </ul>
            </div>
            <div class="mb-3 text-[11px]">
                <div class="mb-1 font-semibold text-sm sm:text-[16px]">Mã giảm giá</div>
                <div class="flex flex-wrap gap-2">
                    <button v-for="coupon in nearestCoupons" :key="coupon.id" @click="openCouponPanel"
                        class="border border-blue-400 rounded px-2 sm:px-3 py-1 text-blue-600 text-xs sm:text-[13px] hover:bg-blue-50 transition-colors cursor-pointer">
                        {{ coupon.code }}
                    </button>
                    <button @click="openCouponPanel"
                        class="border border-blue-400 rounded px-2 sm:px-3 py-1 text-blue-600 text-xs sm:text-[13px] hover:bg-blue-50 transition-colors cursor-pointer">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Variants -->
        <div class="space-y-4" v-if="product.variants && product.variants.length > 0">
            <!-- Size -->
            <div v-if="sizes.length > 0">
                <h3 class="font-medium mb-2 text-base sm:text-[17px]">Kích thước</h3>
                <div class="flex gap-2">
                    <button v-for="size in sizes" :key="size" @click="handleSizeChange(size)"
                        @mouseenter="hoveredSize = size" @mouseleave="hoveredSize = ''" :class="[
                            'px-3 sm:px-4 py-2 border rounded-md transition-colors text-sm sm:text-base cursor-pointer',
                            hoveredSize === size
                                ? 'bg-[#e0f2fe] border-[#81AACC] text-[#0369a1]'
                                : selectedSize === size
                                    ? 'bg-[#81AACC] text-white border-[#81AACC]'
                                    : 'border-gray-300 hover:border-[#81AACC]'
                        ]">
                        {{ size }}
                    </button>
                </div>
            </div>

            <!-- Color -->
            <div v-if="colors.length > 0">
                <h3 class="font-medium mb-2 text-base sm:text-[17px]">Màu sắc</h3>
                <div class="flex gap-2">
                    <button v-for="color in colors" :key="color.name" @click="handleColorChange(color)"
                        @mouseenter="hoveredColor = color" @mouseleave="hoveredColor = null" :class="[
                            'w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 transition-colors cursor-pointer',
                            hoveredColor && hoveredColor.name === color.name
                                ? 'border-[#38bdf8] ring-2 ring-[#38bdf8]'
                                : selectedColor && selectedColor.name === color.name
                                    ? 'border-[#81AACC] ring-2 ring-[#81AACC]'
                                    : 'border-gray-300 hover:border-[#81AACC]'
                        ]" :style="{ backgroundColor: color.code }" :title="color.name">
                    </button>
                </div>
            </div>
        </div>

        <!-- Quantity -->
        <div>
            <h3 class="font-medium mb-1 text-base sm:text-[17px]">Số lượng</h3>
            <div class="flex items-center gap-4">
                <div class="flex items-center border border-gray-300 rounded-md">
                    <button @click="decreaseQuantity"
                        class="px-2 sm:px-3 py-2 hover:bg-gray-100 cursor-pointer">-</button>
                    <input type="number" :value="quantity"
                        @input="handleQuantityInput" min="1"
                        class="w-16 sm:w-20 text-center border-x border-gray-300 py-3 text-sm sm:text-[16px]" />
                    <button @click="increaseQuantity"
                        class="px-2 sm:px-3 py-2 hover:bg-gray-100 cursor-pointer">+</button>
                </div>
            </div>

        </div>
        <div class="text-xs sm:text-sm text-gray-500">
            <div v-if="!selectedSize || !selectedColor" class="text-gray-400 italic">
                Vui lòng chọn kích thước và màu sắc
            </div>
            <div v-else>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold">Tồn kho:</span> 
                        <span class="text-gray-700 font-medium">
                            {{ getAvailableStock() }} sản phẩm
                        </span>
                    </div>
                    <div v-if="flashSalePrice && cartQuantity > 0" class="flex items-center gap-2 text-orange-600">
                        <span class="font-semibold">Đã có trong giỏ:</span> 
                        <span class="font-medium">{{ cartQuantity }} sản phẩm</span>
                    </div>
                </div>
            </div>
        </div>
      

        <!-- Actions -->
        <div class="flex gap-4">
            <button :disabled="isAddingToCart || selectedVariantInventory === 0 || !selectedSize || !selectedColor"
                class="flex-1 py-2 text-base sm:text-[18px] rounded-md transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                :class="[
                    isAddingToCart || selectedVariantInventory === 0 || !selectedSize || !selectedColor
                        ? 'bg-gray-400 text-gray-600 cursor-not-allowed'
                        : 'bg-[#81AACC] text-white hover:bg-[#6B8BA3]'
                ]"
                @click="handleAddToCart">
                <div v-if="isAddingToCart" class="flex items-center justify-center gap-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                    <span>Đang thêm...</span>
                </div>
                <span v-else-if="!selectedSize || !selectedColor">Chọn kích thước và màu sắc</span>
                <span v-else-if="selectedVariantInventory === 0">Hết hàng</span>
                <span v-else>Thêm vào giỏ hàng</span>
            </button>
        </div>

        <!-- Status -->
        <div class="flex items-center gap-2 text-sm sm:text-[16px]">
            <span :class="[
                'font-medium',
                productInventory.length > 0 ? 'text-green-600' : 'text-red-600'
            ]">
                {{ productInventory.length > 0 ? 'Còn hàng' : 'Hết hàng' }}
            </span>
            <span class="text-gray-500">|</span>
            <span class="text-gray-500">Giao hàng trong 1-3 ngày</span>
        </div>
    </div>

    <!-- Coupon Panel -->
    <CouponPanel :isOpen="isCouponPanelOpen" @close="closeCouponPanel" />
</template>

<script setup>
import { ref, computed, watch, onUnmounted, onMounted } from 'vue'
import { useCoupon } from '../../composable/useCoupon'
import { push } from 'notivue'
import CouponPanel from '../common/CouponPanel.vue'

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    selectedSize: {
        type: String,
        default: ''
    },
    selectedColor: {
        type: Object,
        default: null
    },
    quantity: {
        type: Number,
        default: 1
    },
    selectedVariantStock: {
        type: Number,
        default: 0
    },
    displayPrice: {
        type: Number,
        required: true
    },
    showOriginalPrice: {
        type: Boolean,
        default: false
    },
    flashSaleName: {
        type: String,
        default: ''
    },
    flashSalePrice: {
        type: Number,
        default: 0
    },
    productRaw: {
        type: Object,
        default: null
    },
    flashSaleEndTime: {
        type: String,
        default: ''
    },
    flashSaleSold: {
        type: Number,
        default: 0
    },
    flashSaleQuantity: {
        type: Number,
        default: 0
    },
    productInventory: {
        type: Array,
        default: () => []
    },
    isAddingToCart: {
        type: Boolean,
        default: false
    },
    cartQuantity: {
        type: Number,
        default: 0
    },
})

const emit = defineEmits([
    'update:selectedSize',
    'update:selectedColor',
    'update:quantity',
    'addToCart',
    'variant-change'
])

const hoveredSize = ref('')
const hoveredColor = ref(null)
const countdown = ref({ days: '--', hours: '--', minutes: '--', seconds: '--' })
let countdownInterval = null

const sizes = computed(() => {
    if (!props.product?.variants?.length) return []
    const uniqueSizes = new Set()
    props.product.variants.forEach(variant => {
        if (variant.size) uniqueSizes.add(variant.size)
    })
    return Array.from(uniqueSizes)
})

const colors = computed(() => {
    if (!props.product?.variants?.length) return []
    const uniqueColors = new Set()
    props.product.variants.forEach(variant => {
        if (variant.color) uniqueColors.add(variant.color)
    })
    return Array.from(uniqueColors).map(color => ({
        name: color,
        code: color
    }))
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

function getDiscountPercent(price, flashPrice) {
    if (!price || !flashPrice) return 0
    return Math.round(100 - (flashPrice / price) * 100)
}

function getSoldPercent(product) {
    if (product.quantity && product.sold) {
        let percent = Math.round((product.sold / (product.quantity + product.sold)) * 100)
        return Math.max(percent, 10)
    }
    return 50
}

function updateCountdown(endTime) {
    if (!endTime) {
        countdown.value = { days: '--', hours: '--', minutes: '--', seconds: '--' }
        return
    }
    const now = new Date()
    const end = new Date(endTime)
    let diff = Math.max(0, end - now)
    if (diff <= 0) {
        countdown.value = { days: '00', hours: '00', minutes: '00', seconds: '00' }
        return
    }
    const days = String(Math.floor(diff / (1000 * 60 * 60 * 24))).padStart(2, '0')
    diff %= 1000 * 60 * 60 * 24
    const hours = String(Math.floor(diff / (1000 * 60 * 60))).padStart(2, '0')
    diff %= 1000 * 60 * 60
    const minutes = String(Math.floor(diff / (1000 * 60))).padStart(2, '0')
    diff %= 1000 * 60
    const seconds = String(Math.floor(diff / 1000)).padStart(2, '0')
    countdown.value = { days, hours, minutes, seconds }
}

watch(() => props.flashSaleEndTime, (newVal) => {
    if (countdownInterval) clearInterval(countdownInterval)
    updateCountdown(newVal)
    if (newVal) {
        countdownInterval = setInterval(() => updateCountdown(newVal), 1000)
    }
}, { immediate: true })

onUnmounted(() => {
    if (countdownInterval) clearInterval(countdownInterval)
})

const selectedVariant = computed(() => {
    if (!props.product?.variants?.length) return null
    return props.product.variants.find(
        v => v.size === props.selectedSize && v.color === props.selectedColor?.name
    )
})

const flashSalePercent = computed(() => {
    if (!props.flashSalePrice || !props.product.price) return 0
    return Math.round(100 - (props.flashSalePrice / props.product.price) * 100)
})

const selectedVariantSalePrice = computed(() => {
    if (!selectedVariant.value) return null
    if (flashSalePercent.value > 0) {
        return Math.round(selectedVariant.value.price * (1 - flashSalePercent.value / 100))
    }
    return selectedVariant.value.price
})

const selectedVariantInventory = computed(() => {
    if (!selectedVariant.value) return 0
    const inv = props.productInventory.find(
        inv =>
            (inv.variant_id && inv.variant_id === selectedVariant.value.id) ||
            (inv.size === selectedVariant.value.size && inv.color === selectedVariant.value.color)
    )
    return inv ? inv.quantity : 0
})

const maxQuantity = computed(() => {
    let availableStock = 0
    
    if (props.flashSalePrice && props.flashSaleQuantity > 0) {
        availableStock = props.flashSaleQuantity
    } else if (props.selectedSize && props.selectedColor) {
        availableStock = selectedVariantInventory.value
    } else {
        availableStock = 0
    }
    
    return availableStock > 0 ? availableStock : 0
})

const canIncrease = computed(() => {
    if (!props.selectedSize || !props.selectedColor) return false
    const variantStock = selectedVariantInventory.value
    if (variantStock === 0) return false
    
    const totalQuantity = props.cartQuantity + props.quantity
    return totalQuantity < variantStock
})

const remainingQuantity = computed(() => {
    if (!props.selectedSize || !props.selectedColor) return 0
    const variantStock = selectedVariantInventory.value
    return Math.max(0, variantStock - props.cartQuantity)
})

const canAddToCart = computed(() => {
    if (!props.selectedSize || !props.selectedColor) return false
    if (props.quantity <= 0) return false
    
    const variantStock = selectedVariantInventory.value
    if (variantStock === 0) return false
    
    const totalQuantity = props.cartQuantity + props.quantity
    return totalQuantity <= variantStock
})

const isQuantityValid = computed(() => {
    if (!props.selectedSize || !props.selectedColor) {
        return false
    }
    
    const variantStock = selectedVariantInventory.value
    if (variantStock === 0) return false
    
    if (props.quantity <= 0) return false
    
    const totalQuantity = props.cartQuantity + props.quantity
    return totalQuantity <= variantStock
})

const handleSizeChange = (size) => {
    emit('update:selectedSize', size)
    const variantData = { size, color: props.selectedColor?.name }
    emit('variant-change', variantData)
}

const handleColorChange = (color) => {
    emit('update:selectedColor', color)
    const variantData = { size: props.selectedSize, color: color.name }
    emit('variant-change', variantData)
}

const validateQuantity = (newQuantity) => {
    if (newQuantity < 0) {
        push.error('Số lượng không được nhỏ hơn 0')
        return false
    }
    if (newQuantity === 0) {
        push.error('Số lượng không được bằng 0')
        return false
    }
    
    let maxStock = 0
    if (props.flashSalePrice && props.flashSaleQuantity > 0) {
        maxStock = Number(props.flashSaleQuantity || 0)
    } else {
        maxStock = selectedVariantInventory.value
    }
    
    if (maxStock === 0) {
        push.warning('Biến thể này hiện tại hết hàng')
        return false
    }
    
    const totalQuantity = props.cartQuantity + newQuantity
    
    if (totalQuantity > maxStock) {
        const remainingAfterCart = maxStock - props.cartQuantity
        if (remainingAfterCart <= 0) {
            push.warning(`Bạn đã có ${props.cartQuantity} sản phẩm trong giỏ hàng. Không thể thêm thêm.`)
        } else {
            push.warning(`Bạn đã có ${props.cartQuantity} sản phẩm trong giỏ hàng. Chỉ có thể thêm tối đa ${remainingAfterCart} sản phẩm nữa.`)
        }
        return false
    }
    
    return true
}

const decreaseQuantity = () => {
    const newQuantity = props.quantity - 1
    if (newQuantity < 1) {
        push.warning('Số lượng tối thiểu là 1')
        return
    }
    if (validateQuantity(newQuantity)) {
        emit('update:quantity', newQuantity)
    }
}

const increaseQuantity = () => {
    const newQuantity = props.quantity + 1
    
    let maxStock = 0
    if (props.flashSalePrice && props.flashSaleQuantity > 0) {
        maxStock = Number(props.flashSaleQuantity || 0)
    } else {
        maxStock = selectedVariantInventory.value
    }
    
    const totalQuantity = props.cartQuantity + newQuantity
    
    if (totalQuantity > maxStock) {
        const remainingAfterCart = maxStock - props.cartQuantity
        if (remainingAfterCart <= 0) {
            push.warning(`Bạn đã có ${props.cartQuantity} sản phẩm trong giỏ hàng. Không thể thêm thêm.`)
        } else {
            push.warning(`Bạn đã có ${props.cartQuantity} sản phẩm trong giỏ hàng. Chỉ có thể thêm tối đa ${remainingAfterCart} sản phẩm nữa.`)
        }
        return
    }
    
    if (validateQuantity(newQuantity)) {
        emit('update:quantity', newQuantity)
    }
}

const handleQuantityInput = (event) => {
    const inputValue = event.target.value
    
    if (inputValue === '') {
        push.warning('Vui lòng nhập số lượng sản phẩm')
        return
    }
    
    const newQuantity = parseInt(inputValue)
    
    if (isNaN(newQuantity)) {
        push.error('Vui lòng nhập số hợp lệ')
        event.target.value = props.quantity
        return
    }
    
    if (newQuantity > 0) {
        let maxStock = 0
        if (props.flashSalePrice && props.flashSaleQuantity > 0) {
            maxStock = Number(props.flashSaleQuantity || 0)
        } else {
            maxStock = selectedVariantInventory.value
        }
        
        const totalQuantity = props.cartQuantity + newQuantity
        
        if (totalQuantity > maxStock) {
            const maxCanAdd = Math.max(0, maxStock - props.cartQuantity)
            if (maxCanAdd > 0) {
                push.info(`Số lượng sản phẩm tối đa có thể thêm vào giỏ hàng là ${maxCanAdd}.`)
                emit('update:quantity', maxCanAdd)
                return
            } else {
                push.warning('Không thể thêm sản phẩm. Đã có đủ trong giỏ hàng.')
                emit('update:quantity', 1)
                return
            }
        }
    }
    
    if (validateQuantity(newQuantity)) {
        emit('update:quantity', newQuantity)
    }
}



const handleAddToCart = () => {
    if (!props.selectedSize || !props.selectedColor) {
        push.warning('Vui lòng chọn kích thước và màu sắc trước khi thêm vào giỏ hàng')
        return
    }
    
    if (props.quantity <= 0) {
        push.error('Vui lòng chọn số lượng hợp lệ')
        return
    }
    
    let maxStock = 0
    if (props.flashSalePrice && props.flashSaleQuantity > 0) {
        maxStock = Number(props.flashSaleQuantity || 0)
    } else {
        maxStock = selectedVariantInventory.value
    }
    
    if (maxStock === 0) {
        push.warning('Biến thể này hiện tại hết hàng')
        return
    }
    
    const totalQuantity = props.cartQuantity + props.quantity
    
    if (totalQuantity > maxStock) {
        if (props.cartQuantity >= maxStock) {
            push.error(` Giỏ hàng đã có ${props.cartQuantity} sản phẩm, vượt quá số lượng tồn kho (${maxStock}). Không thể thêm thêm sản phẩm.`)
        } else {
            const remainingAfterCart = maxStock - props.cartQuantity
            push.warning(`Sản phẩm bạn thêm vào giỏ hàng vượt quá số lượng tồn kho của biến thể này. Chỉ có thể thêm tối đa ${remainingAfterCart} sản phẩm nữa.`)
        }
        return
    }
    
    emit('addToCart')
}

const getAvailableStock = () => {
    if (props.flashSalePrice && props.flashSaleQuantity > 0) {
        const fsQuantity = Number(props.flashSaleQuantity || 0)
        return Math.max(0, fsQuantity - props.cartQuantity)
    } else {
        return Math.max(0, selectedVariantInventory.value - props.cartQuantity)
    }
}

watch(() => [props.selectedSize, props.selectedColor], ([newSize, newColor]) => {
}, { deep: true })

watch(() => props.quantity, (newQuantity) => {
    const variantStock = selectedVariantInventory.value
    const totalQuantity = props.cartQuantity + newQuantity
    
})

const { getCoupons, getNearestCoupons } = useCoupon()
const isCouponPanelOpen = ref(false)
const nearestCoupons = ref([])

const openCouponPanel = () => {
    isCouponPanelOpen.value = true
}

const closeCouponPanel = () => {
    isCouponPanelOpen.value = false
}

const fetchNearestCoupons = async () => {
    try {
        // Sử dụng function getNearestCoupons từ useCoupon
        nearestCoupons.value = await getNearestCoupons(3)
    } catch (error) {
        console.error('Error fetching nearest coupons:', error)
        // Fallback với một số coupon mặc định
        nearestCoupons.value = [
            { id: 1, code: 'DEVGANG', discount_type: 'percentage', discount_value: 5 },
            { id: 2, code: 'FREESHIP', discount_type: 'shipping', discount_value: 0 },
            { id: 3, code: 'GIAM50K', discount_type: 'fixed', discount_value: 50000 }
        ]
    }
}

onMounted(() => {
    fetchNearestCoupons()
})
</script>