<template>
    <div class="max-w-7xl mx-auto px-4 py-8 container mt-5">
        <div class="grid grid-cols-1 lg:grid-cols-2">
            <!-- Left Column - Contact Form -->
            <div class="bg-white p-8 rounded-[5px]">
                <h1 class="text-2xl font-normal mb-6">
                    Công ty TNHH EGANY
                </h1>
                <ul class="text-sm text-gray-900 space-y-2 mb-6">
                    <li class="flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-[14px]"></i>
                        <span>Địa chỉ: 150/8 Nguyễn Duy Cung, Phường 12, Tp.HCM</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-mobile-alt text-[14px]"></i>
                        <span>Số điện thoại: 19001393</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-envelope text-[14px]"></i>
                        <span>Email: support@egany.com</span>
                    </li>
                </ul>
                <hr class="border-gray-300 mb-6" />
                <form @submit.prevent="handleSubmit">
                    <h2 class="text-xs font-semibold mb-3 uppercase tracking-wide">
                        LIÊN HỆ VỚI CHÚNG TÔI
                    </h2>
                    <input v-model="form.name"
                        class="w-full mb-4 px-3 py-2 border border-gray-300 rounded text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-black"
                        placeholder="Họ tên*" required type="text" />
                    <input v-model="form.email"
                        class="w-full mb-4 px-3 py-2 border border-gray-300 rounded text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-black"
                        placeholder="Email*" required type="email" />
                    <input v-model="form.phone"
                        class="w-full mb-4 px-3 py-2 border border-gray-300 rounded text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-black"
                        placeholder="Số điện thoại*" required type="tel" />
                    <textarea v-model="form.message"
                        class="w-full mb-1 px-3 py-2 border border-gray-300 rounded text-sm placeholder-gray-500 resize-y focus:outline-none focus:ring-1 focus:ring-black"
                        placeholder="Nhập nội dung*" required rows="5"></textarea>
                    <div id="cf-turnstile" data-theme="light"></div>
                    <button
                        class="w-full bg-[#81AACC] text-white py-3 rounded transition-all duration-300 hover:bg-[#478ac0]"
                        type="submit">
                        Gửi liên hệ của bạn
                    </button>
                </form>
            </div>

            <!-- Right Column - Map -->
            <div class="bg-white p-8 rounded-[5px]">
                <h2 class="text-xl font-semibold mb-4">Vị trí của chúng tôi</h2>
                <div class="aspect-w-16 aspect-h-9">
                    <img alt="Google map showing location of EGANY Tech at 150/8 Nguyễn Duy Cung, Phường 12, Gò Vấp, Hồ Chí Minh with map details and markers"
                        class="w-full h-full object-cover rounded-lg"
                        src="https://storage.googleapis.com/a1aa/image/4abda26d-a90e-4460-3cca-f64c03d08a06.jpg" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
const form = ref({
    name: '',
    email: '',
    phone: '',
    message: ''
})

const handleSubmit = () => {
    console.log('Form submitted:', form.value)
}
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
</style>
