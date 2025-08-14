<template>
    <div ref="target" class="lazy-loader">
        <div v-if="!isVisible && !isLoaded" class="loading-placeholder">
            <div class="animate-pulse bg-gray-200 rounded-lg h-32"></div>
        </div>
        <div v-else-if="isVisible && !isLoaded" class="loading-content">
            <div class="flex items-center justify-center p-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-2 text-gray-600">Đang tải...</span>
            </div>
        </div>
        <div v-else>
            <slot />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    threshold: {
        type: Number,
        default: 0.1
    },
    rootMargin: {
        type: String,
        default: '50px'
    }
})

const emit = defineEmits(['visible'])

const target = ref(null)
const isVisible = ref(false)
const isLoaded = ref(false)

let observer = null

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    isVisible.value = true
                    emit('visible')
                    // Tự động đánh dấu đã tải sau khi emit event
                    setTimeout(() => {
                        isLoaded.value = true
                    }, 100)
                }
            })
        },
        {
            threshold: props.threshold,
            rootMargin: props.rootMargin
        }
    )

    if (target.value) {
        observer.observe(target.value)
    }
})

onUnmounted(() => {
    if (observer) {
        observer.disconnect()
    }
})
</script>

<style scoped>
.loading-placeholder {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}
</style>