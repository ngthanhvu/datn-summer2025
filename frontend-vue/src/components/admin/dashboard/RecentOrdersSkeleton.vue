<template>
    <div class="bg-white rounded-lg shadow p-6">
        <!-- Header skeleton -->
        <div class="flex justify-between items-center mb-4">
            <div class="h-6 bg-gray-200 rounded w-1/4 skeleton-animation"></div>
            <div class="h-4 bg-gray-200 rounded w-16 skeleton-animation"></div>
        </div>

        <!-- Table skeleton -->
        <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-300 bg-gray-50">
                        <th class="px-4 py-3 text-left">
                            <div class="h-4 bg-gray-200 rounded w-16 skeleton-animation"></div>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <div class="h-4 bg-gray-200 rounded w-20 skeleton-animation"></div>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <div class="h-4 bg-gray-200 rounded w-16 skeleton-animation"></div>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <div class="h-4 bg-gray-200 rounded w-16 skeleton-animation"></div>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <div class="h-4 bg-gray-200 rounded w-20 skeleton-animation"></div>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <div class="h-4 bg-gray-200 rounded w-16 skeleton-animation"></div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in 5" :key="i" class="border-b border-gray-300">
                        <td class="px-4 py-3">
                            <div class="h-4 bg-gray-200 rounded w-12 skeleton-animation"></div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="h-4 bg-gray-200 rounded w-24 skeleton-animation"></div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="h-4 bg-gray-200 rounded w-20 skeleton-animation"></div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="h-4 bg-gray-200 rounded w-16 skeleton-animation"></div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="h-6 bg-gray-200 rounded w-20 skeleton-animation"></div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="h-4 bg-gray-200 rounded w-20 skeleton-animation"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
// No props needed for skeleton
</script>

<style scoped>
.skeleton-animation {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
    background-size: 400% 100%;
    animation: skeleton-loading 1.4s ease infinite;
}

@keyframes skeleton-loading {
    0% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}
</style>