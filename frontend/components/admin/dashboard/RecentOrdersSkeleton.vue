<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <!-- Header skeleton -->
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
            <div class="tw-h-6 tw-bg-gray-200 tw-rounded tw-w-1/4 skeleton-animation"></div>
            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-16 skeleton-animation"></div>
        </div>

        <!-- Table skeleton -->
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th class="tw-px-4 tw-py-3 tw-text-left">
                            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-16 skeleton-animation"></div>
                        </th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">
                            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-20 skeleton-animation"></div>
                        </th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">
                            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-16 skeleton-animation"></div>
                        </th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">
                            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-16 skeleton-animation"></div>
                        </th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">
                            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-20 skeleton-animation"></div>
                        </th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">
                            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-16 skeleton-animation"></div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in 5" :key="i" class="tw-border-b">
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-12 skeleton-animation"></div>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-24 skeleton-animation"></div>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-20 skeleton-animation"></div>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-16 skeleton-animation"></div>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-h-6 tw-bg-gray-200 tw-rounded tw-w-20 skeleton-animation"></div>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-20 skeleton-animation"></div>
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