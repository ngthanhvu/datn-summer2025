<template>
    <div v-if="show" class="tw-fixed tw-inset-0 tw-z-[9999] tw-bg-black/60 tw-flex tw-items-center tw-justify-center"
        @click.self="close">
        <div class="tw-bg-white tw-rounded-lg tw-shadow-lg tw-relative tw-p-6 tw-flex tw-gap-8 tw-items-start">
            <button class="tw-absolute tw-top-2 tw-right-2 tw-text-2xl tw-text-gray-500 hover:tw-text-gray-700"
                @click="close">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="tw-max-w-xs tw-w-64">
                <ProductImages :product-images="product.images?.map(img => img.image_path) || []"
                    :main-image="mainImage" :product-name="product.name" @update:mainImage="mainImage = $event" />
            </div>
            <div class="tw-flex-1 tw-pl-8">
                <h2 class="tw-text-2xl tw-font-bold tw-mb-2">{{ product.name }}</h2>
                <div class="tw-text-base tw-mb-1">
                    <span class="tw-font-semibold">SKU:</span> {{ product.sku }}
                    <span v-if="product.in_stock"
                        class="tw-bg-green-100 tw-text-green-700 tw-px-2 tw-py-0.5 tw-rounded tw-ml-2 tw-text-xs">Còn
                        hàng</span>
                </div>
                <div class="tw-text-2xl tw-font-bold tw-text-[#d43f3f] tw-mb-2">
                    {{ formatPrice(product.discount_price && product.discount_price > 0 ? product.discount_price :
                        product.price) }}
                </div>
                <div v-if="product.variants && product.variants.length > 0" class="tw-mb-2">
                    <div class="tw-font-medium tw-mb-1">Tiêu đề:</div>
                    <div class="tw-flex tw-gap-2 tw-flex-wrap">
                        <button v-for="variant in product.variants" :key="variant.id"
                            @click="selectedVariant = variant; selectedColor = (variant.colors && variant.colors[0]) || null"
                            :class="[
                                'tw-px-3 tw-py-1 tw-border tw-rounded-md tw-text-base',
                                selectedVariant && selectedVariant.id === variant.id ? 'tw-bg-[#81AACC] tw-text-white tw-border-[#81AACC]' : 'tw-border-gray-300 hover:tw-border-[#81AACC]'
                            ]">
                            {{ variant.size || variant.title || variant.name || 'Chọn' }}
                        </button>
                    </div>
                </div>
                <div v-if="selectedVariant && selectedVariant.colors && selectedVariant.colors.length > 0"
                    class="tw-mb-2">
                    <div class="tw-font-medium tw-mb-1">Màu sắc:</div>
                    <div class="tw-flex tw-gap-2 tw-flex-wrap">
                        <button v-for="color in selectedVariant.colors" :key="color" @click="selectedColor = color"
                            :class="[
                                'tw-px-3 tw-py-1 tw-border tw-rounded-md tw-text-base',
                                selectedColor === color ? 'tw-bg-[#81AACC] tw-text-white tw-border-[#81AACC]' : 'tw-border-gray-300 hover:tw-border-[#81AACC]'
                            ]">
                            {{ color }}
                        </button>
                    </div>
                </div>
                <div class="tw-mb-2">
                    <div class="tw-font-medium tw-mb-1">Số lượng:</div>
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <button @click="quantity > 1 && (quantity--)"
                            class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-lg">-</button>
                        <input type="number" v-model="quantity" min="1"
                            class="tw-w-16 tw-text-center tw-border tw-rounded tw-text-lg" />
                        <button @click="quantity++" class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-lg">+</button>
                    </div>
                </div>
                <div class="tw-flex tw-items-center tw-gap-4 tw-mt-4">
                    <button
                        class="tw-bg-[#81AACC] tw-text-white tw-py-2 tw-px-6 tw-rounded tw-font-semibold tw-text-base hover:tw-bg-[#6B8BA3]"
                        style="min-width: 180px;" @click="addToCart">
                        Thêm vào giỏ hàng
                    </button>
                    <a v-if="product.slug" :href="`/chi-tiet/${product.slug}`"
                        class="tw-text-[#81AACC] tw-underline tw-text-base hover:tw-text-[#6B8BA3]">Xem chi tiết »</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import ProductImages from './ProductImages.vue'
import { useCarts } from '~/composables/useCarts'
const notyf = useNuxtApp().$notyf


const props = defineProps({
    show: Boolean,
    product: Object
})
const emit = defineEmits(['close', 'addToCart'])

const { addToCart: addToCartComposable } = useCarts()

const mainImage = ref('')
const selectedVariant = ref(null)
const selectedColor = ref(null)
const quantity = ref(1)

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
            notyf.error('Vui lòng chọn biến thể')
            return
        }
        if (selectedVariant.value.colors && selectedVariant.value.colors.length > 0 && !selectedColor.value) {
            notyf.error('Vui lòng chọn màu sắc')
            return
        }
        if (quantity.value > selectedVariant.value.stock) {
            notyf.error('Số lượng vượt quá số lượng trong kho')
            return
        }
        await addToCartComposable(selectedVariant.value.id, quantity.value)
        notyf.success('Đã thêm vào giỏ hàng')
        emit('close')
    } catch (error) {
        console.error('Error adding to cart:', error)
        notyf.error('Có lỗi xảy ra khi thêm vào giỏ hàng')
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