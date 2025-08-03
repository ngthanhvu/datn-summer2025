<template>
    <div v-if="show" class="fixed inset-0 z-[9999] bg-black/60 flex items-center justify-center" @click.self="close">
        <div class="bg-white rounded-lg shadow-lg relative p-6 flex gap-8 items-start">
            <button class="absolute top-2 right-2 text-2xl text-gray-500 hover:text-gray-700 cursor-pointer"
                @click="close">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="w-80">
                <ProductImages :product-images="product.images?.map(img => img.image_path) || []"
                    :main-image="mainImage" :product-name="product.name" @update:mainImage="mainImage = $event" />
            </div>
            <div class="flex-1 pl-8">
                <h2 class="text-2xl font-bold mb-2">{{ product.name }}</h2>
                <div class="text-base mb-1">
                    <span class="font-semibold">SKU:</span> {{ selectedVariant?.sku || product.sku }}
                    <span v-if="product.in_stock"
                        class="bg-green-100 text-green-700 px-2 py-0.5 rounded ml-2 text-xs">Còn
                        hàng</span>
                </div>
                <div class="text-2xl font-bold text-[#d43f3f] mb-2">
                    {{ formatPrice(product.discount_price && product.discount_price > 0 ? product.discount_price :
                        product.price) }}
                </div>
                <div v-if="product.variants && product.variants.length > 0" class="mb-2">
                    <div class="font-medium mb-1">Kích cỡ:</div>
                    <div class="flex gap-2 flex-wrap">
                        <button v-for="variant in uniqueVariants" :key="variant.id"
                            @click="selectedVariant = variant; selectedColor = (variant.colors && variant.colors[0]) || null"
                            :class="[
                                'px-3 py-1 border rounded-md text-base cursor-pointer',
                                selectedVariant && selectedVariant.id === variant.id ? 'bg-[#81AACC] text-white border-[#81AACC]' : 'border-gray-300 hover:border-[#81AACC]'
                            ]">
                            {{ variant.size || variant.title || variant.name || 'Chọn' }}
                        </button>
                    </div>
                </div>
                <div v-if="selectedVariant && selectedVariant.colors && selectedVariant.colors.length > 0" class="mb-2">
                    <div class="font-medium mb-1">Màu sắc:</div>
                    <div class="flex gap-2 flex-wrap">
                        <button v-for="color in selectedVariant.colors" :key="color" @click="selectedColor = color"
                            :class="[
                                'w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center cursor-pointer',
                                selectedColor === color ? 'ring-2 ring-[#81AACC] border-[#81AACC]' : ''
                            ]" :style="{ backgroundColor: getColorCode(color), cursor: 'pointer' }" :title="color">
                            <span v-if="!isColorCode(color)" class="text-xs text-gray-700">{{ color }}</span>
                        </button>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="font-medium mb-1">Số lượng:</div>
                    <div class="flex items-center gap-2">
                        <button @click="quantity > 1 && (quantity--)"
                            class="px-3 py-1 border border-gray-300 rounded text-lg cursor-pointer">-</button>
                        <input type="number" v-model="quantity" min="1"
                            class="w-17 px-3 py-1 text-center border border-gray-300 rounded text-lg" />
                        <button @click="quantity++"
                            class="px-3 py-1 border border-gray-300 rounded text-lg cursor-pointer">+</button>
                    </div>
                </div>
                <div class="flex items-center gap-4 mt-4">
                    <button
                        class="bg-[#81AACC] text-white py-2 px-6 rounded font-semibold text-base hover:bg-[#6B8BA3] cursor-pointer"
                        style="min-width: 180px;" @click="addToCart">
                        Thêm vào giỏ hàng
                    </button>
                    <a v-if="product.slug" :href="`/chi-tiet/${product.slug}`"
                        class="text-[#81AACC] underline text-base hover:text-[#6B8BA3]">Xem chi tiết »</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import ProductImages from './ProductImages.vue'
import { useCart } from '../../composable/useCart'
import { push } from 'notivue'

const props = defineProps({
    show: Boolean,
    product: Object
})
const emit = defineEmits(['close', 'addToCart'])

const { addToCart: addToCartComposable } = useCart()

const mainImage = ref('')
const selectedVariant = ref(null)
const selectedColor = ref(null)
const quantity = ref(1)

const uniqueVariants = computed(() => {
    if (!props.product?.variants) return []
    const seen = new Set()
    return props.product.variants.filter(variant => {
        const size = variant.size || variant.title || variant.name
        if (!size || seen.has(size)) return false
        seen.add(size)
        return true
    })
})

// Map tên màu sang mã màu hex nếu cần
const colorMap = {
    'Đen': '#222',
    'Xanh': '#2a9d8f',
    'Trắng': '#fff',
    'Đỏ': '#e63946',
    'Vàng': '#f4d35e',
    'Xanh lá': '#43aa8b',
    // ... thêm các màu khác nếu cần
}
function getColorCode(color) {
    if (/^#|rgb/.test(color)) return color
    return colorMap[color] || '#ccc'
}
function isColorCode(color) {
    return /^#|rgb/.test(color)
}

watch(() => props.product, (newProduct) => {
    if (newProduct?.images?.length) {
        const mainImg = newProduct.images.find(img => img.is_main) || newProduct.images[0]
        mainImage.value = mainImg.image_path
    } else {
        mainImage.value = '/images/placeholder.jpg'
    }
    selectedVariant.value = newProduct?.variants?.[0] || null
    selectedColor.value = (selectedVariant.value && selectedVariant.value.colors && selectedVariant.value.colors[0]) || null
    quantity.value = 1
}, { immediate: true })

function close() {
    emit('close')
}

const addToCart = async () => {
    try {
        if (!selectedVariant.value) {
            push.error('Vui lòng chọn biến thể')
            return
        }
        if (selectedVariant.value.colors && selectedVariant.value.colors.length > 0 && !selectedColor.value) {
            push.error('Vui lòng chọn màu sắc')
            return
        }
        if (quantity.value > selectedVariant.value.stock) {
            push.error('Số lượng vượt quá số lượng trong kho')
            return
        }
        await addToCartComposable(selectedVariant.value.id, quantity.value)
        push.success('Đã thêm vào giỏ hàng')
        emit('close')
    } catch (error) {
        console.error('Error adding to cart:', error)
        push.error('Có lỗi xảy ra khi thêm vào giỏ hàng')
    }
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}
</script>

<style lang="scss" scoped></style>