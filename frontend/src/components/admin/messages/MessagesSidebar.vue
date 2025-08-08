<template>
    <div class="messages-sidebar w-full sm:w-[320px] bg-white border-r flex flex-col h-[calc(100vh-64px)]">
        <!-- Header (sticky) -->
        <div class="p-3 sm:p-4 border-b bg-white sticky top-0 z-10">
            <div class="relative">
                <input type="text" v-model="searchQuery" placeholder="Tìm kiếm tin nhắn..."
                    class="w-full border rounded px-4 py-2 pl-10 text-sm">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>

        <!-- Danh sách cuộc trò chuyện (cuộn) -->
        <div class="message-list flex-1 overflow-y-auto">
            <div v-for="message in filteredMessages" :key="message.id" @click="$emit('select', message)"
                class="message-item p-3 sm:p-4 border-b cursor-pointer"
                :class="[selectedMessageId === message.id ? 'bg-primary/10' : 'hover:bg-gray-50']">
                <div class="flex gap-3">
                    <div class="relative">
                        <img :src="getAvatarUrl(message.avatar)" :alt="message.name"
                            class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover">
                        <div v-if="!message.read"
                            class="w-3 h-3 bg-primary rounded-full absolute right-0 bottom-0 border-2 border-white">
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <h3 class="font-medium truncate text-sm sm:text-base">{{ message.name }}</h3>
                            <span class="text-xs text-gray-500 whitespace-nowrap ml-2">{{ message.time }}</span>
                        </div>
                        <p class="text-xs sm:text-sm text-gray-600 truncate">{{ message.lastMessage }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    messages: {
        type: Array,
        required: true
    },
    selectedMessageId: {
        type: Number,
        default: null
    }
})

const emit = defineEmits(['select'])

const searchQuery = ref('')

const filteredMessages = computed(() => {
    if (!searchQuery.value) return props.messages
    const query = searchQuery.value.toLowerCase()
    return props.messages.filter(
        (message) =>
            message.name.toLowerCase().includes(query) ||
            message.lastMessage.toLowerCase().includes(query)
    )
})

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL
const defaultAvatar =
    'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'

const getAvatarUrl = (avatar) => {
    if (!avatar) return defaultAvatar
    if (avatar.startsWith('http')) return avatar
    let url = `${apiBaseUrl}/${avatar.replace(/^\/+/, '')}`
    return url.replace(/\/{2,}storage\//, '/storage/')
}
</script>

<style scoped>
.message-list::-webkit-scrollbar {
    width: 4px;
}

.bg-primary {
    background-color: #3bb77e;
}

.bg-primary\/10 {
    background-color: rgba(59, 183, 126, 0.1);
}
</style>