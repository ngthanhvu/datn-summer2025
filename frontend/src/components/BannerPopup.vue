<template>
    <div v-if="show" class="fixed inset-0 bg-gray-900/80 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg relative max-w-lg w-full p-4 text-center">

            <button @click="closePopup"
                class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-xl font-bold cursor-pointer">
                <i class="fa-regular fa-circle-xmark"></i>
            </button>

            <img v-if="currentImage" :src="currentImage" alt="Banner khuyến mãi" class="rounded-lg w-full h-auto" />
            <div v-else class="h-48 flex items-center justify-center text-gray-400">
                Đang tải…
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const images = ref([
    'https://i.imgur.com/zEBJ8Gy.png',
    'https://i.imgur.com/2RV2C7r.png'
])

const show = ref(false)
const currentImage = ref('')

function pickRandomImage() {
    if (!images.value.length) return ''
    const idx = Math.floor(Math.random() * images.value.length)
    return images.value[idx]
}

function openPopupOncePerSession() {
    if (!sessionStorage.getItem('bannerShown')) {
        currentImage.value = pickRandomImage()
        show.value = true
        sessionStorage.setItem('bannerShown', 'true')
    }
}

function closePopup() {
    show.value = false
}

function handleKeydown(e) {
    if (e.key === 'Escape') {
        closePopup()
    }
}

onMounted(() => {
    openPopupOncePerSession()
    window.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown)
})
</script>
