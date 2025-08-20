<template>
    <div class="flex w-full lg:w-auto flex-col gap-4 justify-center p-5 h-full">
        <!-- Main Image -->
        <div
            class="relative bg-white rounded-xl border border-gray-100 p-4 sm:p-6 lg:p-10 flex items-center justify-center w-full aspect-square max-w-[500px] sm:max-w-[500px] lg:max-w-[600px] mx-auto">
            <img :src="currentMainImage" :alt="productName"
                class="w-full h-full object-contain rounded-lg cursor-pointer" @click="openModal" />

            <button v-if="displayImages && displayImages.length > 1" @click.stop="previousImage"
                class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-1.5 sm:p-2 shadow-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <button v-if="displayImages && displayImages.length > 1" @click.stop="nextImage"
                class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-1.5 sm:p-2 shadow-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>

        <!-- Thumbnail -->
        <div class="flex flex-row gap-3 items-center justify-start flex-nowrap overflow-x-auto thumbnail-container pb-2"
            style="max-width: 100%;" @mousedown="startDrag" @mousemove="onDrag" @mouseup="stopDrag"
            @mouseleave="stopDrag" @touchstart="startDrag" @touchmove="onDrag" @touchend="stopDrag">
            <div v-for="(img, idx) in displayImages || []" :key="`img-${idx}-${getImgSrc(img)}`"
                class="thumbnail-item flex-shrink-0 w-20 h-20 sm:w-24 sm:h-24 lg:w-28 lg:h-28 rounded-lg border-2 bg-white cursor-pointer transition-all hover:scale-105 flex items-center justify-center relative overflow-hidden shadow-sm hover:shadow-md"
                :class="{
                    'ring-2 ring-blue-400 border-blue-400': getImgSrc(img) === currentMainImage,
                    'border-gray-200 hover:border-gray-300': getImgSrc(img) !== currentMainImage
                }" @click="selectImage(getImgSrc(img))">

                <!-- Loading state -->
                <div v-if="!img.image_path" class="w-full h-full bg-gray-100 animate-pulse rounded"></div>

                <!-- Image with error handling -->
                <img v-else :src="getImgSrc(img)" :alt="`${productName} - Image ${idx + 1}`"
                    class="w-full h-full object-cover rounded" @load="handleImageLoad(getImgSrc(img))"
                    @error="handleImageError(getImgSrc(img))" loading="lazy" />

                <!-- Error state -->
                <div v-if="hasImageError(getImgSrc(img))"
                    class="absolute inset-0 bg-gray-100 flex items-center justify-center rounded">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <transition name="fade-zoom">
            <div v-if="isModalOpen" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
                @click.self="closeModal" @wheel="handleWheel">
                <div class="relative p-4 max-w-full max-h-full overflow-hidden">
                    <img :src="getImgSrc(displayImages[modalIndex])" :alt="productName"
                        class="max-w-full max-h-[90vh] rounded-lg shadow-xl transition-all duration-300" :style="{
                            transform: `scale(${zoomLevel}) translate(${panX}px, ${panY}px)`,
                            cursor: zoomLevel > 1 ? 'grab' : 'zoom-in'
                        }" @mousedown="startPan" @mousemove="pan" @mouseup="stopPan" @mouseleave="stopPan"
                        @dblclick="resetZoom" />

                    <button @click="closeModal"
                        class="absolute top-2 right-2 bg-white rounded-full p-2 shadow hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <button v-if="displayImages.length > 1" @click.stop="prevModalImage"
                        class="absolute left-2 top-1/2 -translate-y-1/2 bg-white rounded-full p-2 shadow hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <button v-if="displayImages.length > 1" @click.stop="nextModalImage"
                        class="absolute right-2 top-1/2 -translate-y-1/2 bg-white rounded-full p-2 shadow hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <div
                        class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-black/50 text-white px-3 py-1 rounded-full text-sm">
                        {{ modalIndex + 1 }} / {{ displayImages.length }}
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    productImages: {
        type: Array,
        default: () => []
    },
    mainImage: String,
    productName: String,
    selectedSize: String,
    selectedColor: Object,
    product: Object
})

const emit = defineEmits(['update:mainImage'])

const currentImages = computed(() => {
    return Array.isArray(props.productImages) ? props.productImages : []
})

const currentImagesCount = computed(() => {
    const count = displayImages.value.length
    return count
})

const currentMainImage = computed(() => {
    if (!props.mainImage) return ''

    const exists = displayImages.value.some(img => getImgSrc(img) === props.mainImage)

    if (!exists && displayImages.value.length > 0) {
        const firstMainImage = displayImages.value.find(img => img.type === 'main')
        if (firstMainImage) {
            return getImgSrc(firstMainImage)
        }

        return getImgSrc(displayImages.value[0])
    }

    return props.mainImage
})

const filteredImagesByColor = computed(() => {
    return currentImages.value
})

const displayImages = computed(() => {
    return filteredImagesByColor.value
})

watch(displayImages, (newImages, oldImages) => {
    if (newImages && newImages.length > 0) {
        modalIndex.value = 0
    }
}, { deep: true })

watch(currentMainImage, (newMainImage) => {
    if (newMainImage && newMainImage !== props.mainImage) {
        emit('update:mainImage', newMainImage)
    }
})

watch(() => props.selectedColor, (newColor, oldColor) => {
    if (newColor?.name && oldColor?.name && newColor.name !== oldColor.name) {

        const colorVariant = props.product?.variants?.find(v =>
            String(v.color) === String(newColor.name)
        )

        if (colorVariant?.images && colorVariant.images.length > 0) {
            emit('update:mainImage', colorVariant.images[0].image_path)
        } else {
            console.log('No images found for color:', newColor.name)
        }
    }
}, { deep: true })

const isModalOpen = ref(false)
const modalIndex = ref(0)
const zoomLevel = ref(1)
const panX = ref(0)
const panY = ref(0)
const isPanning = ref(false)
const lastPanX = ref(0)
const lastPanY = ref(0)

const imageLoading = ref({})
const imageError = ref({})

const isDragging = ref(false)
const startX = ref(0)
const scrollLeft = ref(0)
const thumbnailContainer = ref(null)

const handleImageLoad = (imagePath) => {
    imageLoading.value[imagePath] = false
    imageError.value[imagePath] = false
}

const handleImageError = (imagePath) => {
    imageLoading.value[imagePath] = false
    imageError.value[imagePath] = true
}

const isImageLoading = (imagePath) => {
    return imageLoading.value[imagePath] === true
}

const hasImageError = (imagePath) => {
    return imageError.value[imagePath] === true
}

const setImageLoading = (imagePath) => {
    imageLoading.value[imagePath] = true
    imageError.value[imagePath] = false
}

function getImgSrc(img) {
    if (!img) return ''
    if (typeof img === 'string') return img
    if (typeof img === 'object' && img.image_path) return img.image_path
    return ''
}

const currentIndex = computed(() => {
    if (!displayImages.value || displayImages.value.length === 0) return -1

    const index = displayImages.value.findIndex(img => getImgSrc(img) === props.mainImage)
    return index
})

function openModal() {
    if (displayImages.value && displayImages.value.length > 0) {
        modalIndex.value = currentIndex.value !== -1 ? currentIndex.value : 0
        modalIndex.value = Math.min(modalIndex.value, displayImages.value.length - 1)
    } else {
        modalIndex.value = 0
    }
    isModalOpen.value = true
    resetZoom()
}

function closeModal() {
    isModalOpen.value = false
    resetZoom()
}

function resetZoom() {
    zoomLevel.value = 1
    panX.value = 0
    panY.value = 0
}

function handleWheel(e) {
    e.preventDefault()
    zoomLevel.value = Math.min(3, Math.max(0.5, zoomLevel.value + (e.deltaY < 0 ? 0.3 : -0.3)))
}

function startPan(e) {
    if (zoomLevel.value > 1) {
        isPanning.value = true
        lastPanX.value = e.clientX
        lastPanY.value = e.clientY
    }
}

function pan(e) {
    if (isPanning.value && zoomLevel.value > 1) {
        const dx = e.clientX - lastPanX.value
        const dy = e.clientY - lastPanY.value
        panX.value += dx
        panY.value += dy
        lastPanX.value = e.clientX
        lastPanY.value = e.clientY
    }
}

function stopPan() {
    isPanning.value = false
}

function prevModalImage() {
    if (displayImages.value && displayImages.value.length > 1) {
        modalIndex.value =
            modalIndex.value <= 0 ? displayImages.value.length - 1 : modalIndex.value - 1
        resetZoom()
    }
}

function nextModalImage() {
    if (displayImages.value && displayImages.value.length > 1) {
        modalIndex.value =
            modalIndex.value >= displayImages.value.length - 1 ? 0 : modalIndex.value + 1
        resetZoom()
    }
}

function previousImage() {
    if (!displayImages.value || displayImages.value.length === 0) return

    const newIndex =
        currentIndex.value <= 0
            ? displayImages.value.length - 1
            : currentIndex.value - 1
    emit('update:mainImage', getImgSrc(displayImages.value[newIndex]))
}

function nextImage() {
    if (!displayImages.value || displayImages.value.length === 0) return

    const newIndex =
        currentIndex.value >= displayImages.value.length - 1
            ? 0
            : currentIndex.value + 1
    emit('update:mainImage', getImgSrc(displayImages.value[newIndex]))
}

const selectImage = (imagePath) => {
    emit('update:mainImage', imagePath)
}

const startDrag = (e) => {
    isDragging.value = true
    const container = e.currentTarget
    startX.value = e.type === 'mousedown' ? e.pageX - container.offsetLeft : e.touches[0].pageX - container.offsetLeft
    scrollLeft.value = container.scrollLeft
    container.style.cursor = 'grabbing'
    container.style.userSelect = 'none'
    container.classList.add('dragging')
}

const onDrag = (e) => {
    if (!isDragging.value) return
    e.preventDefault()

    const container = e.currentTarget
    const x = e.type === 'mousemove' ? e.pageX - container.offsetLeft : e.touches[0].pageX - container.offsetLeft
    const walk = (x - startX.value) * 2
    container.scrollLeft = scrollLeft.value - walk
}

const stopDrag = (e) => {
    if (!isDragging.value) return
    isDragging.value = false
    const container = e.currentTarget
    container.style.cursor = 'grab'
    container.style.userSelect = 'auto'
    container.classList.remove('dragging')
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

/* Đảm bảo ảnh không bị nhảy */
img {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    backface-visibility: hidden;
    transform: translateZ(0);
    will-change: transform;
    max-width: 100%;
    max-height: 100%;
}

/* Container ảnh chính */
.main-image-container {
    min-height: 400px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

/* Thumbnail container */
.thumbnail-container {
    min-height: 120px;
    max-height: 140px;
    background: #f8fafc;
    padding: 12px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #e97373 #f1f1f1;
    box-sizing: border-box;
    overflow-x: auto;
    overflow-y: hidden;
    max-width: 100%;
    scrollbar-gutter: stable;
    width: calc(5 * 80px + 4 * 12px + 24px);
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.thumbnail-container:active {
    cursor: grabbing;
}

.thumbnail-container.dragging {
    cursor: grabbing !important;
}

/* Hiển thị thanh cuộn cho desktop */
.thumbnail-container::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.thumbnail-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.thumbnail-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.thumbnail-container::-webkit-scrollbar-thumb:hover {
    background: #9b9b9b;
}

.thumbnail-item {
    transition: all 0.2s ease-in-out;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    min-width: 60px;
    min-height: 60px;
    box-sizing: border-box;
}

.thumbnail-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.thumbnail-item img {
    transition: transform 0.3s ease;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.thumbnail-item:hover img {
    transform: scale(1.05);
}

/* Responsive thumbnail sizing */
@media (min-width: 640px) {
    .thumbnail-item {
        min-width: 96px;
        min-height: 96px;
    }
}

@media (min-width: 1024px) {
    .thumbnail-item {
        min-width: 112px;
        min-height: 112px;
    }
}

.thumbnail-container {
    scroll-behavior: smooth;
}

.thumbnail-container {
    width: 100%;
    max-width: 100%;
    scrollbar-gutter: stable;
    overflow-x: scroll;
    overflow-y: hidden;
    flex-shrink: 0;
}

/* Responsive container sizing */
@media (min-width: 640px) {
    .thumbnail-container {
        max-width: calc(5 * 96px + 4 * 12px + 24px);
        width: calc(5 * 96px + 4 * 12px + 24px);
    }
}

@media (min-width: 1024px) {
    .thumbnail-container {
        max-width: calc(5 * 112px + 4 * 12px + 24px);
        width: calc(5 * 112px + 4 * 12px + 24px);
    }
}

.thumbnail-container {
    scrollbar-width: thin;
    scrollbar-color: #e7e7e7 #f1f1f1;
    -ms-overflow-style: auto;
    scrollbar-gutter: stable;
}

.thumbnail-container {
    scrollbar-width: thin;
    scrollbar-color: #c9c9c9 #f1f1f1;
}

.thumbnail-container::-webkit-scrollbar {
    height: 8px;
    width: 8px;
    display: block !important;
}

.thumbnail-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
    margin: 2px;
    display: block !important;
}

.thumbnail-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
    border: 1px solid #f1f1f1;
    display: block !important;
}

.thumbnail-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.thumbnail-container::-webkit-scrollbar-corner {
    background: #f1f1f1;
}
</style>