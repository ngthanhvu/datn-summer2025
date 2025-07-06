<template>
    <div class="tw-flex tw-w-full lg:tw-w-auto tw-flex-col tw-gap-4 tw-justify-center tw-p-5 tw-h-full">
        <!-- Ảnh chính lớn -->
        <div class="tw-relative tw-bg-white tw-rounded-xl tw-border tw-border-gray-100 tw-p-10 tw-flex tw-items-center tw-justify-center tw-max-w-[650px] tw-max-h-[750px] tw-h-full"
            @click="openModal">
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

        <transition name="fade-zoom">
            <div v-if="isModalOpen"
                class="tw-fixed tw-inset-0 tw-bg-black/70 tw-flex tw-items-center tw-justify-center tw-z-50"
                @click.self="closeModal" @wheel="handleWheel">
                <div class="tw-relative tw-p-4 tw-max-w-full tw-max-h-full tw-overflow-hidden">
                    <img :src="props.productImages[modalIndex]" :alt="props.productName"
                        class="tw-max-w-full tw-max-h-[90vh] tw-rounded-lg tw-shadow-xl tw-transition-all tw-duration-300 tw-cursor-zoom-in"
                        :style="{
                            transform: `scale(${zoomLevel}) translate(${panX}px, ${panY}px)`,
                            cursor: zoomLevel > 1 ? 'grab' : 'zoom-in'
                        }" @mousedown="startPan" @mousemove="pan" @mouseup="stopPan" @mouseleave="stopPan"
                        @dblclick="resetZoom" />

                    <!-- Nút đóng -->
                    <button @click="closeModal"
                        class="tw-absolute tw-top-2 tw-right-2 tw-bg-white tw-rounded-full tw-p-2 tw-shadow hover:tw-bg-gray-50 tw-transition-colors">
                        <svg class="tw-w-5 tw-h-5 tw-text-gray-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Nút trái -->
                    <button v-if="props.productImages.length > 1" @click.stop="prevModalImage"
                        class="tw-absolute tw-left-2 tw-top-1/2 -tw-translate-y-1/2 tw-bg-white tw-rounded-full tw-p-2 tw-shadow hover:tw-bg-gray-50 tw-transition-colors">
                        <svg class="tw-w-6 tw-h-6 tw-text-gray-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Nút phải -->
                    <button v-if="props.productImages.length > 1" @click.stop="nextModalImage"
                        class="tw-absolute tw-right-2 tw-top-1/2 -tw-translate-y-1/2 tw-bg-white tw-rounded-full tw-p-2 tw-shadow hover:tw-bg-gray-50 tw-transition-colors">
                        <svg class="tw-w-6 tw-h-6 tw-text-gray-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Hiển thị số ảnh -->
                    <div
                        class="tw-absolute tw-bottom-2 tw-left-1/2 -tw-translate-x-1/2 tw-bg-black/50 tw-text-white tw-px-3 tw-py-1 tw-rounded-full tw-text-sm">
                        {{ modalIndex + 1 }} / {{ props.productImages.length }}
                    </div>
                </div>
            </div>
        </transition>

    </div>
</template>

<script setup>
import { computed, ref } from 'vue'

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

const isModalOpen = ref(false)
const modalIndex = ref(0)
const zoomLevel = ref(1)
const panX = ref(0)
const panY = ref(0)
const isPanning = ref(false)
const lastPanX = ref(0)
const lastPanY = ref(0)

const openModal = () => {
    modalIndex.value = currentIndex.value !== -1 ? currentIndex.value : 0
    isModalOpen.value = true
    resetZoom()
}

const closeModal = () => {
    isModalOpen.value = false
    resetZoom()
}

const currentIndex = computed(() => {
    return props.productImages.findIndex(img => img === props.mainImage)
})

const prevModalImage = () => {
    if (props.productImages.length <= 1) return
    modalIndex.value = modalIndex.value <= 0
        ? props.productImages.length - 1
        : modalIndex.value - 1
    resetZoom()
}

const nextModalImage = () => {
    if (props.productImages.length <= 1) return
    modalIndex.value = modalIndex.value >= props.productImages.length - 1
        ? 0
        : modalIndex.value + 1
    resetZoom()
}

const resetZoom = () => {
    zoomLevel.value = 1
    panX.value = 0
    panY.value = 0
}

const handleWheel = (e) => {
    e.preventDefault()
    if (e.deltaY < 0) {
        // Zoom in
        if (zoomLevel.value < 3) {
            zoomLevel.value = Math.min(3, zoomLevel.value + 0.3)
        }
    } else {
        // Zoom out
        if (zoomLevel.value > 0.5) {
            zoomLevel.value = Math.max(0.5, zoomLevel.value - 0.3)
        }
    }
}

const startPan = (e) => {
    if (zoomLevel.value > 1) {
        isPanning.value = true
        lastPanX.value = e.clientX
        lastPanY.value = e.clientY
    }
}

const pan = (e) => {
    if (isPanning.value && zoomLevel.value > 1) {
        const deltaX = e.clientX - lastPanX.value
        const deltaY = e.clientY - lastPanY.value

        panX.value += deltaX
        panY.value += deltaY

        lastPanX.value = e.clientX
        lastPanY.value = e.clientY
    }
}

const stopPan = () => {
    isPanning.value = false
}

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

<style scoped>
.fade-zoom-enter-active,
.fade-zoom-leave-active {
    transition: all 0.3s ease;
}

.fade-zoom-enter-from,
.fade-zoom-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

.fade-zoom-enter-to,
.fade-zoom-leave-from {
    opacity: 1;
    transform: scale(1);
}
</style>