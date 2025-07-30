<template>
    <div class="bg-white rounded-lg shadow p-6">
        <!-- Title skeleton -->
        <div class="h-6 bg-gray-200 rounded mb-4 w-1/3 skeleton-animation"></div>

        <!-- Chart area skeleton -->
        <div class="h-80 bg-gray-100 rounded flex items-center justify-center">
            <div class="text-center">
                <!-- Chart placeholder -->
                <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto mb-4 skeleton-animation"></div>
                <!-- Loading text -->
                <div class="h-4 bg-gray-200 rounded w-24 mx-auto skeleton-animation"></div>
            </div>
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