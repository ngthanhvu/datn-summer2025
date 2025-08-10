<template>
    <!-- đặt ở góc phải/dưới, đổi bằng prop position -->
    <div :class="['fixed z-[1000] pointer-events-none', positionClass]">
        <Transition enter-active-class="transform transition duration-300" enter-from-class="translate-y-3 opacity-0"
            enter-to-class="translate-y-0 opacity-100" leave-active-class="transform transition duration-300"
            leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-3 opacity-0">
            <div v-if="visible"
                class="pointer-events-auto w-[360px] rounded-2xl bg-white shadow-xl ring-1 ring-black/5 p-3 relative flex gap-3"
                @mouseenter="pause" @mouseleave="resume" role="status" aria-live="polite">
                <img :src="current.image" alt="" class="w-14 h-20 rounded-md object-cover" />
                <div class="flex-1">
                    <a href="#" class="text-sm font-semibold text-blue-600 hover:underline">
                        {{ current.title }}
                    </a>
                    <p class="text-sm text-gray-700 mt-1">
                        Khách hàng <span class="font-medium">{{ current.customer }}</span> tại
                        <span class="font-medium">{{ current.city }}</span> vừa mua sản phẩm
                        cách đây {{ current.minutes }} phút
                    </p>
                </div>
                <button @click="hideNow" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700"
                    aria-label="Đóng">×</button>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'

const props = defineProps({
    intervalMs: { type: Number, default: 30000 },   // bao lâu thì pop cái mới
    durationMs: { type: Number, default: 10000 },   // hiển thị trong bao lâu
    position: { type: String, default: 'bottom-left' }, // 'bottom-right' | 'bottom-left' | 'top-right' | 'top-left'
})

const positionClass = computed(() => ({
    'bottom-right': 'bottom-6 right-6',
    'bottom-left': 'bottom-6 left-6',
    'top-right': 'top-6 right-6',
    'top-left': 'top-6 left-6',
}[props.position]))

const PRODUCTS = [
    { title: 'Áo thun nam - White', image: 'https://product.hstatic.net/200000696635/product/frame_1_e87e3a915cf44ec7843f3c34812a1b23_medium.png' },
    { title: 'Áo thun basic - Black', image: 'https://product.hstatic.net/200000696635/product/frame_2_2cd3c91f91254241bdaa5f2a464c8ffd_medium.png' },
    { title: 'Quần jeans slim', image: 'https://product.hstatic.net/200000696635/product/frame_5_7423829c9cc249e2afafe641e8a78f3c_medium.png' },
]
const CUSTOMERS = ['Phan Thái Tổ', 'Gia Huy', 'Khánh Linh', 'Minh Tuấn', 'Bảo Ngọc', 'Hữu Tín']
const CITIES = ['Bạc Liêu', 'Hà Nội', 'Hồ Chí Minh', 'Đà Nẵng', 'Cần Thơ', 'Hải Phòng']

const visible = ref(false)
const current = ref({ title: '', image: '', customer: '', city: '', minutes: 0 })
let showTimer = null, intervalTimer = null
let remaining = props.durationMs, endAt = 0, paused = false

function pick() {
    const p = PRODUCTS[Math.floor(Math.random() * PRODUCTS.length)]
    current.value = {
        ...p,
        customer: CUSTOMERS[Math.floor(Math.random() * CUSTOMERS.length)],
        city: CITIES[Math.floor(Math.random() * CITIES.length)],
        minutes: Math.floor(Math.random() * 55) + 5, // 5–60 phút
    }
}

function show() {
    pick()
    visible.value = true
    remaining = props.durationMs
    endAt = Date.now() + remaining
    showTimer = setTimeout(hideNow, remaining)
}
function hideNow() {
    visible.value = false
    clearTimeout(showTimer); showTimer = null
}
function pause() {
    if (paused || !visible.value) return
    paused = true
    remaining = Math.max(0, endAt - Date.now())
    clearTimeout(showTimer)
}
function resume() {
    if (!paused) return
    paused = false
    endAt = Date.now() + remaining
    showTimer = setTimeout(hideNow, remaining)
}

onMounted(() => {
    setTimeout(show, 1200) // hiện lần đầu sau 1.2s
    intervalTimer = setInterval(() => { if (!visible.value) show() }, props.intervalMs)
})
onBeforeUnmount(() => {
    clearTimeout(showTimer); clearInterval(intervalTimer)
})
</script>
