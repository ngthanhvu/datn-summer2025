<template>
    <div class="tw-bg-white tw-flex tw-items-center tw-justify-center tw-font-sans tw-mt-[150px] tw-mb-[100px]">
        <div class="tw-border tw-rounded-lg tw-p-8 tw-w-full tw-max-w-md">
            <div class="tw-flex tw-justify-center tw-mb-4">
                <i class="fas fa-bolt tw-text-orange-500 tw-text-2xl"></i>
            </div>
            <h2 class="tw-text-center tw-text-gray-900 tw-font-semibold tw-text-xl tw-mb-8 tw-font-inter">
                Quên mật khẩu?
            </h2>
            <form @submit.prevent>
                <label for="email"
                    class="tw-block tw-text-gray-700 tw-text-[16px] tw-mb-2 tw-font-semibold">Email</label>
                <div class="tw-relative tw-mb-6">
                    <span class="tw-absolute tw-inset-y-0 tw-left-3 tw-flex tw-items-center tw-text-gray-400">
                        <i class="far fa-envelope"></i>
                    </span>
                    <input id="email" type="email" placeholder="Nhập email của bạn"
                        class="tw-pl-10 tw-pr-4 tw-py-2 tw-w-full tw-border tw-border-gray-300 tw-rounded-md tw-text-gray-700 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC] focus:tw-border-[#81AACC]" />
                </div>
                <button type="submit"
                    class="tw-bg-[#81AACC] tw-text-white hover:tw-bg-[#66a2d3] w-100 tw-py-2 tw-rounded-lg tw-relative">
                    Gửi
                </button>
            </form>
            <div class="tw-text-center tw-mt-4">
                <NuxtLink to="/login" class="tw-text-gray-600 tw-text-sm tw-font-normal tw-hover:underline"><i
                        class="fas fa-arrow-left tw-mr-2"></i>Trở lại đăng nhập</NuxtLink>
            </div>
        </div>
    </div>
</template>

<script setup>
// Add any component-specific logic here
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@600&display=swap');

.tw-font-inter {
    font-family: 'Inter', sans-serif;
}
</style>