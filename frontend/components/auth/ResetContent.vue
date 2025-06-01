<template>
    <div class="tw-flex tw-items-center tw-justify-center tw-p-4 tw-mt-5">
        <main
            class="tw-bg-white tw-rounded-xl tw-max-w-md tw-w-full tw-p-[30px] tw-text-center tw-border tw-border-gray-200">
            <h1 class="tw-font-extrabold tw-text-xl tw-leading-tight tw-mb-3">Đặt lại mật khẩu</h1>
            <p class="tw-text-[#4A5568] tw-mb-8 tw-text-base tw-leading-relaxed">
                Nhập 6 số từ email của bạn để xác nhận đổi mật khẩu
            </p>
            <form class="tw-mb-6">
                <div class="tw-flex tw-justify-center tw-gap-4 tw-mb-8">
                    <input v-for="(digit, index) in 6" :key="index" ref="otpInputs" type="text" maxlength="1"
                        class="tw-w-14 tw-h-14 tw-rounded-md tw-bg-[#F1F5F9] tw-text-center tw-text-xl tw-font-semibold tw-text-black focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-[#81AACC]"
                        inputmode="numeric" pattern="[0-9]*" :aria-label="`Digit ${index + 1}`"
                        @input="onDigitInput($event, index)" />
                </div>

                <input type="hidden" placeholder="Email"
                    class="tw-mb-3 tw-pl-3 tw-pr-4 tw-py-2 tw-w-full tw-border tw-border-gray-300 tw-rounded-md tw-text-gray-700 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC] focus:tw-border-[#81AACC]"
                    aria-label="Email" />
                <input type="password" placeholder="Mật khẩu mới"
                    class="tw-mb-3 tw-pl-3 tw-pr-4 tw-py-2 tw-w-full tw-border tw-border-gray-300 tw-rounded-md tw-text-gray-700 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC] focus:tw-border-[#81AACC]"
                    aria-label="New Password" />
                <input type="password" placeholder="Xác nhận mật khẩu mới"
                    class="tw-mb-3 tw-pl-3 tw-pr-4 tw-py-2 tw-w-full tw-border tw-border-gray-300 tw-rounded-md tw-text-gray-700 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC] focus:tw-border-[#81AACC]"
                    aria-label="Confirm New Password" />
                <button type="submit"
                    class="tw-bg-[#81AACC] tw-text-white hover:tw-bg-[#66a2d3] w-100 tw-py-2 tw-rounded-lg tw-relative">
                    Xác nhận đổi mật khẩu
                </button>
            </form>
            <p class="tw-mt-5 tw-text-[#4A5568] tw-text-sm">
                Bạn không nhận được mã?
                <a href="#" class="tw-text-[#81AACC] tw-font-semibold">Gửi lại</a>
            </p>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const otpInputs = ref([])

const onDigitInput = (event, index) => {
    const value = event.target.value
    if (value.length === 1 && index < otpInputs.value.length - 1) {
        otpInputs.value[index + 1].focus()
    }
}

// Fix Nuxt refs with v-for issue
onMounted(() => {
    otpInputs.value = otpInputs.value.slice(0, 6)
})
</script>

<style scoped>
body {
    font-family: 'Inter', sans-serif;
}
</style>