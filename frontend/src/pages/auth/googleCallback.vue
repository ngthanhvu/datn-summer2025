<template>
    <div class="flex items-center justify-center mt-[100px]">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#81AACC] mx-auto"></div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Đang xử lý đăng nhập Google...
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Vui lòng đợi trong giây lát
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../../composable/useAuth'

const route = useRoute()
const router = useRouter()
const { handleGoogleCallback } = useAuth()

onMounted(async () => {
    try {
        const tokenFromQuery = route.query.token
        const userFromQuery = route.query.user
        const error = route.query.error

        const success = await handleGoogleCallback(tokenFromQuery, userFromQuery, error)
        if (success) {
            setTimeout(() => {
                window.location.href = '/'
            }, 2000)
        }
    } catch (error) {
        console.error('Google callback error:', error)
        router.push({
            path: '/login',
            query: { error: error.message || 'Đăng nhập Google thất bại' }
        })
    }
})
</script>