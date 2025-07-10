<template>
    <div
        class="tw-w-full lg:tw-flex-1 tw-flex tw-flex-col tw-justify-start tw-max-w-[480px] tw-space-y-6 tw-pt-5 tw-h-full">
        <div>
            <h1 class="tw-text-[22px] tw-font-semibold tw-leading-[28px] tw-mb-2">
                {{ product.name }}
            </h1>
            <div v-if="flashSalePrice" class="tw-mb-2">
                <div class="tw-bg-blue-50 tw-p-2 tw-rounded tw-flex tw-items-center tw-justify-between tw-mb-1">
                    <span class="tw-text-xs tw-text-blue-700 tw-font-semibold">{{flashSaleName}} giảm đến {{ getDiscountPercent(product.price, flashSalePrice) }}%</span>
                    <span class="tw-text-xs">
                        Kết thúc sau
                        <span class="tw-bg-black tw-text-white tw-px-1.5 tw-py-0.5 tw-rounded">{{ countdown.days }}</span> ngày
                        <span class="tw-bg-black tw-text-white tw-px-1.5 tw-py-0.5 tw-rounded">{{ countdown.hours }}</span> :
                        <span class="tw-bg-black tw-text-white tw-px-1.5 tw-py-0.5 tw-rounded">{{ countdown.minutes }}</span> :
                        <span class="tw-bg-black tw-text-white tw-px-1.5 tw-py-0.5 tw-rounded">{{ countdown.seconds }}</span>
                    </span>
                </div>
                <div class="tw-relative tw-h-6 tw-bg-gray-200 tw-rounded-full tw-mb-2">
                    <div class="tw-absolute tw-left-0 tw-top-0 tw-h-6 tw-bg-blue-600 tw-rounded-full" :style="`width: ${getSoldPercent(productRaw || product)}%; transition: width 0.3s;`"></div>
                    <div class="tw-absolute tw-left-3 tw-top-0 tw-h-6 tw-flex tw-items-center tw-z-10 tw-text-white tw-font-semibold tw-text-sm">
                        Đã bán {{ flashSaleSold || (productRaw || product).sold || 0 }} sản phẩm
                    </div>
                </div>
            </div>
            <div class="tw-text-[15px] tw-text-gray-600 tw-mb-4">
                Thương hiệu:
                <a class="tw-text-[#2f6ad8] hover:tw-underline" href="#">
                    {{ product.brand?.name || 'DEVGANG' }}
                </a>
                <span class="tw-mx-2">|</span>
                Mã sản phẩm:
                <a class="tw-text-[#2f6ad8] hover:tw-underline" href="#">
                    {{ product.sku || 'Đang cập nhật' }}
                </a>
            </div>
            <div class="tw-flex tw-items-center tw-justify-between tw-text-[13px] tw-font-semibold tw-mb-2">
            </div>
            <div class="tw-flex tw-items-center tw-gap-3 tw-mb-3">
                <span class="tw-text-[22px] tw-font-bold">
                    {{ selectedVariant ? formatPrice(selectedVariantSalePrice) : formatPrice(displayPrice) }}
                </span>
                <span v-if="selectedVariant" class="tw-line-through tw-text-gray-400 tw-text-[15px]">
                    {{ formatPrice(selectedVariant.price) }}
                </span>
                <span v-if="selectedVariant && flashSalePercent > 0" class="tw-text-[#d43f3f] tw-text-[15px] tw-font-semibold">
                    -{{ flashSalePercent }}%
                </span>
            </div>
            <div v-if="showOriginalPrice" class="tw-text-[13px] tw-text-gray-500 tw-mb-4">
                (Tiết kiệm {{ formatPrice(product.price - displayPrice) }})
            </div>
            <p class="tw-text-sm tw-text-gray-500">Giá đã bao gồm VAT</p>

            <!-- Khuyến mãi - Ưu đãi -->
            <div
                class="tw-border tw-border-dashed tw-border-blue-400 tw-rounded-md tw-px-4 tw-py-4 tw-mb-4 tw-text-[15px] tw-text-gray-700 tw-leading-5">
                <div class="tw-flex tw-items-center tw-gap-1 tw-mb-1 tw-font-semibold tw-text-blue-600">
                    <i class="fas fa-gift"></i>
                    <span>KHUYẾN MÃI - ƯU ĐÃI</span>
                </div>
                <ul class="tw-list-disc tw-list-inside tw-space-y-0.5">
                    <li>
                        Nhập mã <span class="tw-font-semibold">DEVGANG</span> thêm 5% đơn hàng
                        <a class="tw-text-red-600 hover:tw-underline" href="#">Sao chép</a>
                    </li>
                    <li>Hỗ trợ 10.000 phí Ship cho đơn hàng từ 200.000₫</li>
                    <li>Miễn phí Ship cho đơn hàng từ 300.000₫</li>
                    <li>Đổi trả trong 30 ngày nếu sản phẩm lỗi bất kì</li>
                </ul>
            </div>
            <div class="tw-mb-3 tw-text-[11px]">
                <div class="tw-mb-1 tw-font-semibold tw-text-[16px]">Mã giảm giá</div>
                <div class="tw-flex tw-flex-wrap tw-gap-2">
                    <button
                        class="tw-border tw-border-blue-400 tw-rounded tw-px-3 tw-py-1 tw-text-blue-600 tw-text-[13px]">DEVGAMGREESHIP</button>
                    <button
                        class="tw-border tw-border-blue-400 tw-rounded tw-px-3 tw-py-1 tw-text-blue-600 tw-text-[13px]">GIAM50K</button>
                    <button
                        class="tw-border tw-border-blue-400 tw-rounded tw-px-3 tw-py-1 tw-text-blue-600 tw-text-[13px]">GIAM30</button>
                    <button
                        class="tw-border tw-border-blue-400 tw-rounded tw-px-3 tw-py-1 tw-text-blue-600 tw-text-[13px]">GIAM40</button>
                </div>
            </div>
        </div>

        <!-- Variants -->
        <div class="tw-space-y-4" v-if="product.variants && product.variants.length > 0">
            <!-- Size -->
            <div v-if="sizes.length > 0">
                <h3 class="tw-font-medium tw-mb-2 tw-text-[17px]">Kích thước</h3>
                <div class="tw-flex tw-gap-2">
                    <button v-for="size in sizes" :key="size" @click="$emit('update:selectedSize', size)"
                        @mouseenter="hoveredSize = size" @mouseleave="hoveredSize = ''" :class="[
                            'tw-px-4 tw-py-2 tw-border tw-rounded-md tw-transition-colors',
                            hoveredSize === size
                                ? 'tw-bg-[#e0f2fe] tw-border-[#81AACC] tw-text-[#0369a1]'
                                : selectedSize === size
                                    ? 'tw-bg-[#81AACC] tw-text-white tw-border-[#81AACC]'
                                    : 'tw-border-gray-300 hover:tw-border-[#81AACC]'
                        ]">
                        {{ size }}
                    </button>
                </div>
            </div>

            <!-- Color -->
            <div v-if="colors.length > 0">
                <h3 class="tw-font-medium tw-mb-2 tw-text-[17px]">Màu sắc</h3>
                <div class="tw-flex tw-gap-2">
                    <button v-for="color in colors" :key="color.name" @click="$emit('update:selectedColor', color)"
                        @mouseenter="hoveredColor = color" @mouseleave="hoveredColor = null" :class="[
                            'tw-w-10 tw-h-10 tw-rounded-full tw-border-2 tw-transition-colors',
                            hoveredColor && hoveredColor.name === color.name
                                ? 'tw-border-[#38bdf8] tw-ring-2 tw-ring-[#38bdf8]'
                                : selectedColor && selectedColor.name === color.name
                                    ? 'tw-border-[#81AACC] tw-ring-2 tw-ring-[#81AACC]'
                                    : 'tw-border-gray-300 hover:tw-border-[#81AACC]'
                        ]" :style="{ backgroundColor: color.code }" :title="color.name">
                    </button>
                </div>
            </div>
        </div>

        <!-- Quantity -->
        <div>
            <h3 class="tw-font-medium tw-mb-2 tw-text-[17px]">Số lượng</h3>
            <div class="tw-flex tw-items-center tw-gap-4">
                <div class="tw-flex tw-items-center tw-border tw-rounded-md">
                    <button @click="quantity > 1 && $emit('update:quantity', quantity - 1)"
                        class="tw-px-3 tw-py-2 hover:tw-bg-gray-100">-</button>
                    <input type="number" :value="quantity"
                        @input="$emit('update:quantity', parseInt($event.target.value) || 1)" min="1"
                        :max="flashSalePrice ? flashSaleQuantity : selectedVariantStock" class="tw-w-20 tw-text-center tw-border-x tw-py-3 tw-text-[16px]" />
                    <button @click="quantity < (flashSalePrice ? flashSaleQuantity : selectedVariantStock) && $emit('update:quantity', quantity + 1)"
                        class="tw-px-3 tw-py-2 hover:tw-bg-gray-100">+</button>
                </div>
                <span class="tw-text-sm tw-text-gray-500">
                  Còn lại: {{ flashSalePrice ? flashSaleQuantity : selectedVariantStock }} sản phẩm
                </span>
            </div>
        </div>

        <!-- Actions -->
        <div class="tw-flex tw-gap-4">
            <button
                class="tw-flex-1 tw-bg-[#81AACC] tw-text-white tw-py-2 tw-text-[18px] tw-rounded-md hover:tw-bg-[#6B8BA3] tw-transition-colors"
                @click="$emit('addToCart')">
                Thêm vào giỏ hàng
            </button>
        </div>

        <!-- Status -->
        <div class="tw-flex tw-items-center tw-gap-2 tw-text-[16px]">
            <span :class="[
                'tw-font-medium',
                selectedVariantStock > 0 ? 'tw-text-green-600' : 'tw-text-red-600'
            ]">
                {{ selectedVariantStock > 0 ? 'Còn hàng' : 'Hết hàng' }}
            </span>
            <span class="tw-text-gray-500">|</span>
            <span class="tw-text-gray-500">Giao hàng trong 1-3 ngày</span>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

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