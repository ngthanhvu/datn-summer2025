<template>
    <div v-if="show" class="fixed inset-0 z-[9999] bg-black/60 flex items-center justify-center p-4"
        @click.self="close">
        <div class="bg-white rounded-lg shadow-lg relative max-w-6xl w-full max-h-[90vh] overflow-hidden">
            <button class="absolute top-4 right-4 text-2xl text-gray-500 hover:text-gray-700 cursor-pointer z-10"
                @click="close">
                <i class="bi bi-x-lg"></i>
            </button>

            <div class="flex flex-col lg:flex-row h-full">
                <!-- Left Section - Product Images -->
                <div class="w-full lg:w-2/5 p-6 bg-gray-50">
                    <div class="relative">
                        <!-- Main Image -->
                        <div class="relative mb-4">
                            <img :src="getImageUrl(mainImage) || '/images/placeholder.jpg'"
                                :alt="props.product?.name || 'Product'" class="w-full object-cover rounded-lg" />
                        </div>

                        <!-- Thumbnail Images -->
                        <div class="flex gap-2 justify-center">
                            <div v-for="(image, index) in productImages" :key="index"
                                @click="mainImage = image.image_path || image" :class="[
                                    'w-16 h-16 border-2 border-gray-300 rounded cursor-pointer overflow-hidden',
                                    mainImage === (image.image_path || image) ? 'border-black' : 'border-gray-300'
                                ]">
                                <img :src="getImageUrl(image.image_path || image)"
                                    :alt="`${props.product?.name || 'Product'} - ${index + 1}`"
                                    class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Section - Product Details -->
                <div class="w-full lg:w-3/5 p-6 overflow-y-auto">
                    <div class="space-y-4">
                        <!-- Flash Sale Badge -->
                        <div class="bg-red-500 text-white px-4 py-2 rounded-lg text-center">
                            <span class="font-bold text-lg">🔥 FLASH SALE - {{ flashSalePercent }}% OFF</span>
                        </div>

                        <!-- Product Name -->
                        <h2 class="text-2xl font-bold text-gray-900">{{ props.product?.name || 'Tên sản phẩm' }}</h2>

                        <!-- Brand & SKU -->
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">Thương hiệu:</span> {{ props.product?.brand?.name || 'Khác' }}
                            <span class="mx-2">|</span>
                            <span class="font-medium">Mã sản phẩm:</span> {{ props.product?.sku || 'Đang cập nhật' }}
                        </div>

                        <!-- Price Information -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl font-bold text-red-600">
                                    {{ formatPrice(props.flashSalePrice || 0) }}
                                </span>
                                <span class="line-through text-gray-400 text-xl">
                                    {{ formatPrice(props.product?.price || 0) }}
                                </span>
                                <span class="bg-red-500 text-white px-2 py-1 rounded text-sm font-medium">
                                    -{{ flashSalePercent }}%
                                </span>
                            </div>
                        </div>

                        <!-- Flash Sale Info -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-fire text-red-500"></i>
                                <span class="font-semibold text-red-700">Thông tin Flash Sale</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Số lượng còn lại:</span>
                                    <span class="font-semibold text-red-600 ml-2">{{ props.flashSaleQuantity || 0
                                        }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Đã bán:</span>
                                    <span class="font-semibold text-gray-800 ml-2">{{ props.product?.sold || 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Size Selection -->
                        <div v-if="sizes.length > 0">
                            <div class="font-medium mb-2">Kích thước:</div>
                            <div class="flex gap-2 flex-wrap">
                                <button v-for="size in sizes" :key="size" @click="handleSizeChange(size)"
                                    @mouseenter="hoveredSize = size" @mouseleave="hoveredSize = ''" :class="[
                                        'px-3 py-2 border rounded-md transition-colors text-sm cursor-pointer',
                                        hoveredSize === size
                                            ? 'bg-[#e0f2fe] border-[#81AACC] text-[#0369a1]'
                                            : localSelectedSize === size
                                                ? 'bg-[#81AACC] text-white border-[#81AACC]'
                                                : 'border-gray-300 hover:border-[#81AACC]'
                                    ]">
                                    {{ size }}
                                </button>
                            </div>
                        </div>

                        <!-- Color Selection -->
                        <div v-if="colors.length > 0">
                            <div class="font-medium mb-2">Màu sắc:</div>
                            <div class="flex gap-2 items-center">
                                <button v-for="color in colors" :key="color.name" @click="handleColorChange(color)"
                                    @mouseenter="hoveredColor = color" @mouseleave="hoveredColor = null" :class="[
                                        'w-10 h-10 rounded-full border-2 transition-colors cursor-pointer',
                                        hoveredColor && hoveredColor.name === color.name
                                            ? 'border-[#38bdf8] ring-2 ring-[#38bdf8]'
                                            : localSelectedColor && localSelectedColor.name === color.name
                                                ? 'border-[#81AACC] ring-2 ring-[#81AACC]'
                                                : 'border-gray-300 hover:border-[#81AACC]'
                                    ]" :style="{ backgroundColor: color.code }" :title="color.name">
                                    <i v-if="!isColorCode(color.code)" class="bi bi-tshirt text-xs text-gray-700"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Quantity Selector -->
                        <div class="flex items-center gap-4">
                            <div class="flex items-center border border-gray-300 rounded-md">
                                <button @click="handleQuantityChange(Math.max(1, localQuantity - 1))"
                                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-lg">-</button>
                                <input type="number" :value="localQuantity"
                                    @input="handleQuantityChange(Math.max(1, parseInt($event.target.value) || 1))"
                                    min="1" :max="maxQuantity"
                                    class="w-16 text-center border-x border-gray-300 py-2 text-lg" />
                                <button @click="handleQuantityChange(localQuantity + 1)" :disabled="!canIncrease"
                                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-lg disabled:opacity-50 disabled:cursor-not-allowed">+</button>
                            </div>

                            <!-- Stock Info -->
                            <span class="text-sm text-gray-500">
                                Còn lại: {{ props.flashSaleQuantity || 0 }} sản phẩm
                            </span>
                        </div>

                        <!-- Add to Cart Button -->
                        <div class="flex items-center gap-4">
                            <button @click="addToCart" :disabled="!canAddToCart"
                                class="bg-red-500 text-white py-3 px-8 rounded-md font-semibold text-lg hover:bg-red-600 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                THÊM VÀO GIỎ FLASH SALE
                            </button>

                            <!-- View Details Link -->
                            <a v-if="props.product?.slug" :href="`/san-pham/${props.product?.slug}`"
                                class="text-[#81AACC] underline text-base hover:text-[#6B8BA3]">
                                Xem chi tiết »
                            </a>
                        </div>

                        <!-- Status -->
                        <div class="flex items-center gap-2 text-sm">
                            <span :class="[
                                'font-medium',
                                (props.flashSaleQuantity || 0) > 0 ? 'text-green-600' : 'text-red-600'
                            ]">
                                {{ (props.flashSaleQuantity || 0) > 0 ? 'Còn hàng' : 'Hết hàng' }}
                            </span>
                            <span class="text-gray-500">|</span>
                            <span class="text-gray-500">Giao hàng trong 1-3 ngày</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useCart } from '../../composable/useCart'
import { push } from 'notivue'

const props = defineProps({
    show: Boolean,
    product: Object,
    flashSalePrice: {
        type: Number,
        required: true
    },
    flashSalePercent: {
        type: Number,
        required: true
    },
    flashSaleQuantity: {
        type: Number,
        required: true
    }
})

const emit = defineEmits(['close', 'add-to-cart'])

const { addToCart: addToCartComposable } = useCart()

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

const mainImage = ref('')
const hoveredSize = ref('')
const hoveredColor = ref(null)
const localSelectedSize = ref('')
const localSelectedColor = ref(null)
const localQuantity = ref(1)

const getImageUrl = (path) => {
    if (!path) return '/images/placeholder.jpg'
    if (path.startsWith('http://') || path.startsWith('https://')) return path

    const base = apiBaseUrl.replace(/\/$/, '')

    if (path.startsWith('/storage/')) return `${base}${path}`
    if (path.startsWith('storage/')) return `${base}/${path}`

    return `${base}/storage/${path}`
}

const productImages = computed(() => {
    if (props.product?.images?.length > 0) {
        return props.product.images
    }
    if (props.product?.main_image) {
        return [props.product.main_image]
    }
    return []
})

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
        code: getColorCode(color)
    }))
})

const maxQuantity = computed(() => {
    return props.flashSaleQuantity > 0 ? props.flashSaleQuantity : 1
})

const canIncrease = computed(() => localQuantity.value < maxQuantity.value)

const canAddToCart = computed(() => {
    if (localQuantity.value > maxQuantity.value) return false
    return maxQuantity.value > 0
})

// Color mapping
const colorMap = {
    'Black': '#000000',
    'White': '#FFFFFF',
    'Red': '#FF0000',
    'Blue': '#0000FF',
    'Green': '#008000',
    'Yellow': '#FFFF00',
    'Pink': '#FFC0CB',
    'Purple': '#800080',
    'Orange': '#FFA500',
    'Gray': '#808080'
}

function getColorCode(color) {
    if (!color) return '#ccc'
    if (/^#|rgb/.test(color)) return color

    const normalizedColor = color.charAt(0).toUpperCase() + color.slice(1).toLowerCase()
    return colorMap[normalizedColor] || color
}

function isColorCode(color) {
    if (!color) return false
    return /^#|rgb/.test(color)
}

// Event handlers
const handleSizeChange = (size) => {
    localSelectedSize.value = size
}

const handleColorChange = (color) => {
    localSelectedColor.value = color
}

const handleQuantityChange = (newQuantity) => {
    localQuantity.value = Math.max(1, newQuantity)
}

const close = () => {
    emit('close')
}

const addToCart = async () => {
    try {
        if (localQuantity.value > maxQuantity.value) {
            push.error('Số lượng vượt quá số lượng còn lại')
            return
        }

        // Tạo variant data cho flash sale
        const variantData = {
            id: props.product.id,
            size: localSelectedSize.value,
            color: localSelectedColor.value?.name,
            price: props.flashSalePrice,
            flash_sale: true
        }

        await addToCartComposable(variantData.id, localQuantity.value, props.flashSalePrice)
        push.success('Đã thêm vào giỏ hàng Flash Sale!')
        emit('add-to-cart')
        emit('close')
    } catch (error) {
        console.error('Error adding to cart:', error)
        push.error('Có lỗi xảy ra khi thêm vào giỏ hàng')
    }
}

const formatPrice = (price) => {
    if (!price || isNaN(price)) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

// Watch for product changes
watch(() => props.product, (newProduct) => {
    if (newProduct) {
        // Set main image
        if (productImages.value.length > 0) {
            const mainImg = productImages.value.find(img => img.is_main) || productImages.value[0]
            mainImage.value = mainImg.image_path || mainImg
        } else {
            mainImage.value = '/images/placeholder.jpg'
        }

        // Initialize selections
        if (sizes.value.length > 0 && !localSelectedSize.value) {
            localSelectedSize.value = sizes.value[0]
        }
        if (colors.value.length > 0 && !localSelectedColor.value) {
            localSelectedColor.value = colors.value[0]
        }
    }
}, { immediate: true })
</script>

<style scoped>
.quick-view-modal {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

@media (max-width: 1024px) {
    .max-w-6xl {
        max-width: 95vw;
    }
}

@media (max-width: 768px) {
    .flex-col {
        flex-direction: column;
    }

    .w-2\/5,
    .w-3\/5 {
        width: 100%;
    }

    .h-80 {
        height: 60vh;
    }

    .text-3xl {
        font-size: 1.5rem;
    }

    .text-2xl {
        font-size: 1.25rem;
    }
}
</style>