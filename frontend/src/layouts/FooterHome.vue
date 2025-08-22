<template>
    <footer class="bg-[#81AACC] text-white pt-10 pb-4">
        <div class="container mx-auto px-4 py-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Cột 1: Thông tin liên hệ -->
                <div>
                    <div class="mb-4 font-bold text-2xl flex items-center gap-2">
                        <img :src="settings.logo || 'https://placehold.co/150x50?text=LOGO'" alt="Logo" class="w-32" />
                    </div>
                    <div class="flex items-start gap-2 mb-2">
                        <span class="text-2xl font-semibold">{{ settings.storeName || "Tên cửa hàng" }}</span>
                    </div>

                    <div class="flex items-start gap-2 mb-2">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>Địa chỉ: {{ settings.address || "chưa cập nhật" }}</span>
                    </div>
                    <div class="flex items-start gap-2 mb-2">
                        <i class="bi bi-telephone-fill"></i>
                        <span>Số điện thoại: {{ settings.phone || "chưa cập nhật" }}</span>
                    </div>
                    <div class="flex items-start gap-2 mb-2">
                        <i class="bi bi-envelope-fill"></i>
                        <span>Email: {{ settings.email || "chưa cập nhật" }}</span>
                    </div>
                    <div class="mt-4 text-xs text-gray-200">
                        © Bản quyền thuộc về <span class="font-bold text-white">DEVGANG</span> | Cung cấp bởi
                        <span class="text-white">DEVGANG</span>
                    </div>
                </div>

                <!-- Cột 2 & 3: Chính sách và Hỗ trợ khách hàng - DYNAMIC -->
                <div class="md:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Chính sách -->
                        <div>
                            <h3 class="text-lg font-semibold text-white-300 mb-4">CHÍNH SÁCH</h3>
                            <ul class="space-y-2 text-gray-100">
                                <li v-for="page in policyPages" :key="page.id">
                                    <a :href="`/trang/${page.slug}`" class="hover:text-[#1E293B] transition-colors">
                                        {{ page.title }}
                                    </a>
                                </li>
                                <li v-if="policyPages.length === 0" class="text-gray-200 text-sm">Đang tải...</li>
                            </ul>
                        </div>

                        <!-- Hỗ trợ khách hàng -->
                        <div>
                            <h3 class="text-lg font-semibold text-white-300 mb-4">HỖ TRỢ KHÁCH HÀNG</h3>
                            <ul class="space-y-2 text-gray-100">
                                <li v-for="page in supportPages" :key="page.id">
                                    <a :href="`/trang/${page.slug}`" class="hover:text-[#1E293B] transition-colors">
                                        {{ page.title }}
                                    </a>
                                </li>
                                <li v-if="supportPages.length === 0" class="text-gray-200 text-sm">Đang tải...</li>
                            </ul>
                            <div class="mt-50 flex gap-2">
                                <img src="https://theme.hstatic.net/200000696635/1001373943/14/footer_trustbadge.png?v=6"
                                    alt="">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột 4: Đăng ký nhận tin -->
                <div>
                    <div class="font-bold mb-4">ĐĂNG KÝ NHẬN TIN</div>
                    <form class="relative mb-4">
                        <input type="email" placeholder="Nhập địa chỉ email"
                            class="rounded-full bg-white px-3 py-3 w-full border border-[#5A82AB] text-gray-700 focus:outline-none pr-16" />
                        <button type="submit"
                            class="absolute top-1/2 right-[5px] -translate-y-1/2 bg-[#81aacc] text-white px-4 py-2 rounded-full hover:bg-[#5A82AB] flex items-center gap-1 cursor-pointer">
                            Đăng ký
                        </button>
                    </form>
                    <div class="flex gap-4 mb-4">
                        <a href="#" class="text-white text-2xl"><i class="fa-brands fa-facebook"></i></a>
                        <a href="#" class="text-white text-2xl"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="text-white text-2xl"><i class="fa-brands fa-telegram"></i></a>
                        <a href="#" class="text-white text-2xl"><i class="fa-brands fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useSettings } from '../composable/useSettingsApi';
import { usePages } from '../composable/usePages';

const { settings, fetchSettings } = useSettings()
const { getPagesByType } = usePages()

const policyPages = ref([])
const supportPages = ref([])

const loadFooterPages = async () => {
    try {
        const policyRes = await getPagesByType('policy')
        if (policyRes && policyRes.data) {
            policyPages.value = policyRes.data.filter(page => page.status)
        }
        const supportRes = await getPagesByType('support')
        if (supportRes && supportRes.data) {
            supportPages.value = supportRes.data.filter(page => page.status)
        }
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error('FooterHome - Error loading pages:', e)
    }
}

onMounted(() => {
    fetchSettings()
    loadFooterPages()
})
</script>