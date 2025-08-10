<template>
    <Transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition-opacity duration-300" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <button v-if="visible" @click="scrollToTop"
            class="fixed bottom-[200px] right-6 bg-white border border-[#81aacc] text-[#81aacc] w-12 h-12 flex items-center justify-center rounded-full shadow-lg z-1 cursor-pointer"
            aria-label="Back to top">
            <i class="fa-solid fa-arrow-up"></i>
        </button>
    </Transition>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const visible = ref(false)

function checkScroll() {
    visible.value = window.scrollY > 200
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
    window.addEventListener('scroll', checkScroll)
})

onBeforeUnmount(() => {
    window.removeEventListener('scroll', checkScroll)
})
</script>
