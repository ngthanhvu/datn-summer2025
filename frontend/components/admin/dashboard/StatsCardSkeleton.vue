<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-start">
            <div class="tw-flex-1">
                <!-- Title skeleton -->
                <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-mb-2 tw-w-3/4 skeleton-animation"></div>
                <!-- Value skeleton -->
                <div class="tw-h-8 tw-bg-gray-200 tw-rounded tw-mb-2 tw-w-1/2 skeleton-animation"></div>
                <!-- Growth/Subtext skeleton -->
                <div class="tw-h-3 tw-bg-gray-200 tw-rounded tw-w-2/3 skeleton-animation"></div>
            </div>
            <!-- Icon skeleton -->
            <div class="tw-w-12 tw-h-12 tw-bg-gray-200 tw-rounded-full tw-flex-shrink-0 skeleton-animation"></div>
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