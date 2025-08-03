<template>
    <div class="max-w-7xl mx-auto px-4 py-8 container mt-5">
        <div class="grid grid-cols-1 lg:grid-cols-2">
            <!-- Left Column - Contact Form -->
            <div class="bg-white p-8 rounded-[5px]">
                <h1 class="text-2xl font-normal mb-6">
                    {{ settings.storeName || "Tên cửa hàng" }}
                </h1>
                <ul class="text-sm text-gray-900 space-y-2 mb-6">
                    <li class="flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-[14px]"></i>
                        <span>
                            Địa chỉ: {{ settings.address || "chưa cập nhật" }}
                        </span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-mobile-alt text-[14px]"></i>
                        <span>
                            Số điện thoại: {{ settings.phone || "chưa cập nhật" }}
                        </span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-envelope text-[14px]"></i>
                        <span>
                            Email: {{ settings.email || "chưa cập nhật" }}
                        </span>
                    </li>
                </ul>
                <hr class="border-gray-300 mb-6" />
                <form @submit.prevent="handleSubmit">
                    <h2 class="text-xs font-semibold mb-3 uppercase tracking-wide">
                        LIÊN HỆ VỚI CHÚNG TÔI
                    </h2>
                    <input v-model="form.name"
                        class="w-full mb-4 px-3 py-2 border border-gray-300 rounded text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#81aacc]"
                        placeholder="Họ tên*" required type="text" />
                    <input v-model="form.email"
                        class="w-full mb-4 px-3 py-2 border border-gray-300 rounded text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#81aacc]"
                        placeholder="Email*" required type="email" />
                    <input v-model="form.phone"
                        class="w-full mb-4 px-3 py-2 border border-gray-300 rounded text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#81aacc]"
                        placeholder="Số điện thoại*" required type="tel" />
                    <textarea v-model="form.message"
                        class="w-full mb-1 px-3 py-2 border border-gray-300 rounded text-sm placeholder-gray-500 resize-y focus:outline-none focus:ring-1 focus:ring-[#81aacc]"
                        placeholder="Nhập nội dung*" required rows="5"></textarea>
                    <div id="cf-turnstile" data-theme="light"></div>
                    <button :disabled="loading"
                        class="w-full bg-[#81AACC] text-white py-3 rounded transition-all duration-300 hover:bg-[#478ac0] disabled:opacity-50 disabled:cursor-not-allowed"
                        type="submit">
                        <span v-if="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Đang gửi...
                        </span>
                        <span v-else>Gửi liên hệ của bạn</span>
                    </button>
                </form>
            </div>

            <!-- Right Column - Map -->
            <div class="bg-white p-8 rounded-[5px]">
                <h2 class="text-xl font-semibold mb-4">Vị trí của chúng tôi</h2>
                <div class="aspect-w-16 aspect-h-9">
                    <iframe width="100%" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                        src="https://www.openstreetmap.org/export/embed.html?bbox=108.064902%2C12.700145%2C108.084902%2C12.720145&layer=mapnik&marker=12.710145%2C108.074902"
                        style="border: 1px solid black">
                    </iframe>
                    <small><a class="underline text-[#81aacc] hover:no-underline" target="_blank"
                            href="https://www.openstreetmap.org/?mlat=12.710145&mlon=108.074902#map=15/12.710145/108.074902">Xem
                            bản đồ lớn</a></small>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useContact } from '../../composable/useContact'
import { useSettings } from '../../composable/useSettingsApi'
import { push } from 'notivue'

const { sendContact } = useContact()
const { settings, fetchSettings } = useSettings()

const form = ref({
    name: '',
    email: '',
    phone: '',
    message: ''
})

const loading = ref(false)

const handleSubmit = async () => {
    loading.value = true
    try {
        await sendContact(form.value)
        push.success('Liên hệ của bạn được gửi thông. Chúng tôi sẽ phản hồi sớm nhất có thể!')
        form.value = { name: '', email: '', phone: '', message: '' }
    } catch (error) {
        console.error(error)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchSettings()
})
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
</style>