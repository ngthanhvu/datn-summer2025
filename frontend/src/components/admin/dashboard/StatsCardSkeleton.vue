<template>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <!-- Title skeleton -->
                <div class="h-4 bg-gray-200 rounded mb-2 w-3/4 skeleton-animation"></div>
                <!-- Value skeleton -->
                <div class="h-8 bg-gray-200 rounded mb-2 w-1/2 skeleton-animation"></div>
                <!-- Growth/Subtext skeleton -->
                <div class="h-3 bg-gray-200 rounded w-2/3 skeleton-animation"></div>
            </div>
            <!-- Icon skeleton -->
            <div class="w-12 h-12 bg-gray-200 rounded-full flex-shrink-0 skeleton-animation"></div>
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