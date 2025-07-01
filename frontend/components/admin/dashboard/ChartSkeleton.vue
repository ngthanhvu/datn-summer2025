<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <!-- Title skeleton -->
        <div class="tw-h-6 tw-bg-gray-200 tw-rounded tw-mb-4 tw-w-1/3 skeleton-animation"></div>

        <!-- Chart area skeleton -->
        <div class="tw-h-80 tw-bg-gray-100 tw-rounded tw-flex tw-items-center tw-justify-center">
            <div class="tw-text-center">
                <!-- Chart placeholder -->
                <div class="tw-w-32 tw-h-32 tw-bg-gray-200 tw-rounded-full tw-mx-auto tw-mb-4 skeleton-animation"></div>
                <!-- Loading text -->
                <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-24 tw-mx-auto skeleton-animation"></div>
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