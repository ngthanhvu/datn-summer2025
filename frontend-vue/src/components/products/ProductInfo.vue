<template>
    <div class="w-full lg:flex-1 flex flex-col justify-start max-w-[480px] space-y-6 pt-5 h-full">
        <div>
            <h1 class="text-[22px] font-semibold leading-[28px] mb-2">
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
            <div class="text-[15px] text-gray-600 mb-4">
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
                <span class="text-[22px] font-bold">
                    {{ selectedVariant ? formatPrice(selectedVariantSalePrice) : formatPrice(displayPrice) }}
                </span>
                <span v-if="selectedVariant" class="line-through text-gray-400 text-[15px]">
                    {{ formatPrice(selectedVariant.price) }}
                </span>
                <span v-if="selectedVariant && flashSalePercent > 0" class="text-[#d43f3f] text-[15px] font-semibold">
                    -{{ flashSalePercent }}%
                </span>
            </div>
            <div v-if="showOriginalPrice" class="text-[13px] text-gray-500 mb-4">
                (Tiết kiệm {{ formatPrice(product.price - displayPrice) }})
            </div>
            <p class="text-sm text-gray-500">Giá đã bao gồm VAT</p>

            <!-- Khuyến mãi - Ưu đãi -->
            <div
                class="border border-dashed border-blue-400 rounded-md px-4 py-4 mb-4 text-[15px] text-gray-700 leading-5">
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
                <div class="mb-1 font-semibold text-[16px]">Mã giảm giá</div>
                <div class="flex flex-wrap gap-2">
                    <button
                        class="border border-blue-400 rounded px-3 py-1 text-blue-600 text-[13px]">DEVGAMGREESHIP</button>
                    <button class="border border-blue-400 rounded px-3 py-1 text-blue-600 text-[13px]">GIAM50K</button>
                    <button class="border border-blue-400 rounded px-3 py-1 text-blue-600 text-[13px]">GIAM30</button>
                    <button class="border border-blue-400 rounded px-3 py-1 text-blue-600 text-[13px]">GIAM40</button>
                </div>
            </div>
        </div>

        <!-- Variants -->
        <div class="space-y-4" v-if="product.variants && product.variants.length > 0">
            <!-- Size -->
            <div v-if="sizes.length > 0">
                <h3 class="font-medium mb-2 text-[17px]">Kích thước</h3>
                <div class="flex gap-2">
                    <button v-for="size in sizes" :key="size" @click="$emit('update:selectedSize', size)"
                        @mouseenter="hoveredSize = size" @mouseleave="hoveredSize = ''" :class="[
                            'px-4 py-2 border rounded-md transition-colors',
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
                <h3 class="font-medium mb-2 text-[17px]">Màu sắc</h3>
                <div class="flex gap-2">
                    <button v-for="color in colors" :key="color.name" @click="$emit('update:selectedColor', color)"
                        @mouseenter="hoveredColor = color" @mouseleave="hoveredColor = null" :class="[
                            'w-10 h-10 rounded-full border-2 transition-colors',
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
            <h3 class="font-medium mb-1 text-[17px]">Số lượng</h3>
            <div class="flex items-center gap-4">
                <div class="flex items-center border border-gray-300 rounded-md">
                    <button @click="quantity > 1 && $emit('update:quantity', quantity - 1)"
                        class="px-3 py-2 hover:bg-gray-100">-</button>
                    <input type="number" :value="quantity"
                        @input="$emit('update:quantity', parseInt($event.target.value) || 1)" min="1"
                        :max="flashSalePrice ? flashSaleQuantity : selectedVariantStock"
                        class="w-20 text-center border-x border-gray-300 py-3 text-[16px]" />
                    <button
                        @click="quantity < (flashSalePrice ? flashSaleQuantity : selectedVariantStock) && $emit('update:quantity', quantity + 1)"
                        class="px-3 py-2 hover:bg-gray-100">+</button>
                </div>
                <span class="text-sm text-gray-500">
                    Còn lại: {{ flashSalePrice ? flashSaleQuantity : selectedVariantInventory }} sản phẩm
                </span>
            </div>
        </div>
        <div v-if="productInventory && productInventory.length > 0" class="mb-3 text-sm text-gray-600">
            Số lượng tồn kho: <b>{{productInventory.reduce((sum, inv) => sum + (inv.quantity || 0), 0)}}</b>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
            <button
                class="flex-1 bg-[#81AACC] text-white py-2 text-[18px] rounded-md hover:bg-[#6B8BA3] transition-colors"
                @click="$emit('addToCart')">
                Thêm vào giỏ hàng
            </button>
        </div>

        <!-- Status -->
        <div class="flex items-center gap-2 text-[16px]">
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
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'

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
})

const emit = defineEmits([
    'update:selectedSize',
    'update:selectedColor',
    'update:quantity',
    'addToCart'
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
    // Ưu tiên theo variant_id nếu có, nếu không thì theo size/color
    const inv = props.productInventory.find(
        inv =>
            (inv.variant_id && inv.variant_id === selectedVariant.value.id) ||
            (inv.size === selectedVariant.value.size && inv.color === selectedVariant.value.color)
    )
    return inv ? inv.quantity : 0
})

const addToCart = async () => {
    try {
        if (!selectedVariant.value) {
            notyf.error('Vui lòng chọn size và màu sắc')
            return
        }
        if (props.quantity > (flashSalePercent.value > 0 ? props.flashSaleQuantity : selectedVariant.value.stock)) {
            notyf.error('Số lượng vượt quá số lượng còn lại')
            return
        }
        await addToCartComposable(selectedVariant.value.id, props.quantity, selectedVariantSalePrice.value)
        notyf.success('Đã thêm vào giỏ hàng')
    } catch (error) {
        notyf.error('Có lỗi xảy ra khi thêm vào giỏ hàng')
    }
}
</script>