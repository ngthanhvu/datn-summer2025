<template>
    <div class="flex w-full lg:w-auto flex-col gap-4 justify-center p-5 h-full">
        <!-- Main Image -->
        <div class="relative bg-white rounded-xl border border-gray-100 p-10 flex items-center justify-center max-w-[650px] max-h-[750px] h-full"
            @click="openModal">
            <img :src="getImgSrc(mainImage)" :alt="productName"
                class="w-full h-full max-w-[600px] max-h-[700px] object-contain rounded-lg" />

            <button v-if="productImages && productImages.length > 1" @click="previousImage"
                class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <button v-if="productImages && productImages.length > 1" @click="nextImage"
                class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>

        <!-- Thumbnails -->
        <div class="flex flex-row gap-2 items-center justify-center flex-wrap">
            <img v-for="(img, idx) in productImages || []" :key="idx" :src="getImgSrc(img)" :alt="productName"
                class="w-16 h-16 object-contain rounded cursor-pointer border-2 bg-white transition-all hover:scale-105"
                :class="{
                    'ring-2 ring-blue-400 border-blue-400': getImgSrc(img) === getImgSrc(mainImage),
                    'border-gray-200 hover:border-gray-300': getImgSrc(img) !== getImgSrc(mainImage)
                }" @click="selectImage(getImgSrc(img))" />
        </div>

        <!-- Modal -->
        <transition name="fade-zoom">
            <div v-if="isModalOpen" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
                @click.self="closeModal" @wheel="handleWheel">
                <div class="relative p-4 max-w-full max-h-full overflow-hidden">
                    <img :src="getImgSrc(productImages[modalIndex])" :alt="productName"
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

                    <button v-if="productImages.length > 1" @click.stop="prevModalImage"
                        class="absolute left-2 top-1/2 -translate-y-1/2 bg-white rounded-full p-2 shadow hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <button v-if="productImages.length > 1" @click.stop="nextModalImage"
                        class="absolute right-2 top-1/2 -translate-y-1/2 bg-white rounded-full p-2 shadow hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <div
                        class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-black/50 text-white px-3 py-1 rounded-full text-sm">
                        {{ modalIndex + 1 }} / {{ productImages.length }}
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    productImages: {
        type: Array,
        default: () => []
    },
    mainImage: String,
    productName: String
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

function getImgSrc(img) {
    if (!img) return ''
    if (typeof img === 'string') return img
    if (typeof img === 'object' && img.image_path) return img.image_path
    return ''
}

const currentIndex = computed(() =>
    props.productImages.findIndex(img => getImgSrc(img) === getImgSrc(props.mainImage))
)

function openModal() {
    modalIndex.value = currentIndex.value !== -1 ? currentIndex.value : 0
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
    if (props.productImages.length > 1) {
        modalIndex.value =
            modalIndex.value <= 0 ? props.productImages.length - 1 : modalIndex.value - 1
        resetZoom()
    }
}

function nextModalImage() {
    if (props.productImages.length > 1) {
        modalIndex.value =
            modalIndex.value >= props.productImages.length - 1 ? 0 : modalIndex.value + 1
        resetZoom()
    }
}

function previousImage() {
    const newIndex =
        currentIndex.value <= 0
            ? props.productImages.length - 1
            : currentIndex.value - 1
    emit('update:mainImage', getImgSrc(props.productImages[newIndex]))
}

function nextImage() {
    const newIndex =
        currentIndex.value >= props.productImages.length - 1
            ? 0
            : currentIndex.value + 1
    emit('update:mainImage', getImgSrc(props.productImages[newIndex]))
}

function selectImage(imgSrc) {
    emit('update:mainImage', imgSrc)
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
