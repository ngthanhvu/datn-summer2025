<template>
    <Transition name="mobile-menu">
        <div v-if="isOpen" class="mobile-menu">
            <div class="flex justify-between items-center p-4 border-b border-gray-300">
                <h3 class="m-0 font-medium">Menu</h3>
                <button class="bg-transparent border-0" @click="close">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>

            <!-- Mobile Icons -->
            <div class="flex justify-around items-center p-4 border-b border-gray-300">
                <a href="#" class="text-gray-700" @click="close">
                    <i class="bi bi-search text-xl"></i>
                </a>
                <router-link to="/san-pham-yeu-thich" class="text-gray-700 relative" @click="close">
                    <i class="bi bi-heart text-xl"></i>
                    <span
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">2</span>
                </router-link>
                <router-link to="/gio-hang" class="text-gray-700 relative" @click="close">
                    <i class="bi bi-cart text-xl"></i>
                    <span
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">2</span>
                </router-link>
                <!-- User Profile Icon - Show different content based on auth status -->
                <div v-if="isLoggedIn" class="relative">
                    <router-link to="/trang-ca-nhan" class="text-gray-700" @click="close">
                        <i class="bi bi-person text-xl"></i>
                    </router-link>
                </div>
                <div v-else>
                    <router-link to="/login" class="text-gray-700" @click="close">
                        <i class="bi bi-person text-xl"></i>
                    </router-link>
                </div>
            </div>

            <!-- User Info Section (if logged in) -->
            <div v-if="isLoggedIn" class="p-4 border-b border-gray-300 bg-gray-50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                        <i class="bi bi-person text-white"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800 m-0">{{ user?.name || user?.email || 'User' }}</p>
                        <p class="text-sm text-gray-600 m-0">{{ user?.role || 'Customer' }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <ul class="list-none p-0 m-0">
                <li class="py-3 border-b border-gray-300">
                    <router-link to="/" class="text-gray-700 font-medium px-4 block" @click="close">Trang chủ
                    </router-link>
                </li>
                <li class="py-3 border-b border-gray-300">
                    <router-link to="/san-pham" class="text-gray-700 font-medium px-4 block" @click="close">
                        Sản phẩm</router-link>
                </li>
                <li class="py-3 border-b border-gray-300">
                    <router-link to="/gioi-thieu" class="text-gray-700 font-medium px-4 block" @click="close">Giới
                        thiệu</router-link>
                </li>
                <li class="py-3 border-b border-gray-300">
                    <router-link to="/tin-tuc" class="text-gray-700 font-medium px-4 block" @click="close">Tin
                        tức</router-link>
                </li>
                <li class="py-3 border-b border-gray-300">
                    <router-link to="/lien-he" class="text-gray-700 font-medium px-4 block" @click="close">
                        Liên hệ</router-link>
                </li>
                <li class="py-3 border-b border-gray-300">
                    <router-link to="/tra-cuu-don-hang" class="text-gray-700 font-medium px-4 block" @click="close">
                        Kiểm tra đơn hàng</router-link>
                </li>

                <!-- Admin Section (if user is admin) -->
                <li v-if="isAdmin" class="py-3 border-b border-gray-300">
                    <div class="px-4">
                        <p class="text-sm text-gray-500 mb-2">Quản trị</p>
                        <router-link to="/admin" class="text-blue-600 font-medium block" @click="close">
                            <i class="bi bi-gear me-2"></i>Trang quản trị
                        </router-link>
                    </div>
                </li>

                <!-- Auth Section -->
                <template v-if="!isLoggedIn">
                    <li class="py-3 border-b border-gray-300">
                        <router-link to="/login" class="text-blue-600 font-medium px-4 block" @click="close">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                        </router-link>
                    </li>
                    <li class="py-3 border-b border-gray-300">
                        <router-link to="/register" class="text-green-600 font-medium px-4 block" @click="close">
                            <i class="bi bi-person-plus me-2"></i>Đăng ký
                        </router-link>
                    </li>
                </template>

                <!-- Logout Section (if logged in) -->
                <li v-if="isLoggedIn" class="py-3 border-b border-gray-300">
                    <button @click="handleLogout" class="text-red-600 font-medium px-4 block w-full text-left">
                        <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                    </button>
                </li>
            </ul>
        </div>
    </Transition>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close'])
const authStore = useAuthStore()
const router = useRouter()

// Computed properties
const isLoggedIn = computed(() => !!authStore.user)
const user = computed(() => authStore.user)
// const isAdmin = computed(() => authStore.user?.role === 'admin')
const isAdmin = computed(() => {
    ['admin', 'master_admin'].includes(authStore.user?.role)
})

const close = () => {
    emit('close')
}

const handleLogout = () => {
    authStore.clearUser()
    close()
    router.push('/')
}
</script>

<style scoped>
.mobile-menu {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    background: white;
    z-index: 1000;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
}

/* Vue Transition Classes */
.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.mobile-menu-enter-from {
    transform: translateX(100%);
    opacity: 0;
}

.mobile-menu-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

.mobile-menu-enter-to,
.mobile-menu-leave-from {
    transform: translateX(0);
    opacity: 1;
}

@media (min-width: 992px) {
    .mobile-menu {
        display: none !important;
    }
}
</style>