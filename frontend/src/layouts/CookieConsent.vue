<template>
    <Transition name="fade-up">
        <div v-if="showBanner"
            class="fixed inset-x-0 bottom-0 flex flex-col justify-between gap-4 bg-white py-3 ring-1 ring-gray-900/10 md:flex-row md:items-center lg:px-8 z-[10]">
            <p class="max-w-4xl text-sm leading-6 text-gray-900 px-3">
                Trang web này sử dụng cookie để cải thiện trải nghiệm, phân tích lưu lượng truy cập,
                và mang lại dịch vụ tốt hơn. Bằng việc tiếp tục sử dụng, bạn đồng ý với việc chúng tôi dùng cookie.
                Xem chi tiết trong
                <router-link to="/chinh-sach-cookie" class="font-semibold text-[#81aacc] hover:underline">
                    chính sách cookie
                </router-link>.
            </p>

            <div class="flex flex-none items-center gap-x-5 px-3">
                <button type="button" @click="acceptCookies"
                    class="rounded-md bg-[#81aacc] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#81aacc]/80 cursor-pointer">
                    Đồng ý
                </button>
                <button type="button" @click="rejectCookies"
                    class="text-sm font-semibold leading-6 text-gray-900 hover:text-red-600 cursor-pointer">
                    Từ chối
                </button>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const showBanner = ref(false)

onMounted(() => {
    const consent = localStorage.getItem('cookie_consent')
    if (!consent) {
        showBanner.value = true
    }
})

function acceptCookies() {
    localStorage.setItem('cookie_consent', 'accepted')
    showBanner.value = false
}

function rejectCookies() {
    localStorage.setItem('cookie_consent', 'rejected')
    showBanner.value = false
}
</script>

<style scoped>
.fade-up-enter-active,
.fade-up-leave-active {
    transition: all 0.3s ease;
}

.fade-up-enter-from,
.fade-up-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>
