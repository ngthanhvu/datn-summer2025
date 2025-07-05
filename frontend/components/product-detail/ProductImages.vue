<template>
    <div class="tw-flex tw-w-full lg:tw-w-auto tw-flex-col tw-gap-4 tw-justify-center tw-p-5 tw-h-full">
        <!-- Ảnh chính lớn -->
        <div
            class="tw-relative tw-bg-white tw-rounded-xl tw-border tw-border-gray-100 tw-p-10 tw-flex tw-items-center tw-justify-center tw-max-w-[650px] tw-max-h-[750px] tw-h-full">
            <img :src="props.mainImage" :alt="productName"
                class="tw-w-full tw-h-full tw-max-w-[600px] tw-max-h-[700px] tw-object-contain tw-rounded-lg" />

            <!-- Nút mũi tên trái -->
            <button v-if="productImages.length > 1" @click="previousImage"
                class="tw-absolute tw-left-2 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-bg-white tw-rounded-full tw-p-2 tw-shadow-lg tw-border tw-border-gray-200 hover:tw-bg-gray-50 tw-transition-colors">
                <svg class="tw-w-6 tw-h-6 tw-text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <!-- Nút mũi tên phải -->
            <button v-if="productImages.length > 1" @click="nextImage"
                class="tw-absolute tw-right-2 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-bg-white tw-rounded-full tw-p-2 tw-shadow-lg tw-border tw-border-gray-200 hover:tw-bg-gray-50 tw-transition-colors">
                <svg class="tw-w-6 tw-h-6 tw-text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>

        <!-- Thumbnails ngang -->
        <div class="tw-flex tw-flex-row tw-gap-2 tw-items-center tw-justify-center tw-flex-wrap">
            <img v-for="(img, idx) in productImages" :key="idx" :src="img" :alt="productName"
                class="tw-w-16 tw-h-16 tw-object-contain tw-rounded tw-cursor-pointer tw-border-2 tw-bg-white tw-transition-all hover:tw-scale-105"
                :class="{
                    'tw-ring-2 tw-ring-[#81AACC] tw-border-[#81AACC]': img === props.mainImage,
                    'tw-border-gray-200 hover:tw-border-gray-300': img !== props.mainImage
                }" @click="selectImage(img)" />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    productImages: {
        type: Array,
        required: true
    },
    mainImage: {
        type: String,
        required: true
    },
    productName: {
        type: String,
        required: true
    }
})

const emit = defineEmits(['update:mainImage'])

// Tìm index của ảnh hiện tại
const currentIndex = computed(() => {
    return props.productImages.findIndex(img => img === props.mainImage)
})

// Chuyển đến ảnh trước
const previousImage = () => {
    if (props.productImages.length <= 1) return

    const newIndex = currentIndex.value <= 0
        ? props.productImages.length - 1
        : currentIndex.value - 1

    console.log('previousImage: emitting', props.productImages[newIndex]) // Debug log
    emit('update:mainImage', props.productImages[newIndex])
}

// Chuyển đến ảnh tiếp theo
const nextImage = () => {
    if (props.productImages.length <= 1) return

    const newIndex = currentIndex.value >= props.productImages.length - 1
        ? 0
        : currentIndex.value + 1

    emit('update:mainImage', props.productImages[newIndex])
}

// Chọn ảnh từ thumbnail
const selectImage = (image) => {
    console.log('selectImage: emitting', image) // Debug log
    emit('update:mainImage', image)
}
</script>