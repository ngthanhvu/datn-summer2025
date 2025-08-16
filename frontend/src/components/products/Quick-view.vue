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
                        <!-- Main Image with Border -->
                        <div class="relative mb-4">
                            <div class="bg-gradient-to-br from-blue-400 to-blue-600 p-1 rounded-lg">
                                <img :src="mainImage || '/images/placeholder.jpg'" :alt="product.name"
                                    class="w-full h-80 object-cover rounded-lg" />
                            </div>
                            <!-- Banner overlay -->
                            <div
                                class="absolute bottom-0 left-0 right-0 bg-blue-400 text-white text-center py-2 rounded-b-lg">
                                <span class="text-sm font-medium">MUA LÀ CÓ QUÀ</span>
                            </div>
                        </div>

                        <!-- Thumbnail Images -->
                        <div class="flex gap-2 justify-center">
                            <div v-for="(image, index) in product.images?.slice(0, 4) || []" :key="index"
                                @click="mainImage = image.image_path" :class="[
                                    'w-16 h-16 border-2 rounded cursor-pointer overflow-hidden',
                                    mainImage === image.image_path ? 'border-black' : 'border-gray-300'
                                ]">
                                <img :src="image.image_path" :alt="`${product.name} - ${index + 1}`"
                                    class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Section - Product Details -->
                <div class="w-full lg:w-3/5 p-6 overflow-y-auto">
                    <div class="space-y-4">
                        <!-- Product Name -->
                        <h2 class="text-2xl font-bold text-gray-900">{{ product.name }}</h2>

                        <!-- Brand & SKU -->
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">Thương hiệu:</span> {{ product.brand?.name || 'Khác' }}
                            <span class="mx-2">|</span>
                            <span class="font-medium">Mã sản phẩm:</span> {{ selectedVariant?.sku || product.sku ||
                                'Đang cập nhật' }}
                        </div>

                        <!-- Price Information -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl font-bold text-blue-600">
                                    {{
                                        selectedVariant
                                            ? formatPrice(selectedVariantSalePrice)
                                            : (flashSalePrice ? formatPrice(flashSalePrice) : formatPrice(displayPrice))
                                    }}
                                </span>
                                <span v-if="selectedVariant && flashSalePercent > 0"
                                    class="line-through text-gray-400 text-xl">
                                    {{ formatPrice(selectedVariant.price) }}
                                </span>
                                <span v-else-if="!selectedVariant && flashSalePrice"
                                    class="line-through text-gray-400 text-xl">
                                    {{ formatPrice(displayPrice) }}
                                </span>
                                <span
                                    v-if="(selectedVariant && flashSalePercent > 0) || (!selectedVariant && flashSalePercent > 0)"
                                    class="bg-red-500 text-white px-2 py-1 rounded text-sm font-medium">
                                    -{{ flashSalePercent }}%
                                </span>
                            </div>

                            <!-- Trending Tag -->
                            <div
                                class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 px-3 py-1 rounded-full">
                                <i class="bi bi-infinity text-sm"></i>
                                <span class="text-sm font-medium">TRENDING</span>
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
                                            : selectedSize === size
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
                                            : selectedColor && selectedColor.name === color.name
                                                ? 'border-[#81AACC] ring-2 ring-[#81AACC]'
                                                : 'border-gray-300 hover:border-[#81AACC]'
                                    ]" :style="{ backgroundColor: color.code }" :title="color.name">
                                    <i v-if="!isColorCode(color.code)" class="bi bi-tshirt text-xs text-gray-700"></i>
                                </button>

                                <!-- Heart icons -->
                                <div class="flex gap-1 ml-2">
                                    <i class="bi bi-heart text-gray-400 text-lg cursor-pointer hover:text-red-500"></i>
                                    <i class="bi bi-heart-fill text-blue-500 text-lg cursor-pointer"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Promotional Offers -->
                        <div class="border-2 border-dashed border-blue-400 rounded-lg p-4 bg-blue-50">
                            <div class="flex items-center gap-2 mb-3">
                                <i class="fas fa-gift text-blue-600"></i>
                                <span class="font-semibold text-blue-600">KHUYẾN MÃI - ƯU ĐÃI</span>
                            </div>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-center justify-between">
                                    <span>Nhập mã <span class="font-semibold">EGAFREESHIP</span> miễn phí vận chuyển đơn
                                        hàng</span>
                                    <button @click="copyCouponCode('EGAFREESHIP')"
                                        class="text-red-600 hover:underline text-xs">Sao chép</button>
                                </li>
                                <li>Đồng giá ship toàn quốc 25k</li>
                                <li>Hỗ trợ 20k phí ship cho đơn hàng từ 200.000₫</li>
                                <li>Miễn phí ship cho đơn hàng từ 500.000₫</li>
                                <li>Đổi trả trong 30 ngày nếu sản phẩm lỗi bất kì</li>
                            </ul>
                        </div>

                        <!-- Quantity Selector -->
                        <div class="flex items-center gap-4">
                            <div class="flex items-center border border-gray-300 rounded-md">
                                <button @click="handleQuantityChange(Math.max(1, quantity - 1))"
                                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-lg">-</button>
                                <input type="number" :value="quantity"
                                    @input="handleQuantityChange(Math.max(1, parseInt($event.target.value) || 1))"
                                    min="1" max="maxQuantity"
                                    class="w-16 text-center border-x border-gray-300 py-2 text-lg" />
                                <button @click="handleQuantityChange(quantity + 1)" :disabled="!canIncrease"
                                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-lg disabled:opacity-50 disabled:cursor-not-allowed">+</button>
                            </div>

                            <!-- Stock Info -->
                            <span class="text-sm text-gray-500">
                                Còn lại: {{ flashSalePrice ? flashSaleQuantity : selectedVariantInventory }} sản phẩm
                            </span>
                        </div>

                        <!-- Add to Cart Button -->
                        <div class="flex items-center gap-4">
                            <button @click="addToCart" :disabled="!canAddToCart"
                                class="bg-[#81AACC] text-white py-3 px-8 rounded-md font-semibold text-lg hover:bg-[#6B8BA3] transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                THÊM VÀO GIỎ
                            </button>

                            <!-- View Details Link -->
                            <a v-if="product.slug" :href="`/san-pham/${product.slug}`"
                                class="text-[#81AACC] underline text-base hover:text-[#6B8BA3]">
                                Xem chi tiết »
                            </a>
                        </div>

                        <!-- Status -->
                        <div class="flex items-center gap-2 text-sm">
                            <span :class="[
                                'font-medium',
                                selectedVariantInventory > 0 ? 'text-green-600' : 'text-red-600'
                            ]">
                                {{ selectedVariantInventory > 0 ? 'Còn hàng' : 'Hết hàng' }}
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
import { ref, watch, computed } from 'vue'
import { useCart } from '../../composable/useCart'
import { useInventories } from '../../composable/useInventorie'
import { push } from 'notivue'

const props = defineProps({
    show: Boolean,
    product: Object,
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
    displayPrice: {
        type: Number,
        required: true
    },
    showOriginalPrice: {
        type: Boolean,
        default: false
    },
    flashSalePrice: {
        type: Number,
        default: 0
    },
    flashSalePercent: {
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
    }
})

const emit = defineEmits(['close', 'addToCart', 'update:selectedSize', 'update:selectedColor', 'update:quantity', 'variant-change'])

const { addToCart: addToCartComposable } = useCart()
const { getInventories } = useInventories()

const mainImage = ref('')
const hoveredSize = ref('')
const hoveredColor = ref(null)
const localProductInventory = ref([])

// Computed properties giống như ProductInfo.vue
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

const selectedVariant = computed(() => {
    if (!props.product?.variants?.length) return null
    return props.product.variants.find(
        v => v.size === props.selectedSize && v.color === props.selectedColor?.name
    )
})

const selectedVariantSalePrice = computed(() => {
    if (!selectedVariant.value) return null
    if (props.flashSalePercent > 0) {
        return Math.round(selectedVariant.value.price * (1 - props.flashSalePercent / 100))
    }
    return selectedVariant.value.price
})

const selectedVariantInventory = computed(() => {
    if (!selectedVariant.value) return 0
    const inv = (props.productInventory.length > 0 ? props.productInventory : localProductInventory.value).find(
        inv =>
            (inv.variant_id && inv.variant_id === selectedVariant.value.id) ||
            (inv.size === selectedVariant.value.size && inv.color === selectedVariant.value.color)
    )
    return inv ? inv.quantity : 0
})

const maxQuantity = computed(() => {
    const max = props.flashSalePrice ? props.flashSaleQuantity : selectedVariantInventory.value
    return max > 0 ? max : Infinity
})

const canIncrease = computed(() => props.quantity < maxQuantity.value)

const canAddToCart = computed(() => {
    if (!selectedVariant.value) return false
    if (props.quantity > maxQuantity.value) return false
    return maxQuantity.value > 0
})

// Map tên màu sang mã màu hex nếu cần
const colorMap = {
    'Đen': '#222',
    'Black': '#222',
    'Xanh': '#2a9d8f',
    'Blue': '#2a9d8f',
    'Trắng': '#fff',
    'White': '#fff',
    'Đỏ': '#e63946',
    'Red': '#e63946',
    'Vàng': '#f4d35e',
    'Yellow': '#f4d35e',
    'Xanh lá': '#43aa8b',
    'Green': '#43aa8b',
    'Nâu': '#8B4513',
    'Brown': '#8B4513',
    // ... thêm các màu khác nếu cần
}

function getColorCode(color) {
    if (!color) return '#ccc'
    if (/^#|rgb/.test(color)) return color
    return colorMap[color] || '#ccc'
}

function isColorCode(color) {
    if (!color) return false
    return /^#|rgb/.test(color)
}

const copyCouponCode = async (code) => {
    try {
        await navigator.clipboard.writeText(code)
        push.success(`Đã sao chép mã: ${code}`)
    } catch (error) {
        console.error('Error copying coupon code:', error)
        // Fallback cho các trình duyệt cũ
        const textArea = document.createElement('textarea')
        textArea.value = code
        document.body.appendChild(textArea)
        textArea.select()
        document.execCommand('copy')
        document.body.removeChild(textArea)
        push.success(`Đã sao chép mã: ${code}`)
    }
}

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

const handleQuantityChange = (newQuantity) => {
    emit('update:quantity', newQuantity)
}

const fetchProductInventory = async () => {
    try {
        if (props.product?.id && props.productInventory.length === 0) {
            const inventoryData = await getInventories({ product_id: props.product.id })
            localProductInventory.value = Array.isArray(inventoryData) ? inventoryData : inventoryData.data || []
        }
    } catch (error) {
        console.error('Error fetching product inventory:', error)
        localProductInventory.value = []
    }
}

watch(() => props.product, (newProduct) => {
    if (newProduct?.images?.length) {
        const mainImg = newProduct.images.find(img => img.is_main) || newProduct.images[0]
        mainImage.value = mainImg.image_path
    } else {
        mainImage.value = '/images/placeholder.jpg'
    }

    if (newProduct?.id) {
        fetchProductInventory()
    }

    if (newProduct?.variants?.length && !props.selectedSize && sizes.value.length > 0) {
        handleSizeChange(sizes.value[0])
    }
    if (newProduct?.variants?.length && !props.selectedColor && colors.value.length > 0) {
        handleColorChange(colors.value[0])
    }
}, { immediate: true })

function close() {
    emit('close')
}

const addToCart = async () => {
    try {
        if (!selectedVariant.value) {
            push.error('Vui lòng chọn size và màu sắc')
            return
        }
        if (props.quantity > maxQuantity.value) {
            push.error('Số lượng vượt quá số lượng còn lại')
            return
        }
        await addToCartComposable(selectedVariant.value.id, props.quantity, selectedVariantSalePrice.value)
        push.success('Đã thêm vào giỏ hàng')
        emit('addToCart')
        emit('close')
    } catch (error) {
        console.error('Error adding to cart:', error)
        push.error('Có lỗi xảy ra khi thêm vào giỏ hàng')
    }
}

function formatPrice(price) {
    if (!price || isNaN(price)) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}
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

.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f7fafc;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #cbd5e0;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background-color: #a0aec0;
}
</style>