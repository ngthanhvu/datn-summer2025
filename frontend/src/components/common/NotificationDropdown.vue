<template>
    <div class="relative" @mouseenter="onEnter" @mouseleave="onLeave">
        <!-- Bell Icon + Badge -->
        <slot name="icon">
            <button class="relative p-2 rounded-lg hover:bg-gray-100/80 transition-colors duration-200 cursor-pointer">
                <i class="fas fa-bell text-yellow-400 text-lg" :class="{ 'animate-pulse': hasNewNotifications }"></i>
                <span v-if="notifications.length"
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-medium px-2 py-0.5 rounded-full ring-2 ring-white animate-bounce">
                    {{ notifications.length }}
                </span>
            </button>
        </slot>
        <!-- Dropdown -->
        <div v-if="show"
            class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-xl border border-gray-100/50 z-50 animate-fade-in">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-[#3BB77E] rounded-t-2xl">
                <h3 class="text-sm font-semibold text-white">Thông báo</h3>
            </div>
            <div v-if="notifications.length > 0" class="max-h-72 overflow-y-auto">
                <div v-for="(n, index) in notifications" :key="n.id || index"
                    class="px-5 py-3.5 text-sm text-gray-700 hover:bg-gray-50/80 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors duration-200"
                    :class="{ 'bg-blue-50/50': isNewNotification(n) }">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <span class="block font-medium">{{ n.data?.title }}</span>
                            <span class="block text-gray-600">{{ n.data?.message }}</span>
                            <span class="block text-xs text-gray-500 mt-0.5">{{ n.created_at ? new
                                Date(n.created_at).toLocaleString() : '' }}</span>
                        </div>
                        <div v-if="isNewNotification(n)" class="ml-2">
                            <span class="inline-block w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="px-5 py-6 text-center">
                <i class="fas fa-bell-slash text-gray-400 text-lg mb-2"></i>
                <p class="text-sm text-gray-500">Không có thông báo mới.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    notifications: { type: Array, default: () => [] }
})
const show = ref(false)
let hideTimeout = null

const viewedNotifications = ref(new Set())

const hasNewNotifications = computed(() => {
    return props.notifications.some(n => !viewedNotifications.value.has(n.id))
})

function isNewNotification(notification) {
    return !viewedNotifications.value.has(notification.id)
}

function onEnter() {
    clearTimeout(hideTimeout)
    show.value = true
    props.notifications.forEach(n => {
        if (n.id) {
            viewedNotifications.value.add(n.id)
        }
    })
}

function onLeave() {
    hideTimeout = setTimeout(() => {
        show.value = false
    }, 200)
}
</script>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.2s ease-out;
}
</style>