<template>
    <div class="messages-sidebar tw-w-[320px] tw-bg-white tw-border-r tw-flex tw-flex-col tw-h-[calc(100vh-64px)]">
        <!-- Header (sticky) -->
        <div class="tw-p-4 tw-border-b tw-bg-white tw-sticky tw-top-0 tw-z-10">
            <div class="tw-relative">
                <input type="text" v-model="searchQuery" placeholder="Tìm kiếm tin nhắn..."
                    class="tw-w-full tw-border tw-rounded tw-px-4 tw-py-2 tw-pl-10">
                <i
                    class="fas fa-search tw-absolute tw-left-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400"></i>
            </div>
        </div>

        <!-- Danh sách cuộc trò chuyện (cuộn) -->
        <div class="message-list tw-flex-1 tw-overflow-y-auto">
            <div v-for="message in filteredMessages" :key="message.id" @click="$emit('select', message)"
                class="message-item tw-p-4 tw-border-b tw-cursor-pointer"
                :class="[selectedMessageId === message.id ? 'tw-bg-primary/10' : 'hover:tw-bg-gray-50']">
                <div class="tw-flex tw-gap-3">
                    <div class="tw-relative">
                        <img :src="getAvatarUrl(message.avatar)" :alt="message.name"
                            class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover">
                        <div v-if="!message.read"
                            class="tw-w-3 tw-h-3 tw-bg-primary tw-rounded-full tw-absolute tw-right-0 tw-bottom-0 tw-border-2 tw-border-white">
                        </div>
                    </div>
                    <div class="tw-flex-1 tw-min-w-0">
                        <div class="tw-flex tw-justify-between tw-items-start">
                            <h3 class="tw-font-medium tw-truncate">{{ message.name }}</h3>
                            <span class="tw-text-xs tw-text-gray-500 tw-whitespace-nowrap tw-ml-2">{{ message.time
                                }}</span>
                        </div>
                        <p class="tw-text-sm tw-text-gray-600 tw-truncate">{{ message.lastMessage }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRuntimeConfig } from '#imports'

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
    return props.messages.filter(message =>
        message.name.toLowerCase().includes(query) ||
        message.lastMessage.toLowerCase().includes(query)
    )
})

const runtimeConfig = useRuntimeConfig()
const defaultAvatar = 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'
const getAvatarUrl = (avatar) => {
    if (!avatar) return defaultAvatar
    if (avatar.startsWith('http')) return avatar
    let url = runtimeConfig.public.apiBaseUrl + '/' + avatar.replace(/^\/+/, '')
    url = url.replace(/\/{2,}storage\//, '/storage/')
    return url
}
</script>

<style scoped>
.message-list::-webkit-scrollbar {
    width: 4px;
}

.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary\/10 {
    background-color: rgba(59, 183, 126, 0.1);
}
</style>