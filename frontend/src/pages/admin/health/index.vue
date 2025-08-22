<template>
    <div class="p-6 md:p-10">
        <div class="max-w-6xl mx-auto flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Trạng thái hệ thống</h1>
            <button @click="refresh" :disabled="loading"
                class="flex items-center bg-gray-500 hover:bg-gray-600 disabled:opacity-60 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg shadow-md text-sm font-medium cursor-pointer">
                <svg v-if="loading" class="animate-spin h-4 w-4 mr-2 text-white" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z">
                    </path>
                </svg>
                <span>{{ loading ? 'Đang tải...' : 'Làm mới' }}</span>
            </button>
        </div>

        <div class="max-w-6xl mx-auto space-y-8">
            <!-- App Info -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div v-for="n in 4" :key="'app-' + n" class="bg-white rounded-lg shadow p-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-1">
                        <template v-if="!loading">
                            {{ ['Tên ứng dụng', 'Môi trường', 'Phiên bản', 'Debug'][n - 1] }}
                        </template>
                        <div v-else class="h-4 w-24 bg-gray-200 animate-pulse rounded"></div>
                    </h3>
                    <p class="text-sm text-gray-600">
                        <template v-if="!loading">
                            {{ [health.app?.name, health.app?.env, health.app?.version, health.app?.debug ? 'Bật' :
                                'Tắt'][n - 1] }}
                        </template>
                    <div v-else class="h-4 w-16 bg-gray-200 animate-pulse rounded"></div>
                    </p>
                </div>
            </div>

            <!-- Health Checks -->
            <div>
                <h2 class="text-lg font-bold text-gray-700 mb-4">Dịch vụ</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <template v-if="loading">
                        <div v-for="i in 6" :key="'skeleton-' + i"
                            class="bg-white rounded-lg shadow p-5 h-20 animate-pulse"></div>
                    </template>
                    <template v-else>
                        <div v-for="(check, key) in health.checks" :key="key" v-if="key !== 'php' && key !== 'laravel'"
                            class="bg-white rounded-lg shadow p-5 flex items-start space-x-4">
                            <!-- Status Icon -->
                            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
                                :class="check.status === 'ok' ? 'bg-green-500' : 'bg-red-500'">
                                <svg v-if="check.status === 'ok'" xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>

                            <!-- Info -->
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-gray-800 mb-1 capitalize">
                                    {{ key }}
                                </h3>
                                <p class="text-sm">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium" :class="check.status === 'ok'
                                        ? 'bg-green-100 border border-green-300 text-green-700'
                                        : 'bg-red-100 border border-red-300 text-red-700'">
                                        {{ check.value || check.message || '---' }}
                                    </span>
                                </p>
                                <p v-if="check.ping" class="text-xs text-gray-400">Ping: {{ check.ping }}</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Versions -->
            <div>
                <h2 class="text-lg font-bold text-gray-700 mb-4">Phiên bản</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow p-5">
                        <h3 class="text-sm font-semibold text-gray-800 mb-1">PHP</h3>
                        <p class="text-sm text-gray-600">
                            <template v-if="!loading">{{ health.php?.version }}</template>
                        <div v-else class="h-4 w-20 bg-gray-200 animate-pulse rounded"></div>
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-5">
                        <h3 class="text-sm font-semibold text-gray-800 mb-1">Laravel</h3>
                        <p class="text-sm text-gray-600">
                            <template v-if="!loading">{{ health.laravel?.version }}</template>
                        <div v-else class="h-4 w-20 bg-gray-200 animate-pulse rounded"></div>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="max-w-6xl mx-auto mt-8 text-sm text-gray-500 text-center">
            Lần kiểm tra gần nhất: {{ loading ? 'Đang tải...' : health.time || '---' }}
        </div>
    </div>
</template>

<script setup>
import { useHead } from '@vueuse/head'
import { onMounted } from 'vue'
import { useHealth } from '../../../composable/useHealth'

useHead({
    title: 'Trạng thái hệ thống | DEVGANG',
    meta: [
        {
            name: 'description',
            content: 'Trạng thái hệ thống'
        }
    ]
})
const { health, loading, getHealth } = useHealth()

const refresh = () => {
    getHealth()
}

onMounted(() => {
    getHealth()
})
</script>
