<template>
    <div class="messages-content tw-flex-1 tw-bg-gray-50 tw-flex tw-flex-col tw-h-full">
        <div v-if="message" class="tw-h-full tw-flex tw-flex-col">
            <!-- Message Header (sticky) -->
            <div class="tw-bg-white tw-px-6 tw-py-4 tw-border-b tw-sticky tw-top-0 tw-z-10">
                <div class="tw-flex tw-items-center tw-gap-4">
                    <img :src="message.avatar" :alt="message.name"
                        class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover">
                    <div>
                        <h3 class="tw-font-semibold">{{ message.name }}</h3>
                        <p class="tw-text-sm tw-text-gray-500">{{ message.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Messages (scrollable) -->
            <div class="tw-flex-1 tw-overflow-y-auto tw-p-6">
                <div class="tw-max-w-3xl tw-mx-auto tw-space-y-4">
                    <div v-for="(msg, index) in message.messages" :key="index" class="tw-flex"
                        :class="[msg.isAdmin ? 'tw-justify-end' : 'tw-justify-start']">
                        <div class="tw-flex tw-items-end tw-gap-2" :class="[msg.isAdmin ? 'tw-flex-row-reverse' : '']">
                            <img :src="msg.isAdmin ? adminAvatar : message.avatar"
                                :alt="msg.isAdmin ? 'Admin' : message.name"
                                class="tw-w-8 tw-h-8 tw-rounded-full tw-object-cover">
                            <div :class="[
                                'tw-max-w-md tw-rounded-2xl tw-px-4 tw-py-2',
                                msg.isAdmin ? 'tw-bg-primary tw-text-white tw-rounded-br-none' : 'tw-bg-white tw-rounded-bl-none'
                            ]">
                                <p>{{ msg.content }}</p>
                                <span class="tw-text-xs tw-opacity-75 tw-block tw-mt-1">{{ msg.time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Input (sticky) -->
            <div class="tw-bg-white tw-px-6 tw-py-4 tw-border-t tw-sticky tw-bottom-0 tw-z-10">
                <div class="tw-max-w-3xl tw-mx-auto">
                    <div class="tw-flex tw-gap-4">
                        <input type="text" v-model="newMessage" @keyup.enter="handleSend" placeholder="Nhập tin nhắn..."
                            class="tw-flex-1 tw-border tw-rounded-full tw-px-6 tw-py-3 focus:tw-ring-2 focus:tw-ring-primary/20 focus:tw-border-primary">
                        <button @click="handleSend"
                            class="tw-bg-primary tw-text-white tw-rounded-full tw-w-12 tw-h-12 tw-flex tw-items-center tw-justify-center hover:tw-bg-primary-dark">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="tw-h-full tw-flex tw-items-center tw-justify-center tw-text-gray-500">
            <div class="tw-text-center">
                <i class="fas fa-comments tw-text-4xl tw-mb-2"></i>
                <p>Chọn một cuộc trò chuyện để bắt đầu</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    message: {
        type: Object,
        default: null
    },
    adminAvatar: {
        type: String,
        required: true
    }
})

const emit = defineEmits(['send'])

const newMessage = ref('')

const handleSend = () => {
    if (!newMessage.value.trim()) return
    emit('send', newMessage.value)
    newMessage.value = ''
}
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}

::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>