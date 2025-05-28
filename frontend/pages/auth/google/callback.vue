<!-- pages/auth/google/callback.vue -->
<template>
    <div class="flex justify-center items-center h-screen">
        <div class="text-2xl font-bold">Đang xử lý đăng nhập Google...</div>
    </div>
</template>

<script setup>
import { useAuth } from '~/composables/useAuth'

const { handleGoogleCallback } = useAuth()

onMounted(async () => {
    try {
        await handleGoogleCallback()
    } catch (err) {
        console.error('Lỗi xử lý callback Google:', err)
    }
})
</script>