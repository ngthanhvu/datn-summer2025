<template>
    <div class="mt-6">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6 max-w-7xl mx-auto">
            <a href="/" class="hover:text-[#81AACC]">Trang chủ</a>
            <span>/</span>
            <a href="/san-pham" class="hover:text-[#81AACC]">Sản phẩm</a>
            <span>/</span>
            <span class="text-gray-900">{{ product?.name }}</span>
            <span v-if="flashSaleName" class="block text-base text-red-500 font-semibold ml-2">
                {{ flashSaleName }}
            </span>
        </div>
        <!-- Product Section -->
        <div class="max-w-7xl mx-auto mb-5">
            <div
                class="flex flex-col lg:flex-row items-stretch justify-start p-5 bg-white rounded-[10px] border border-gray-200">
                <!-- Product Images Section -->
                <ProductImages :product-images="productImages" :main-image="mainImage" :product-name="product?.name"
                    @update:main-image="$emit('update:mainImage', $event)" />

                <!-- Product Info -->
                <ProductInfo :product="product" :selected-size="selectedSize" :selected-color="selectedColor"
                    :quantity="quantity" :selected-variant-stock="selectedVariantStock" :display-price="displayPrice"
                    :show-original-price="showOriginalPrice" :flash-sale-name="flashSaleName"
                    :flash-sale-price="flashSalePrice" :flash-sale-end-time="flashSaleEndTime"
                    :flash-sale-sold="flashSaleSold" :flash-sale-quantity="flashSaleQuantity" :product-raw="product"
                    :flash-sale-percent="flashSalePercent" :product-inventory="productInventory"
                    @update:selected-size="$emit('update:selectedSize', $event)"
                    @update:selected-color="$emit('update:selectedColor', $event)"
                    @update:quantity="$emit('update:quantity', $event)" @add-to-cart="$emit('addToCart')" />
            </div>
        </div>

        <!-- Description & Reviews -->
        <div class="max-w-7xl mx-auto mb-5">
            <div class="pt-2 bg-white p-3 sm:p-8 rounded-[10px] border border-gray-200">
                <div class="flex flex-wrap gap-2 sm:gap-8 mb-4 sm:mb-8 justify-start">
                    <button v-for="tab in tabs" :key="tab.id" @click="$emit('update:activeTab', tab.id)" :class="[
                        'px-2 py-1 text-sm sm:px-4 sm:py-2 sm:text-base font-medium border-b-2 transition-colors',
                        activeTab === tab.id
                            ? 'border-[#81AACC] text-[#81AACC]'
                            : 'border-transparent hover:border-gray-300'
                    ]">
                        {{ tab.name }}
                    </button>
                </div>

                <!-- Description -->
                <ProductDescription v-if="activeTab === 'description'" :description="product?.description" />

                <!-- Reviews -->
                <ProductReviews v-if="activeTab === 'reviews'" :review-stats="reviewStats"
                    :show-review-form="showReviewForm" :is-authenticated="isAuthenticated"
                    :user-has-reviewed="userHasReviewed" :user-review="userReview" :review-form="reviewForm"
                    :editing-review-id="editingReviewId" :is-submitting="isSubmitting" :preview-images="previewImages"
                    :reviews-loading="reviewsLoading" :reviews="reviews" :review-pagination-data="reviewPaginationData"
                    :total-review-pages="totalReviewPages" :total-reviews="totalReviews"
                    :reviews-per-page="reviewsPerPage" :current-review-page="currentReviewPage" :user="user"
                    @update:review-form="$emit('update:reviewForm', $event)"
                    @update:show-review-form="$emit('update:showReviewForm', $event)"
                    @submit-review="$emit('submitReview')" @handle-image-upload="$emit('handleImageUpload', $event)"
                    @remove-image="$emit('removeImage', $event)" @cancel-edit="$emit('cancelEdit')"
                    @edit-review="$emit('editReview', $event)" @remove-review="$emit('removeReview', $event)"
                    @open-image-modal="$emit('openImageModal', $event)"
                    @handle-review-page-change="$emit('handleReviewPageChange', $event)" />

                <!-- Preservation Information -->
                <PreservationInformation v-if="activeTab === 'preservation-information'" />

                <!-- Shipping Return -->
                <ShippingReturn v-if="activeTab === 'shipping-return'" />

            </div>
        </div>

        <!-- Related Products -->
        <div class="max-w-7xl mx-auto">
            <RelatedProducts :related-products="relatedProducts" />
        </div>
    </div>
</template>

<script setup>
import ProductImages from './ProductImages.vue'
import ProductInfo from './ProductInfo.vue'
import ProductDescription from './ProductDescription.vue'
import ProductReviews from './ProductsReview.vue'
import PreservationInformation from './ProductPreservation.vue';
import ShippingReturn from './ProductShippingReturn.vue'

import RelatedProducts from './RelatedProducts.vue'
import { toRef } from 'vue'
const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    productImages: {
        type: Array,
        required: true
    },
    mainImage: {
        type: String,
        required: true
    },
    selectedSize: String,
    selectedColor: Object,
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
    activeTab: {
        type: String,
        default: 'description'
    },
    reviewStats: {
        type: Object,
        required: true
    },
    showReviewForm: {
        type: Boolean,
        default: false
    },
    isAuthenticated: {
        type: Boolean,
        default: false
    },
    userHasReviewed: {
        type: Boolean,
        default: false
    },
    userReview: {
        type: Object,
        default: null
    },
    reviewForm: {
        type: Object,
        required: true
    },
    editingReviewId: {
        type: [String, Number],
        default: null
    },
    isSubmitting: {
        type: Boolean,
        default: false
    },
    previewImages: {
        type: Array,
        default: () => []
    },
    reviewsLoading: {
        type: Boolean,
        default: false
    },
    reviews: {
        type: Array,
        default: () => []
    },
    reviewPaginationData: {
        type: Object,
        default: null
    },
    totalReviewPages: {
        type: Number,
        default: 1
    },
    totalReviews: {
        type: Number,
        default: 0
    },
    reviewsPerPage: {
        type: Number,
        default: 3
    },
    currentReviewPage: {
        type: Number,
        default: 1
    },
    user: {
        type: Object,
        default: null
    },
    relatedProducts: {
        type: Array,
        default: () => []
    },
    flashSaleName: {
        type: String,
        default: ''
    },
    flashSalePrice: {
        type: Number,
        default: 0
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
    flashSalePercent: {
        type: Number,
        default: 0
    },
    productInventory: {
        type: Array,
        default: 0
    }
})

const emit = defineEmits([
    'update:selectedSize',
    'update:selectedColor',
    'update:quantity',
    'update:activeTab',
    'addToCart',
    'update:reviewForm',
    'update:showReviewForm',
    'submitReview',
    'handleImageUpload',
    'removeImage',
    'cancelEdit',
    'editReview',
    'removeReview',
    'openImageModal',
    'handleReviewPageChange',
    'update:mainImage'
])

const tabs = [
    { id: 'description', name: 'Mô tả' },
    { id: 'reviews', name: 'Đánh giá' },
    { id: 'preservation-information', name: 'Thông tin bảo quản' },
    { id: 'shipping-return', name: 'Giao hàng & Đổi trả' }
]

const selectedSize = toRef(props, 'selectedSize')
const selectedColor = toRef(props, 'selectedColor')
</script>