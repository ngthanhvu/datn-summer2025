<template>
    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 tw-py-8 container tw-mt-5">
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2">
            <!-- Left Column - Contact Form -->
            <div class="tw-bg-white tw-p-8 tw-rounded-[5px]">
                <h1 class="tw-text-2xl tw-font-normal tw-mb-6">
                    Công ty TNHH EGANY
                </h1>
                <ul class="tw-text-sm tw-text-gray-900 tw-space-y-2 tw-mb-6">
                    <li class="tw-flex tw-items-center tw-gap-2">
                        <i class="fas fa-map-marker-alt tw-text-[14px]"></i>
                        <span>
                            Địa chỉ: 150/8 Nguyễn Duy Cung, Phường 12, Tp.HCM
                        </span>
                    </li>
                    <li class="tw-flex tw-items-center tw-gap-2">
                        <i class="fas fa-mobile-alt tw-text-[14px]"></i>
                        <span>
                            Số điện thoại: 19001393
                        </span>
                    </li>
                    <li class="tw-flex tw-items-center tw-gap-2">
                        <i class="fas fa-envelope tw-text-[14px]"></i>
                        <span>
                            Email: support@egany.com
                        </span>
                    </li>
                </ul>
                <hr class="tw-border-gray-300 tw-mb-6" />
                <form @submit.prevent="handleSubmit">
                    <h2 class="tw-text-xs tw-font-semibold tw-mb-3 tw-uppercase tw-tracking-wide">
                        LIÊN HỆ VỚI CHÚNG TÔI
                    </h2>
                    <input v-model="form.name"
                        class="tw-w-full tw-mb-4 tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded tw-text-sm tw-placeholder-gray-500 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-black"
                        placeholder="Họ tên*" required type="text" />
                    <input v-model="form.email"
                        class="tw-w-full tw-mb-4 tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded tw-text-sm tw-placeholder-gray-500 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-black"
                        placeholder="Email*" required type="email" />
                    <input v-model="form.phone"
                        class="tw-w-full tw-mb-4 tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded tw-text-sm tw-placeholder-gray-500 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-black"
                        placeholder="Số điện thoại*" required type="tel" />
                    <textarea v-model="form.message"
                        class="tw-w-full tw-mb-1 tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded tw-text-sm tw-placeholder-gray-500 tw-resize-y focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-black"
                        placeholder="Nhập nội dung*" required rows="5"></textarea>
                    <div id="cf-turnstile" data-theme="light"></div>
                    <button
                        class="tw-w-full tw-bg-[#81AACC] tw-text-white tw-py-3 tw-rounded tw-transition-all tw-duration-300 hover:tw-bg-[#478ac0]"
                        type="submit">
                        Gửi liên hệ của bạn
                    </button>
                </form>
            </div>

            <!-- Right Column - Map -->
            <div class="tw-bg-white tw-p-8 tw-rounded-[5px]">
                <h2 class="tw-text-xl tw-font-semibold tw-mb-4">Vị trí của chúng tôi</h2>
                <div class="tw-aspect-w-16 tw-aspect-h-9">
                    <img alt="Google map showing location of EGANY Tech at 150/8 Nguyễn Duy Cung, Phường 12, Gò Vấp, Hồ Chí Minh with map details and markers"
                        class="tw-w-full tw-h-full tw-object-cover tw-rounded-lg"
                        src="https://storage.googleapis.com/a1aa/image/4abda26d-a90e-4460-3cca-f64c03d08a06.jpg" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
const { captchaToken, renderCaptcha } = useCaptcha();

const form = ref({
    name: '',
    email: '',
    phone: '',
    message: ''
})

const handleSubmit = () => {
    // Handle form submission here
    console.log('Form submitted:', form.value)
}
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
</style>