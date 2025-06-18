<template>
    <NuxtLayout>
        <div class="tw-mt-[100px] tw-flex tw-flex-col tw-items-center tw-justify-center">
            <div class="tw-text-center">
                <p class="tw-font-bold tw-text-gray-800" style="font-size: 100px;">{{ error.statusCode }}</p>
                <p class="tw-text-xl tw-text-gray-600 tw-mb-6">{{ error.message }}</p>
                <button @click="goHome"
                    class="tw-px-6 tw-py-2 tw-bg-[#81AACC] tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-[#6B8FA8] tw-transition-colors tw-duration-300">
                    Trở về trang chủ
                </button>
            </div>
        </div>
    </NuxtLayout>
</template>

<script setup>
definePageMeta({
    layout: "default"
})

const props = defineProps(['error'])

useHead({
    title: `${props.error.statusCode} - Not found`
})

const goHome = () => {
    clearError({ redirect: '/' })
}
</script>
