<template>
    <div>
        <!-- Product Section -->
        <div class="tw-max-w-7xl tw-mx-auto tw-mb-5">
            <div
                class="tw-flex tw-flex-col lg:tw-flex-row tw-items-stretch tw-justify-start tw-p-5 tw-bg-white tw-rounded-[10px] tw-border tw-border-bg-gray-200">
                <!-- Product Images Section -->
                <ProductImages :product-images="productImages" :main-image="mainImage" :product-name="product.name"
                    @update:main-image="$emit('update:mainImage', $event)" />

                <!-- Product Info -->
                <ProductInfo :product="product" :selected-size="selectedSize" :selected-color="selectedColor"
                    :quantity="quantity" :selected-variant-stock="selectedVariantStock" :display-price="displayPrice"
                    :show-original-price="showOriginalPrice"
                    :flash-sale-name="flashSaleName" :flash-sale-price="flashSalePrice" :flash-sale-end-time="flashSaleEndTime" :flash-sale-sold="flashSaleSold" :flash-sale-quantity="flashSaleQuantity" :product-raw="product"
                    :flash-sale-percent="flashSalePercent"
                    @update:selected-size="$emit('update:selectedSize', $event)"
                    @update:selected-color="$emit('update:selectedColor', $event)"
                    @update:quantity="$emit('update:quantity', $event)" @add-to-cart="$emit('addToCart')" />
            </div>
        </div>

        <!-- Description & Reviews -->
        <div class="tw-max-w-7xl tw-mx-auto tw-mb-5">
            <div class="tw-pt-3 tw-bg-white tw-p-8 tw-rounded-[10px] tw-border tw-border-bg-gray-200">
                <div class="tw-flex tw-gap-8 tw-mb-8">
                    <button v-for="tab in tabs" :key="tab.id" @click="$emit('update:activeTab', tab.id)" :class="[
                        'tw-px-4 tw-py-2 tw-font-medium tw-border-b-2 tw-transition-colors',
                        activeTab === tab.id
                            ? 'tw-border-[#81AACC] tw-text-[#81AACC]'
                            : 'tw-border-transparent hover:tw-border-gray-300'
                    ]">
                        {{ tab.name }}
                    </button>
                </div>

                <!-- Description -->
                <ProductDescription v-if="activeTab === 'description'" :description="product.description" />

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
            </div>
        </div>

        <!-- Related Products -->
        <div class="tw-max-w-7xl tw-mx-auto">
            <RelatedProducts :related-products="relatedProducts" />
        </div>
    </div>
</template>

<script setup>
import ProductImages from './ProductImages.vue'
import ProductInfo from './ProductInfo.vue'
import ProductDescription from './ProductDescription.vue'
import ProductReviews from './ProductReviews.vue'
import RelatedProducts from './RelatedProducts.vue'
import { ref, computed, watch, toRef } from 'vue'

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
]

const selectedSize = toRef(props, 'selectedSize')
const selectedColor = toRef(props, 'selectedColor')
</script>