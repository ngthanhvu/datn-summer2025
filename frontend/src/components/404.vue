<template>
    <div class="flex flex-col justify-center items-center px-4 py-10">
        <div class="w-full max-w-md space-y-8 text-center">
            <div class="mb-8">
                <h2 class="text-6xl sm:text-7xl font-extrabold text-gray-900">404</h2>
                <p class="mt-2 text-2xl sm:text-3xl font-bold text-gray-900">Không tìm thấy trang</p>
                <p class="mt-2 text-sm text-gray-600">
                    Rất tiếc, chúng tôi không tìm thấy người bạn đang tìm kiếm.
                </p>
            </div>
            <div class="mt-8">
                <RouterLink to="/"
                    class="inline-flex items-center px-4 py-2 text-sm sm:text-base border border-transparent font-medium rounded-md text-white bg-[#81aacc] hover:bg-[#2f86ce] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2f86ce]">
                    <svg class="mr-2 -ml-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12h18m-9-9l9 9-9 9" />
                    </svg>
                    Trở về trang chủ
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useHead } from '@vueuse/head'

useHead({
    title: '404 - Không tìm thấy trang',
    meta: [{ name: 'description', content: '404 không tìm thấy trang' }],
})
</script>
